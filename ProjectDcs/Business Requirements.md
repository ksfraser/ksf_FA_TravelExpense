# Business Requirements - ksf_TravelExpense

## Project Overview
Travel and expense tracking - supplier management, trip scheduling, expense reports.

## Problem Statement
- Need preferred suppliers list
- Need expense entry and approval
- Need trip management as mini-project
- Need per-diems and expense codes through GL

## Supplier Management

### Suppliers (NOT stock_master - separate)
- Supplier name
- Service type: Hotel, Car Rental, Taxi, Transit, Meal, Other
- Contact, website, rate code
- Preference order (1st, 2nd, 3rd)
- Corporate rate available (yes/no)

### Examples
| Supplier | Type | Preference |
|----------|------|-------------|
| Enterprise Rent-A-Car | Car Rental | 1 |
| Hertz | Car Rental | 2 |
| Turo | Car Rental | 3 |
| Marriott | Hotel | 1 |
| Hilton | Hotel | 2 |

## Trip Management

### Trip as Mini-Project
- Employee assigned
- Schedule calendar events (meetings)
- Pre-approval tasks
- Expense tasks

### Trip States
- Planned → Approved → In Progress → Complete
- Rejected / Cancelled

## Expense Entry

### Expense Line
- Date
- Category (Meal, Hotel, Car, Transit, Taxi, Other)
- Amount
- Project/task (billable to)
- GL expense code
- Receipt upload
- Notes

### Categories with GL Codes
| Category | Default GL Code |
|-----------|-----------------|
| Meals - Breakfast | MEAL-BREAKFAST |
| Meals - Lunch | MEAL-LUNCH |
| Meals - Dinner | MEAL-DINNER |
| Hotel | HOTEL |
| Car Rental | CAR_RENTAL |
| Taxi/Uber | TAXI |
| Transit/Bus/Rail | TRANSIT |
| Per Diem | PER_DIEM |

### Per Diem Rules
- Daily allowance by city/country
- First/last day rules (%)
- Excess return to employer

## Workflow

### Employee Flow
1. Create trip request with dates
2. Manager pre-approves (task)
3. Employee travels
4. Creates expense entries (linked to trip)
5. Submits expense report
6. Manager approves
7. Finance verifies
8. Reimbursement or GL allocation

### Manager Flow
1. Receive approval task
2. Review trip schedule
3. Accept/reject
4. Later: Approve expense report

## Integration
- ksf_ProjectManagement: Trip as project with tasks
- ksf_Timesheets: Time during trip billable
- ksf_HRM: Employee linked to trip
- ksf_FA: GL entries for expenses