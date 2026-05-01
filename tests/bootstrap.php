<?php
/**
 * Bootstrap for TravelExpense Tests
 */

if (!defined('TB_PREF')) define('TB_PREF', 'fa_');
if (!defined('TB_DB')) define('TB_DB', 'fa_company_db');

// Use FAMock for FA function stubs
require_once __DIR__ . '/../vendor/ksfraser/famock/php/FAMock.php';

// Mock global $db that includes expected methods
class MockDB {
    public $insert_id = 0;
    public $last_query = '';
    private $nextInsertId = 1;
    private $queries = [];
    private $results = [];
    
    public function __construct() {
        $this->nextInsertId = 1;
    }
    
    public function escape($val) {
        return "'" . addslashes($val) . "'";
    }
    
    public function query($sql) {
        $this->last_query = $sql;
        $this->queries[] = $sql;
        
        if (stripos($sql, 'INSERT') !== false) {
            $this->insert_id = $this->nextInsertId++;
        }
        
        $result = new stdClass();
        $result->_sql = $sql;
        return $result;
    }
    
    public function num_rows($result) {
        return 0;
    }
    
    public function fetch($result) {
        return null;
    }
}

// Global mock DB
$db = new MockDB();

// Mock user_id
function user_id() {
    return 1;
}