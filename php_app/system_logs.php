<?php
require_once 'includes/db_connection.php';
require_once 'includes/layout.php';

// Page title and identifier
$title = 'System Logs - BookSL Train Management System';
$currentPage = 'systemLogs';

// Function to create the logs table if it doesn't exist
function createLogsTableIfNotExists() {
    $conn = getDbConnection();
    if (!$conn) {
        return false;
    }

    try {
        // Check if the table exists
        $checkTableSql = "
            SELECT EXISTS (
                SELECT FROM information_schema.tables 
                WHERE table_schema = 'public' 
                AND table_name = 'system_logs'
            );
        ";
        $stmt = $conn->query($checkTableSql);
        $tableExists = $stmt->fetchColumn();

        if (!$tableExists) {
            // Create the logs table
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
            
            // Log the table creation
            $logSql = "
                INSERT INTO system_logs (
                    log_type, operation, query_details, parameters, result_status, user_id, ip_address
                ) VALUES (
                    'SYSTEM', 'TABLE_CREATION', 'Created system_logs table', NULL, 'SUCCESS', 'system', ?
                );
            ";
            $stmt = $conn->prepare($logSql);
            $stmt->execute([$_SERVER['REMOTE_ADDR'] ?? 'unknown']);
            
            return true;
        }
        
        return true;
    } catch (PDOException $e) {
        error_log('Error creating logs table: ' . $e->getMessage());
        return false;
    }
}

// Function to get logs with filtering
function getLogs($filters = []) {
    $conn = getDbConnection();
    if (!$conn) {
        return [];
    }

    try {
        $whereClauses = [];
        $params = [];
        
        // Apply filters
        if (!empty($filters['log_type'])) {
            $whereClauses[] = "log_type = ?";
            $params[] = $filters['log_type'];
        }
        
        if (!empty($filters['operation'])) {
            $whereClauses[] = "operation LIKE ?";
            $params[] = '%' . $filters['operation'] . '%';
        }
        
        if (!empty($filters['date_from'])) {
            $whereClauses[] = "created_at >= ?";
            $params[] = $filters['date_from'] . ' 00:00:00';
        }
        
        if (!empty($filters['date_to'])) {
            $whereClauses[] = "created_at <= ?";
            $params[] = $filters['date_to'] . ' 23:59:59';
        }
        
        if (!empty($filters['result_status'])) {
            $whereClauses[] = "result_status = ?";
            $params[] = $filters['result_status'];
        }
        
        // Build the query
        $sql = "SELECT * FROM system_logs";
        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(" AND ", $whereClauses);
        }
        $sql .= " ORDER BY created_at DESC LIMIT 1000";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('Error fetching logs: ' . $e->getMessage());
        return [];
    }
}


// Process form submission for filtering
$filters = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['log_type']) && !empty($_POST['log_type'])) {
        $filters['log_type'] = $_POST['log_type'];
    }
    
    if (isset($_POST['operation']) && !empty($_POST['operation'])) {
        $filters['operation'] = $_POST['operation'];
    }
    
    if (isset($_POST['date_from']) && !empty($_POST['date_from'])) {
        $filters['date_from'] = $_POST['date_from'];
    }
    
    if (isset($_POST['date_to']) && !empty($_POST['date_to'])) {
        $filters['date_to'] = $_POST['date_to'];
    }
    
    if (isset($_POST['result_status']) && !empty($_POST['result_status'])) {
        $filters['result_status'] = $_POST['result_status'];
    }
}

// Create the logs table if it doesn't exist
$tableCreated = createLogsTableIfNotExists();

// Get logs with applied filters
$logs = getLogs($filters);

// Generate test logs if requested
if (isset($_GET['generate_test_logs']) && $_GET['generate_test_logs'] === 'true') {
    $conn = getDbConnection(false);
    if ($conn) {
        // Generate some test logs
        logDatabaseActivity($conn, 'QUERY', 'SELECT', 'SELECT * FROM trains');
        logDatabaseActivity($conn, 'QUERY', 'INSERT', 'INSERT INTO train_journeys VALUES (...)', '{"train_id": 101, "journey_date": "2025-05-01"}');
        logDatabaseActivity($conn, 'QUERY', 'UPDATE', 'UPDATE train_schedules SET scheduled_time = ? WHERE train_id = ?', '{"scheduled_time": "08:30:00", "train_id": 102}');
        logDatabaseActivity($conn, 'QUERY', 'DELETE', 'DELETE FROM train_journeys WHERE journey_id = ?', '{"journey_id": 5}', 'FAILURE', 'Foreign key constraint violation');
        logDatabaseActivity($conn, 'CONNECTION', 'CONNECT', 'Database connection established');
    }
    
    // Redirect to remove the query parameter
    header('Location: system_logs.php');
    exit;
}

// Start building the page content
ob_start();
?>

