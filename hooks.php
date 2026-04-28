<?php
/**
 * TravelExpense Module for FrontAccounting
 */

$module_id = 'TravelExpense';
$module_version = '1.0.0';

$module_name = 'Travel & Expense';
$module_description = 'Travel request and expense tracking';

$module_tables = ['fa_travel_requests', 'fa_travel_expenses'];

$module_capabilities = [
    'SA_TRAVELVIEW' => 'View Travel Requests',
    'SA_TRAVELCREATE' => 'Create Travel Requests',
    'SA_TRAVELAPPROVE' => 'Approve Travel',
    'SA_TRAVELEXpenses' => 'Manage Expenses',
];

function travel_install(): bool { return install_module_sql('TravelExpense'); }
function travel_enable(): bool { return enable_module('TravelExpense'); }
function travel_disable(): bool { return disable_module('TravelExpense'); }
function travel_remove(): bool { return remove_module_sql('TravelExpense'); }

add_module($module_name, $module_version, $module_description);