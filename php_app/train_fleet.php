<?php
// Include necessary files
require_once 'includes/db_connection.php';
require_once 'includes/data_service.php';
require_once 'includes/layout.php';

// Load data
$data = transformData();

// Group data by train_id
$trainFleet = [];
foreach ($data as $entry) {
    $trainId = $entry['train_id'];
    if (!isset($trainFleet[$trainId])) {
        $trainFleet[$trainId] = [
            'train_id' => $trainId,
            'train_name' => $entry['train_name'],
            'trips' => [],
            'total_revenue' => 0,
            'total_passengers' => 0,
            'total_trips' => 0,
            'avg_occupancy_rate' => 0,
            'avg_delay' => 0,
            'on_time_percentage' => 0,
            'routes' => [],
            'classes' => []
        ];
    }
    
    // Add trip data
    $trainFleet[$trainId]['trips'][] = [
        'departure_date' => $entry['departure_date'],
        'from_city' => $entry['from_city'],
        'to_city' => $entry['to_city'],
        'class' => $entry['class'],
        'scheduled_time' => $entry['scheduled_time'],
        'actual_time' => $entry['actual_time'],
        'delay_minutes' => $entry['delay_minutes'],
        'capacity' => $entry['capacity'],
        'occupancy' => $entry['occupancy'],
        'occupancy_rate' => $entry['occupancyRate'],
        'revenue' => $entry['revenue']
    ];
    
    // Update summary statistics
    $trainFleet[$trainId]['total_revenue'] += $entry['revenue'];
    $trainFleet[$trainId]['total_passengers'] += $entry['occupancy'];
    $trainFleet[$trainId]['total_trips']++;
    $trainFleet[$trainId]['avg_occupancy_rate'] += $entry['occupancyRate'];
    $trainFleet[$trainId]['avg_delay'] += $entry['delay_minutes'];
    
    // Track routes
    $route = $entry['from_city'] . ' - ' . $entry['to_city'];
    if (!in_array($route, $trainFleet[$trainId]['routes'])) {
        $trainFleet[$trainId]['routes'][] = $route;
    }
    
    // Track classes
    if (!in_array($entry['class'], $trainFleet[$trainId]['classes'])) {
        $trainFleet[$trainId]['classes'][] = $entry['class'];
    }
}

// Calculate averages and additional statistics
foreach ($trainFleet as $trainId => &$train) {
    $totalTrips = $train['total_trips'];
    if ($totalTrips > 0) {
        $train['avg_occupancy_rate'] = $train['avg_occupancy_rate'] / $totalTrips;
        $train['avg_delay'] = $train['avg_delay'] / $totalTrips;
        
        // Calculate on-time percentage
        $onTimeTrips = count(array_filter($train['trips'], function($trip) {
            return $trip['delay_minutes'] === 0;
        }));
        $train['on_time_percentage'] = ($onTimeTrips / $totalTrips) * 100;
    }
    
    // Calculate revenue per passenger
    $train['revenue_per_passenger'] = $train['total_passengers'] > 0 
        ? $train['total_revenue'] / $train['total_passengers'] 
        : 0;
    
    // Calculate revenue per trip
    $train['revenue_per_trip'] = $totalTrips > 0 
        ? $train['total_revenue'] / $totalTrips 
        : 0;
    
    // Sort trips by date (most recent first)
    usort($train['trips'], function($a, $b) {
        return strtotime($b['departure_date']) - strtotime($a['departure_date']);
    });
}

// Sort trains by ID
ksort($trainFleet);

// Calculate fleet-wide statistics
$fleetStats = [
    'total_trains' => count($trainFleet),
    'total_revenue' => array_sum(array_column($trainFleet, 'total_revenue')),
    'total_passengers' => array_sum(array_column($trainFleet, 'total_passengers')),
    'total_trips' => array_sum(array_column($trainFleet, 'total_trips')),
    'avg_occupancy_rate' => 0,
    'avg_delay' => 0,
    'on_time_percentage' => 0
];