<div class="p-6 bg-dashboard-panel m-4 rounded-lg shadow-lg">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-dashboard-header">System Logs</h1>
        <div>
            <a href="system_logs.php?generate_test_logs=true" class="bg-dashboard-blue text-white px-4 py-2 rounded hover:bg-blue-600 transition">Generate Test Logs</a>
        </div>
    </div>
    
    <!-- Filter Form -->
    <div class="mb-6 bg-gray-800 p-4 rounded-lg">
        <h2 class="text-lg font-medium text-dashboard-header mb-4">Filter Logs</h2>
        <form method="POST" action="system_logs.php" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="log_type" class="block text-sm font-medium text-dashboard-subtext mb-1">Log Type</label>
                <select id="log_type" name="log_type" class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-dashboard-text">
                    <option value="">All Types</option>
                    <option value="QUERY" <?= isset($filters['log_type']) && $filters['log_type'] === 'QUERY' ? 'selected' : '' ?>>Query</option>
                    <option value="CONNECTION" <?= isset($filters['log_type']) && $filters['log_type'] === 'CONNECTION' ? 'selected' : '' ?>>Connection</option>
                    <option value="SYSTEM" <?= isset($filters['log_type']) && $filters['log_type'] === 'SYSTEM' ? 'selected' : '' ?>>System</option>
                </select>
            </div>
            
            <div>
                <label for="operation" class="block text-sm font-medium text-dashboard-subtext mb-1">Operation</label>
                <input type="text" id="operation" name="operation" value="<?= isset($filters['operation']) ? htmlspecialchars($filters['operation']) : '' ?>" class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-dashboard-text" placeholder="e.g. SELECT, INSERT">
            </div>
            
            <div>
                <label for="result_status" class="block text-sm font-medium text-dashboard-subtext mb-1">Result Status</label>
                <select id="result_status" name="result_status" class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-dashboard-text">
                    <option value="">All Statuses</option>
                    <option value="SUCCESS" <?= isset($filters['result_status']) && $filters['result_status'] === 'SUCCESS' ? 'selected' : '' ?>>Success</option>
                    <option value="FAILURE" <?= isset($filters['result_status']) && $filters['result_status'] === 'FAILURE' ? 'selected' : '' ?>>Failure</option>
                </select>
            </div>
            
            <div>
                <label for="date_from" class="block text-sm font-medium text-dashboard-subtext mb-1">Date From</label>
                <input type="date" id="date_from" name="date_from" value="<?= isset($filters['date_from']) ? htmlspecialchars($filters['date_from']) : '' ?>" class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-dashboard-text">
            </div>
            
            <div>
                <label for="date_to" class="block text-sm font-medium text-dashboard-subtext mb-1">Date To</label>
                <input type="date" id="date_to" name="date_to" value="<?= isset($filters['date_to']) ? htmlspecialchars($filters['date_to']) : '' ?>" class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-dashboard-text">
            </div>
            
            <div class="flex items-end">
                <button type="submit" class="bg-dashboard-purple text-white px-4 py-2 rounded hover:bg-purple-700 transition">Apply Filters</button>
                <a href="system_logs.php" class="ml-2 text-dashboard-subtext hover:text-dashboard-text px-4 py-2">Reset</a>
            </div>
        </form>
    </div>
    
    <!-- Logs Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full bg-gray-800 rounded-lg overflow-hidden">
            <thead class="bg-gray-700">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-dashboard-header uppercase tracking-wider">ID</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-dashboard-header uppercase tracking-wider">Type</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-dashboard-header uppercase tracking-wider">Operation</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-dashboard-header uppercase tracking-wider">Query Details</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-dashboard-header uppercase tracking-wider">Parameters</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-dashboard-header uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-dashboard-header uppercase tracking-wider">Timestamp</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                <?php if (empty($logs)): ?>
                <tr>
                    <td colspan="7" class="px-4 py-4 text-center text-dashboard-subtext">
                        <?php if ($tableCreated): ?>
                            No logs found. Use the "Generate Test Logs" button to create sample logs.
                        <?php else: ?>
                            Unable to create or access the logs table. Please check database connection.
                        <?php endif; ?>
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                    <tr class="hover:bg-gray-700">
                        <td class="px-4 py-3 text-sm text-dashboard-text"><?= htmlspecialchars($log['log_id']) ?></td>
                        <td class="px-4 py-3 text-sm">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                <?php 
                                switch ($log['log_type']) {
                                    case 'QUERY':
                                        echo 'bg-blue-800 text-blue-100';
                                        break;
                                    case 'CONNECTION':
                                        echo 'bg-green-800 text-green-100';
                                        break;
                                    case 'SYSTEM':
                                        echo 'bg-purple-800 text-purple-100';
                                        break;
                                    default:
                                        echo 'bg-gray-800 text-gray-100';
                                }
                                ?>">
                                <?= htmlspecialchars($log['log_type']) ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-dashboard-text"><?= htmlspecialchars($log['operation']) ?></td>
                        <td class="px-4 py-3 text-sm text-dashboard-text">
                            <div class="max-w-xs truncate" title="<?= htmlspecialchars($log['query_details']) ?>">
                                <?= htmlspecialchars($log['query_details']) ?>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm text-dashboard-text">
                            <?php if ($log['parameters']): ?>
                                <div class="max-w-xs truncate" title="<?= htmlspecialchars($log['parameters']) ?>">
                                    <?= htmlspecialchars($log['parameters']) ?>
                                </div>
                            <?php else: ?>
                                <span class="text-dashboard-subtext">None</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                <?= $log['result_status'] === 'SUCCESS' ? 'bg-green-800 text-green-100' : 'bg-red-800 text-red-100' ?>">
                                <?= htmlspecialchars($log['result_status']) ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-dashboard-text"><?= htmlspecialchars($log['created_at']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
// Render the layout with the content
$content = ob_get_clean();
echo renderLayout($title, $currentPage, $content);
?>
