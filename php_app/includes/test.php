<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Load environment variables from .env file located in the same directory
require 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Check if environment variables are set
echo "PGHOST: " . getenv('PGHOST') . "\n";
echo "PGUSER: " . getenv('PGUSER') . "\n";
echo "PGPASSWORD: " . getenv('PGPASSWORD') . "\n";
echo "PGDATABASE: " . getenv('PGDATABASE') . "\n";
echo "PGPORT: " . getenv('PGPORT') . "\n";

// Database connection configuration
function getDbConnection() {
    // Get environment variables or use defaults
    $host = getenv('PGHOST') ?: 'localhost';
    $user = getenv('PGUSER') ?: 'postgres';
    $password = getenv('PGPASSWORD') ?: 'go4megct';
    $dbname = getenv('PGDATABASE') ?: 'booksl_train';
    $port = getenv('PGPORT') ?: '5432';

    try {
        // Create a new PDO instance for PostgreSQL connection
        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;";
        $conn = new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        return $conn;
    } catch (PDOException $e) {
        // Log the error and return null
        error_log('Database Connection Error: ' . $e->getMessage());
        return null;
    }
}

// Check if connection is successful before using it
$conn = getDbConnection();
if ($conn) {
    echo "connected!";
} else {
    echo "failed!";
}

// Function to check if database connection is available
function isDatabaseAvailable() {
    $conn = getDbConnection();
    if ($conn) {
        try {
            $conn->query('SELECT 1');
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
    return false;
}
?>