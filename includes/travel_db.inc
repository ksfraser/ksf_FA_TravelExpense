<?php
/**
 * Travel Expense Database Functions
 */

function create_travel_request(int $employee_id, string $purpose, string $start_date, string $end_date, float $estimated_cost = null, string $project_id = null, string $task_id = null, float $per_diem_rate = null): int
{
    global $db;
    $sql = "INSERT INTO " . TB_PREF . "travel_requests (employee_id, purpose, start_date, end_date, estimated_cost, project_id, task_id, per_diem_rate, created_by)
        VALUES (" . $employee_id . ", " . $db->escape($purpose) . ", " . $db->escape($start_date) . ", " . $db->escape($end_date) . ", " . ($estimated_cost ?: 'NULL') . ", " . ($project_id ? $db->escape($project_id) : 'NULL') . ", " . ($task_id ? $db->escape($task_id) : 'NULL') . ", " . ($per_diem_rate ?: 'NULL') . ", " . user_id() . ")";
    $db->query($sql);
    return $db->insert_id;
}

function get_travel_requests(array $filters = []): ?object
{
    global $db;
    $sql = "SELECT t.*, e.name as employee_name, p.name as project_name 
        FROM " . TB_PREF . "travel_requests t
        LEFT JOIN " . TB_PREF . "hr_employees e ON t.employee_id = e.id
        LEFT JOIN " . TB_PREF . "projects p ON t.project_id = p.project_id WHERE 1=1";
    
    if (!empty($filters['employee_id'])) $sql .= " AND t.employee_id = " . $filters['employee_id'];
    if (!empty($filters['project_id'])) $sql .= " AND t.project_id = " . $db->escape($filters['project_id']);
    if (!empty($filters['task_id'])) $sql .= " AND t.task_id = " . $db->escape($filters['task_id']);
    if (!empty($filters['status'])) $sql .= " AND t.status = " . $db->escape($filters['status']);
    
    $sql .= " ORDER BY t.start_date DESC";
    $result = $db->query($sql);
    return $result ?: null;
}

function get_travel_request(int $id): ?array
{
    global $db;
    $sql = "SELECT t.*, e.name as employee_name, p.name as project_name 
        FROM " . TB_PREF . "travel_requests t
        LEFT JOIN " . TB_PREF . "hr_employees e ON t.employee_id = e.id
        LEFT JOIN " . TB_PREF . "projects p ON t.project_id = p.project_id
        WHERE t.id = " . $id;
    $result = $db->query($sql);
    return $result && $db->num_rows($result) > 0 ? $db->fetch($result) : null;
}

function update_travel_request(int $id, array $data): bool
{
    global $db;
    $sets = [];
    foreach ($data as $field => $value) {
        if ($value === null) {
            $sets[] = $field . " = NULL";
        } else {
            $sets[] = $field . " = " . (is_numeric($value) ? $value : $db->escape($value));
        }
    }
    if (empty($sets)) return false;
    
    $sql = "UPDATE " . TB_PREF . "travel_requests SET " . implode(', ', $sets) . " WHERE id = " . $id;
    $result = $db->query($sql);
    return $result !== false;
}

function approve_travel_request(int $id, int $approver_id, string $status = 'Approved'): bool
{
    global $db;
    $status_field = $status === 'Rejected' ? 'rejected_by' : 'approved_by';
    $date_field = $status === 'Rejected' ? 'rejected_at' : 'approved_at';
    return $db->query("UPDATE " . TB_PREF . "travel_requests SET status = " . $db->escape($status) . ", " . $status_field . " = " . $approver_id . ", " . $date_field . " = NOW() WHERE id = " . $id) !== false;
}

function reject_travel_request(int $id, int $rejecter_id, string $reason): bool
{
    global $db;
    return $db->query("UPDATE " . TB_PREF . "travel_requests SET status = 'Rejected', rejected_by = " . $rejecter_id . ", rejected_at = NOW(), rejection_reason = " . $db->escape($reason) . " WHERE id = " . $id) !== false;
}

function add_expense(int $travel_id, string $expense_type, float $amount, string $description = '', array $options = []): int
{
    global $db;
    
    $fields = ['travel_id', 'expense_type', 'amount', 'description', 'date'];
    $values = [$travel_id, $db->escape($expense_type), $amount, $db->escape($description), 'CURDATE()'];
    
    $optional = ['gl_code', 'project_id', 'task_id', 'activity_code', 'vendor', 'vendor_type', 'mileage_miles', 'mileage_rate', 'billable'];
    foreach ($optional as $opt) {
        if (isset($options[$opt])) {
            $fields[] = $opt;
            $values[] = is_numeric($options[$opt]) ? $options[$opt] : $db->escape($options[$opt]);
        }
    }
    
    $sql = "INSERT INTO " . TB_PREF . "travel_expenses (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $values) . ")";
    $db->query($sql);
    return $db->insert_id;
}

