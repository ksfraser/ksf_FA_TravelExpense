# Architecture - ksf_TravelExpense

## Overview

This document describes the technical architecture for the Travel and Expense Management module, including the layered architecture, component design, database schema, and integration patterns.

---

## 1. System Architecture

### 1.1 High-Level Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    Presentation Layer                      │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐   │
│  │Dashboard │ │ Suppliers│ │  Trips   │ │Expenses │   │
│  │   Page   │ │  Page    │ │  Page    │ │  Page    │   │
│  └────┬─────┘ └────┬─────┘ └────┬─────┘ └────┬─────┘   │
│       │           │           │           │           │         │
│       └───────────┴───────────┴───────────┘           │
│                         │                             │
├─────────────────────────┼─────────────────────────────┤
│                    Service Layer                      │
│  ┌──────────────────────────────────────────────────┐  │
│  │                te_db.inc                         │  │
│  │   Database functions (CRUD operations)          │  │
│  └──────────────────────────────────────────────────┘  │
│  ┌──────────────────────────────────────────────────┐  │
│  │                te_ui.inc                         │  │
│  │   UI helper functions and display logic         │  │
│  └──────────────────────────────────────────────────┘  │
├──────────────────────────────────────────────────────────────┤
│                    Business Layer                       │
│  ┌──────────────────────────────────────────────────┐  │
│  │              TEContainer (DI Container)          │  │
│  │   - SupplierService                              │  │
│  │   - TripService                               │  │
│  │   - ExpenseService                           │  │
│  │   - PerDiemService                           │  │
│  │   - GLService                                │  │
│  └──────────────────────────────────────────────────┘  │
├──────────────────────────────────────────────────────────────┤
│                    Data Layer                          │
│  ��──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐    │
│  │Suppliers  │ │  Trips   │ │Expenses  │ │Per Diem  │    │
│  │  Table    │ │  Table   │ │  Table   │ │  Rules   │    │
│  └──────────┘ └──────────┘ └──────────┘ └──────────┘    │
├──────────────────────────────────────────────────────────────┤
│                  Integration Layer                      │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐    │
│  │FA Employee│ │ksf-Project│ │ FA GL    │ │  FA Core │    │
│  │   Mgmt   │ │    Mgmt  │ │          │ │          │    │
│  └──────────┘ └──────────┘ └──────────┘ └──────────┘    │
└─────────────────────────────────────────────────────────────┘
```

### 1.2 Module Structure

```
ksf_FA_TravelExpense/
├── FA_TE_Module.php        # Module class with permissions
├── hooks.php              # FA lifecycle hooks
├── te.php                # API controller
├── _init/
│   └── init.inc         # Module initialization
├── includes/
│   ├── import.php       # Import functionality
│   ├── te_db.inc       # Database functions
│   └── te_ui.inc       # UI helpers
├── pages/
│   ├── dashboard.php    # Dashboard view
│   ├── suppliers.php  # Supplier CRUD
│   ├── trips.php      # Trip CRUD
│   ├── expenses.php   # Expense CRUD
│   ├── reports.php    # Reporting
│   └── settings.php   # Settings
└── sql/
    ├── install.sql   # Schema creation
    └── uninstall.sql # Schema removal
