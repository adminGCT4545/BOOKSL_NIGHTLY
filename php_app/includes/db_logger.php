<?php
/**
 * Database Logger
 * 
 * This file provides functions and classes to log database operations
 */

/**
 * Log a database operation
 * 
 * @param string $logType Type of log (QUERY, CONNECTION, SYSTEM)
 * @param string $operation Operation being performed (SELECT, INSERT, UPDATE, DELETE, CONNECT, etc.)
 * @param string $queryDetails Details of the query or operation
 * @param array|null $parameters Parameters used in the query
 * @param string $resultStatus Status of the operation (SUCCESS, FAILURE)
 * @param string|null $errorMessage Error message if operation failed
 * @return bool Whether logging was successful
 */
function logDatabaseOperation($logType, $operation, $queryDetails, $parameters = null, $resultStatus = 'SUCCESS', $errorMessage = null) {
    // Get database connection
    $conn = getDbConnection(false); // Pass false to avoid recursive logging
    if (!$conn) {
        error_log("Failed to log database operation: No database connection");
        return false;
    }

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
            $queryDetails,
            $parameters ? json_encode($parameters) : null,
            $resultStatus,
            $errorMessage,
            'admin', // Hardcoded for now, could be from session
            $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
        
        return true;
    } catch (PDOException $e) {
        error_log('Error logging database operation: ' . $e->getMessage());
        return false;
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
 * Extended PDO class with logging capabilities
 */
class LoggingPDO extends PDO {
    /**
     * Constructor
     */
    public function __construct($dsn, $username, $password, $options = []) {
        parent::__construct($dsn, $username, $password, $options);
        
        // Log the successful connection
        $this->logConnection($dsn);
    }
    
    /**
     * Log the database connection
     */
    private function logConnection($dsn) {
        try {
            // Check if the system_logs table exists
            $checkTableSql = "
                SELECT EXISTS (
                    SELECT FROM information_schema.tables 
                    WHERE table_schema = 'public' 
                    AND table_name = 'system_logs'
                );
            ";
            $stmt = parent::query($checkTableSql);
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
                parent::exec($createTableSql);
            }

            // Insert the connection log directly
            $logSql = "
                INSERT INTO system_logs (
                    log_type, operation, query_details, result_status, user_id, ip_address
                ) VALUES (
                    'CONNECTION', 'CONNECT', ?, 'SUCCESS', 'admin', ?
                );
            ";
            $stmt = parent::prepare($logSql);
            $stmt->execute(["Connected to database: $dsn", $_SERVER['REMOTE_ADDR'] ?? 'unknown']);
        } catch (PDOException $e) {
            // Just log to error_log if we can't log to the database
            error_log("Could not log connection: " . $e->getMessage());
        }
    }
    
    /**
     * Override prepare method to log queries
     */
    public function prepare(string $query, array $options = []): PDOStatement|false {
        $stmt = parent::prepare($query, $options);
        if ($stmt === false) {
            return false;
        }
        return new LoggingPDOStatement($stmt, $query);
    }
    
    /**
     * Override query method to log direct queries
     */
    public function query(string $query, ?int $fetchMode = null, mixed ...$fetchModeArgs): PDOStatement|false {
        $startTime = microtime(true);
        
        try {
            // Execute the original method
            $result = ($fetchMode === null) 
                ? parent::query($query) 
                : parent::query($query, $fetchMode, ...$fetchModeArgs);
                
            if ($result === false) {
                return false;
            }
            
            $endTime = microtime(true);
            $executionTime = round(($endTime - $startTime) * 1000, 2); // in milliseconds
            
            // Log the successful query
            logDatabaseOperation(
                'QUERY',
                determineQueryType($query),
                $query,
                null,
                'SUCCESS',
                "Execution time: {$executionTime}ms"
            );
            
            return $result;
        } catch (PDOException $e) {
            // Log the failed query
            logDatabaseOperation(
                'QUERY',
                determineQueryType($query),
                $query,
                null,
                'FAILURE',
                $e->getMessage()
            );
            
            // Re-throw the exception
            throw $e;
        }
    }
    
    /**
     * Override exec method to log direct executions
     */
    public function exec(string $query): int|false {
        $startTime = microtime(true);
        
        try {
            // Execute the original method
            $result = parent::exec($query);
            $endTime = microtime(true);
            $executionTime = round(($endTime - $startTime) * 1000, 2); // in milliseconds
            
            // Log the successful execution
            logDatabaseOperation(
                'QUERY',
                determineQueryType($query),
                $query,
                null,
                'SUCCESS',
                "Execution time: {$executionTime}ms"
            );
            
            return $result;
        } catch (PDOException $e) {
            // Log the failed execution
            logDatabaseOperation(
                'QUERY',
                determineQueryType($query),
                $query,
                null,
                'FAILURE',
                $e->getMessage()
            );
            
            // Re-throw the exception
            throw $e;
        }
    }
}