// Calculate fleet averages
if ($fleetStats['total_trains'] > 0) {
    $fleetStats['avg_occupancy_rate'] = array_sum(array_column($trainFleet, 'avg_occupancy_rate')) / $fleetStats['total_trains'];
    $fleetStats['avg_delay'] = array_sum(array_column($trainFleet, 'avg_delay')) / $fleetStats['total_trains'];
    $fleetStats['on_time_percentage'] = array_sum(array_column($trainFleet, 'on_time_percentage')) / $fleetStats['total_trains'];
}

// Prepare chart data
$trainRevenueData = [
    'labels' => array_column($trainFleet, 'train_name'),
    'values' => array_column($trainFleet, 'total_revenue'),
    'label' => 'Total Revenue (LKR)'
];

$trainOccupancyData = [
    'labels' => array_column($trainFleet, 'train_name'),
    'values' => array_column($trainFleet, 'avg_occupancy_rate'),
    'label' => 'Average Occupancy Rate (%)'
];

$trainDelayData = [
    'labels' => array_column($trainFleet, 'train_name'),
    'values' => array_column($trainFleet, 'avg_delay'),
    'label' => 'Average Delay (minutes)'
];

$trainOnTimeData = [
    'labels' => array_column($trainFleet, 'train_name'),
    'values' => array_column($trainFleet, 'on_time_percentage'),
    'label' => 'On-Time Performance (%)'
];

// Define colors
$colors = ['#7e57c2', '#4e7fff', '#b39ddb', '#64b5f6', '#9575cd', '#5c6bc0', '#7986cb', '#4fc3f7'];

// Start building the page content
ob_start();
?>