```

---

## 2. Component Design

### 2.1 Core Components

#### TEContainer
The DI container provides service instantiation and dependency management.

**Purpose**: Central service locator implementing PSR-11 ContainerInterface

**Services Provided**:
- `DatabaseAdapterInterface` - FADatabaseAdapter
- `EmployeeServiceInterface` - FAEmployeeService
- `GLServiceInterface` - FAGLService
- `SupplierServiceInterface` - Core supplier logic
- `TripServiceInterface` - Trip management
- `ExpenseServiceInterface` - Expense management
- `PerDiemServiceInterface` - Per diem calculations
- `EventDispatcherInterface` - FAEventDispatcher (PSR-14)
- `LoggerInterface` - NullLogger (PSR-3)

**Responsibilities**:
- Service instantiation on demand
- Dependency injection into services
- Service lifecycle management

```php
class TEContainer implements ContainerInterface
{
    public function get(string $id): mixed
    public function has(string $id): bool
}
```

#### ExpenseService
Manages expense CRUD and workflow.

**Methods**:
```php
interface ExpenseServiceInterface
{
    public function createExpense(array $data): string
    public function updateExpense(string $expenseId, array $data): bool
    public function deleteExpense(string $expenseId): bool
    public function submitExpense(string $expenseId): bool
    public function approveExpense(string $expenseId): bool
    public function rejectExpense(string $expenseId, string $reason): bool
}
```

#### PerDiemService
Handles per-diem calculations.

**Methods**:
```php
interface PerDiemServiceInterface
{
    public function calculatePerDiem(string $tripId): array
    public function getPerDiemRate(string $city, string $country): ?array
    public function calculateExcess(string $tripId): float
}
```

#### GLService
Integrates with FA GL for posting expenses.

**Methods**:
```php
interface GLServiceInterface
{
    public function postExpenseToGL(string $expenseId): bool
    public function postTripToGL(string $tripId): bool
    public function validateGLCode(string $code): bool
    public function getGLAccount(string $code): ?array
}
```

### 2.2 Database Functions (te_db.inc)

Provides procedural database operations for CRUD.

#### Supplier Functions
- `get_te_suppliers($service_type)` - List suppliers
- `get_te_supplier($supplier_id)` - Get single supplier
- `insert_te_supplier($data)` - Create supplier
- `update_te_supplier($supplier_id, $data)` - Update supplier
- `delete_te_supplier($supplier_id)` - Delete supplier

#### Trip Functions
- `get_te_trips($search, $status)` - List trips
- `get_te_trip($trip_id)` - Get single trip
- `get_te_trips_by_employee($employee_id)` - Get employee trips
- `insert_te_trip($data)` - Create trip
- `update_te_trip($trip_id, $data)` - Update trip
- `delete_te_trip($trip_id)` - Delete trip

#### Expense Functions
- `get_te_expenses($trip_id, $status)` - List expenses
- `get_te_expense($expense_id)` - Get single expense
- `insert_te_expense($data)` - Create expense
- `update_te_expense($expense_id, $data)` - Update expense
- `delete_te_expense($expense_id)` - Delete expense
- `get_te_expense_totals($trip_id)` - Calculate totals

#### Per Diem Functions
- `get_te_per_diem_rules()` - List per diem rules
- `get_te_per_diem_rule($city, $country)` - Get rule by location
- `insert_te_per_diem_rule($data)` - Create rule
- `calculate_te_per_diem($trip_id)` - Calculate per diem
- `calculate_te_per_diem_excess($trip_id)` - Calculate excess

#### Expense Report Functions
- `get_te_expense_reports($employee_id, $status)` - List reports
- `get_te_expense_report($report_id)` - Get single report
- `create_te_expense_report($trip_id)` - Create report
- `submit_te_expense_report($report_id)` - Submit report
- `approve_te_expense_report($report_id)` - Approve report
- `reject_te_expense_report($report_id, $reason)` - Reject report

#### Activity Functions
- `get_te_recent_activities($limit)` - Get activity log

### 2.3 UI Functions (te_ui.inc)

Provides presentation logic and helpers.

#### Navigation
- `te_navigation_menu()` - Main menu tabs

#### Display
- `display_te_supplier_list($suppliers)` - Supplier table
- `display_te_trip_list($trips)` - Trip table
- `display_te_expense_list($expenses)` - Expense table
- `display_te_dashboard_stats($stats)` - Dashboard statistics

#### Select Helpers
- `sel_service_type($selected)` - Service type dropdown
- `sel_supplier($suppliers, $selected)` - Supplier dropdown
- `sel_trip($trips, $selected)` - Trip dropdown
- `sel_expense_category($selected)` - Category dropdown
- `sel_gl_code($selected)` - GL code dropdown
- `sel_trip_status($selected)` - Trip status dropdown
- `sel_expense_status($selected)` - Expense status dropdown
- `sel_employee($selected)` - Employee dropdown

#### Status Helpers
- `get_te_supplier_status_class($status)` - CSS class for supplier
- `get_te_trip_status_class($status)` - CSS class for trip
- `get_te_expense_status_class($status)` - CSS class for expense

---

## 3. Database Schema

### 3.1 Entity Relationship Diagram

```
┌─────────────────┐       ┌─────────────────┐
│    employees    │       │    projects     │
│   (FA HRM)     │       │ (ksf-PM)       │
└────────┬────────┘       └────────┬────────┘
         │                         │
         │ 1:N                     │ 1:N
         ▼                         ▼
