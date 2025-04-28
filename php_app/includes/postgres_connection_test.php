<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "PostgreSQL Connection Test\n";
echo "=========================\n\n";

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
    
} catch (PDOException $e) {
    echo "PDO Connection: FAILED\n";
    echo "Error: " . $e->getMessage() . "\n\n";
}

// Test connection using pg_connect
echo "Testing connection using pg_connect:\n";
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

// Test connection using socket
echo "\nTesting connection using Unix socket (peer authentication):\n";
try {
    $dsn = "pgsql:dbname=$dbname";
    $conn = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    echo "Socket Connection: SUCCESS\n";
} catch (PDOException $e) {
    echo "Socket Connection: FAILED\n";
    echo "Error: " . $e->getMessage() . "\n";
}

// Test connection using psql command
echo "\nTesting connection using psql command:\n";
echo "1. With password authentication:\n";
$command = "PGPASSWORD=$password psql -U $user -h $host -d $dbname -c \"SELECT 1 as connection_test;\" 2>&1";
$output = shell_exec($command);
if (strpos($output, 'connection_test') !== false) {
    echo "psql with password: SUCCESS\n";
    echo "Output: " . trim($output) . "\n";
} else {
    echo "psql with password: FAILED\n";
    echo "Output: " . trim($output) . "\n";
}

echo "\n2. Without password (peer authentication):\n";
$command = "psql -U $user -d $dbname -c \"SELECT 1 as connection_test;\" 2>&1";
$output = shell_exec($command);
if (strpos($output, 'connection_test') !== false) {
    echo "psql without password: SUCCESS\n";
    echo "Output: " . trim($output) . "\n";
} else {
    echo "psql without password: FAILED\n";
    echo "Output: " . trim($output) . "\n";
}

echo "\n=========================\n";
echo "Connection Troubleshooting Guide\n";
echo "=========================\n\n";

echo "If you're experiencing connection issues with PostgreSQL, here are some common solutions:\n\n";

echo "1. Password Authentication Issues:\n";
echo "   - Ensure you're using the correct password for the PostgreSQL user\n";
echo "   - When connecting with psql, use the -W flag to force password prompt or set PGPASSWORD environment variable\n";
echo "   - Example: PGPASSWORD=yourpassword psql -U postgres -h localhost -d booksl_train\n\n";

echo "2. Peer Authentication Issues:\n";
echo "   - When connecting locally without specifying a host, PostgreSQL uses peer authentication\n";
echo "   - This requires that your operating system username matches the PostgreSQL username\n";
echo "   - To use password authentication instead, specify the host explicitly: psql -h localhost\n\n";

echo "3. PostgreSQL Configuration:\n";
echo "   - The authentication methods are defined in pg_hba.conf\n";
echo "   - You may need to modify this file to change authentication methods\n";
echo "   - Location: /etc/postgresql/17/main/pg_hba.conf\n\n";

echo "4. Connection Methods:\n";
echo "   - Socket connection (peer authentication): psql -U postgres\n";
echo "   - TCP/IP connection (password authentication): psql -h localhost -U postgres\n\n";

echo "5. PHP Applications:\n";
echo "   - PHP applications typically use password authentication via PDO or pg_connect\n";
echo "   - Ensure your connection parameters in PHP match your PostgreSQL configuration\n\n";
?>
