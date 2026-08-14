<?php
if (!defined("BASE_URL")) {
    define("BASE_URL", "/stlaf_wip_supplies_ledger/");
}

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Requires: composer require vlucas/phpdotenv
// (see the no-dependency fallback in chat if you'd rather not add the package)
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

class Database
{
    private $conn;

    public function __construct()
    {
        $databaseUrl = $_ENV['DATABASE_URL'] ?? getenv('DATABASE_URL');

        if (!$databaseUrl) {
            die("Connection failed: DATABASE_URL is not set in .env");
        }

        $parts = parse_url($databaseUrl);
        if ($parts === false || !isset($parts['host'])) {
            die("Connection failed: DATABASE_URL is malformed");
        }

        $host   = $parts['host'];
        $port   = $parts['port'] ?? 5432;
        $dbname = isset($parts['path']) ? ltrim($parts['path'], '/') : 'postgres';
        $user   = isset($parts['user']) ? rawurldecode($parts['user']) : '';
        $pass   = isset($parts['pass']) ? rawurldecode($parts['pass']) : '';

        $dsn = "pgsql:host={$host};port={$port};dbname={$dbname};sslmode=require";

        try {
            $this->conn = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            die("Connection failed (PDO pgsql): " . $e->getMessage());
        }
    }

    public function getConnection()
    {
        return $this->conn;
    }
}