<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "Testing PostgreSQL Connection\n";
echo "----------------------------\n\n";

// Display connection parameters
echo "Connection Parameters:\n";
$host = getenv('PGHOST') ?: 'localhost';
$user = getenv('PGUSER') ?: 'postgres';
$password = getenv('PGPASSWORD') ?: 'postgres';
$dbname = getenv('PGDATABASE') ?: 'booksl_train';
$port = getenv('PGPORT') ?: '5432';

echo "Host: $host\n";
echo "User: $user\n";
echo "Password: " . str_repeat('*', strlen($password)) . "\n";
echo "Database: $dbname\n";
echo "Port: $port\n\n";

// Test connection using PDO
echo "Testing connection using PDO:\n";
try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;";
    $conn = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    echo "PDO Connection: SUCCESS\n";
    
    // Get PostgreSQL version
    $stmt = $conn->query("SELECT version()");
    $version = $stmt->fetchColumn();
    echo "PostgreSQL Version: " . $version . "\n\n";
    
    // Get database name
    $stmt = $conn->query("SELECT current_database()");
    $dbname = $stmt->fetchColumn();
    echo "Current Database: " . $dbname . "\n\n";
    
    // Get list of tables
    $stmt = $conn->query("SELECT table_name FROM information_schema.tables WHERE table_schema='public' ORDER BY table_name");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Tables in the database:\n";
    if (count($tables) > 0) {
        foreach ($tables as $table) {
            echo "- " . $table . "\n";
        }
    } else {
        echo "No tables found in the public schema.\n";
    }
    
} catch (PDOException $e) {
    echo "PDO Connection: FAILED\n";
    echo "Error: " . $e->getMessage() . "\n\n";
}

// Test connection using pg_connect
echo "\nTesting connection using pg_connect:\n";
if (function_exists('pg_connect')) {
    $conn_string = "host=$host port=$port dbname=$dbname user=$user password=$password";
    $conn = @pg_connect($conn_string);
    
    if ($conn) {
        echo "pg_connect: SUCCESS\n";
        pg_close($conn);
    } else {
        echo "pg_connect: FAILED\n";
        echo "Error: " . pg_last_error() . "\n";
    }
} else {
    echo "pg_connect: NOT AVAILABLE (pg_connect function does not exist)\n";
}

// Include the database connection file
echo "\nTesting connection using db_connection.php:\n";
require_once 'db_connection.php';

// Get a database connection
$conn = getDbConnection();

if ($conn) {
    echo "db_connection.php: SUCCESS\n";
} else {
    echo "db_connection.php: FAILED\n";
}
?>
