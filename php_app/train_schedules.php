<?php
// Include necessary files
require_once 'includes/db_connection.php';
require_once 'includes/data_service.php';
require_once 'includes/layout.php';

// Load data
$data = transformData();

// Group data by train_id
$trainSchedules = [];
foreach ($data as $entry) {
    $trainId = $entry['train_id'];
    if (!isset($trainSchedules[$trainId])) {
        $trainSchedules[$trainId] = [
            'train_id' => $trainId,
            'train_name' => $entry['train_name'],
            'routes' => []
        ];
    }
    
    $route = $entry['from_city'] . ' - ' . $entry['to_city'];
    if (!in_array($route, array_column($trainSchedules[$trainId]['routes'], 'route'))) {
        $trainSchedules[$trainId]['routes'][] = [
            'route' => $route,
            'from_city' => $entry['from_city'],
            'to_city' => $entry['to_city'],
            'scheduled_time' => $entry['scheduled_time'],
            'trips' => []
        ];
    }
    
    // Find the route index
    $routeIndex = array_search($route, array_column($trainSchedules[$trainId]['routes'], 'route'));
    
    // Add trip to the route
    $trainSchedules[$trainId]['routes'][$routeIndex]['trips'][] = [
        'departure_date' => $entry['departure_date'],
        'scheduled_time' => $entry['scheduled_time'],
        'actual_time' => $entry['actual_time'],
        'delay_minutes' => $entry['delay_minutes'],
        'class' => $entry['class'],
        'occupancy_rate' => $entry['occupancyRate'],
        'revenue' => $entry['revenue']
    ];
}

// Calculate statistics for each train
foreach ($trainSchedules as $trainId => &$train) {
    $train['total_trips'] = 0;
    $train['avg_delay'] = 0;
    $train['avg_occupancy'] = 0;
    $train['total_revenue'] = 0;
    $train['on_time_percentage'] = 0;
    $train['delayed_trips'] = 0;
    
    $totalTrips = 0;
    $totalDelay = 0;
    $totalOccupancy = 0;
    $totalRevenue = 0;
    $delayedTrips = 0;
    
    foreach ($train['routes'] as &$route) {
        $route['avg_delay'] = 0;
        $route['avg_occupancy'] = 0;
        $route['total_revenue'] = 0;
        
        $routeTotalDelay = 0;
        $routeTotalOccupancy = 0;
        $routeTotalRevenue = 0;
        $routeTrips = count($route['trips']);
        
        foreach ($route['trips'] as $trip) {
            $routeTotalDelay += $trip['delay_minutes'];
            $routeTotalOccupancy += $trip['occupancy_rate'];
            $routeTotalRevenue += $trip['revenue'];
            
            if ($trip['delay_minutes'] > 0) {
                $delayedTrips++;
            }
        }
        
        $route['avg_delay'] = $routeTrips > 0 ? $routeTotalDelay / $routeTrips : 0;
        $route['avg_occupancy'] = $routeTrips > 0 ? $routeTotalOccupancy / $routeTrips : 0;
        $route['total_revenue'] = $routeTotalRevenue;
        
        $totalTrips += $routeTrips;
        $totalDelay += $routeTotalDelay;
        $totalOccupancy += $routeTotalOccupancy;
        $totalRevenue += $routeTotalRevenue;
    }
    
    $train['total_trips'] = $totalTrips;
    $train['avg_delay'] = $totalTrips > 0 ? $totalDelay / $totalTrips : 0;
    $train['avg_occupancy'] = $totalTrips > 0 ? $totalOccupancy / $totalTrips : 0;
    $train['total_revenue'] = $totalRevenue;
    $train['on_time_percentage'] = $totalTrips > 0 ? (($totalTrips - $delayedTrips) / $totalTrips) * 100 : 0;
    $train['delayed_trips'] = $delayedTrips;
}

// Sort trains by ID
ksort($trainSchedules);

// Start building the page content
ob_start();
?>

<div class="p-4">
    <div class="mb-4 px-2">
        <h1 class="text-xl font-medium text-dashboard-header">Train Schedules</h1>
        <p class="text-dashboard-subtext mt-1">View and manage train schedules, routes, and performance metrics.</p>
    </div>
    
    <!-- Train Schedules Grid -->
    <div class="grid grid-cols-1 gap-4">
        <?php foreach ($trainSchedules as $train): ?>
            <div class="bg-dashboard-panel rounded shadow">
                <div class="p-4 border-b border-gray-700 flex justify-between items-center">
                    <div>
                        <h2 class="text-dashboard-header text-lg">
                            Train <?= htmlspecialchars($train['train_id']) ?> - <?= htmlspecialchars($train['train_name']) ?>
                        </h2>
                        <div class="text-dashboard-subtext text-sm mt-1">
                            <?= $train['total_trips'] ?> trips | 
                            <?= number_format($train['avg_occupancy'], 1) ?>% avg. occupancy | 
                            <?= number_format($train['on_time_percentage'], 1) ?>% on-time
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-dashboard-header"><?= number_format($train['total_revenue']) ?> LKR</div>
                        <div class="text-dashboard-subtext text-sm">Total Revenue</div>
                    </div>
                </div>
                
                <div class="p-4">
                    <h3 class="text-dashboard-subtext text-sm mb-2">Routes & Performance</h3>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-dashboard-text text-sm">
                            <thead>
                                <tr class="border-b border-gray-700">
                                    <th class="text-left py-2 px-3">Route</th>
                                    <th class="text-center py-2 px-3">Scheduled Time</th>
                                    <th class="text-center py-2 px-3">Avg. Delay</th>
                                    <th class="text-center py-2 px-3">Avg. Occupancy</th>
                                    <th class="text-right py-2 px-3">Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($train['routes'] as $route): ?>
                                    <tr class="border-b border-gray-700 hover:bg-gray-700">
                                        <td class="py-2 px-3"><?= htmlspecialchars($route['route']) ?></td>
                                        <td class="py-2 px-3 text-center"><?= htmlspecialchars($route['scheduled_time']) ?></td>
                                        <td class="py-2 px-3 text-center <?= $route['avg_delay'] > 0 ? 'text-yellow-400' : 'text-green-400' ?>">
                                            <?= number_format($route['avg_delay'], 1) ?> min
                                        </td>
                                        <td class="py-2 px-3 text-center"><?= number_format($route['avg_occupancy'], 1) ?>%</td>
                                        <td class="py-2 px-3 text-right"><?= number_format($route['total_revenue']) ?> LKR</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
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
                                    <?php 
                                    // Get all trips for this train
                                    $allTrips = [];
                                    foreach ($train['routes'] as $route) {
                                        foreach ($route['trips'] as $trip) {
                                            $trip['route'] = $route['route'];
                                            $allTrips[] = $trip;
                                        }
                                    }
                                    
                                    // Sort by date, most recent first
                                    usort($allTrips, function($a, $b) {
                                        return strtotime($b['departure_date']) - strtotime($a['departure_date']);
                                    });
                                    
                                    // Display only the 5 most recent trips
                                    $recentTrips = array_slice($allTrips, 0, 5);
                                    
                                    foreach ($recentTrips as $trip):
                                    ?>
                                        <tr class="border-b border-gray-700 hover:bg-gray-800">
                                            <td class="py-2 px-3"><?= htmlspecialchars($trip['departure_date']) ?></td>
                                            <td class="py-2 px-3"><?= htmlspecialchars($trip['route']) ?></td>
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
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php
$content = ob_get_clean();

// Render the layout with the content
echo renderLayout('Train Schedules - BookSL Train Dashboard', 'schedules', $content);
?>
