<?php
/**
 * Database Interceptor
 * 
 * This file provides functions to intercept and log database operations
 */

require_once __DIR__ . '/db_connection.php';

/**
 * Execute a SQL query with logging
 * 
 * @param PDO $conn Database connection
 * @param string $query SQL query
 * @param array|null $params Query parameters
 * @return PDOStatement|false PDO statement
 */
function executeQuery($conn, $query, $params = null) {
    $startTime = microtime(true);
    $queryType = determineQueryType($query);
    
    try {
        if ($params === null) {
            // Direct query
            $stmt = $conn->query($query);
        } else {
            // Prepared statement
            $stmt = $conn->prepare($query);
            $stmt->execute($params);
        }
        
        $endTime = microtime(true);
        $executionTime = round(($endTime - $startTime) * 1000, 2); // in milliseconds
        
        // Log the successful query
        logDatabaseActivity(
            $conn,
            'QUERY',
            $queryType,
            $query,
            $params,
            'SUCCESS',
            "Execution time: {$executionTime}ms"
        );
        
        return $stmt;
    } catch (PDOException $e) {
        // Log the failed query
        logDatabaseActivity(
            $conn,
            'QUERY',
            $queryType,
            $query,
            $params,
            'FAILURE',
            $e->getMessage()
        );
        
        // Re-throw the exception
        throw $e;
    }
}

/**
 * Execute a SQL statement that doesn't return a result set
 * 
 * @param PDO $conn Database connection
 * @param string $query SQL query
 * @param array|null $params Query parameters
 * @return int|false Number of affected rows
 */
function executeStatement($conn, $query, $params = null) {
    $startTime = microtime(true);
    $queryType = determineQueryType($query);
    
    try {
        if ($params === null) {
            // Direct execution
            $result = $conn->exec($query);
        } else {
            // Prepared statement
            $stmt = $conn->prepare($query);
            $stmt->execute($params);
            $result = $stmt->rowCount();
        }
        
        $endTime = microtime(true);
        $executionTime = round(($endTime - $startTime) * 1000, 2); // in milliseconds
        
        // Log the successful execution
        logDatabaseActivity(
            $conn,
            'QUERY',
            $queryType,
            $query,
            $params,
            'SUCCESS',
            "Execution time: {$executionTime}ms"
        );
        
        return $result;
    } catch (PDOException $e) {
        // Log the failed execution
        logDatabaseActivity(
            $conn,
            'QUERY',
            $queryType,
            $query,
            $params,
            'FAILURE',
            $e->getMessage()
        );
        
        // Re-throw the exception
        throw $e;
    }
}

/**
 * Determine the type of SQL query
 * 
 * @param string $query SQL query
 * @return string Query type (SELECT, INSERT, UPDATE, DELETE, etc.)
 */
function determineQueryType($query) {
    $query = trim($query);
    
    if (preg_match('/^SELECT\s/i', $query)) {
        return 'SELECT';
    } elseif (preg_match('/^INSERT\s/i', $query)) {
        return 'INSERT';
    } elseif (preg_match('/^UPDATE\s/i', $query)) {
        return 'UPDATE';
    } elseif (preg_match('/^DELETE\s/i', $query)) {
        return 'DELETE';
    } elseif (preg_match('/^CREATE\s/i', $query)) {
        return 'CREATE';
    } elseif (preg_match('/^ALTER\s/i', $query)) {
        return 'ALTER';
    } elseif (preg_match('/^DROP\s/i', $query)) {
        return 'DROP';
    } elseif (preg_match('/^TRUNCATE\s/i', $query)) {
        return 'TRUNCATE';
    } else {
        return 'OTHER';
    }
}

/**
 * Get a database connection with logging
 * 
 * @return PDO|null PDO connection
 */
function getLoggingDbConnection() {
    return getDbConnection(true);
}
?>
