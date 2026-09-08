# FR-EXPENSE-001-001 - Expense Report Entry

## Functional Requirement

**Module**: TravelExpense
**Priority**: P0 - Critical
**Status**: Proposed
**Integration**: Hook-based

### Description

Create and manage expense reports with project/activity linkage and billing rules.

### Acceptance Criteria

| ID | Criteria | Hook |
|----|----------|------|
| AC-001 | Create expense report header | - |
| AC-002 | Add expense lines with project/activity | Emit: expense_line_added |
| AC-003 | Get available activities for project stage | Query: project_stage_get_activities |
| AC-004 | Apply billing rule from contract | Query: expense_get_billing_rule |
| AC-005 | Calculate amount with adjustments | - |
| AC-006 | Multi-currency with conversion | - |
| AC-007 | Attach receipt | - |
| AC-008 | Submit for approval | Emit: expense_submitted |

### Hooks

```php
// Query: Get billing rule for expense
$result = hook_invoke_first('expense_get_billing_rule', [
    'project_id' => $projectId,
    'activity_id' => $activityId,
    'expense_category' => 'hotel',
]);
// Returns: ['rule' => 'cost_plus', 'margin' => 0.15, 'is_billable' => true]

// Emit: Expense line added
hook_invoke_all('expense_line_added', [
    'expense_report_id' => $reportId,
    'line_id' => $lineId,
    'project_id' => $projectId,
    'activity_id' => $activityId,
    'amount' => 150.00,
]);

// Emit: Expense submitted
hook_invoke_all('expense_submitted', [
    'expense_report_id' => $reportId,
    'submitter_id' => $userId,
    'total_amount' => 1500.00,
    'currency' => 'USD',
    'project_id' => $projectId,
]);
```

### Adjustment Calculation

```php
// Adjustment types: +amount, -amount, +%, -%, =amount
$baseAmount = 100.00;
$adjustmentType = '+15%';  // or '+25.00' or '=150.00'
$calculated = calculateAdjustment($baseAmount, $adjustmentType);
// +15% = 115.00
// +25.00 = 125.00
// =150.00 = 150.00
```
