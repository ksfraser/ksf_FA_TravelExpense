<?php
/**
 * FA_TravelExpense Module Hooks for FrontAccounting
 */

define('SS_TRAVELEXPENSE', 141 << 8);

class hooks_ksf_FA_TravelExpense extends hooks {

    private function ensure_composer_dependencies(): void {
        $module_dir = dirname(__FILE__);
        $autoload_path = $module_dir . '/vendor/autoload.php';
        
        if (!file_exists($autoload_path)) {
            $composer_path = $module_dir . '/composer.json';
            if (file_exists($composer_path)) {
                chdir($module_dir);
                $output = [];
                $return_code = 0;
                exec('composer install --no-interaction --prefer-dist 2>&1', $output, $return_code);
                if ($return_code !== 0) {
                    error_log('KSF Module: composer install failed: ' . implode("\n", $output));
                }
            }
        }
    }

    function install_options($app) {
        global $path_to_root;

        switch($app->id) {
            case 'HR':
                $app->add_lapp_function(0, _("Travel Requests"),
                    $path_to_root."/modules/".$this->module_name."/requests.php", 'SA_TRAVELVIEW', MENU_ENTRY);
                $app->add_lapp_function(1, _("Create Request"),
                    $path_to_root."/modules/".$this->module_name."/create.php", 'SA_TRAVELCREATE', MENU_ENTRY);
                $app->add_lapp_function(2, _("Expenses"),
                    $path_to_root."/modules/".$this->module_name."/expenses.php", 'SA_TRAVELEXPENSES', MENU_ENTRY);
                $app->add_rapp_function(3, _("Approve Travel"),
                    $path_to_root."/modules/".$this->module_name."/approve.php", 'SA_TRAVELAPPROVE', MENU_INQUIRY);
                break;
        }
    }

    function install_access() {
        $security_sections[SS_TRAVELEXPENSE] = _("Travel & Expense Management");
        $security_areas['SA_TRAVELVIEW'] = array(SS_TRAVELEXPENSE | 1, _("View Travel Requests"));
        $security_areas['SA_TRAVELCREATE'] = array(SS_TRAVELEXPENSE | 2, _("Create Travel Requests"));
        $security_areas['SA_TRAVELAPPROVE'] = array(SS_TRAVELEXPENSE | 3, _("Approve Travel"));
        $security_areas['SA_TRAVELEXPENSES'] = array(SS_TRAVELEXPENSE | 4, _("Manage Expenses"));
        return array($security_areas, $security_sections);
    }

    function install_extension($check_only=true) {
        return true;
    }

    function install_tabs($app) {
    }

    function activate_extension($company, $check_only=true) {
        $updates = array('sql/update.sql' => array($this->module_name));
        $ok = $this->update_databases($company, $updates, $check_only);
        if ($check_only || !$ok) {
            return $ok;
        }
        $this->ensure_travelexpense_schema();
        return $ok;
    }

    private function table_exists($table) {
        $sql = "SHOW TABLES LIKE " . db_escape($table);
        $res = db_query($sql, 'Failed checking table existence');
        return db_num_rows($res) > 0;
    }

    private function ensure_travelexpense_schema() {
        $tables = array(
            TB_PREF . "fa_travel_requests" => "
                CREATE TABLE IF NOT EXISTS `" . TB_PREF . "fa_travel_requests` (
                    `id` INT(11) NOT NULL AUTO_INCREMENT,
                    `request_number` VARCHAR(30) NOT NULL,
                    `employee_id` VARCHAR(100) NOT NULL,
                    `destination` VARCHAR(100) DEFAULT NULL,
                    `purpose` TEXT,
                    `start_date` DATE NOT NULL,
                    `end_date` DATE NOT NULL,
                    `estimated_cost` DECIMAL(15,2) DEFAULT 0,
                    `status` VARCHAR(20) DEFAULT 'Pending',
                    `approved_by` VARCHAR(100) DEFAULT NULL,
                    `approved_at` DATETIME DEFAULT NULL,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `idx_request_number` (`request_number`),
                    KEY `idx_employee` (`employee_id`),
                    KEY `idx_status` (`status`),
                    KEY `idx_dates` (`start_date`, `end_date`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            TB_PREF . "fa_travel_expenses" => "
                CREATE TABLE IF NOT EXISTS `" . TB_PREF . "fa_travel_expenses` (
                    `id` INT(11) NOT NULL AUTO_INCREMENT,
                    `request_id` INT(11) NOT NULL,
                    `expense_type` VARCHAR(20) DEFAULT 'Meal',
                    `amount` DECIMAL(15,2) NOT NULL,
                    `expense_date` DATE NOT NULL,
                    `description` TEXT,
                    `receipt_path` VARCHAR(500) DEFAULT NULL,
                    `status` VARCHAR(20) DEFAULT 'Pending',
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    KEY `idx_request` (`request_id`),
                    KEY `idx_date` (`expense_date`),
                    KEY `idx_status` (`status`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        foreach ($tables as $table_name => $sql) {
            db_query($sql, "Could not create Travel Expense table: $table_name");
        }
    }

    function db_prevoid($trans_type, $trans_no) {
        // Handle voiding if needed
    }
}
?>
