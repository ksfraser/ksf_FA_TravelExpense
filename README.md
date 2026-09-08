# ksf_TravelExpense - Travel and Expense Management Module

## Overview

The TravelExpense module provides comprehensive travel and expense tracking functionality for FrontAccounting. It enables organizations to manage preferred suppliers, coordinate business trips, submit expense reports, and handle per-diem allowances with GL integration.

## Features

### Supplier Management
- Maintain preferred supplier list (separate from FA stock_master)
- Service type categorization: Hotel, Car Rental, Taxi, Transit, Meal, Other
- Contact information and website tracking
- Preference ordering (1st, 2nd, 3rd choice)
- Corporate rate availability flag
- Rate code management

### Trip Management
- Trip requests treated as mini-projects
- Employee assignment and scheduling
- Meeting/event calendar
- Pre-approval task workflow
- Expense task management

#### Trip States
- Planned -> Approved -> In Progress -> Complete
- Rejected / Cancelled

### Expense Entry
- Expense line items with categories:
  - Meals (Breakfast, Lunch, Dinner)
  - Hotel
  - Car Rental
  - Taxi/Uber
  - Transit (Bus, Rail)
  - Per Diem
  - Other
- Per-project/task billing allocation
- GL expense code mapping
- Receipt upload capability
- Notes and documentation

### Per Diem Rules
- Daily allowance by city/country
- First/last day percentage rules
- Excess return tracking to employer

### Workflow
1. Employee creates trip request with dates
2. Manager pre-approves (task)
3. Employee travels
4. Creates expense entries (linked to trip)
5. Submits expense report
6. Manager approves
7. Finance verifies
8. Reimbursement or GL allocation

## Quick Start

### Installation

1. Copy the module to your FrontAccounting modules directory:
   ```
   /modules/ksf_FA_TravelExpense/
   ```

2. Activate the module via FA Modules admin panel

3. The SQL installer will create required tables

### Configuration

1. Navigate to TravelExpense > Settings
2. Configure default GL codes for expense categories
3. Set up per diem rules by city/country
4. Add preferred suppliers

### Usage

1. **Create Trip**: TravelExpense > Trips > New Trip
2. **Add Expenses**: Trip detail page > Add Expense
3. **Submit Report**: Trip > Submit for Approval
4. **Review/Approve**: Manager receives task notification

## Database Tables

