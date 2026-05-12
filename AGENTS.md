# AGENTS.md - ksf_FA_TravelExpense#

## Architecture Overview#

**FA Module** for Travel & Expense Management - expense reports, approvals, and reimbursement.

### Core Principles#
- **SOLID**, **DRY**, **TDD**, **DI**, **SRP**#

## Repository Structure#

```
ksf_FA_TravelExpense/
├── sql/#
│   ├── fa_travel_requests.sql#
│   ├── fa_travel_expenses.sql#
│   └── fa_expense_approvals.sql#
├── includes/#
│   ├── requests_db.inc#
│   ├── expenses_db.inc#
│   └── approvals_db.inc#
├── pages/#
├── hooks.php#
├── composer.json#
└── ProjectDocs/#
```

## Dependencies#

- **ksf_FA_TravelExpense_Core** (business logic)#
- **ksf_FA_HRM** (link to employees)#
- **ksf_FA_Wallet** (reimbursement)#
- **FrontAccounting 2.4+**#
