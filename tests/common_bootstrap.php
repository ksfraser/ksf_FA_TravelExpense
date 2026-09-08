<?php
/**
 * Common Test Bootstrap for KS FA Modules
 * 
 * Supports switching between:
 * - FAMock (default in CI/dev)
 * - Real MariaDB connection (when UAT credentials provided)
 * - ksf_ModulesDAO adapter
 */

namespace Ksfraser\FA;

// Detect environment and load appropriate DB layer
class TestDbFactory {
    
    private static $db = null;
    private static $mode = 'mock';
    
    const MODE_MOCK = 'mock';
    const MODE_UAT = 'uat';
    const MODE_LOCAL = 'local';
    
    /**
     * Initialize DB connection based on environment
     */
    public static function init(): void {
        // Check for UAT environment variables
        $hasUatCreds = getenv('UAT_DB_HOST') && getenv('UAT_DB_USER');
        
        if ($hasUatCreds) {
            self::$mode = self::MODE_UAT;
            self::initUat();
        } else {
            self::$mode = self::MODE_MOCK;
            self::initMock();
        }
    }
    
    /**
     * Get current mode
     */
    public static function getMode(): string {
        return self::$mode;
    }
    
    /**
     * Initialize FAMock
     */
    private static function initMock(): void {
        // Load FAMock if available
        $famockPath = __DIR__ . '/../vendor/ksfraser/famock/php/FAMock.php';
        if (file_exists($famockPath)) {
            require_once $famockPath;
        }
        
        // Set up mock $db global
        global $db;
        if (!$db) {
            $db = new MockDatabase();
        }
        self::$db = $db;
    }
    
    /**
     * Initialize UAT MariaDB connection
     */
    private static function initUat(): void {
        $host = getenv('UAT_DB_HOST');
        $port = getenv('UAT_DB_PORT') ?: 3306;
        $dbname = getenv('UAT_DB_NAME');
        $user = getenv('UAT_DB_USER');
        $pass = getenv('UAT_DB_PASS');
        
        try {
            // Try PDO first (modern)
            $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
            self::$db = new \PDO($dsn, $user, $pass, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
            ]);
            self::$mode = self::MODE_UAT;
        } catch (\PDOException $e) {
            // Fall back to mysqli if PDO fails
            self::$db = new \mysqli($host, $user, $pass, $dbname, $port);
            if (self::$db->connect_error) {
                throw new \RuntimeException("UAT DB connection failed: " . self::$db->connect_error);
            }
            self::$mode = self::MODE_UAT;
        }
        
        // Make available as global $db
        global $db;
        $db = self::$db;
    }
    
    /**
     * Get DB connection (mock or real)
     */
    public static function getDb() {
        if (self::$db === null) {
            self::init();
        }
        return self::$db;
    }
    
    /**
     * Query wrapper that works with both mock and real DB
     */
    public static function query(string $sql) {
        $db = self::getDb();
        
        if (self::$mode === self::MODE_MOCK) {
            return db_query($sql);
        }
        
        if ($db instanceof \PDO) {
            return $db->query($sql);
        }
        
        if ($db instanceof \mysqli) {
            return $db->query($sql);
        }
        
        return false;
    }
    
    /**
     * Escape wrapper
     */
    public static function escape(string $value): string {
        $db = self::getDb();
        
        if (self::$mode === self::MODE_MOCK) {
            return db_escape($value);
        }
        
        if ($db instanceof \PDO) {
            return $db->quote($value);
        }
        
        if ($db instanceof \mysqli) {
            return $db->real_escape_string($value);
        }
        
        return addslashes($value);
    }
    
    /**
     * Last insert ID
     */
    public static function insertId(): int {
        $db = self::getDb();
        
        if (self::$mode === self::MODE_MOCK) {
            return db_insert_id();
        }
        
        if ($db instanceof \PDO) {
            return (int)$db->lastInsertId();
        }
        
        if ($db instanceof \mysqli) {
            return (int)$db->insert_id;
        }
        
        return 0;
    }
}

/**
 * MockDatabase class for when FAMock isn't available
 */
class MockDatabase {
    public $insert_id = 0;
    public $queries = [];
    private $nextInsertId = 1;
    
    public function escape($val) {
        return "'" . addslashes($val) . "'";
    }
    
    public function query($sql) {
        $this->queries[] = $sql;
        if (stripos($sql, 'INSERT') !== false) {
            $this->insert_id = $this->nextInsertId++;
        }
        $result = new \stdClass();
        $result->_sql = $sql;
        return $result;
    }
    
    public function num_rows($result) { return 0; }
    public function fetch($result) { return null; }
}

// Auto-initialize
TestDbFactory::init();