<?php
/**
 * EventCloseExpenseServiceTest — BR-007 expense attachment tests.
 *
 * Covers FR-EXPENSE-007-001:
 *   - ARI  NEW header created once; retrigger returns the same request, no dup
 *   - AZZ  EXISTING append keeps old lines, adds event lines, total = old + new
 *   - BON  membership is NEVER consulted (external attendee works fine)
 *   - CAN  open event refused; no attendees -> nothing to do
 * plus the (event_id, attendee_email) idempotency guards.
 *
 * The service talks to FakeDb (in-memory connection) — no FA required.
 *
 * @since 2.4.3
 * @BABOK Related: BR-007, FR-EXPENSE-007-001
 */

declare(strict_types=1);

namespace Ksfraser\Tests\Unit\FA\TravelExpense;

use ksfraser\FrontAccounting\TravelExpense\Service\EventCloseExpenseService;
use PHPUnit\Framework\TestCase;

class FakeDb
{
    public $requests = array();
    public $expenses = array();
    public $txLog = array();
    private $lastId = 0;

    private function nextId(): int
    {
        return ++$this->lastId;
    }

    public function fetchAssoc(string $sql, array $params = array()): ?array
    {
        if (strpos($sql, '0_travel_requests') !== false) {
            if (strpos($sql, 'event_id = ? AND event_attendee_email = ?') !== false) {
                foreach ($this->requests as $r) {
                    if ((string) $r['event_id'] === (string) $params[0]
                        && strtolower((string) $r['event_attendee_email']) === strtolower((string) $params[1])) {
                        return $r;
                    }
                }
                return null;
            }
            if (strpos($sql, 'WHERE id = ?') !== false) {
                foreach ($this->requests as $r) {
                    if ((int) $r['id'] === (int) $params[0]) {
                        return $r;
                    }
                }
                return null;
            }
            if (strpos($sql, "status = 'Pending'") !== false) {
                $pending = array_filter($this->requests, function ($r) use ($params) {
                    return $r['status'] === 'Pending' && (int) $r['employee_id'] === (int) $params[0];
                });
                if (empty($pending)) {
                    return null;
                }
                usort($pending, function ($a, $b) {
                    return strcmp((string) $b['created_at'], (string) $a['created_at']);
                });
                return $pending[0];
            }
            return null;
        }
        if (strpos($sql, '0_travel_expenses') !== false) {
            foreach ($this->expenses as $e) {
                if ((int) $e['travel_id'] === (int) $params[0]
                    && (string) $e['event_id'] === (string) $params[1]
                    && strtolower((string) $e['event_attendee_email']) === strtolower((string) $params[2])) {
                    return $e;
                }
            }
            return null;
        }
        return null;
    }

    public function fetchAll(string $sql, array $params = array()): array
    {
        return array();
    }

    public function fetchScalar(string $sql, array $params = array())
    {
        return null;
    }

    public function executeUpdate(string $sql, array $params = array()): bool
    {
        if (strpos($sql, '0_travel_requests') !== false) {
            $this->requests[] = array(
                'id' => $this->nextId(),
                'employee_id' => (int) $params[0],
                'project_id' => $params[1],
                'task_id' => $params[2],
                'purpose' => $params[3],
                'destination' => $params[4],
                'start_date' => $params[5],
                'end_date' => $params[6],
                'status' => 'Pending',
                'created_by' => $params[7],
                'event_id' => $params[8],
                'event_attendee_email' => $params[9],
                'created_at' => '2026-09-21 10:00:00',
            );
            return true;
        }
        if (strpos($sql, '0_travel_expenses') !== false) {
            $this->expenses[] = array(
                'id' => $this->nextId(),
                'travel_id' => (int) $params[0],
                'expense_type' => (string) $params[1],
                'amount' => (float) $params[2],
                'gl_code' => $params[3],
                'project_id' => $params[4],
                'task_id' => $params[5],
                'activity_code' => $params[6],
                'vendor' => $params[7],
                'description' => $params[8],
                'date' => $params[9],
                'billable' => (int) $params[10],
                'status' => 'Pending',
                'event_id' => $params[11],
                'event_attendee_email' => $params[12],
            );
            return true;
        }
        return false;
    }

