# BR-EXPENSE-001 - Expense Tracking System

## Business Requirement

**Module**: ksf_FA_TravelExpense
**Status**: Proposed (enhancement)
**Integration**: Hook-based (hook_invoke_all)

### Problem Statement

Current expense tracking lacks:
- Contract-linked billing rules (cost, cost+, fixed)
- Multi-layer activity codes from project templates
- Quick-entry style adjustments (amount +/-, % +/-)
- Billable flag per expense line
- Integration with project templates

### Scope

#### In Scope
1. Expense entry with project/activity linkage
2. Activity code selection (constrained by project stage)
3. Contract billing rules (cost, cost+, fixed margin)
4. Quick-entry adjustments
5. Multi-currency with conversion
6. Receipt attachments
7. Approval workflow via hooks
8. GL posting integration

#### Out of Scope
1. Receipt scanning/OCR (future)
2. Mobile offline sync (future)
3. Per diem auto-calculation (separate BR)

### Hook Integration Points

```php
// Expense submitted for approval
$result = hook_invoke_all('expense_submitted', [
    'expense_report_id' => $reportId,
    'user_id' => $userId,
    'total_amount' => $total,
    'project_id' => $projectId,
    'currency' => $currency,
]);

// Expense approved
hook_invoke_all('expense_approved', [
    'expense_report_id' => $reportId,
    'approver_id' => $approverId,
    'project_id' => $projectId,
    'amount' => $amount,
]);

// Expense rejected
hook_invoke_all('expense_rejected', [
    'expense_report_id' => $reportId,
    'rejector_id' => $rejectorId,
    'reason' => $reason,
]);

// Expense reimbursed
hook_invoke_all('expense_reimbursed', [
    'expense_report_id' => $reportId,
    'employee_id' => $employeeId,
    'amount' => $amount,
    'payment_method' => $method,
]);

// Get billing rule for expense
$result = hook_invoke_all('expense_get_billing_rule', [
    'project_id' => $projectId,
    'activity_id' => $activityId,
    'expense_category' => $category,
]);
// Returns: ['rule' => 'cost_plus', 'margin' => 0.15, 'rate' => null]
```

### Billing Rule Integration

```php
// Fetch contract billing rule for project/activity
hook_invoke_first('contract_get_billing_rule', [
    'project_id' => $projectId,
    'activity_code' => $activityCode,
]);

// Contract types:
// - 'cost': Cost only (no markup)
// - 'cost_plus': Cost + percentage margin
// - 'fixed_rate': Fixed rate per unit
// - 'not_billable': Internal use only
```

### Adjustment Quick-Entry

| Type | Description | Example |
|------|-------------|---------|
| `+amount` | Add fixed amount | +25.00 |
| `-amount` | Subtract fixed amount | -10.00 |
| `+%` | Add percentage | +15% |
| `-%` | Subtract percentage | -10% |
| `=amount` | Override to fixed | =100.00 |

### Expense Categories

| Category | Default GL Code | Billable |
|----------|-----------------|----------|
| Meals - Breakfast | EXP-MEAL-BRK | Yes |
| Meals - Lunch | EXP-MEAL-LCH | Yes |
| Meals - Dinner | EXP-MEAL-DIN | Yes |
| Hotel | EXP-HOTEL | Yes |
| Car Rental | EXP-CAR | Yes |
| Taxi/Ride-share | EXP-TAXI | Yes |
| Transit | EXP-TRANSIT | Yes |
| Airfare | EXP-AIR | Yes |
| Parking | EXP-PARK | Yes |
| Office Supplies | EXP-OFFICE | No |
| Client Entertainment | EXP-ENT | Yes |
| Fuel | EXP-FUEL | Yes |
| Other | EXP-OTHER | Depends |

### Dependencies

- ksf_FA_ProjectManagement (project/activity linkage)
- ksf_FA_ProjectManagement (BR-PROJECT-001: fixed price contracts for billing rules)
- ksf_FA_Teams (approval chain)
- ksf_FA_RBAC (permission checks)
- ksf_FA_Common (EncryptedFields for receipt storage)
