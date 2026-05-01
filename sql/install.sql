-- Travel Expense module database schema for FrontAccounting

CREATE TABLE IF NOT EXISTS `fa_travel_requests` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `employee_id` INT(11) NOT NULL,
    `project_id` VARCHAR(20) DEFAULT NULL,
    `task_id` VARCHAR(20) DEFAULT NULL,
    `purpose` VARCHAR(255) NOT NULL,
    `destination` VARCHAR(255) DEFAULT NULL,
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `status` ENUM('Pending','Approved','Rejected','InProgress','Completed','Paid') NOT NULL DEFAULT 'Pending',
    `estimated_cost` DECIMAL(10,2) DEFAULT NULL,
    `actual_cost` DECIMAL(10,2) DEFAULT NULL,
    `per_diem_rate` DECIMAL(10,2) DEFAULT NULL,
    `approved_by` INT(11) DEFAULT NULL,
    `approved_at` DATETIME DEFAULT NULL,
    `rejected_by` INT(11) DEFAULT NULL,
    `rejected_at` DATETIME DEFAULT NULL,
    `rejection_reason` TEXT DEFAULT NULL,
    `created_by` INT(11) DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `employee_id` (`employee_id`),
    KEY `project_id` (`project_id`),
    KEY `task_id` (`task_id`),
    KEY `status` (`status`),
    KEY `dates` (`start_date`, `end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `fa_travel_expenses` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `travel_id` INT(11) NOT NULL,
    `expense_type` ENUM(
        'Hotel', 'Meals_Breakfast', 'Meals_Lunch', 'Meals_Dinner',
        'Car_Rental', 'Taxi', 'Bus', 'Train', 'Plane',
        'Mileage', 'Parking', 'Tolls', 'Phone', 'Internet', 'Other'
    ) NOT NULL DEFAULT 'Other',
    `amount` DECIMAL(10,2) NOT NULL,
    `gl_code` VARCHAR(30) DEFAULT NULL,
    `project_id` VARCHAR(20) DEFAULT NULL,
    `task_id` VARCHAR(20) DEFAULT NULL,
    `activity_code` VARCHAR(30) DEFAULT NULL,
    `vendor` VARCHAR(100) DEFAULT NULL,
    `vendor_type` ENUM('Hotel','Car_Rental','Taxi','Restaurant','Other') DEFAULT NULL,
    `description` VARCHAR(255) DEFAULT NULL,
    `receipt_path` VARCHAR(500) DEFAULT NULL,
    `date` DATE DEFAULT NULL,
    `billable` TINYINT(1) DEFAULT 1,
    `reimbursed` TINYINT(1) DEFAULT 0,
    `mileage_miles` DECIMAL(10,2) DEFAULT NULL,
    `mileage_rate` DECIMAL(5,4) DEFAULT NULL,
    `status` ENUM('Pending','Approved','Rejected','Reimbursed') NOT NULL DEFAULT 'Pending',
    PRIMARY KEY (`id`),
    KEY `travel_id` (`travel_id`),
    KEY `project_id` (`project_id`),
    KEY `task_id` (`task_id`),
    KEY `activity_code` (`activity_code`),
    KEY `expense_type` (`expense_type`),
    KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Per diem rules by location
CREATE TABLE IF NOT EXISTS `fa_travel_per_diem` (
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
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_location` (`city`, `country`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Activity codes for project billing
CREATE TABLE IF NOT EXISTS `fa_travel_activity_codes` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `code` VARCHAR(30) NOT NULL,
    `description` VARCHAR(255) NOT NULL,
    `gl_code` VARCHAR(30) DEFAULT NULL,
    `billable_rate` DECIMAL(10,2) DEFAULT NULL,
    `active` TINYINT(1) DEFAULT 1,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Suppliers/vendors for travel
CREATE TABLE IF NOT EXISTS `fa_travel_suppliers` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `service_type` ENUM('Hotel','Car_Rental','Taxi','Restaurant','Transit','Other') NOT NULL,
    `contact_name` VARCHAR(100) DEFAULT NULL,
    `contact_phone` VARCHAR(30) DEFAULT NULL,
    `contact_email` VARCHAR(100) DEFAULT NULL,
    `website` VARCHAR(255) DEFAULT NULL,
    `rate_code` VARCHAR(30) DEFAULT NULL,
    `preference_order` INT(11) DEFAULT 1,
    `corporate_rate` TINYINT(1) DEFAULT 0,
    `notes` TEXT DEFAULT NULL,
    `inactive` TINYINT(1) DEFAULT 0,
    PRIMARY KEY (`id`),
    KEY `service_type` (`service_type`),
    KEY `preference_order` (`preference_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `fa_modules` (`name`, `version`, `enabled`, `installed`) VALUES ('TravelExpense', '1.1.0', 1, NOW()) ON DUPLICATE KEY UPDATE `version` = '1.1.0';