    public function lastInsertId(): int
    {
        return $this->lastId;
    }

    public function beginTransaction(): void
    {
        $this->txLog[] = 'begin';
    }

    public function commit(): void
    {
        $this->txLog[] = 'commit';
    }

    public function rollBack(): void
    {
        $this->txLog[] = 'rollback';
    }
}

class EventCloseExpenseServiceTest extends TestCase
{
    /** @var FakeDb */
    private $db;

    protected function setUp(): void
    {
        $this->db = new FakeDb();
    }

    private function closedPayload(array $attendees = array('alice@x.io'), int $eventId = 5): array
    {
        return array(
            'event_id'        => (string) $eventId,
            'title'           => 'Client Kickoff',
            'location'        => 'Downtown Office',
            'project_id'      => 'PRJ-001',
            'task_id'         => 'TSK-009',
            'started_at'      => '2026-09-21 09:00:00',
            'closed_at'       => '2026-09-21 17:00:00',
            'closed_by'       => 'admin',
            'attendee_emails' => $attendees,
        );
    }

    private function spendLines(): array
    {
        return array(
            array('expense_type' => 'Parking', 'amount' => 12.50, 'description' => 'event parking'),
            array('expense_type' => 'Meals_Lunch', 'amount' => 25.00, 'description' => 'working lunch'),
        );
    }

    private function service(int $actingUserId = 1, ?callable $invoker = null): EventCloseExpenseService
    {
        $noop = function (string $m, array &$d, array $o): void {
            $this->emits[$m][] = $d;
        };
        return new EventCloseExpenseService($this->db, '0_', $actingUserId, $invoker ?: $noop);
    }

    private $emits = array();

    // ---------------------------------------------------------------
    // ARI — NEW
    // ---------------------------------------------------------------

    public function testNewCreatesHeaderAndLinesOnce(): void
    {
        $result = $this->service()->attachExpenseNew($this->closedPayload(), 'alice@x.io', $this->spendLines());

        $this->assertSame(1, $result['created']);
        $this->assertSame(2, $result['lines_created']);
        $this->assertCount(1, $this->db->requests);
        $this->assertCount(2, $this->db->expenses);

        $header = $this->db->requests[0];
        $this->assertSame('Client Kickoff', $header['purpose']);
        $this->assertSame('Downtown Office', $header['destination']);
        $this->assertSame('2026-09-21', $header['start_date']);
        $this->assertSame('2026-09-21', $header['end_date']);
        $this->assertSame('5', (string) $header['event_id']);
        $this->assertSame('alice@x.io', $header['event_attendee_email']);
    }

    public function testNewRetriggerCreatesNoDuplicateHeader(): void
    {
        $svc = $this->service();

        $first = $svc->attachExpenseNew($this->closedPayload(), 'alice@x.io', $this->spendLines());
        $second = $svc->attachExpenseNew($this->closedPayload(), 'alice@x.io', array(
            array('expense_type' => 'Other', 'amount' => 5.00),
        ));

        $this->assertSame(1, $first['created']);
        $this->assertSame(0, $second['created']);
        $this->assertSame($first['request_id'], $second['request_id']);
        $this->assertCount(1, $this->db->requests);
        $this->assertCount(2, $this->db->expenses); // second line batch blocked by guard
    }

    // ---------------------------------------------------------------
    // AZZ — EXISTING
    // ---------------------------------------------------------------

