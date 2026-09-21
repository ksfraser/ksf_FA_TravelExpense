<?php
/**
 * EventCloseExpenseService — BR-007 TravelExpense subscriber logic.
 *
 * Lets any attendee of a closed calendar event attach the event's spend to a
 * brand-new expense request (NEW) or append it to their already-OPEN request
 * (EXISTING), per FR-EXPENSE-007-001. Unlike the timesheets flow, membership
 * is NOT consulted: spend is offered to member AND external attendees alike.
 *
 * Idempotency (req 4):
 *   - NEW: the 0_travel_requests header carries the unique guard
 *          (event_id, event_attendee_email) — retriggers return the existing
 *          request instead of a duplicate.
 *   - EXISTING: an attached line already bearing (event_id, attendee_email)
 *          on the chosen request blocks re-append.
 *
 * Writes only TravelExpense's own tables; emitters fire AFTER commit
 * (travel_request_created / travel_expense_added).
 *
 * PHP 7.3+ compatible.
 *
 * @since 2.4.3
 * @BABOK Related: BR-007, FR-EXPENSE-007-001
 */

declare(strict_types=1);

namespace ksfraser\FrontAccounting\TravelExpense\Service;

/**
 * @package ksfraser\FrontAccounting\TravelExpense\Service
 */
class EventCloseExpenseService
{
    /** @var object */
    private $db;
    /** @var string */
    private $prefix;
    /** @var int */
    private $actingUserId;
    /** @var callable */
    private $hookInvoker;

    /**
     * @param object        $db           Connection: fetchAssoc/fetchAll/fetchScalar/
     *                                    executeUpdate/lastInsertId/beginTransaction/
     *                                    commit/rollBack (FA db_* or PDO adapter).
     * @param string        $prefix       Table prefix (e.g. "0_").
     * @param int           $actingUserId FA user/employee acting.
     * @param callable|null $hookInvoker  fn(string $method, array &$data, array $opts)
     */
    public function __construct(
        $db,
        string $prefix = '0_',
        int $actingUserId = 0,
        ?callable $hookInvoker = null
    ) {
        $this->db = $db;
        $this->prefix = $prefix;
        $this->actingUserId = $actingUserId;
        $this->hookInvoker = $hookInvoker ?: array(self::class, 'dispatchHook');
    }

    /**
     * Default dispatcher: FA's procedural hook system when present.
     *
     * @param string $method
     * @param array  $data  by-reference
     * @param array  $opts
     */
    public static function dispatchHook(string $method, array &$data, array $opts = array()): void
    {
        if (function_exists('hook_invoke_all')) {
            hook_invoke_all($method, $data, $opts);
        }
    }

    /**
     * ARI: NEW header created once from the DTO, lines appended, retrigger
     * returns the SAME request (created = 0).
     *
     * @param object|array $dto           EventClosedDto or its array form
     * @param string       $attendeeEmail the acting attendee (session user)
     * @param array[]      $spendLines    user-entered spend [
     *                                    ['expense_type','amount','description',...] ]
     * @return array{request_id: int, created: int, lines_created: int}
     * @throws \LogicException on open event or missing attendee
     */
    public function attachExpenseNew($dto, string $attendeeEmail, array $spendLines = array()): array
    {
        $payload = $this->normalize($dto);
        $eventId = $this->stringValue($payload, 'event_id');

        if ($eventId === '') {
            throw new \LogicException('EventCloseExpenseService: no event_id in payload');
        }
        if (!$this->isClosed($payload)) {
            throw new \LogicException('EventCloseExpenseService: event not closed');
        }
        if (trim($attendeeEmail) === '') {
            throw new \LogicException('EventCloseExpenseService: no attendee email');
        }

        $existing = $this->findRequestByEventAttendee($eventId, $attendeeEmail);
        if ($existing) {
            return array(
                'request_id'    => (int) $existing['id'],
                'created'       => 0,
                'lines_created' => $this->appendLinesLocked((int) $existing['id'], $eventId, $attendeeEmail, $spendLines),
            );
        }

        $header = array(
            'employee_id'          => $this->actingUserId,
            'project_id'           => $this->stringValue($payload, 'project_id') === '' ? null : (string) $payload['project_id'],
            'task_id'              => $this->stringValue($payload, 'task_id') === '' ? null : (string) $payload['task_id'],
            'purpose'              => $this->stringValue($payload, 'title') !== ''
                                         ? (string) $payload['title'] : 'Expenses from closed event #' . $eventId,
            'destination'          => $this->stringValue($payload, 'location') === '' ? null : (string) $payload['location'],
            'start_date'           => $this->dateValue($payload, 'started_at'),
            'end_date'             => $this->dateValue($payload, 'closed_at'),
            'created_by'           => $this->actingUserId,
            'event_id'             => $eventId,
            'event_attendee_email' => strtolower(trim($attendeeEmail)),
        );

        $this->db->beginTransaction();
        try {
            $epochInsert = $this->insertRequest($header);
            $lines = $this->appendLinesLockedNow($epochInsert, $eventId, $attendeeEmail, $spendLines);
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }

        return array(
            'request_id'    => $epochInsert,
            'created'       => 1,
            'lines_created' => $lines,
        );
    }

