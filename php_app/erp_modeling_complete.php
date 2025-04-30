<?php
// Include necessary files
require_once 'includes/db_connection.php';
require_once 'includes/data_service.php';
require_once 'includes/layout.php';

// Get database connection
$conn = getDbConnection();

// Function to get database schema information
function getDatabaseSchema($conn) {
    if (!$conn) {
        return [
            'tables' => [],
            'relationships' => []
        ];
    }

    try {
        // Get tables
        $tableQuery = "
            SELECT 
                table_name 
            FROM 
                information_schema.tables 
            WHERE 
                table_schema = 'public' 
            ORDER BY 
                table_name
        ";
        
        $tableStmt = $conn->query($tableQuery);
        $tables = [];
        
        while ($row = $tableStmt->fetch()) {
            $tableName = $row['table_name'];
            
            // Get columns for this table
            $columnQuery = "
                SELECT 
                    column_name, 
                    data_type,
                    is_nullable,
                    column_default
                FROM 
                    information_schema.columns 
                WHERE 
                    table_schema = 'public' 
                    AND table_name = :table_name
                ORDER BY 
                    ordinal_position
            ";
            
            $columnStmt = $conn->prepare($columnQuery);
            $columnStmt->bindParam(':table_name', $tableName);
            $columnStmt->execute();
            
            $columns = [];
            while ($columnRow = $columnStmt->fetch()) {
                $columns[] = [
                    'name' => $columnRow['column_name'],
                    'type' => $columnRow['data_type'],
                    'nullable' => $columnRow['is_nullable'] === 'YES',
                    'default' => $columnRow['column_default']
                ];
            }
            
            $tables[$tableName] = $columns;
        }
        
        // Get foreign key relationships
        $relationshipQuery = "
            SELECT
                tc.table_name, 
                kcu.column_name, 
                ccu.table_name AS foreign_table_name,
                ccu.column_name AS foreign_column_name 
            FROM 
                information_schema.table_constraints AS tc 
                JOIN information_schema.key_column_usage AS kcu
                  ON tc.constraint_name = kcu.constraint_name
                  AND tc.table_schema = kcu.table_schema
                JOIN information_schema.constraint_column_usage AS ccu
                  ON ccu.constraint_name = tc.constraint_name
                  AND ccu.table_schema = tc.table_schema
            WHERE 
                tc.constraint_type = 'FOREIGN KEY' 
                AND tc.table_schema = 'public'
        ";
        
        $relationshipStmt = $conn->query($relationshipQuery);
        $relationships = [];
        
        while ($row = $relationshipStmt->fetch()) {
            $relationships[] = [
                'table' => $row['table_name'],
                'column' => $row['column_name'],
                'foreign_table' => $row['foreign_table_name'],
                'foreign_column' => $row['foreign_column_name']
            ];
        }
        
        return [
            'tables' => $tables,
            'relationships' => $relationships
        ];
    } catch (PDOException $e) {
        error_log('Error getting database schema: ' . $e->getMessage());
        return [
            'tables' => [],
            'relationships' => []
        ];
    }
}