    public function testExistingAppendsKeepingOldLines(): void
    {
        $svc = $this->service();
        $new = $svc->attachExpenseNew($this->closedPayload(array('bob@x.io'), 3), 'bob@x.io');
        $existingId = $new['request_id'];

        // Simulate an expense already present on the report before the event.
        $this->db->expenses[] = array(
            'id' => 99, 'travel_id' => $existingId, 'expense_type' => 'Hotel',
            'amount' => 150.00, 'description' => 'pre-existing hotel', 'date' => '2026-09-20',
            'billable' => 1, 'status' => 'Pending',
            'event_id' => null, 'event_attendee_email' => null,
        );

        $result = $svc->attachExpenseExisting($this->closedPayload(), $existingId, 'alice@x.io', $this->spendLines());

        $this->assertSame(2, $result['lines_created']);
        $this->assertSame($existingId, $result['request_id']);

        $eventLines = array_filter($this->db->expenses, function ($e) {
            return $e['event_id'] === '5';
        });
        $this->assertCount(2, $eventLines);
        // Old line retained:
        $this->assertSame(150.00, $this->db->expenses[0]['amount']);
    }

    public function testExistingRefusesNonPendingRequest(): void
    {
        $svc = $this->service();
        $new = $svc->attachExpenseNew($this->closedPayload(array('bob@x.io'), 4), 'bob@x.io');
        $this->db->requests[0]['status'] = 'Approved';

        $this->expectException(\LogicException::class);
        $svc->attachExpenseExisting($this->closedPayload(), $new['request_id'], 'bob@x.io', $this->spendLines());
    }

    public function testExistingRefusesRequestOfAnotherEmployee(): void
    {
        $svc = $this->service(10); // acting as employee 10
        // Seed a Pending request owned by employee 20:
        $this->db->requests[] = array(
            'id' => 500, 'employee_id' => 20, 'project_id' => 'PRJ-1', 'task_id' => null,
            'purpose' => 'peer trip', 'destination' => null,
            'start_date' => '2026-09-01', 'end_date' => '2026-09-03',
            'status' => 'Pending', 'created_by' => 20,
            'event_id' => null, 'event_attendee_email' => null,
            'created_at' => '2026-09-01 08:00:00',
        );

        $this->expectException(\LogicException::class);
        $svc->attachExpenseExisting($this->closedPayload(), 500, 'peer@x.io', $this->spendLines());
    }

    // ---------------------------------------------------------------
    // BON — membership ignored
    // ---------------------------------------------------------------

    public function testExternalAttendeeIsStillOfferedExpense(): void
    {
        $result = $this->service()->attachExpenseNew($this->closedPayload(), 'ext1@x.io', array(
            array('expense_type' => 'Other', 'amount' => 9.99),
        ));

        $this->assertSame(1, $result['created']);
        $this->assertSame('ext1@x.io', $this->db->requests[0]['event_attendee_email']);
        $this->assertSame(1, $result['lines_created']);
    }

    // ---------------------------------------------------------------
    // CAN — open events / guards
    // ---------------------------------------------------------------

    public function testOpenEventRefused(): void
    {
        $open = $this->closedPayload();
        unset($open['closed_at']);

        $this->expectException(\LogicException::class);
        $this->service()->attachExpenseNew($open, 'alice@x.io');
    }

    public function testWritesAreTransactional(): void
    {
        $svc = $this->service();
        $svc->attachExpenseNew($this->closedPayload(), 'alice@x.io', $this->spendLines());

        $this->assertSame(array('begin', 'commit'), $this->db->txLog);
    }

    public function testOpenRequestLookup(): void
    {
        $svc = $this->service();
        $new = $svc->attachExpenseNew($this->closedPayload(), 'alice@x.io');

        $open = $svc->openRequest(1);

        $this->assertIsArray($open);
        $this->assertSame($new['request_id'], (int) $open['id']);
        $this->assertNull($svc->openRequest(999));
    }

    public function testEmittersFireAfterCommit(): void
    {
        $svc = $this->service(1);
        $svc->attachExpenseNew($this->closedPayload(), 'alice@x.io', $this->spendLines());

        $this->assertArrayHasKey('travel_request_created', $this->emits);
        $this->assertArrayHasKey('travel_expense_added', $this->emits);
        $this->assertCount(2, $this->emits['travel_expense_added']);
    }
}