┌─────────────────────────────────────────────────┐
│              fa_te_trips                        │
│ ┌────────────────────────────────────────────┐ │
│ │ trip_id (PK)                               │ │
│ │ employee_id (FK) ─────────► employees      │ │
│ │ name, description                          │ │
│ │ destination_city, destination_country      │ │
│ │ start_date, end_date                      │ │
│ │ status                                     │ │
│ │ total_estimated, total_actual             │ │
│ │ per_diem_rate                              │ │
│ └────────────────────────────────────────────┘ │
└──────────────────────────┬──────────────────────┘
                          │ 1:N
                          ▼
┌─────────────────────────────────────────────────┐
│             fa_te_expenses                       │
│ ┌────────────────────────────────────────────┐ │
│ │ expense_id (PK)                             │ │
│ │ trip_id (FK) ────────────► trips            │ │
│ │ project_id (FK) ──────► projects (ksf-PM)   │ │
│ │ task_id (FK) ────────► tasks (ksf-PM)      │ │
│ │ expense_date, amount                        │ │
│ │ category                                   │ │
│ │ gl_code (FK) ─────────► chart of accounts    │ │
│ │ status                                    │ │
│ └────��───────────────────────────────────────┘ │
└─────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────┐
│          fa_te_per_diem_rules                    │
│ ┌────────────────────────────────────────────┐ │
│ │ id (PK)                                  │ │
│ │ city, country                            │ │
│ │ daily_rate                              │ │
│ │ first_day_pct, last_day_pct           │ │
│ │ breakfast_pct, lunch_pct, dinner_pct │ │
│ └────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────┐
│            fa_te_suppliers                      │
│ ┌────────────────────────────────────────────┐ │
│ │ supplier_id (PK)                          │ │
│ │ name                                      │ │
│ │ service_type                              │ │
│ │ contact, website, rate_code               │ │
│ │ preference_order                         │ │
│ │ corporate_rate                            │ │
│ └────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────┘
```

### 3.2 Table Details

#### fa_te_suppliers
```sql
CREATE TABLE `@TB_PREF@fa_te_suppliers` (
    `supplier_id` INT(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `service_type` VARCHAR(30) NOT NULL,
    `contact_name` VARCHAR(100) DEFAULT NULL,
    `contact_phone` VARCHAR(30) DEFAULT NULL,
    `contact_email` VARCHAR(100) DEFAULT NULL,
    `website` VARCHAR(255) DEFAULT NULL,
    `rate_code` VARCHAR(30) DEFAULT NULL,
    `preference_order` INT(11) DEFAULT 1,
    `corporate_rate` TINYINT(1) DEFAULT 0,
    `notes` TEXT,
    `inactive` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`supplier_id`),
    KEY `idx_type` (`service_type`),
    KEY `idx_preference` (`preference_order`),
    KEY `idx_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

#### fa_te_trips
```sql
CREATE TABLE `@TB_PREF@fa_te_trips` (
    `trip_id` VARCHAR(20) NOT NULL,
    `employee_id` VARCHAR(100) NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT,
    `destination_city` VARCHAR(100) DEFAULT NULL,
    `destination_country` VARCHAR(50) DEFAULT NULL,
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `status` VARCHAR(30) DEFAULT 'Planned',
    `total_estimated` DECIMAL(15,2) DEFAULT 0.00,
    `total_actual` DECIMAL(15,2) DEFAULT 0.00,
    `per_diem_rate` DECIMAL(10,2) DEFAULT 0.00,
    `per_diem_days` INT(11) DEFAULT 0,
    `created_by` VARCHAR(100) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`trip_id`),
    KEY `idx_employee` (`employee_id`),
    KEY `idx_status` (`status`),
    KEY `idx_dates` (`start_date`, `end_date`),
    CONSTRAINT `fk_trip_employee` FOREIGN KEY (`employee_id`) 
        REFERENCES `@TB_PREF@employee` (`emp_id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

#### fa_te_expenses
```sql
CREATE TABLE `@TB_PREF@fa_te_expenses` (
    `expense_id` VARCHAR(20) NOT NULL,
    `trip_id` VARCHAR(20) NOT NULL,
    `expense_date` DATE NOT NULL,
    `category` VARCHAR(30) NOT NULL,
    `amount` DECIMAL(15,2) NOT NULL,
    `gl_code` VARCHAR(30) NOT NULL,
    `project_id` VARCHAR(20) DEFAULT NULL,
    `task_id` VARCHAR(20) DEFAULT NULL,
    `vendor` VARCHAR(100) DEFAULT NULL,
    `description` TEXT,
    `receipt_id` INT(11) DEFAULT NULL,
    `status` VARCHAR(30) DEFAULT 'Pending',
    `gl_posted` TINYINT(1) DEFAULT 0,
    `gl_posted_at` DATETIME DEFAULT NULL,
    `created_by` VARCHAR(100) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`expense_id`),
    KEY `idx_trip` (`trip_id`),
    KEY `idx_category` (`category`),
    KEY `idx_status` (`status`),
    KEY `idx_gl` (`gl_code`),
    KEY `idx_gl_posted` (`gl_posted`),
    CONSTRAINT `fk_expense_trip` FOREIGN KEY (`trip_id`) 
        REFERENCES `@TB_PREF@fa_te_trips` (`trip_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_expense_project` FOREIGN KEY (`project_id`) 
        REFERENCES `@TB_PREF@fa_pm_projects` (`project_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

#### fa_te_expense_reports
```sql
CREATE TABLE `@TB_PREF@fa_te_expense_reports` (
    `report_id` VARCHAR(20) NOT NULL,
    `trip_id` VARCHAR(20) NOT NULL,
    `employee_id` VARCHAR(100) NOT NULL,
    `period_start` DATE NOT NULL,
    `period_end` DATE NOT NULL,
    `total_amount` DECIMAL(15,2) NOT NULL,
    `total_per_diem` DECIMAL(15,2) DEFAULT 0.00,
    `total_expenses` DECIMAL(15,2) DEFAULT 0.00,
    `total_excess` DECIMAL(15,2) DEFAULT 0.00,
    `status` VARCHAR(30) DEFAULT 'Draft',
    `submitted_by` VARCHAR(100) DEFAULT NULL,
    `submitted_at` DATETIME DEFAULT NULL,
    `approved_by` VARCHAR(100) DEFAULT NULL,
    `approved_at` DATETIME DEFAULT NULL,
    `rejected_by` VARCHAR(100) DEFAULT NULL,
    `rejected_at` DATETIME DEFAULT NULL,
    `rejection_reason` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`report_id`),
    KEY `idx_trip` (`trip_id`),
    KEY `idx_employee` (`employee_id`),
    KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

#### fa_te_per_diem_rules
```sql
CREATE TABLE `@TB_PREF@fa_te_per_diem_rules` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `city` VARCHAR(100) NOT NULL,
    `country` VARCHAR(50) NOT NULL,
    `daily_rate` DECIMAL(10,2) NOT NULL,
    `first_day_pct` DECIMAL(5,2) DEFAULT 75.00,
    `last_day_pct` DECIMAL(5,2) DEFAULT 75.00,
    `breakfast_pct` DECIMAL(5,2) DEFAULT 0.00,
    `lunch_pct` DECIMAL(5,2) DEFAULT 0.00,
    `dinner_pct` DECIMAL(5,2) DEFAULT 0.00,
    `active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_location` (`city`, `country`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

#### fa_te_activity_log
```sql
CREATE TABLE `@TB_PREF@fa_te_activity_log` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `activity_type` VARCHAR(30) NOT NULL,
    `entity_type` VARCHAR(30) NOT NULL,
    `entity_id` VARCHAR(20) NOT NULL,
    `user_id` VARCHAR(100) DEFAULT NULL,
    `action` VARCHAR(50) NOT NULL,
    `details` TEXT,
    `old_values` TEXT,
    `new_values` TEXT,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_entity` (`entity_type`, `entity_id`),
    KEY `idx_user` (`user_id`),
    KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

#### fa_te_receipts
```sql
CREATE TABLE `@TB_PREF@fa_te_receipts` (
    `receipt_id` INT(11) NOT NULL AUTO_INCREMENT,
    `expense_id` VARCHAR(20) DEFAULT NULL,
    `trip_id` VARCHAR(20) DEFAULT NULL,
    `file_name` VARCHAR(255) NOT NULL,
    `original_name` VARCHAR(255) NOT NULL,
    `mime_type` VARCHAR(100) DEFAULT 'application/octet-stream',
    `size` INT(11) DEFAULT 0,
    `storage_type` VARCHAR(20) DEFAULT 'local',
    `storage_path` VARCHAR(500) DEFAULT '',
    `uploaded_by` VARCHAR(100) DEFAULT NULL,
    `uploaded_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`receipt_id`),
    KEY `idx_expense` (`expense_id`),
    KEY `idx_trip` (`trip_id`),
    KEY `idx_uploaded_by` (`uploaded_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## 4. Integration Patterns

### 4.1 FA Integration

The module integrates with FrontAccounting core:

#### Database Integration
- Uses FA's `db_query()`, `db_fetch_assoc()`, etc.
- Uses `TB_PREF` for table prefix
- Uses `TB_PREF . "employee"` for employees
- Uses `TB_PREF . "chart_master"` for GL codes

#### Session Integration
- Uses `$session->check_access()` for permission checks
- Defines permissions in `FA_TE_Module.php`

#### UI Integration
- Uses FA's `page()`, `start_table()`, `end_table()`
- Uses FA's form helpers

### 4.2 Project Management Integration

The module integrates with ksf_FA_ProjectManagement:

```php
// Billable expense to project/task
$expense['project_id'] = 'PRJ-001';
$expense['task_id'] = 'TSK-001';
```

### 4.3 GL Integration

Posting to General Ledger:

```php
// GL entry for approved expense
$gl_entry = [
    'amount' => $expense['amount'],
    'gl_code' => $expense['gl_code'],
    'project_id' => $expense['project_id'],
    'memo' => 'Expense ' . $expense['expense_id']
];
```

---

## 5. Security Architecture

### 5.1 Permission Model

Defined in FA_TE_Module.php:

| Permission | Description |
|------------|-------------|
| TE_VIEW_SUPPLIERS | View supplier list |
| TE_MANAGE_SUPPLIERS | Create/edit/delete suppliers |
| TE_VIEW_TRIPS | View trip list |
| TE_MANAGE_TRIPS | Create/edit/delete trips |
| TE_VIEW_EXPENSES | View expense entries |
| TE_MANAGE_EXPENSES | Create/edit/delete expenses |
| TE_SUBMIT_REPORT | Submit expense reports |
| TE_APPROVE_REPORT | Approve/reject expense reports |
| TE_VIEW_REPORTS | View reports |
| TE_ADMIN | Full admin access |

### 5.2 Data Validation

- SQL injection prevention via `db_escape()`
- Input sanitization via `htmlspecialchars()`
- Required field validation in business logic
- GL code validation against chart of accounts

---

## 6. Design Patterns

### 6.1 Patterns Used

| Pattern | Implementation |
|--------|---------------|
| Service Locator | TEContainer |
| Data Access Object | te_db.inc functions |
| Helper Object | te_ui.inc functions |
| Event Dispatcher | FAEventDispatcher |
| Factory | Container service creation |
| State Machine | Trip/Expense workflow |

### 6.2 Workflow State Machine

```
Trip Status:
    Planned → Approved → In Progress → Complete
       ↓         ↓           ↓
    Rejected   Cancelled   Cancelled

Expense Status:
    Draft → Pending → Submitted → Approved
                               ↓
                             Rejected
```

---

## 7. Configuration

### 7.1 Module Configuration

Located in pages/settings.php:
- Default GL codes by expense category
- Per diem rules by city/country
- Require receipts flag
- Max receipt file size

### 7.2 Default Expense Categories

| Category | Default GL Code |
|----------|-----------------|
| Meals - Breakfast | MEAL-BREAKFAST |
| Meals - Lunch | MEAL-LUNCH |
| Meals - Dinner | MEAL-DINNER |
| Hotel | HOTEL |
| Car Rental | CAR_RENTAL |
| Taxi/Uber | TAXI |
| Transit | TRANSIT |
| Per Diem | PER_DIEM |
| Other | OTHER_EXPENSE |

---

## 8. Deployment

### 8.1 Installation

1. Copy module to `/modules/ksf_FA_TravelExpense`
2. Activate via FA Modules admin
3. SQL creates tables and inserts initial data
4. Permissions created in FA security

### 8.2 Initialization

_init/init.inc handles:
- Menu registration
- Permission setup
- Default data loading
- Version tracking
