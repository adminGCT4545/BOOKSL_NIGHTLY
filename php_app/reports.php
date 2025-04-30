<?php
// Include necessary files
require_once 'includes/db_connection.php';
require_once 'includes/data_service.php';
require_once 'includes/layout.php';

// Get filter values from query parameters
$reportType = 'standard'; // Only support standard reports for now
$standardReport = isset($_GET['standard_report']) ? $_GET['standard_report'] : 'train_schedule';
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$exportFormat = isset($_GET['export_format']) ? $_GET['export_format'] : '';

// Load data
$data = transformData();

// Filter data based on date range
$filteredData = array_filter($data, function($entry) use ($startDate, $endDate) {
    $entryDate = $entry['departure_date'];
    return $entryDate >= $startDate && $entryDate <= $endDate;
});

// Handle export formats
if (!empty($exportFormat)) {
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="report_' . date('Y-m-d') . '.' . $exportFormat . '"');
    echo "BookSL Train Report\n";
    echo "Date Range: " . date('F j, Y', strtotime($startDate)) . " - " . date('F j, Y', strtotime($endDate)) . "\n\n";
    
    foreach ($filteredData as $entry) {
        echo "Train: {$entry['train_name']} ({$entry['train_id']})\n";
        echo "Route: {$entry['from_city']} → {$entry['to_city']}\n";
        echo "Date: {$entry['departure_date']}\n";
        echo "Time: {$entry['scheduled_time']}\n";
        echo "Delay: " . ($entry['delay_minutes'] > 0 ? "{$entry['delay_minutes']} min" : "On Time") . "\n";
        echo "Revenue: " . number_format($entry['revenue'], 2) . " LKR\n\n";
    }
    exit;
}

// Start building the page content
ob_start();
?>