### fa_te_suppliers
Preferred supplier list for travel services.

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
    KEY `idx_preference` (`preference_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### fa_te_trips
Trip records as mini-projects.

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
    `per_diem_first_day_pct` DECIMAL(5,2) DEFAULT 100.00,
    `per_diem_last_day_pct` DECIMAL(5,2) DEFAULT 100.00,
    `created_by` VARCHAR(100) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`trip_id`),
    KEY `idx_employee` (`employee_id`),
    KEY `idx_status` (`status`),
    KEY `idx_dates` (`start_date`, `end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### fa_te_expenses
Expense line items linked to trips.

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
    `submitted_by` VARCHAR(100) DEFAULT NULL,
    `submitted_at` DATETIME DEFAULT NULL,
    `approved_by` VARCHAR(100) DEFAULT NULL,
    `approved_at` DATETIME DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`expense_id`),
    KEY `idx_trip` (`trip_id`),
    KEY `idx_category` (`category`),
    KEY `idx_status` (`status`),
    KEY `idx_gl` (`gl_code`),
    CONSTRAINT `fk_expense_trip` FOREIGN KEY (`trip_id`) 
        REFERENCES `@TB_PREF@fa_te_trips` (`trip_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### fa_te_expense_reports
Expense report headers for submission/approval.

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
    `status` VARCHAR(30) DEFAULT 'Draft',
    `submitted_at` DATETIME DEFAULT NULL,
    `approved_at` DATETIME DEFAULT NULL,
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

### fa_te_per_diem_rules
Per diem rules by location.

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
    KEY `idx_location` (`city`, `country`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### fa_te_activity_log
Activity audit trail.

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

### fa_te_receipts
Uploaded receipt files.

```sql
CREATE TABLE `@TB_PREF@fa_te_receipts` (
    `receipt_id` INT(11) NOT NULL AUTO_INCREMENT,
    `expense_id` VARCHAR(20) DEFAULT NULL,
    `trip_id` VARCHAR(20) DEFAULT NULL,
    `file_name` VARCHAR(255) NOT NULL,
    `original_name` VARCHAR(255) NOT NULL,
    `mime_type` VARCHAR(100) DEFAULT 'application/octet-stream',
    `size` INT(11) DEFAULT 0,
    `storage_path` VARCHAR(500) DEFAULT '',
    `uploaded_by` VARCHAR(100) DEFAULT NULL,
    `uploaded_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`receipt_id`),
    KEY `idx_expense` (`expense_id`),
    KEY `idx_trip` (`trip_id`),
    KEY `idx_uploaded_by` (`uploaded_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

## Permissions

The module defines the following security permissions:

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

## API

### Database Functions (te_db.inc)

#### Supplier Functions
```php
function get_te_suppliers($service_type = null): array
function get_te_supplier($supplier_id): ?array
function insert_te_supplier($data): int
function update_te_supplier($supplier_id, $data): bool
function delete_te_supplier($supplier_id): bool
```

#### Trip Functions
```php
function get_te_trips($search = null, $status = null): array
function get_te_trip($trip_id): ?array
function get_te_trips_by_employee($employee_id): array
function insert_te_trip($data): string
function update_te_trip($trip_id, $data): bool
function delete_te_trip($trip_id): bool
function get_te_trip_totals($trip_id): array
```

#### Expense Functions
```php
function get_te_expenses($trip_id, $status = null): array
function get_te_expense($expense_id): ?array
function insert_te_expense($data): string
function update_te_expense($expense_id, $data): bool
function delete_te_expense($expense_id): bool
function calculate_per_diem($trip_id): array
```

#### Expense Report Functions
```php
function get_te_expense_reports($employee_id = null, $status = null): array
function get_te_expense_report($report_id): ?array
function create_te_expense_report($trip_id): string
function submit_te_expense_report($report_id): bool
function approve_te_expense_report($report_id): bool
function reject_te_expense_report($report_id, $reason): bool
```

### UI Functions (te_ui.inc)

#### Navigation
```php
function te_navigation_menu(): void
```

#### Display
```php
function display_te_supplier_list($suppliers): void
function display_te_trip_list($trips): void
function display_te_expense_list($expenses): void
function display_te_dashboard_stats($stats): void
```

#### Select Helpers
```php
function sel_service_type($selected): string
function sel_supplier($suppliers, $selected): string
function sel_trip($trips, $selected): string
function sel_expense_category($selected): string
function sel_gl_code($selected): string
function sel_trip_status($selected): string
function sel_expense_status($selected): string
```

## Integration

### With ksf_FA_ProjectManagement
- Trips reference projects for billing
- Tasks link to expense entries for billing allocation

### With ksf_FA_Timesheets
- Time during trip is billable to project/task

### With ksf_FA_HRM
- Employee linked to trip (employee_id)

### With ksf_FA (GL)
- Expense amounts post to GL codes
- Expense categories map to GL expense codes

## Expense Categories and GL Codes

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

## Example Suppliers

| Name | Type | Preference |
|------|------|-------------|
| Enterprise Rent-A-Car | Car Rental | 1 |
| Hertz | Car Rental | 2 |
| Turo | Car Rental | 3 |
| Marriott | Hotel | 1 |
| Hilton | Hotel | 2 |

## File Structure

```
ksf_FA_TravelExpense/
├── FA_TE_Module.php       # Module class with permissions
├── hooks.php             # FA lifecycle hooks
├── te.php                # API controller
├── _init/
│   └── init.inc         # Module initialization
├── includes/
│   ├── import.php       # Import functionality
│   ├── te_db.inc      # Database functions
│   └── te_ui.inc      # UI helpers
├── pages/
│   ├── dashboard.php  # Dashboard view
│   ├── suppliers.php  # Supplier CRUD
│   ├── trips.php      # Trip CRUD
│   ├── expenses.php  # Expense CRUD
│   ├── reports.php    # Reporting
│   └── settings.php  # Settings
└── sql/
    ├── install.sql     # Schema creation
    └── uninstall.sql # Schema removal
```

## License

This module is part of the ksf collection for FrontAccounting.
See individual components for license terms.