// Function to get data usage statistics
function getDataUsageStats($conn) {
    if (!$conn) {
        return [
            'row_counts' => [],
            'query_patterns' => []
        ];
    }

    try {
        // Get row counts for each table
        $rowCountQuery = "
            SELECT
                relname as table_name,
                n_live_tup as row_count
            FROM
                pg_stat_user_tables
            ORDER BY
                n_live_tup DESC
        ";
        
        $rowCountStmt = $conn->query($rowCountQuery);
        $rowCounts = [];
        
        while ($row = $rowCountStmt->fetch()) {
            $rowCounts[$row['table_name']] = $row['row_count'];
        }
        
        // Get query patterns (simplified for demo)
        $queryPatterns = [
            [
                'description' => 'Train Schedule Lookup',
                'frequency' => 'High',
                'tables' => ['trains', 'train_schedules'],
                'sample_query' => 'SELECT t.train_name, s.scheduled_time FROM trains t JOIN train_schedules s ON t.train_id = s.train_id'
            ],
            [
                'description' => 'Journey Revenue Analysis',
                'frequency' => 'Medium',
                'tables' => ['train_journeys'],
                'sample_query' => 'SELECT departure_city, arrival_city, SUM(revenue) FROM train_journeys GROUP BY departure_city, arrival_city'
            ],
            [
                'description' => 'Occupancy Rate Calculation',
                'frequency' => 'High',
                'tables' => ['train_journeys'],
                'sample_query' => 'SELECT train_id, journey_date, (reserved_seats::float / total_seats) * 100 AS occupancy_rate FROM train_journeys'
            ],
            [
                'description' => 'Delay Analysis',
                'frequency' => 'Medium',
                'tables' => ['train_journeys', 'train_schedules'],
                'sample_query' => 'SELECT j.train_id, j.journey_date, j.is_delayed, s.default_delay_minutes FROM train_journeys j JOIN train_schedules s ON j.train_id = s.train_id WHERE j.is_delayed = true'
            ]
        ];
        
        return [
            'row_counts' => $rowCounts,
            'query_patterns' => $queryPatterns
        ];
    } catch (PDOException $e) {
        error_log('Error getting data usage stats: ' . $e->getMessage());
        return [
            'row_counts' => [],
            'query_patterns' => []
        ];
    }
}

// Get schema and usage statistics
$schema = getDatabaseSchema($conn);
$usageStats = getDataUsageStats($conn);

// Start building the page content
ob_start();
?>

