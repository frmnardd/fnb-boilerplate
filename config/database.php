<?php
declare(strict_types=1);

/**
 * FnB Boilerplate - Database Connection (PDO Singleton)
 * Supports MySQL 8.0+ / MariaDB with automatic SQLite fallback for local preview.
 */

final class Database
{
    private static ?Database $instance = null;
    private ?PDO $pdo = null;
    private string $activeDriver = 'mysql';
    private array $config = [];

    private function __construct()
    {
        $configFile = __DIR__ . '/env.php';
        if (file_exists($configFile)) {
            $env = require $configFile;
        } else {
            $env = require __DIR__ . '/env.example.php';
        }

        $this->config = $env['database'] ?? [];
        $driver = $this->config['driver'] ?? 'mysql';
        $autoFallback = (bool)($this->config['auto_fallback'] ?? true);

        if ($driver === 'sqlite') {
            $this->connectSqlite();
        } else {
            try {
                $this->connectMysql();
            } catch (PDOException $e) {
                if ($autoFallback) {
                    error_log('[Database] MySQL connection failed (' . $e->getMessage() . '). Falling back to SQLite.');
                    $this->connectSqlite();
                } else {
                    throw $e;
                }
            }
        }
    }

    private function connectMysql(): void
    {
        $host = $this->config['host'] ?? '127.0.0.1';
        $port = $this->config['port'] ?? 3306;
        $database = $this->config['database'] ?? 'fnb_boilerplate';
        $username = $this->config['username'] ?? 'root';
        $password = $this->config['password'] ?? '';
        $charset = $this->config['charset'] ?? 'utf8mb4';

        $dsn = "mysql:host={$host};port={$port};dbname={$database};charset={$charset}";

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$charset} COLLATE {$charset}_unicode_ci",
        ];

        $this->pdo = new PDO($dsn, $username, $password, $options);
        $this->activeDriver = 'mysql';
    }

    private function connectSqlite(): void
    {
        $sqlitePath = $this->config['sqlite_path'] ?? (dirname(__DIR__) . '/database/fnb.sqlite');
        $dir = dirname($sqlitePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $isNewDatabase = !file_exists($sqlitePath) || filesize($sqlitePath) === 0;

        $dsn = "sqlite:{$sqlitePath}";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        $this->pdo = new PDO($dsn, null, null, $options);
        $this->pdo->exec('PRAGMA foreign_keys = ON;');
        $this->activeDriver = 'sqlite';

        // Auto-initialize schema & seed demo data if this is a newly created SQLite file
        if ($isNewDatabase) {
            $this->initializeSqlite();
        }
    }

    public function initializeSqlite(): void
    {
        $schemaFile = dirname(__DIR__) . '/database/schema_sqlite.sql';
        $seedFile = dirname(__DIR__) . '/database/seed_demo.sql';

        if (file_exists($schemaFile)) {
            $sql = file_get_contents($schemaFile);
            $this->pdo->exec($sql);
        }

        if (file_exists($seedFile)) {
            $sql = file_get_contents($seedFile);
            $this->pdo->exec($sql);
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    public function getActiveDriver(): string
    {
        return $this->activeDriver;
    }

    /**
     * Prevent cloning of singleton
     */
    private function __clone() {}

    /**
     * Prevent unserialize of singleton
     */
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }
}

/**
 * Global helper function to get PDO connection instance
 */
function db(): PDO
{
    return Database::getInstance()->getConnection();
}