/**
 * Wrapper for PDOStatement with logging capabilities
 */
class LoggingPDOStatement implements IteratorAggregate {
    private PDOStatement $stmt;
    private string $query;
    
    /**
     * Constructor
     */
    public function __construct(PDOStatement $stmt, string $query) {
        $this->stmt = $stmt;
        $this->query = $query;
    }
    
    /**
     * Execute the statement with logging
     */
    public function execute(?array $params = null): bool {
        $startTime = microtime(true);
        
        try {
            // Execute the original method
            $result = $this->stmt->execute($params);
            $endTime = microtime(true);
            $executionTime = round(($endTime - $startTime) * 1000, 2); // in milliseconds
            
            // Log the successful query
            logDatabaseOperation(
                'QUERY',
                determineQueryType($this->query),
                $this->query,
                $params,
                'SUCCESS',
                "Execution time: {$executionTime}ms"
            );
            
            return $result;
        } catch (PDOException $e) {
            // Log the failed query
            logDatabaseOperation(
                'QUERY',
                determineQueryType($this->query),
                $this->query,
                $params,
                'FAILURE',
                $e->getMessage()
            );
            
            // Re-throw the exception
            throw $e;
        }
    }
    
    /**
     * Implement IteratorAggregate interface
     */
    public function getIterator(): Traversable {
        return $this->stmt;
    }
    
    /**
     * Forward all other method calls to the original statement
     */
    public function __call(string $method, array $args) {
        return call_user_func_array([$this->stmt, $method], $args);
    }
    
    // Explicitly implement common PDOStatement methods
    
    public function fetch(int $mode = PDO::FETCH_DEFAULT, int $cursorOrientation = PDO::FETCH_ORI_NEXT, int $cursorOffset = 0) {
        return $this->stmt->fetch($mode, $cursorOrientation, $cursorOffset);
    }
    
    public function fetchAll(int $mode = PDO::FETCH_DEFAULT, mixed ...$args) {
        return $this->stmt->fetchAll($mode, ...$args);
    }
    
    public function fetchColumn(int $column = 0) {
        return $this->stmt->fetchColumn($column);
    }
    
    public function bindParam(mixed $param, mixed &$var, int $type = PDO::PARAM_STR, int $maxLength = 0, mixed $driverOptions = null): bool {
        return $this->stmt->bindParam($param, $var, $type, $maxLength, $driverOptions);
    }
    
    public function bindValue(mixed $param, mixed $value, int $type = PDO::PARAM_STR): bool {
        return $this->stmt->bindValue($param, $value, $type);
    }
    
    public function rowCount(): int {
        return $this->stmt->rowCount();
    }
    
    public function closeCursor(): bool {
        return $this->stmt->closeCursor();
    }
    
    public function columnCount(): int {
        return $this->stmt->columnCount();
    }
    
    public function getColumnMeta(int $column): array|false {
        return $this->stmt->getColumnMeta($column);
    }
    
    public function setFetchMode(int $mode, mixed ...$args): bool {
        return $this->stmt->setFetchMode($mode, ...$args);
    }
    
    public function nextRowset(): bool {
        return $this->stmt->nextRowset();
    }
    
    public function debugDumpParams(): ?bool {
        return $this->stmt->debugDumpParams();
    }
    
    public function errorCode(): ?string {
        return $this->stmt->errorCode();
    }
    
    public function errorInfo(): array {
        return $this->stmt->errorInfo();
    }
    
    public function setAttribute(int $attribute, mixed $value): bool {
        return $this->stmt->setAttribute($attribute, $value);
    }
    
    public function getAttribute(int $attribute): mixed {
        return $this->stmt->getAttribute($attribute);
    }
}

/**
 * Create a logging-enabled PDO instance
 * 
 * @param string $dsn DSN connection string
 * @param string $username Database username
 * @param string $password Database password
 * @param array $options PDO options
 * @return LoggingPDO PDO instance with logging
 */
function createLoggingPDO($dsn, $username, $password, $options = []) {
    try {
        // Create the LoggingPDO instance
        return new LoggingPDO($dsn, $username, $password, $options);
    } catch (PDOException $e) {
        // Log the failed connection
        logDatabaseOperation(
            'CONNECTION',
            'CONNECT',
            "Failed to connect to database: $dsn",
            null,
            'FAILURE',
            $e->getMessage()
        );
        
        // Re-throw the exception
        throw $e;
    }
}
?>