<div class="p-4">
    <div class="mb-4 px-2 flex justify-between items-center">
        <h1 class="text-xl font-medium text-dashboard-header">ERP Modeling</h1>
        <div class="flex items-center space-x-4">
            <button 
                type="button" 
                class="bg-dashboard-purple hover:bg-purple-700 text-white font-bold py-1 px-4 rounded text-sm"
                onclick="window.print()"
            >
                Print Model
            </button>
        </div>
    </div>
    
    <!-- ERP Modeling Content -->
    <div class="grid grid-cols-1 gap-6">
        <!-- Introduction -->
        <div class="bg-dashboard-panel rounded shadow p-4">
            <h2 class="text-dashboard-header text-lg mb-4">ERP Modeling Based on PostgreSQL Data</h2>
            <p class="text-dashboard-text mb-3">
                This page outlines a structured approach for leveraging your existing PostgreSQL database for future endeavors in an ERP console. 
                The model is based on analysis of your current database schema, data patterns, and business processes.
            </p>
        </div>
        
        <!-- Data Extraction and Analysis -->
        <div class="bg-dashboard-panel rounded shadow p-4">
            <h2 class="text-dashboard-header text-lg mb-4">1. Data Extraction and Analysis</h2>
            
            <!-- Database Schema -->
            <div class="mb-6">
                <h3 class="text-dashboard-light-purple text-md mb-3">Database Schema</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-dashboard-dark rounded">
                        <thead>
                            <tr class="border-b border-gray-700 text-left text-dashboard-subtext">
                                <th class="py-2 px-4">Table</th>
                                <th class="py-2 px-4">Columns</th>
                                <th class="py-2 px-4">Row Count</th>
                                <th class="py-2 px-4">Primary Purpose</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($schema['tables'] as $tableName => $columns): ?>
                                <tr class="border-b border-gray-700 text-dashboard-text">
                                    <td class="py-2 px-4 font-medium"><?= htmlspecialchars($tableName) ?></td>
                                    <td class="py-2 px-4">
                                        <?php 
                                        $columnNames = array_map(function($col) {
                                            return $col['name'] . ' (' . $col['type'] . ')';
                                        }, $columns);
                                        echo htmlspecialchars(implode(', ', $columnNames));
                                        ?>
                                    </td>
                                    <td class="py-2 px-4">
                                        <?= isset($usageStats['row_counts'][$tableName]) ? htmlspecialchars($usageStats['row_counts'][$tableName]) : 'N/A' ?>
                                    </td>
                                    <td class="py-2 px-4">
                                        <?php
                                        switch ($tableName) {
                                            case 'trains':
                                                echo 'Store train master data';
                                                break;
                                            case 'train_schedules':
                                                echo 'Define regular train schedules';
                                                break;
                                            case 'train_journeys':
                                                echo 'Track individual journey details and metrics';
                                                break;
                                            default:
                                                echo 'Supporting data';
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Relationships -->
            <div class="mb-6">
                <h3 class="text-dashboard-light-purple text-md mb-3">Key Relationships</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-dashboard-dark rounded">
                        <thead>
                            <tr class="border-b border-gray-700 text-left text-dashboard-subtext">
                                <th class="py-2 px-4">Source Table</th>
                                <th class="py-2 px-4">Source Column</th>
                                <th class="py-2 px-4">Target Table</th>
                                <th class="py-2 px-4">Target Column</th>
                                <th class="py-2 px-4">Relationship Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($schema['relationships'] as $relationship): ?>
                                <tr class="border-b border-gray-700 text-dashboard-text">
                                    <td class="py-2 px-4"><?= htmlspecialchars($relationship['table']) ?></td>
                                    <td class="py-2 px-4"><?= htmlspecialchars($relationship['column']) ?></td>
                                    <td class="py-2 px-4"><?= htmlspecialchars($relationship['foreign_table']) ?></td>
                                    <td class="py-2 px-4"><?= htmlspecialchars($relationship['foreign_column']) ?></td>
                                    <td class="py-2 px-4">
                                        <?php
                                        if ($relationship['table'] === 'train_schedules' && $relationship['foreign_table'] === 'trains') {
                                            echo 'One-to-One';
                                        } elseif ($relationship['table'] === 'train_journeys' && $relationship['foreign_table'] === 'trains') {
                                            echo 'Many-to-One';
                                        } else {
                                            echo 'Foreign Key';
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Data Usage Patterns -->
            <div>
                <h3 class="text-dashboard-light-purple text-md mb-3">Data Usage Patterns</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-dashboard-dark rounded">
                        <thead>
                            <tr class="border-b border-gray-700 text-left text-dashboard-subtext">
                                <th class="py-2 px-4">Pattern</th>
                                <th class="py-2 px-4">Frequency</th>
                                <th class="py-2 px-4">Tables Involved</th>
                                <th class="py-2 px-4">ERP Relevance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usageStats['query_patterns'] as $pattern): ?>
                                <tr class="border-b border-gray-700 text-dashboard-text">
                                    <td class="py-2 px-4"><?= htmlspecialchars($pattern['description']) ?></td>
                                    <td class="py-2 px-4"><?= htmlspecialchars($pattern['frequency']) ?></td>
                                    <td class="py-2 px-4"><?= htmlspecialchars(implode(', ', $pattern['tables'])) ?></td>
                                    <td class="py-2 px-4">
                                        <?php
                                        switch ($pattern['description']) {
                                            case 'Train Schedule Lookup':
                                                echo 'Resource scheduling module';
                                                break;
                                            case 'Journey Revenue Analysis':
                                                echo 'Financial reporting module';
                                                break;
                                            case 'Occupancy Rate Calculation':
                                                echo 'Capacity planning module';
                                                break;
                                            case 'Delay Analysis':
                                                echo 'Performance management module';
                                                break;
                                            default:
                                                echo 'General operations';
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Business Process Mapping -->
        <div class="bg-dashboard-panel rounded shadow p-4">
            <h2 class="text-dashboard-header text-lg mb-4">2. Business Process Mapping</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <!-- Current Processes -->
                <div class="bg-dashboard-dark rounded p-4">
                    <h3 class="text-dashboard-light-purple text-md mb-3">Current Business Processes</h3>
                    <ul class="list-disc pl-5 text-dashboard-text">
                        <li class="mb-2">
                            <span class="font-medium">Train Management</span>
                            <p class="text-dashboard-subtext text-sm">Managing train master data and basic attributes</p>
                        </li>
                        <li class="mb-2">
                            <span class="font-medium">Schedule Management</span>
                            <p class="text-dashboard-subtext text-sm">Setting and maintaining regular train schedules</p>
                        </li>
                        <li class="mb-2">
                            <span class="font-medium">Journey Tracking</span>
                            <p class="text-dashboard-subtext text-sm">Recording individual journey details including occupancy and revenue</p>
                        </li>
                        <li class="mb-2">
                            <span class="font-medium">Delay Monitoring</span>
                            <p class="text-dashboard-subtext text-sm">Tracking train delays and performance metrics</p>
                        </li>
                        <li class="mb-2">
                            <span class="font-medium">Revenue Reporting</span>
                            <p class="text-dashboard-subtext text-sm">Analyzing revenue by train, route, and class</p>
                        </li>
                    </ul>
                </div>
                
                <!-- Process Gaps -->
                <div class="bg-dashboard-dark rounded p-4">
                    <h3 class="text-dashboard-light-purple text-md mb-3">Identified Process Gaps</h3>
                    <ul class="list-disc pl-5 text-dashboard-text">
                        <li class="mb-2">
                            <span class="font-medium">Inventory Management</span>
                            <p class="text-dashboard-subtext text-sm">No tracking of train maintenance parts and supplies</p>
                        </li>
                        <li class="mb-2">
                            <span class="font-medium">Staff Management</span>
                            <p class="text-dashboard-subtext text-sm">No integration with crew scheduling and management</p>
                        </li>
                        <li class="mb-2">
                            <span class="font-medium">Maintenance Scheduling</span>
                            <p class="text-dashboard-subtext text-sm">No systematic tracking of maintenance schedules and history</p>
                        </li>
                        <li class="mb-2">
                            <span class="font-medium">Customer Relationship</span>
                            <p class="text-dashboard-subtext text-sm">Limited passenger data and no loyalty program integration</p>
                        </li>
                        <li class="mb-2">
                            <span class="font-medium">Procurement Process</span>
                            <p class="text-dashboard-subtext text-sm">No integration with purchasing and vendor management</p>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Process Flow Diagram -->
            <div class="mb-6">
                <h3 class="text-dashboard-light-purple text-md mb-3">Process Flow Diagram</h3>
                <div class="bg-dashboard-dark rounded p-4 text-center">
                    <div class="mb-4 text-dashboard-subtext text-sm">Current Process Flow</div>
                    <div class="flex flex-wrap justify-center items-center">
                        <div class="bg-blue-800 rounded p-3 m-2 w-40">
                            <div class="text-white font-medium">Train Setup</div>
                        </div>
                        <div class="text-dashboard-subtext">→</div>
                        <div class="bg-blue-800 rounded p-3 m-2 w-40">
                            <div class="text-white font-medium">Schedule Creation</div>
                        </div>
                        <div class="text-dashboard-subtext">→</div>
                        <div class="bg-blue-800 rounded p-3 m-2 w-40">
                            <div class="text-white font-medium">Journey Planning</div>
                        </div>
                        <div class="text-dashboard-subtext">→</div>
                        <div class="bg-blue-800 rounded p-3 m-2 w-40">
                            <div class="text-white font-medium">Ticket Sales</div>
                        </div>
                        <div class="text-dashboard-subtext">→</div>
                        <div class="bg-blue-800 rounded p-3 m-2 w-40">
                            <div class="text-white font-medium">Journey Execution</div>
                        </div>
                        <div class="text-dashboard-subtext">→</div>
                        <div class="bg-blue-800 rounded p-3 m-2 w-40">
                            <div class="text-white font-medium">Performance Analysis</div>
                        </div>
                    </div>
                    
                    <div class="mt-8 mb-4 text-dashboard-subtext text-sm">Future ERP Process Flow</div>
                    <div class="flex flex-wrap justify-center items-center">
                        <div class="bg-purple-800 rounded p-3 m-2 w-40">
                            <div class="text-white font-medium">Resource Planning</div>
                        </div>
                        <div class="text-dashboard-subtext">→</div>
                        <div class="bg-purple-800 rounded p-3 m-2 w-40">
                            <div class="text-white font-medium">Maintenance Planning</div>
                        </div>
                        <div class="text-dashboard-subtext">→</div>
                        <div class="bg-purple-800 rounded p-3 m-2 w-40">
                            <div class="text-white font-medium">Schedule Optimization</div>
                        </div>
                        <div class="text-dashboard-subtext">→</div>
                        <div class="bg-purple-800 rounded p-3 m-2 w-40">
                            <div class="text-white font-medium">Integrated Sales</div>
                        </div>
                        <div class="text-dashboard-subtext">→</div>
                        <div class="bg-purple-800 rounded p-3 m-2 w-40">
                            <div class="text-white font-medium">Operations Management</div>
                        </div>
                        <div class="text-dashboard-subtext">→</div>
                        <div class="bg-purple-800 rounded p-3 m-2 w-40">
                            <div class="text-white font-medium">Business Intelligence</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Data Model Transformation -->
        <div class="bg-dashboard-panel rounded shadow p-4">
            <h2 class="text-dashboard-header text-lg mb-4">3. Data Model Transformation</h2>
            
            <!-- Current vs. Future Data Model -->
            <div class="mb-6">
                <h3 class="text-dashboard-light-purple text-md mb-3">Current vs. Future Data Model</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-dashboard-dark rounded p-4">
                        <h4 class="text-dashboard-header text-md mb-3">Current PostgreSQL Model</h4>
                        <div class="text-dashboard-text">
                            <div class="mb-3">
                                <div class="font-medium">trains</div>
                                <div class="text-dashboard-subtext text-sm pl-4">train_id (PK), train_name</div>
                            </div>
                            <div class="mb-3">
                                <div class="font-medium">train_schedules</div>
                                <div class="text-dashboard-subtext text-sm pl-4">train_id (PK, FK), scheduled_time, default_delay_minutes</div>
                            </div>
                            <div class="mb-3">
                                <div class="font-medium">train_journeys</div>
                                <div class="text-dashboard-subtext text-sm pl-4">journey_id (PK), train_id (FK), departure_city, arrival_city, journey_date, class, total_seats, reserved_seats, is_delayed, revenue</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-dashboard-dark rounded p-4">
                        <h4 class="text-dashboard-header text-md mb-3">Future ERP Data Model</h4>
                        <div class="text-dashboard-text">
                            <div class="mb-3">
                                <div class="font-medium">Asset Master</div>
                                <div class="text-dashboard-subtext text-sm pl-4">asset_id (PK), asset_type, asset_name, acquisition_date, status, maintenance_schedule_id</div>
                            </div>
                            <div class="mb-3">
                                <div class="font-medium">Route Master</div>
                                <div class="text-dashboard-subtext text-sm pl-4">route_id (PK), origin, destination, distance, estimated_duration</div>
                            </div>
                            <div class="mb-3">
                                <div class="font-medium">Schedule Master</div>
                                <div class="text-dashboard-subtext text-sm pl-4">schedule_id (PK), asset_id (FK), route_id (FK), departure_time, arrival_time, frequency</div>
                            </div>
                            <div class="mb-3">
                                <div class="font-medium">Trip Transactions</div>
                                <div class="text-dashboard-subtext text-sm pl-4">trip_id (PK), schedule_id (FK), trip_date, actual_departure, actual_arrival, status_id, staff_id</div>
                            </div>
                            <div class="mb-3">
                                <div class="font-medium">Sales Transactions</div>
                                <div class="text-dashboard-subtext text-sm pl-4">sale_id (PK), trip_id (FK), customer_id (FK), ticket_class, seat_number, price, discount, payment_method</div>
                            </div>
                            <div class="mb-3">
                                <div class="font-medium">Maintenance Records</div>
                                <div class="text-dashboard-subtext text-sm pl-4">maintenance_id (PK), asset_id (FK), maintenance_type, scheduled_date, completion_date, cost, vendor_id</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Entity Relationship Diagram -->
            <div class="mb-6">
                <h3 class="text-dashboard-light-purple text-md mb-3">Entity Relationship Diagram</h3>
                <div class="bg-dashboard-dark rounded p-4 text-center">
                    <div class="text-dashboard-subtext mb-2">Future ERP Entity Relationship Diagram</div>
                    <div class="text-dashboard-text text-sm">
                        <p>The diagram illustrates the relationships between key entities in the proposed ERP system.</p>
                        <p>Asset Master is connected to Maintenance Records and Schedule Master.</p>
                        <p>Route Master connects to Schedule Master, which links to Trip Transactions.</p>
                        <p>Trip Transactions connect to Sales Transactions, which link to Customer Master.</p>
                        <p>Staff Master connects to Trip Transactions, and Vendor Master links to Maintenance Records.</p>
                    </div>
                </div>
            </div>
            
            <!-- Data Migration Path -->
            <div>
                <h3 class="text-dashboard-light-purple text-md mb-3">Data Migration Path</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-dashboard-dark rounded">
                        <thead>
                            <tr class="border-b border-gray-700 text-left text-dashboard-subtext">
                                <th class="py-2 px-4">Source (PostgreSQL)</th>
                                <th class="py-2 px-4">Target (ERP)</th>
                                <th class="py-2 px-4">Transformation Required</th>
                                <th class="py-2 px-4">Migration Complexity</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-b border-gray-700 text-dashboard-text">
                                <td class="py-2 px-4">trains</td>
                                <td class="py-2 px-4">Asset Master</td>
                                <td class="py-2 px-4">Add asset type, acquisition date, status fields</td>
                                <td class="py-2 px-4">Low</td>
                            </tr>
                            <tr class="border-b border-gray-700 text-dashboard-text">
                                <td class="py-2 px-4">train_schedules</td>
                                <td class="py-2 px-4">Schedule Master</td>
                                <td class="py-2 px-4">Create route records, link schedules to routes</td>
                                <td class="py-2 px-4">Medium</td>
                            </tr>
                            <tr class="border-b border-gray-700 text-dashboard-text">
                                <td class="py-2 px-4">train_journeys</td>
                                <td class="py-2 px-4">Trip Transactions + Sales Transactions</td>
                                <td class="py-2 px-4">Split journey data into trip and sales records</td>
                                <td class="py-2 px-4">High</td>
                            </tr>
                            <tr class="border-b border-gray-700 text-dashboard-text">
                                <td class="py-2 px-4">N/A</td>
                                <td class="py-2 px-4">Maintenance Records</td>
                                <td class="py-2 px-4">Create new maintenance history (no source data)</td>
                                <td class="py-2 px-4">High</td>
                            </tr>
                            <tr class="border-b border-gray-700 text-dashboard-text">
                                <td class="py-2 px-4">N/A</td>
                                <td class="py-2 px-4">Customer Master</td>
                                <td class="py-2 px-4">Create new customer records (no source data)</td>
                                <td class="py-2 px-4">High</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Module Configuration -->
        <div class="bg-dashboard-panel rounded shadow p-4">
            <h2 class="text-dashboard-header text-lg mb-4">4. Module Configuration</h2>
            
            <!-- ERP Modules -->
            <div class="mb-6">
                <h3 class="text-dashboard-light-purple text-md mb-3">ERP Modules Configuration</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-dashboard-dark rounded p-4">
                        <h4 class="text-dashboard-header text-md mb-2">Asset Management</h4>
                        <p class="text-dashboard-subtext text-sm mb-2">Configuration based on trains table</p>
                        <ul class="list-disc pl-4 text-dashboard-text text-sm">
                            <li>Asset tracking and lifecycle management</li>
                            <li>Maintenance scheduling and history</li>
                            <li>Depreciation and valuation tracking</li>
                            <li>Parts inventory and management</li>
                        </ul>
