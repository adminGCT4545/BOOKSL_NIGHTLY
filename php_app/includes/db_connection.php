
<?php
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
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
*/

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database connection configuration
function getDbConnection($enableLogging = true) {
    // Get environment variables or use defaults
    $host = getenv('PGHOST') ?: 'localhost';
    $user = getenv('PGUSER') ?: 'postgres';
    $password = getenv('PGPASSWORD') ?: 'postgres';
    $dbname = getenv('PGDATABASE') ?: 'booksl_train';
    $port = getenv('PGPORT') ?: '5432';

    try {
        // Create a new PDO instance for PostgreSQL connection
        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        $conn = new PDO($dsn, $user, $password, $options);
        
        // Log the connection if logging is enabled
        if ($enableLogging) {
            logDatabaseActivity($conn, 'CONNECTION', 'CONNECT', "Connected to database: $dsn");
        }
        
        return $conn;
    } catch (PDOException $e) {
        // Log the error message instead of displaying it
        error_log("Database connection error: " . $e->getMessage());
        return null;
    }
}

/**
 * Log a database activity
 * 
 * @param PDO $conn Database connection
 * @param string $logType Type of log (QUERY, CONNECTION, SYSTEM)
 * @param string $operation Operation being performed (SELECT, INSERT, UPDATE, DELETE, CONNECT, etc.)
 * @param string $details Details of the operation
 * @param array|null $parameters Parameters used in the query
 * @param string $status Status of the operation (SUCCESS, FAILURE)
 * @param string|null $errorMessage Error message if operation failed
 * @return bool Whether logging was successful
 */
function logDatabaseActivity($conn, $logType, $operation, $details, $parameters = null, $status = 'SUCCESS', $errorMessage = null) {
    try {
        // Check if the system_logs table exists
        $checkTableSql = "
            SELECT EXISTS (
                SELECT FROM information_schema.tables 
                WHERE table_schema = 'public' 
                AND table_name = 'system_logs'
            );
        ";
        $stmt = $conn->query($checkTableSql);
        $tableExists = $stmt->fetchColumn();

        // Create the table if it doesn't exist
        if (!$tableExists) {
            $createTableSql = "
                CREATE TABLE system_logs (
                    log_id SERIAL PRIMARY KEY,
                    log_type VARCHAR(50) NOT NULL,
                    operation VARCHAR(255) NOT NULL,
                    query_details TEXT,
                    parameters TEXT,
                    result_status VARCHAR(50),
                    error_message TEXT,
                    user_id VARCHAR(100),
                    ip_address VARCHAR(45),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                );
            ";
            $conn->exec($createTableSql);
        }

        // Insert the log entry
        $sql = "
            INSERT INTO system_logs (
                log_type, operation, query_details, parameters, result_status, error_message, user_id, ip_address
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?
            );
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            $logType,
            $operation,
            $details,
            $parameters ? json_encode($parameters) : null,
            $status,
            $errorMessage,
            'admin', // Hardcoded for now, could be from session
            $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
        
        return true;
    } catch (PDOException $e) {
        error_log('Error logging database activity: ' . $e->getMessage());
        return false;
    }
}

// Check if connection is successful before using it
$conn = getDbConnection();
// Commented out echo statements that were causing issues with page rendering
// if ($conn) {
//     echo "Connected to PostgreSQL successfully!";
// } else {
//     echo "Failed to connect to PostgreSQL!";
// }

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
