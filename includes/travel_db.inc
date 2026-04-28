<?php
/**
 * Travel Expense Database Functions
 */

function create_travel_request(int $employee_id, string $purpose, string $start_date, string $end_date, float $estimated_cost = null): int
{
    global $db;
    $sql = "INSERT INTO " . TB_PREF . "travel_requests (employee_id, purpose, start_date, end_date, estimated_cost, created_by)
        VALUES (" . $employee_id . ", " . $db->escape($purpose) . ", " . $db->escape($start_date) . ", " . $db->escape($end_date) . ", " . ($estimated_cost ?: 'NULL') . ", " . user_id() . ")";
    $db->query($sql);
    return $db->insert_id;
}

function get_travel_requests(array $filters = []): ?object
{
    global $db;
    $sql = "SELECT t.*, e.name as employee_name FROM " . TB_PREF . "travel_requests t
        LEFT JOIN " . TB_PREF . "hr_employees e ON t.employee_id = e.id WHERE 1=1";
    
    if (!empty($filters['employee_id'])) $sql .= " AND t.employee_id = " . $filters['employee_id'];
    if (!empty($filters['status'])) $sql .= " AND t.status = " . $db->escape($filters['status']);
    
    $sql .= " ORDER BY t.created_at DESC";
    return $db->query($sql);
}

function add_expense(int $travel_id, string $expense_type, float $amount, string $description = ''): int
{
    global $db;
    $sql = "INSERT INTO " . TB_PREF . "travel_expenses (travel_id, expense_type, amount, description, date)
        VALUES (" . $travel_id . ", " . $db->escape($expense_type) . ", " . $amount . ", " . $db->escape($description) . ", CURDATE())";
    $db->query($sql);
    return $db->insert_id;
}

function get_travel_expenses(int $travel_id): ?object
{
    global $db;
    $sql = "SELECT * FROM " . TB_PREF . "travel_expenses WHERE travel_id = " . $travel_id;
    return $db->query($sql);
}

function approve_travel_request(int $id, int $approver_id): bool
{
    global $db;
    return $db->query("UPDATE " . TB_PREF . "travel_requests SET status = 'Approved', approved_by = " . $approver_id . ", approved_at = NOW() WHERE id = " . $id);
}

function mark_paid(int $id): bool
{
    global $db;
    return $db->query("UPDATE " . TB_PREF . "travel_requests SET status = 'Paid' WHERE id = " . $id);
}