function get_travel_expenses(int $travel_id, array $filters = []): ?object
{
    global $db;
    $sql = "SELECT e.*, t.purpose as trip_purpose, p.name as project_name 
        FROM " . TB_PREF . "travel_expenses e
        LEFT JOIN " . TB_PREF . "travel_requests t ON e.travel_id = t.id
        LEFT JOIN " . TB_PREF . "projects p ON e.project_id = p.project_id
        WHERE e.travel_id = " . $travel_id;
    
    if (!empty($filters['project_id'])) $sql .= " AND e.project_id = " . $db->escape($filters['project_id']);
    if (!empty($filters['activity_code'])) $sql .= " AND e.activity_code = " . $db->escape($filters['activity_code']);
    if (!empty($filters['status'])) $sql .= " AND e.status = " . $db->escape($filters['status']);
    
    $result = $db->query($sql);
    return $result ?: null;
}

function get_expense_by_project(string $project_id, string $task_id = null): ?object
{
    global $db;
    $sql = "SELECT e.*, t.purpose as trip_purpose, t.start_date, t.end_date
        FROM " . TB_PREF . "travel_expenses e
        JOIN " . TB_PREF . "travel_requests t ON e.travel_id = t.id
        WHERE e.project_id = " . $db->escape($project_id);
    
    if ($task_id) $sql .= " AND e.task_id = " . $db->escape($task_id);
    
    $result = $db->query($sql);
    return $result ?: null;
}

function get_project_travel_costs(string $project_id, string $task_id = null): array
{
    global $db;
    
    $sql = "SELECT 
        SUM(e.amount) as total_cost,
        SUM(CASE WHEN e.billable = 1 AND e.status = 'Approved' THEN e.amount ELSE 0 END) as billable_amount,
        SUM(CASE WHEN e.reimbursed = 1 THEN e.amount ELSE 0 END) as reimbursed_amount,
        e.project_id, e.task_id,
        t.purpose, COUNT(e.id) as expense_count
        FROM " . TB_PREF . "travel_expenses e
        JOIN " . TB_PREF . "travel_requests t ON e.travel_id = t.id
        WHERE e.project_id = " . $db->escape($project_id);
    
    if ($task_id) $sql .= " AND e.task_id = " . $db->escape($task_id);
    
    $sql .= " GROUP BY e.project_id, e.task_id, t.purpose";
    
    $result = $db->query($sql);
    $costs = [];
    while ($result && $row = $db->fetch($result)) {
        $costs[] = $row;
    }
    return $costs;
}

function calculate_per_diem(int $travel_id): array
{
    global $db;
    
    $trip = get_travel_request($travel_id);
    if (!$trip || empty($trip['per_diem_rate'])) {
        return ['total' => 0, 'breakdown' => []];
    }
    
    $start = new DateTime($trip['start_date']);
    $end = new DateTime($trip['end_date']);
    $days = (int)$end->diff($start)->format('%a') + 1;
    $rate = $trip['per_diem_rate'];
    
    $first_day_pct = $trip['first_day_pct'] ?? 75;
    $last_day_pct = $trip['last_day_pct'] ?? 75;
    
    $daily = [];
    $total = 0;
    
    for ($i = 0; $i < $days; $i++) {
        $date = clone $start;
        $date->modify("+$i days");
        
        if ($i == 0) {
            $day_rate = $rate * ($first_day_pct / 100);
        } elseif ($i == $days - 1) {
            $day_rate = $rate * ($last_day_pct / 100);
        } else {
            $day_rate = $rate;
        }
        
        $daily[$date->format('Y-m-d')] = $day_rate;
        $total += $day_rate;
    }
    
    return [
        'total' => $total,
        'days' => $days,
        'daily_rate' => $rate,
        'breakdown' => $daily
    ];
}

function get_per_diem_rules(string $city = null, string $country = null): ?object
{
    global $db;
    $sql = "SELECT * FROM " . TB_PREF . "travel_per_diem WHERE active = 1";
    
    if ($city && $country) {
        $sql .= " AND (city = " . $db->escape($city) . " AND country = " . $db->escape($country) . ")";
    }
    
    $result = $db->query($sql);
    return $result ?: null;
}

function get_activity_codes(): ?object
{
    global $db;
    $result = $db->query("SELECT * FROM " . TB_PREF . "travel_activity_codes WHERE active = 1 ORDER BY code");
    return $result ?: null;
}

function get_suppliers(string $service_type = null): ?object
{
    global $db;
    $sql = "SELECT * FROM " . TB_PREF . "travel_suppliers WHERE inactive = 0";
    
    if ($service_type) $sql .= " AND service_type = " . $db->escape($service_type);
    
    $sql .= " ORDER BY preference_order, name";
    
    $result = $db->query($sql);
    return $result ?: null;
}

function set_expense_status(int $expense_id, string $status): bool
{
    global $db;
    $result = $db->query("UPDATE " . TB_PREF . "travel_expenses SET status = " . $db->escape($status) . " WHERE id = " . $expense_id);
    return $result !== false;
}

function mark_expense_reimbursed(int $expense_id): bool
{
    global $db;
    $result = $db->query("UPDATE " . TB_PREF . "travel_expenses SET status = 'Reimbursed', reimbursed = 1 WHERE id = " . $expense_id);
    return $result !== false;
}