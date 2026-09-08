# FR-EXPENSE-001-002 - Expense Approval Workflow

## Functional Requirement

**Module**: TravelExpense
**Priority**: P1 - High
**Status**: Proposed
**Integration**: Hook-based

### Description

Submit expense reports through approval chain.

### Acceptance Criteria

| ID | Criteria | Hook |
|----|----------|------|
| AC-001 | Submit triggers approval_request | Emit: expense_submitted |
| AC-002 | Approval chains expense to project manager | Query: project_get_current_stage |
| AC-003 | Approved expense queued for reimbursement | Emit: expense_approved |
| AC-004 | Rejected expense returned to employee | Emit: expense_rejected |
| AC-005 | Reimbursement recorded | Emit: expense_reimbursed |
| AC-006 | GL entries created on reimbursement | Query: gl_entry_create |

### Hooks

```php
// Emit: After approval chain completes
hook_invoke_all('expense_approved', [
    'expense_report_id' => $reportId,
    'approver_id' => $approverId,
    'project_id' => $projectId,
    'amount' => $amount,
]);

// Emit: After reimbursement
hook_invoke_all('expense_reimbursed', [
    'expense_report_id' => $reportId,
    'employee_id' => $employeeId,
    'amount' => $amount,
    'payment_method' => 'bank_transfer',
]);
```

### Dependencies

- FR-APPROVAL-001-001: Approval chain engine
- FR-EXPENSE-001-001: Expense entry
