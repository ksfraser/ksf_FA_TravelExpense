# UAT-EXPENSE-001 - Expense Tracking UAT Plan

## User Acceptance Testing

**Module**: TravelExpense
**BR**: BR-EXPENSE-001
**Tester**: Employee / Finance Clerk / Manager

---

## Test Scenarios

### UAT-EXPENSE-001-TC01: Create Expense Report with Project Link

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Navigate to Expenses → New Report | Report form displays |
| 2 | Select project "PRJ-001" | Project linked |
| 3 | Current stage shows "Development" | Stage displayed |
| 4 | Available activities show only Development activities | Activities constrained |
| 5 | Add line: Hotel $200, Activity="Client Meeting" | Line added |
| 6 | Verify billing rule auto-filled | cost_plus 15% applied |

**Pass Criteria**: Expense line linked to project stage and activity

---

### UAT-EXPENSE-001-TC02: Expense Adjustments

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Add expense line: $100 | Base $100 |
| 2 | Apply adjustment "+15%" | Total $115 |
| 3 | Apply adjustment "-$10" | Total $105 |
| 4 | Apply adjustment "=150" | Total $150 |
| 5 | Verify final amount | $150 |

**Pass Criteria**: All adjustment types calculate correctly

---

### UAT-EXPENSE-001-TC03: Multi-Currency Expense

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Add expense line: Hotel €200, Rate 1.10 | - |
| 2 | Verify base currency amount | $220 USD |
| 3 | Submit report | Submitted with correct total |

**Pass Criteria**: Currency conversion applied correctly

---

### UAT-EXPENSE-001-TC04: Expense Approval Flow

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Employee submits expense report | Status = pending_approval |
| 2 | Manager receives notification | Email received |
| 3 | Manager approves | Status = approved |
| 4 | Finance processes reimbursement | Status = reimbursed |
| 5 | GL entries created | Entries posted |

**Pass Criteria**: Full approval workflow completes

---

### UAT-EXPENSE-001-TC05: Hook Integration

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Submit expense | Hook: expense_submitted emitted |
| 2 | Approve expense | Hook: expense_approved emitted |
| 3 | Check timesheet module | Notified of project activity |

**Pass Criteria**: Hooks emit and integrate correctly

---

## Sign-Off

| Role | Name | Date | Signature |
|------|------|------|-----------|
| Employee | | | |
| Manager | | | |
| Finance Clerk | | | |