<div class="p-4">
    <div class="mb-4 px-2 flex justify-between items-center">
        <h1 class="text-xl font-medium text-dashboard-header">Reports</h1>
        <div class="flex items-center space-x-4">
            <form id="reportFilterForm" method="GET" class="flex items-center space-x-4">
                <div>
                    <label class="text-dashboard-subtext mr-2 text-sm">Report Type:</label>
                    <div class="inline-block relative">
                        <button 
                            type="button" 
                            id="reportTypeDropdown" 
                            class="bg-dashboard-dark text-dashboard-text border border-gray-700 rounded px-2 py-1 text-sm flex items-center"
                            onclick="toggleDropdown()"
                        >
                            <?php
                            switch ($standardReport) {
                                case 'train_schedule':
                                    echo 'Train Schedule Report';
                                    break;
                                case 'ticket_sales':
                                    echo 'Ticket Sales Report';
                                    break;
                                case 'revenue':
                                    echo 'Revenue Report';
                                    break;
                                case 'delay':
                                    echo 'Delay Analysis Report';
                                    break;
                                case 'occupancy':
                                    echo 'Occupancy Report';
                                    break;
                                default:
                                    echo 'Train Schedule Report';
                            }
                            ?>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div id="reportTypeMenu" class="absolute hidden z-10 mt-1 w-56 bg-dashboard-dark border border-gray-700 rounded shadow-lg">
                            <a href="?standard_report=train_schedule&start_date=<?= htmlspecialchars($startDate) ?>&end_date=<?= htmlspecialchars($endDate) ?>" class="block px-4 py-2 text-dashboard-text hover:bg-gray-700 <?= $standardReport === 'train_schedule' ? 'bg-gray-700' : '' ?>">
                                Train Schedule Report
                            </a>
                            <a href="?standard_report=ticket_sales&start_date=<?= htmlspecialchars($startDate) ?>&end_date=<?= htmlspecialchars($endDate) ?>" class="block px-4 py-2 text-dashboard-text hover:bg-gray-700 <?= $standardReport === 'ticket_sales' ? 'bg-gray-700' : '' ?>">
                                Ticket Sales Report
                            </a>
                            <a href="?standard_report=revenue&start_date=<?= htmlspecialchars($startDate) ?>&end_date=<?= htmlspecialchars($endDate) ?>" class="block px-4 py-2 text-dashboard-text hover:bg-gray-700 <?= $standardReport === 'revenue' ? 'bg-gray-700' : '' ?>">
                                Revenue Report
                            </a>
                            <a href="?standard_report=delay&start_date=<?= htmlspecialchars($startDate) ?>&end_date=<?= htmlspecialchars($endDate) ?>" class="block px-4 py-2 text-dashboard-text hover:bg-gray-700 <?= $standardReport === 'delay' ? 'bg-gray-700' : '' ?>">
                                Delay Analysis Report
                            </a>
                            <a href="?standard_report=occupancy&start_date=<?= htmlspecialchars($startDate) ?>&end_date=<?= htmlspecialchars($endDate) ?>" class="block px-4 py-2 text-dashboard-text hover:bg-gray-700 <?= $standardReport === 'occupancy' ? 'bg-gray-700' : '' ?>">
                                Occupancy Report
                            </a>
                        </div>
                    </div>
                </div>
                
                <div>
                    <label for="startDateInput" class="text-dashboard-subtext mr-2 text-sm">Start Date:</label>
                    <input 
                        type="date" 
                        id="startDateInput" 
                        name="start_date" 
                        value="<?= htmlspecialchars($startDate) ?>" 
                        class="bg-dashboard-dark text-dashboard-text border border-gray-700 rounded px-2 py-1 text-sm"
                    >
                </div>
                
                <div>
                    <label for="endDateInput" class="text-dashboard-subtext mr-2 text-sm">End Date:</label>
                    <input 
                        type="date" 
                        id="endDateInput" 
                        name="end_date" 
                        value="<?= htmlspecialchars($endDate) ?>" 
                        class="bg-dashboard-dark text-dashboard-text border border-gray-700 rounded px-2 py-1 text-sm"
                    >
                </div>
                
                <button 
                    type="submit" 
                    class="bg-dashboard-purple hover:bg-purple-700 text-white font-bold py-1 px-4 rounded text-sm"
                >
                    Apply Filters
                </button>
            </form>
        </div>
    </div>
    
    <!-- Report Content Section -->
    <div class="grid grid-cols-1 gap-4">
        <!-- Report Header -->
        <div class="bg-dashboard-panel rounded shadow p-4">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-dashboard-header text-lg">
                    <?php
                    switch ($standardReport) {
                        case 'train_schedule':
                            echo 'Train Schedule Report';
                            break;
                        case 'ticket_sales':
                            echo 'Ticket Sales Report';
                            break;
                        case 'revenue':
                            echo 'Revenue Report';
                            break;
                        case 'delay':
                            echo 'Delay Analysis Report';
                            break;
                        case 'occupancy':
                            echo 'Occupancy Report';
                            break;
                        default:
                            echo 'Train Schedule Report';
                    }
                    ?>
                </h2>
                <div class="flex space-x-2">
                    <a href="?<?= http_build_query(array_merge($_GET, ['export_format' => 'pdf'])) ?>" class="bg-red-600 hover:bg-red-700 text-white font-bold py-1 px-3 rounded text-sm flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        PDF
                    </a>
                    <a href="?<?= http_build_query(array_merge($_GET, ['export_format' => 'docx'])) ?>" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-1 px-3 rounded text-sm flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        DOCX
                    </a>
                    <a href="?<?= http_build_query(array_merge($_GET, ['export_format' => 'excel'])) ?>" class="bg-green-600 hover:bg-green-700 text-white font-bold py-1 px-3 rounded text-sm flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        Excel
                    </a>
                    <a href="?<?= http_build_query(array_merge($_GET, ['export_format' => 'csv'])) ?>" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-1 px-3 rounded text-sm flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        CSV
                    </a>
                </div>
            </div>
            
            <div class="text-dashboard-subtext text-sm mb-4">
                <p>Date Range: <?= date('F j, Y', strtotime($startDate)) ?> - <?= date('F j, Y', strtotime($endDate)) ?></p>
                <p>Total Records: <?= count($filteredData) ?></p>
            </div>
        </div>
        
        <?php if ($standardReport === 'train_schedule'): ?>
            <!-- Train Schedule Report -->
            <div class="bg-dashboard-panel rounded shadow p-4">
                <h3 class="text-dashboard-header text-md mb-4">Train Schedule Report</h3>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-dashboard-dark rounded">
                        <thead>
                            <tr class="border-b border-gray-700 text-left text-dashboard-subtext">
                                <th class="py-2 px-4">Train ID</th>
                                <th class="py-2 px-4">Train Name</th>
                                <th class="py-2 px-4">From</th>
                                <th class="py-2 px-4">To</th>
                                <th class="py-2 px-4">Date</th>
                                <th class="py-2 px-4">Scheduled Time</th>
                                <th class="py-2 px-4">Actual Time</th>
                                <th class="py-2 px-4">Delay</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($filteredData as $entry): ?>
                                <tr class="border-b border-gray-700 text-dashboard-text">
                                    <td class="py-2 px-4"><?= htmlspecialchars($entry['train_id']) ?></td>
                                    <td class="py-2 px-4"><?= htmlspecialchars($entry['train_name']) ?></td>
                                    <td class="py-2 px-4"><?= htmlspecialchars($entry['from_city']) ?></td>
                                    <td class="py-2 px-4"><?= htmlspecialchars($entry['to_city']) ?></td>
                                    <td class="py-2 px-4"><?= htmlspecialchars($entry['departure_date']) ?></td>
                                    <td class="py-2 px-4"><?= htmlspecialchars($entry['scheduled_time']) ?></td>
                                    <td class="py-2 px-4"><?= htmlspecialchars($entry['actual_time']) ?></td>
                                    <td class="py-2 px-4 <?= $entry['delay_minutes'] > 0 ? 'text-yellow-400' : 'text-green-400' ?>">
                                        <?= $entry['delay_minutes'] > 0 ? "{$entry['delay_minutes']} min" : 'On Time' ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php elseif ($standardReport === 'ticket_sales'): ?>
            <!-- Ticket Sales Report -->
            <div class="bg-dashboard-panel rounded shadow p-4">
                <h3 class="text-dashboard-header text-md mb-4">Ticket Sales Report</h3>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-dashboard-dark rounded">
                        <thead>
                            <tr class="border-b border-gray-700 text-left text-dashboard-subtext">
                                <th class="py-2 px-4">Train ID</th>
                                <th class="py-2 px-4">Train Name</th>
                                <th class="py-2 px-4">Date</th>
                                <th class="py-2 px-4">Route</th>
                                <th class="py-2 px-4">Class</th>
                                <th class="py-2 px-4">Capacity</th>
                                <th class="py-2 px-4">Tickets Sold</th>
                                <th class="py-2 px-4">Occupancy Rate</th>
                                <th class="py-2 px-4">Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($filteredData as $entry): ?>
                                <tr class="border-b border-gray-700 text-dashboard-text">
                                    <td class="py-2 px-4"><?= htmlspecialchars($entry['train_id']) ?></td>
                                    <td class="py-2 px-4"><?= htmlspecialchars($entry['train_name']) ?></td>
                                    <td class="py-2 px-4"><?= htmlspecialchars($entry['departure_date']) ?></td>
                                    <td class="py-2 px-4"><?= htmlspecialchars($entry['from_city']) ?> → <?= htmlspecialchars($entry['to_city']) ?></td>
                                    <td class="py-2 px-4"><?= htmlspecialchars($entry['class']) ?></td>
                                    <td class="py-2 px-4"><?= htmlspecialchars($entry['capacity']) ?></td>
                                    <td class="py-2 px-4"><?= htmlspecialchars($entry['occupancy']) ?></td>
                                    <td class="py-2 px-4"><?= number_format($entry['occupancyRate'], 1) ?>%</td>
                                    <td class="py-2 px-4"><?= number_format($entry['revenue'], 2) ?> LKR</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php elseif ($standardReport === 'revenue'): ?>
            <!-- Revenue Report -->
            <div class="bg-dashboard-panel rounded shadow p-4">
                <h3 class="text-dashboard-header text-md mb-4">Revenue Report</h3>
                
                <?php
                // Calculate revenue summary
                $totalRevenue = array_sum(array_column($filteredData, 'revenue'));
                ?>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="bg-dashboard-dark rounded p-4">
                        <h4 class="text-dashboard-subtext text-sm mb-2">Total Revenue</h4>
                        <p class="text-2xl text-dashboard-header"><?= number_format($totalRevenue, 2) ?> LKR</p>
                    </div>
                    
                    <div class="bg-dashboard-dark rounded p-4">
                        <h4 class="text-dashboard-subtext text-sm mb-2">Average Revenue per Trip</h4>
                        <p class="text-2xl text-dashboard-header">
                            <?= number_format($totalRevenue / (count($filteredData) ?: 1), 2) ?> LKR
                        </p>
                    </div>
                    
                    <div class="bg-dashboard-dark rounded p-4">
                        <h4 class="text-dashboard-subtext text-sm mb-2">Total Trips</h4>
                        <p class="text-2xl text-dashboard-header"><?= count($filteredData) ?></p>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-dashboard-dark rounded">
                        <thead>
                            <tr class="border-b border-gray-700 text-left text-dashboard-subtext">
                                <th class="py-2 px-4">Train ID</th>
                                <th class="py-2 px-4">Train Name</th>
                                <th class="py-2 px-4">Date</th>
                                <th class="py-2 px-4">Route</th>
                                <th class="py-2 px-4">Class</th>
                                <th class="py-2 px-4">Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            // Sort by revenue (highest first)
                            usort($filteredData, function($a, $b) {
                                return $b['revenue'] - $a['revenue'];
                            });
                            
                            foreach ($filteredData as $entry): 
                            ?>
                                <tr class="border-b border-gray-700 text-dashboard-text">
                                    <td class="py-2 px-4"><?= htmlspecialchars($entry['train_id']) ?></td>
                                    <td class="py-2 px-4"><?= htmlspecialchars($entry['train_name']) ?></td>
                                    <td class="py-2 px-4"><?= htmlspecialchars($entry['departure_date']) ?></td>
                                    <td class="py-2 px-4"><?= htmlspecialchars($entry['from_city']) ?> → <?= htmlspecialchars($entry['to_city']) ?></td>
                                    <td class="py-2 px-4"><?= htmlspecialchars($entry['class']) ?></td>
                                    <td class="py-2 px-4"><?= number_format($entry['revenue'], 2) ?> LKR</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php elseif ($standardReport === 'delay'): ?>
            <!-- Delay Analysis Report -->
            <div class="bg-dashboard-panel rounded shadow p-4">
                <h3 class="text-dashboard-header text-md mb-4">Delay Analysis Report</h3>
                
                <?php
                // Calculate delay statistics
                $totalDelayMinutes = array_sum(array_column($filteredData, 'delay_minutes'));
                $avgDelayMinutes = count($filteredData) > 0 ? $totalDelayMinutes / count($filteredData) : 0;
                $onTimeCount = count(array_filter($filteredData, function($entry) {
                    return $entry['delay_minutes'] === 0;
                }));
                $onTimePercentage = count($filteredData) > 0 ? ($onTimeCount / count($filteredData)) * 100 : 0;
                ?>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="bg-dashboard-dark rounded p-4">
                        <h4 class="text-dashboard-subtext text-sm mb-2">Average Delay</h4>
                        <p class="text-2xl text-dashboard-header"><?= number_format($avgDelayMinutes, 1) ?> min</p>
                    </div>
                    
                    <div class="bg-dashboard-dark rounded p-4">
                        <h4 class="text-dashboard-subtext text-sm mb-2">On-Time Performance</h4>
                        <p class="text-2xl text-dashboard-header"><?= number_format($onTimePercentage, 1) ?>%</p>
                    </div>
                    
                    <div class="bg-dashboard-dark rounded p-4">
                        <h4 class="text-dashboard-subtext text-sm mb-2">Total Delay Minutes</h4>
                        <p class="text-2xl text-dashboard-header"><?= number_format($totalDelayMinutes) ?> min</p>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-dashboard-dark rounded">
                        <thead>
                            <tr class="border-b border-gray-700 text-left text-dashboard-subtext">
                                <th class="py-2 px-4">Train ID</th>
                                <th class="py-2 px-4">Train Name</th>
                                <th class="py-2 px-4">Route</th>
                                <th class="py-2 px-4">Date</th>
                                <th class="py-2 px-4">Scheduled Time</th>
                                <th class="py-2 px-4">Actual Time</th>
                                <th class="py-2 px-4">Delay</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            // Sort by delay (highest first)
                            usort($filteredData, function($a, $b) {
                                return $b['delay_minutes'] - $a['delay_minutes'];
                            });
                            
                            foreach ($filteredData as $entry): 
                            ?>
                                <tr class="border-b border-gray-700 text-dashboard-text">
                                    <td class="py-2 px-4"><?= htmlspecialchars($entry['train_id']) ?></td>
                                    <td class="py-2 px-4"><?= htmlspecialchars($entry['train_name']) ?></td>
                                    <td class="py-2 px-4"><?= htmlspecialchars($entry['from_city']) ?> → <?= htmlspecialchars($entry['to_city']) ?></td>
                                    <td class="py-2 px-4"><?= htmlspecialchars($entry['departure_date']) ?></td>
                                    <td class="py-2 px-4"><?= htmlspecialchars($entry['scheduled_time']) ?></td>
                                    <td class="py-2 px-4"><?= htmlspecialchars($entry['actual_time']) ?></td>
                                    <td class="py-2 px-4 <?= $entry['delay_minutes'] > 0 ? 'text-yellow-400' : 'text-green-400' ?>">
                                        <?= $entry['delay_minutes'] > 0 ? "{$entry['delay_minutes']} min" : 'On Time' ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php elseif ($standardReport === 'occupancy'): ?>
            <!-- Occupancy Report -->
            <div class="bg-dashboard-panel rounded shadow p-4">
                <h3 class="text-dashboard-header text-md mb-4">Occupancy Report</h3>
                
                <?php
                // Calculate occupancy statistics
                $totalCapacity = array_sum(array_column($filteredData, 'capacity'));
                $totalOccupancy = array_sum(array_column($filteredData, 'occupancy'));
                $avgOccupancyRate = $totalCapacity > 0 ? ($totalOccupancy / $totalCapacity) * 100 : 0;
                ?>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="bg-dashboard-dark rounded p-4">
                        <h4 class="text-dashboard-subtext text-sm mb-2">Average Occupancy Rate</h4>
                        <p class="text-2xl text-dashboard-header"><?= number_format($avgOccupancyRate, 1) ?>%</p>
                    </div>
                    
                    <div class="bg-dashboard-dark rounded p-4">
                        <h4 class="text-dashboard-subtext text-sm mb-2">Total Capacity</h4>
                        <p class="text-2xl text-dashboard-header"><?= number_format($totalCapacity) ?> seats</p>
                    </div>
                    
                    <div class="bg-dashboard-dark rounded p-4">
                        <h4 class="text-dashboard-subtext text-sm mb-2">Total Tickets Sold</h4>
                        <p class="text-2xl text-dashboard-header"><?= number_format($totalOccupancy) ?> tickets</p>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-dashboard-dark rounded">
                        <thead>
                            <tr class="border-b border-gray-700 text-left text-dashboard-subtext">
                                <th class="py-2 px-4">Train ID</th>
                                <th class="py-2 px-4">Train Name</th>
                                <th class="py-2 px-4">Route</th>
                                <th class="py-2 px-4">Date</th>
                                <th class="py-2 px-4">Class</th>
                                <th class="py-2 px-4">Capacity</th>
                                <th class="py-2 px-4">Tickets Sold</th>
                                <th class="py-2 px-4">Occupancy Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            // Sort by occupancy rate (highest first)
                            usort($filteredData, function($a, $b) {
                                return $b['occupancyRate'] - $a['occupancyRate'];
                            });
                            
                            foreach ($filteredData as $entry): 
                            ?>
                                <tr class="border-b border-gray-700 text-dashboard-text">
                                    <td class="py-2 px-4"><?= htmlspecialchars($entry['train_id']) ?></td>
                                    <td class="py-2 px-4"><?= htmlspecialchars($entry['train_name']) ?></td>
                                    <td class="py-2 px-4"><?= htmlspecialchars($entry['from_city']) ?> → <?= htmlspecialchars($entry['to_city']) ?></td>
                                    <td class="py-2 px-4"><?= htmlspecialchars($entry['departure_date']) ?></td>
                                    <td class="py-2 px-4"><?= htmlspecialchars($entry['class']) ?></td>
                                    <td class="py-2 px-4"><?= htmlspecialchars($entry['capacity']) ?></td>
                                    <td class="py-2 px-4"><?= htmlspecialchars($entry['occupancy']) ?></td>
                                    <td class="py-2 px-4"><?= number_format($entry['occupancyRate'], 1) ?>%</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Function to toggle the dropdown menu
function toggleDropdown() {
    const menu = document.getElementById('reportTypeMenu');
    menu.classList.toggle('hidden');
}

// Close the dropdown when clicking outside
document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('reportTypeDropdown');
    const menu = document.getElementById('reportTypeMenu');
    
    if (!dropdown.contains(event.target) && !menu.contains(event.target)) {
        menu.classList.add('hidden');
    }
});

// Add event listeners to form elements
document.addEventListener('DOMContentLoaded', function() {
    // Add event listener to date inputs
    const startDateInput = document.getElementById('startDateInput');
    const endDateInput = document.getElementById('endDateInput');
    
    if (startDateInput && endDateInput) {
        startDateInput.addEventListener('change', function() {
            document.getElementById('reportFilterForm').submit();
        });
        
        endDateInput.addEventListener('change', function() {
            document.getElementById('reportFilterForm').submit();
        });
    }
});
</script>

<?php
$content = ob_get_clean();

// Render the layout with the content
echo renderLayout('BookSL Train Reports', 'reports', $content);
?>
