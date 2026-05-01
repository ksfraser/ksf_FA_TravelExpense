# KS FA Module Relationships ERD

## Core Modules (Required)

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                            FRONTACCOUNTING CORE                            │
├─────────────────────────────────────────────────────────────────────────────┤
│  fa_users      │  fa_debtors_master    │  fa_gl_online         │  fa_modules  │
│  (users)     │  (customers)         │  (GL posting)         │  (registry) │
└─────────────────────────────────────────────────────────────────────────────┘
         ▲              ▲                    ▲                    ▲
         │              │                    │                    │
         └──────────────┴────────────────────┴────────────────────┘
                              Base Layer
```

## Core Modules (Always Available)

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                          ksf_FA_HRM                                      │
├─────────────────────────────────────────────────────────────────────────────┤
│  hr_employees        │  hr_employee_details     │  hr_compensation      │
│  * employee_id PK   │  * employee_id FK    │  * employee_id FK  │
│    name            │    dob              │    salary         │
│    status         │    hire_date        │    hourly_rate   │
│    manager_id →   │    termination_date│    benefits     │
└─────────────────────────────────────────────────────────────────────────────┘
         │
         │◄──────────────────────────┐
         │                         │
         ▼                         ▼
┌──────────────────────┐  ┌──────────────────────┐
│ ksf_FA_Timesheets  │  │  ksf_FA_Leave      │
├────────────────────┤  ├───────────────────┤
│ ts_timesheets      │  │  le_leaves         │
│ * employee_id FK │  │  * employee_id FK │
│ * project_id FK │  │  * leave_type     │
│ * task_id FK   │  │  * status        │
│ * hours       │  │  * start_date    │
│ * activity_code│  │  * end_date     │
└──────────────────────┘  └──────────────────────┘
```

## Project Management Hub

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                      ksf_FA_ProjectManagement                         │
├─────────────────────────────────────────────────────────────────────────────┤
│  projects          │  project_tasks        │  project_teams         │
│  * project_id PK   │  * task_id PK       │  * project_id FK      │
│    name          │  * project_id FK    │  * employee_id FK  │
│    customer_id → │  * parent_task_id  │    role           │
│    status        │  * assignee_id → │    allocation %   │
│    budget       │  * status        │    start/end     │
│    manager_id → │  * progress     │                   │
└──────────────────┴───────────────────┴──────────────────────┘
         │                                        │
         │◄────────────────────────────────────────┤
         │                                        │
         ▼                                        ▼
┌──────────────────────┐                ┌──────────────────────────┐
│ ksf_FA_TravelExpense │                │ ksf_FA_Timesheets    │
├───────────────────┤                ├───────────────────┤
│ travel_requests   │                │ ts_timesheets      │
│ * employee_id FK │                │ * project_id FK ──┼┐
│ * project_id ───┼─── optional       │ * task_id FK     ││
│ * task_id ─────┼─── (dashed)      │                ││
│ * status       │                │                │◄┘
│                │                │
│ travel_expenses  │                │
│ * project_id ──┼─── optional      │
│ * task_id ────┼─── (dashed)      │
│ * activity_code               │
│ * billable   │                │
│ * amount    │                │
└───────────────────┘                └───────────────────────┘
         │                                        
         │◄──────────────────────────────────────────┤
         │                                          │
         ▼                                          ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                   OPTIONAL: GL POSTING                         │
├─────────────────────────────────────────────────────────────────────────────┤
│  When Expense Approved → Quick Entry → GL Journal         │
│                                                         │
│  DR: Travel Expense (project default or 6100)              │
│  CR: Cash/Accrual (2100)                               │
│                                                         │
│  OR (if project-linked + billable):                     │
│  DR: Due from Customer (1200)                         │
│  CR: Revenue (4000)                                 │
└─────���─��─────────────────────────────────────────────┘
```

## Additional Modules (Standalone or Linked)

```
┌──────────────────────┐  ┌──────────────────────┐  ┌──────────────────────┐
│  ksf_FA_Assets    │  │  ksf_FA_Fleet    │  │  ksf_FA_Service │
├───────────────────┤  ├─────────────────┤  ├─────────────────┤
│ assets           │  │ fleet_vehicles  │  │ service_tickets │
│ * employee_id →  │  │ * assigned_to →│  │ * customer_id →│
│ * category_id → │  │ * driver_id → │  │ * project_id ─┼┐
│ * location_id → │  │ * project_id ─┤│  │ * status     │ │
└───────────────────┘  └─────────────────┘  └───────────────┘│
                                                              │
                                              optional ──────────┘
┌───────────────────────────────────────┐
│  ksf_FA_Training                     │
├───────────────────────────────────────┤
│ training_courses                     │
│ * project_id ───────────────────────► optional
│ * instructor_id →
│ * enrollment_id →
└───────────────────────────────────┘
```

## Module Coupling Matrix

| Module         | HRM | Projects | Timesheets | Leave | Travel | Assets | Fleet | Service | Training | GL |
|---------------|-----|---------|---------|-------|--------|-------|-------|--------|--------|-------|----|
| **HRM**       | -   | O       | R       | O     | R      | O     | O     | O      | O     | O   |
| **Projects**    | O   | -       | R       | -     | O      | -     | O     | -      | O     | O   |
| **Timesheets**  | R   | R       | -       | -     | O      | -     | -     | -      | -     | O   |
| **Leave**      | O   | -       | -       | -     | -      | -     | -     | -      | -     | -   |
| **Travel**     | R   | O       | O       | -     | -      | -     | -     | -      | -     | O   |
| **Assets**    | O   | -       | -       | -     | -      | -     | O     | -      | -     | O   |
| **Fleet**      | O   | O       | -       | -     | -      | O     | -     | -      | -     | O   |
| **Service**   | O   | O       | -       | -     | -      | -     | -     | -      | -     | O   |
| **Training**  | O   | O       | -       | -     | -      | -     | -     | -      | -     | O   |
| **GL**        | O   | O       | O       | -     | O      | O     | O     | O      | O     | -   |

Legend:
- **R** = Required (module won't work without it)
- **O** = Optional (module works independently, can link if available)
- **-** = No coupling

## Per Diem / Quick Entries Architecture

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                 Per Diem + Quick Entries (Similar)                  │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                             ��
│  fa_travel_per_diem          │     fa_quick_entries          │
│  ┌─────────────────────┐    │    ┌───────────────────┐   │
│  │ city + country PK   │    │    │ id + type PK      │   │
│  │ daily_rate        │    │    │ description      │   │
│  │ first_day_pct    │    │    │ amount          │   │
│  │ last_day_pct    │    │    │ gl_code         │   │
│  │ breakfast_pct   │    │    │ tax_type_id     │   │
│  │ lunch_pct     │    │    │ inactive       │   │
│  │ dinner_pct    │    │    └───────────────────┘   │
│  │ active        │    │                              │
│  └─────────────────────┘    │                              │
│         │                 │                              │
│    similar structure      │    can adapt:                │
│                      │    - Different rates per    │
│  Project-specific:    │      project/customer   │
│  - Project A: $75   │    - Different rates    │
│  - Project B: $100  │      by locale       │
│  - International:    │    - Meal breakdown   │
│    London: $120    │      (b/l/d)        │
│    NYC: $100      │                              │
└─────────────────────────────────────────────────────────────┘
```

## Summary

- **Solid lines** (─) = Required coupling  
- **Dashed lines** (◌) = Optional coupling (works without module)
- **Modules are loosely coupled** - all work standalone
- **GL posting** is always optional (use Quick Entries or manual)
- **Per diem** can mirror Quick Entries pattern for locale-specific rates