<div class="p-4">
    <div class="mb-4 px-2">
        <h1 class="text-xl font-medium text-dashboard-header">Train Fleet</h1>
        <p class="text-dashboard-subtext mt-1">View and manage train fleet performance and statistics.</p>
    </div>
    
    <!-- Fleet Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-dashboard-panel rounded shadow p-4">
            <div class="text-dashboard-subtext text-sm">Total Fleet Size</div>
            <div class="text-dashboard-header text-2xl font-semibold mt-1"><?= $fleetStats['total_trains'] ?> Trains</div>
            <div class="text-dashboard-subtext text-sm mt-2"><?= array_sum(array_map(function($train) { return count($train['routes']); }, $trainFleet)) ?> Active Routes</div>
        </div>
        
        <div class="bg-dashboard-panel rounded shadow p-4">
            <div class="text-dashboard-subtext text-sm">Total Fleet Revenue</div>
            <div class="text-dashboard-header text-2xl font-semibold mt-1"><?= number_format($fleetStats['total_revenue']) ?> LKR</div>
            <div class="text-dashboard-subtext text-sm mt-2"><?= number_format($fleetStats['total_trips']) ?> Total Trips</div>
        </div>
        
        <div class="bg-dashboard-panel rounded shadow p-4">
            <div class="text-dashboard-subtext text-sm">Average Occupancy</div>
            <div class="text-dashboard-header text-2xl font-semibold mt-1"><?= number_format($fleetStats['avg_occupancy_rate'], 1) ?>%</div>
            <div class="text-dashboard-subtext text-sm mt-2"><?= number_format($fleetStats['total_passengers']) ?> Total Passengers</div>
        </div>
        
        <div class="bg-dashboard-panel rounded shadow p-4">
            <div class="text-dashboard-subtext text-sm">On-Time Performance</div>
            <div class="text-dashboard-header text-2xl font-semibold mt-1"><?= number_format($fleetStats['on_time_percentage'], 1) ?>%</div>
            <div class="text-dashboard-subtext text-sm mt-2">Avg. Delay: <?= number_format($fleetStats['avg_delay'], 1) ?> min</div>
        </div>
    </div>
    
    <!-- Fleet Performance Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
        <div class="bg-dashboard-panel rounded shadow p-4">
            <h2 class="text-dashboard-header text-lg mb-4">Revenue by Train</h2>
            <div style="height: 300px;">
                <canvas class="bar-chart" data-chart='<?= json_encode($trainRevenueData) ?>'></canvas>
            </div>
        </div>
        
        <div class="bg-dashboard-panel rounded shadow p-4">
            <h2 class="text-dashboard-header text-lg mb-4">Occupancy Rate by Train</h2>
            <div style="height: 300px;">
                <canvas class="bar-chart" data-chart='<?= json_encode($trainOccupancyData) ?>'></canvas>
            </div>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
        <div class="bg-dashboard-panel rounded shadow p-4">
            <h2 class="text-dashboard-header text-lg mb-4">Average Delay by Train</h2>
            <div style="height: 300px;">
                <canvas class="bar-chart" data-chart='<?= json_encode($trainDelayData) ?>'></canvas>
            </div>
        </div>
        
        <div class="bg-dashboard-panel rounded shadow p-4">
            <h2 class="text-dashboard-header text-lg mb-4">On-Time Performance by Train</h2>
            <div style="height: 300px;">
                <canvas class="bar-chart" data-chart='<?= json_encode($trainOnTimeData) ?>'></canvas>
            </div>
        </div>
    </div>
    
    <!-- Train Fleet Table -->
    <div class="bg-dashboard-panel rounded shadow p-4 mb-6">
        <h2 class="text-dashboard-header text-lg mb-4">Train Fleet Overview</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-dashboard-text text-sm">
                <thead>
                    <tr class="border-b border-gray-700">
                        <th class="text-left py-2 px-3">Train ID</th>
                        <th class="text-left py-2 px-3">Train Name</th>
                        <th class="text-center py-2 px-3">Routes</th>
                        <th class="text-center py-2 px-3">Classes</th>
                        <th class="text-right py-2 px-3">Total Trips</th>
                        <th class="text-right py-2 px-3">Total Revenue</th>
                        <th class="text-right py-2 px-3">Avg. Occupancy</th>
                        <th class="text-right py-2 px-3">On-Time %</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($trainFleet as $train): ?>
                        <tr class="border-b border-gray-700 hover:bg-gray-700">
                            <td class="py-2 px-3"><?= htmlspecialchars($train['train_id']) ?></td>
                            <td class="py-2 px-3"><?= htmlspecialchars($train['train_name']) ?></td>
                            <td class="py-2 px-3 text-center"><?= count($train['routes']) ?></td>
                            <td class="py-2 px-3 text-center"><?= implode(', ', $train['classes']) ?></td>
                            <td class="py-2 px-3 text-right"><?= number_format($train['total_trips']) ?></td>
                            <td class="py-2 px-3 text-right"><?= number_format($train['total_revenue']) ?> LKR</td>
                            <td class="py-2 px-3 text-right"><?= number_format($train['avg_occupancy_rate'], 1) ?>%</td>
                            <td class="py-2 px-3 text-right"><?= number_format($train['on_time_percentage'], 1) ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Individual Train Details -->
    <h2 class="text-xl font-medium text-dashboard-header mb-4 px-2">Individual Train Details</h2>
    
    <div class="grid grid-cols-1 gap-4">
        <?php foreach ($trainFleet as $train): ?>
            <div class="bg-dashboard-panel rounded shadow">
                <div class="p-4 border-b border-gray-700 flex justify-between items-center">
                    <div>
                        <h2 class="text-dashboard-header text-lg">
                            Train <?= htmlspecialchars($train['train_id']) ?> - <?= htmlspecialchars($train['train_name']) ?>
                        </h2>
                        <div class="text-dashboard-subtext text-sm mt-1">
                            <?= count($train['routes']) ?> routes | 
                            <?= $train['total_trips'] ?> trips | 
                            <?= number_format($train['avg_occupancy_rate'], 1) ?>% avg. occupancy | 
                            <?= number_format($train['on_time_percentage'], 1) ?>% on-time
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-dashboard-header"><?= number_format($train['total_revenue']) ?> LKR</div>
                        <div class="text-dashboard-subtext text-sm">Total Revenue</div>
                    </div>
                </div>
                
                <div class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div class="bg-dashboard-dark rounded p-3">
                            <div class="text-dashboard-subtext text-sm">Revenue per Trip</div>
                            <div class="text-dashboard-header text-xl mt-1"><?= number_format($train['revenue_per_trip']) ?> LKR</div>
                        </div>
                        
                        <div class="bg-dashboard-dark rounded p-3">
                            <div class="text-dashboard-subtext text-sm">Revenue per Passenger</div>
                            <div class="text-dashboard-header text-xl mt-1"><?= number_format($train['revenue_per_passenger'], 2) ?> LKR</div>
                        </div>
                        
                        <div class="bg-dashboard-dark rounded p-3">
                            <div class="text-dashboard-subtext text-sm">Average Delay</div>
                            <div class="text-dashboard-header text-xl mt-1"><?= number_format($train['avg_delay'], 1) ?> minutes</div>
                        </div>
                    </div>
                    
                    <h3 class="text-dashboard-subtext text-sm mb-2">Routes Served</h3>
                    <div class="bg-dashboard-dark rounded p-3 mb-4">
                        <div class="flex flex-wrap gap-2">
                            <?php foreach ($train['routes'] as $route): ?>
                                <span class="bg-gray-700 text-dashboard-text px-2 py-1 rounded text-xs">
                                    <?= htmlspecialchars($route) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <h3 class="text-dashboard-subtext text-sm mb-2">Recent Trips</h3>
                    <div class="overflow-x-auto bg-dashboard-dark rounded">
                        <table class="w-full text-dashboard-text text-sm">
                            <thead>
                                <tr class="border-b border-gray-700">
                                    <th class="text-left py-2 px-3">Date</th>
                                    <th class="text-left py-2 px-3">Route</th>
                                    <th class="text-center py-2 px-3">Class</th>
                                    <th class="text-center py-2 px-3">Scheduled</th>
                                    <th class="text-center py-2 px-3">Actual</th>
                                    <th class="text-center py-2 px-3">Delay</th>
                                    <th class="text-center py-2 px-3">Occupancy</th>
                                    <th class="text-right py-2 px-3">Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($train['trips'], 0, 5) as $trip): ?>
                                    <tr class="border-b border-gray-700 hover:bg-gray-800">
                                        <td class="py-2 px-3"><?= htmlspecialchars($trip['departure_date']) ?></td>
                                        <td class="py-2 px-3"><?= htmlspecialchars($trip['from_city']) ?> → <?= htmlspecialchars($trip['to_city']) ?></td>
                                        <td class="py-2 px-3 text-center"><?= htmlspecialchars($trip['class']) ?></td>
                                        <td class="py-2 px-3 text-center"><?= htmlspecialchars($trip['scheduled_time']) ?></td>
                                        <td class="py-2 px-3 text-center"><?= htmlspecialchars($trip['actual_time']) ?></td>
                                        <td class="py-2 px-3 text-center <?= $trip['delay_minutes'] > 0 ? 'text-yellow-400' : 'text-green-400' ?>">
                                            <?= $trip['delay_minutes'] ?> min
                                        </td>
                                        <td class="py-2 px-3 text-center"><?= number_format($trip['occupancy_rate'], 1) ?>%</td>
                                        <td class="py-2 px-3 text-right"><?= number_format($trip['revenue']) ?> LKR</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php
$content = ob_get_clean();

// Render the layout with the content
echo renderLayout('Train Fleet - BookSL Train Dashboard', 'trainFleet', $content);
?>