    /**
     * AZZ: append event spend lines to the attendee's OPEN request.
     *
     * Existing lines are untouched; only (event, attendee)-guarded lines are
     * added. Membership is never consulted.
     *
     * @param object|array $dto                EventClosedDto / array
     * @param int          $existingRequestId  the user's open request
     * @param string       $attendeeEmail      the acting attendee
     * @param array[]      $spendLines         expense line payloads
     * @return array{request_id: int, lines_created: int}
     * @throws \LogicException on open event, missing request, not pending,
     *                         or request of another employee
     */
    public function attachExpenseExisting($dto, int $existingRequestId, string $attendeeEmail, array $spendLines = array()): array
    {
        $payload = $this->normalize($dto);
        $eventId = $this->stringValue($payload, 'event_id');

        if ($eventId === '') {
            throw new \LogicException('EventCloseExpenseService: no event_id in payload');
        }
        if (!$this->isClosed($payload)) {
            throw new \LogicException('EventCloseExpenseService: event not closed');
        }
        if (trim($attendeeEmail) === '') {
            throw new \LogicException('EventCloseExpenseService: no attendee email');
        }

        $request = $this->findRequestById($existingRequestId);
        if (!$request) {
            throw new \LogicException('EventCloseExpenseService: request not found');
        }
        if ($request['status'] !== 'Pending') {
            throw new \LogicException('EventCloseExpenseService: request is not open (Pending)');
        }
        if ((int) $request['employee_id'] !== $this->actingUserId) {
            throw new \LogicException('EventCloseExpenseService: request belongs to another employee');
        }

        $this->db->beginTransaction();
        try {
            $lines = $this->appendLinesLockedNow($existingRequestId, $eventId, $attendeeEmail, $spendLines);
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }

        return array('request_id' => $existingRequestId, 'lines_created' => $lines);
    }

    /**
     * The attendee's open (Pending) request they are currently filing, if any.
     *
     * @param int $employeeId
     * @return array|null newest Pending request
     */
    public function openRequest(int $employeeId): ?array
    {
        $sql = "SELECT * FROM {$this->prefix}travel_requests
                WHERE employee_id = ? AND status = 'Pending'
                ORDER BY created_at DESC, id DESC LIMIT 1";
        return $this->db->fetchAssoc($sql, array($employeeId));
    }

    // ---------------------------------------------------------------
    // Internals
    // ---------------------------------------------------------------

