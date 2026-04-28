-- Travel Expense module database schema for FrontAccounting

CREATE TABLE IF NOT EXISTS `fa_travel_requests` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `employee_id` INT(11) NOT NULL,
    `purpose` VARCHAR(255) NOT NULL,
    `destination` VARCHAR(255) DEFAULT NULL,
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `status` ENUM('Pending','Approved','Rejected','Paid') NOT NULL DEFAULT 'Pending',
    `estimated_cost` DECIMAL(10,2) DEFAULT NULL,
    `actual_cost` DECIMAL(10,2) DEFAULT NULL,
    `approved_by` INT(11) DEFAULT NULL,
    `approved_at` DATETIME DEFAULT NULL,
    `created_by` INT(11) DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `employee_id` (`employee_id`),
    KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `fa_travel_expenses` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `travel_id` INT(11) NOT NULL,
    `expense_type` ENUM('Transport','Accommodation','Meals','Other') NOT NULL DEFAULT 'Other',
    `amount` DECIMAL(10,2) NOT NULL,
    `description` VARCHAR(255) DEFAULT NULL,
    `receipt_path` VARCHAR(500) DEFAULT NULL,
    `date` DATE DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `travel_id` (`travel_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `fa_modules` (`name`, `version`, `enabled`, `installed`) VALUES ('TravelExpense', '1.0.0', 1, NOW()) ON DUPLICATE KEY UPDATE `version` = '1.0.0';