    /**
     * Guarded append used by the retrigger path in attachExpenseNew: create a
     * header lock first so the (event, attendee) unique key serialises us.
     *
     * @return int lines created
     */
    private function appendLinesLocked(int $requestId, string $eventId, string $attendeeEmail, array $spendLines): int
    {
        $this->db->beginTransaction();
        try {
            $lines = $this->appendLinesLockedNow($requestId, $eventId, $attendeeEmail, $spendLines);
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
        return $lines;
    }

    /**
     * Append user-entered spend lines, skipping when the (event, attendee)
     * guard already exists on this request.
     *
     * @return int lines actually created
     */
    private function appendLinesLockedNow(int $requestId, string $eventId, string $attendeeEmail, array $spendLines): int
    {
        if (empty($spendLines)) {
            return 0;
        }
        if ($this->hasAttachedLine($requestId, $eventId, $attendeeEmail)) {
            return 0;
        }

        $created = 0;
        foreach ($spendLines as $line) {
            $expenseType = $this->stringValue($line, 'expense_type');
            if ($expenseType === '') {
                $expenseType = 'Other';
            }
            $amount = (float) ($line['amount'] ?? 0);
            $sql = "INSERT INTO {$this->prefix}travel_expenses
                    (travel_id, expense_type, amount, gl_code, project_id, task_id,
                     activity_code, vendor, description, date, billable, status,
                     event_id, event_attendee_email)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', ?, ?)";
            $this->db->executeUpdate($sql, array(
                $requestId, $expenseType, $amount,
                $this->nullIfEmpty($line['gl_code'] ?? null),
                $this->nullIfEmpty($line['project_id'] ?? null),
                $this->nullIfEmpty($line['task_id'] ?? null),
                $this->nullIfEmpty($line['activity_code'] ?? null),
                $this->nullIfEmpty($line['vendor'] ?? null),
                $this->stringValue($line, 'description'),
                $this->dateValue($line, 'date', date('Y-m-d')),
                isset($line['billable']) ? (int) $line['billable'] : 1,
                $eventId, strtolower(trim($attendeeEmail)),
            ));
            $expenseId = (int) $this->db->lastInsertId();
            $created++;

            $emit = array(
                'request_id'    => $requestId,
                'expense_id'    => $expenseId,
                'expense_type'  => $expenseType,
                'amount'        => $amount,
                'event_id'      => $eventId,
                'attendee_email'=> strtolower(trim($attendeeEmail)),
            );
            $invoker = $this->hookInvoker;
            $invoker('travel_expense_added', $emit, array());
        }

        return $created;
    }

    /**
     * Create the NEW-mode request header (unique guard enforced by DB).
     *
     * @param array $header
     * @return int request id
     */
    private function insertRequest(array $header): int
    {
        $sql = "INSERT INTO {$this->prefix}travel_requests
                (employee_id, project_id, task_id, purpose, destination, start_date,
                 end_date, created_by, event_id, event_attendee_email)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $this->db->executeUpdate($sql, array(
            $header['employee_id'], $header['project_id'], $header['task_id'],
            $header['purpose'], $header['destination'], $header['start_date'],
            $header['end_date'], $header['created_by'],
            $header['event_id'], $header['event_attendee_email'],
        ));

        $requestId = (int) $this->db->lastInsertId();

        $emit = array(
            'request_id' => $requestId,
            'employee_id'=> $header['employee_id'],
            'purpose'    => $header['purpose'],
            'event_id'   => $header['event_id'],
        );
        $invoker = $this->hookInvoker;
        $invoker('travel_request_created', $emit, array());

        return $requestId;
    }

    /**
     * @param string $eventId
     * @param string $attendeeEmail
     * @return array|null request already guarding (event, attendee)
     */
    private function findRequestByEventAttendee(string $eventId, string $attendeeEmail): ?array
    {
        $sql = "SELECT * FROM {$this->prefix}travel_requests
                WHERE event_id = ? AND event_attendee_email = ? LIMIT 1";
        return $this->db->fetchAssoc($sql, array($eventId, strtolower(trim($attendeeEmail))));
    }

    /**
     * @param int $id
     * @return array|null
     */
    private function findRequestById(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->prefix}travel_requests WHERE id = ? LIMIT 1";
        return $this->db->fetchAssoc($sql, array($id));
    }

    /**
     * @param int    $requestId
     * @param string $eventId
     * @param string $attendeeEmail
     * @return bool a guarded line already attached to this request
     */
    private function hasAttachedLine(int $requestId, string $eventId, string $attendeeEmail): bool
    {
        $sql = "SELECT id FROM {$this->prefix}travel_expenses
                WHERE travel_id = ? AND event_id = ? AND event_attendee_email = ?
                LIMIT 1";
        $row = $this->db->fetchAssoc($sql, array($requestId, $eventId, strtolower(trim($attendeeEmail))));
        return (bool) $row;
    }

    // ---------------------------------------------------------------
    // Value helpers
    // ---------------------------------------------------------------

    /**
     * Normalise DTO (object or array) to a plain array.
     *
     * @param object|array $dto
     * @return array
     */
    private function normalize($dto): array
    {
        if (is_array($dto)) {
            return $dto;
        }
        if (is_object($dto) && method_exists($dto, 'toArray')) {
            $arr = $dto->toArray();
            return is_array($arr) ? $arr : array();
        }
        return array();
    }

    /**
     * @param array  $payload
     * @param string $key
     * @return string trimmed scalar or ''
     */
    private function stringValue(array $payload, string $key): string
    {
        $value = $payload[$key] ?? $payload[strtolower($key)] ?? null;
        if ($value === null) {
            return '';
        }
        return trim((string) $value);
    }

    /**
     * @param array       $payload
     * @param string      $key
     * @param string|null $default
     * @return string YYYY-MM-DD
     */
    private function dateValue(array $payload, string $key, string $default = null): string
    {
        $raw = $this->stringValue($payload, $key);
        if ($raw === '') {
            return $default ?: date('Y-m-d');
        }
        $ts = strtotime($raw);
        return $ts === false ? ($default ?: date('Y-m-d')) : date('Y-m-d', $ts);
    }

    /**
     * @param array $payload
     * @return bool
     */
    private function isClosed(array $payload): bool
    {
        return $this->stringValue($payload, 'closed_at') !== '';
    }

    /**
     * @param mixed $value
     * @return mixed null when empty
     */
    private function nullIfEmpty($value)
    {
        if ($value === null || (is_string($value) && trim($value) === '')) {
            return null;
        }
        return $value;
    }
}