<?php
// Include necessary files
require_once 'includes/db_connection.php';
require_once 'includes/data_service.php';
require_once 'includes/layout.php';

// Load data
$data = transformData();

// Get current date and time
$currentDate = new DateTime();
$currentDateStr = $currentDate->format('Y-m-d');
$currentTimeStr = $currentDate->format('H:i:s');

// Simulate active trains (trains with trips today or in the future)
$activeTrains = [];
$inactiveTrains = [];

foreach ($data as $entry) {
    $trainId = $entry['train_id'];
    $trainName = $entry['train_name'];
    $departureDate = $entry['departure_date'];
    
    // Check if the train is already in the active or inactive list
    $isInActive = isset($activeTrains[$trainId]);
    $isInInactive = isset($inactiveTrains[$trainId]);
    
    // If the train is not in either list, add it to the appropriate one
    if (!$isInActive && !$isInInactive) {
        // Simulate some trains as active (departure date >= today)
        if ($departureDate >= $currentDateStr) {
            $activeTrains[$trainId] = [
                'train_id' => $trainId,
                'train_name' => $trainName,
                'status' => 'Active',
                'last_update' => $currentDateStr . ' ' . $currentTimeStr,
                'current_location' => $entry['from_city'], // Simulate current location
                'next_destination' => $entry['to_city'],
                'scheduled_departure' => $entry['scheduled_time'],
                'estimated_arrival' => calculateEstimatedArrival($entry['scheduled_time'], $entry['delay_minutes']),
                'delay_minutes' => $entry['delay_minutes'],
                'occupancy_rate' => $entry['occupancyRate'],
                'maintenance_status' => 'Normal',
                'fuel_level' => rand(60, 100), // Simulate fuel level (60-100%)
                'speed' => rand(0, 120), // Simulate speed (0-120 km/h)
                'temperature' => rand(20, 25), // Simulate temperature (20-25°C)
                'alerts' => []
            ];
            
            // Simulate some alerts for trains with delays
            if ($entry['delay_minutes'] > 0) {
                $activeTrains[$trainId]['alerts'][] = [
                    'type' => 'Delay',
                    'message' => 'Train is delayed by ' . $entry['delay_minutes'] . ' minutes.',
                    'severity' => $entry['delay_minutes'] > 10 ? 'High' : 'Medium',
                    'timestamp' => $currentDateStr . ' ' . $currentTimeStr
                ];
            }
            
            // Simulate some random maintenance alerts
            if (rand(1, 10) === 1) {
                $alertTypes = [
                    'Maintenance check required',
                    'Low fuel warning',
                    'Engine temperature high',
                    'Brake system check required'
                ];
                $alertType = $alertTypes[array_rand($alertTypes)];
                $activeTrains[$trainId]['alerts'][] = [
                    'type' => 'Maintenance',
                    'message' => $alertType,
                    'severity' => 'Medium',
                    'timestamp' => $currentDateStr . ' ' . $currentTimeStr
                ];
                
                // Update maintenance status if there's a maintenance alert
                $activeTrains[$trainId]['maintenance_status'] = 'Requires Attention';
            }
        } else {
            $inactiveTrains[$trainId] = [
                'train_id' => $trainId,
                'train_name' => $trainName,
                'status' => 'Inactive',
                'last_update' => $currentDateStr . ' ' . $currentTimeStr,
                'maintenance_status' => rand(1, 5) === 1 ? 'Maintenance Scheduled' : 'Normal',
                'last_active' => $departureDate
            ];
        }
    }
}

// Calculate system health metrics
$systemHealth = [
    'active_trains' => count($activeTrains),
    'inactive_trains' => count($inactiveTrains),
    'total_trains' => count($activeTrains) + count($inactiveTrains),
    'trains_with_alerts' => count(array_filter($activeTrains, function($train) {
        return count($train['alerts']) > 0;
    })),
    'trains_requiring_maintenance' => count(array_filter($activeTrains, function($train) {
        return $train['maintenance_status'] === 'Requires Attention';
    })) + count(array_filter($inactiveTrains, function($train) {
        return $train['maintenance_status'] === 'Maintenance Scheduled';
    })),
    'avg_delay' => count($activeTrains) > 0 ? array_sum(array_column($activeTrains, 'delay_minutes')) / count($activeTrains) : 0,
    'avg_occupancy' => count($activeTrains) > 0 ? array_sum(array_column($activeTrains, 'occupancy_rate')) / count($activeTrains) : 0,
    'system_status' => 'Operational'
];

// Simulate some system alerts
$systemAlerts = [];
if ($systemHealth['trains_with_alerts'] > 0) {
    $systemAlerts[] = [
        'type' => 'Train Alerts',
        'message' => $systemHealth['trains_with_alerts'] . ' trains have active alerts.',
        'severity' => 'Medium',
        'timestamp' => $currentDateStr . ' ' . $currentTimeStr
    ];
}

if ($systemHealth['trains_requiring_maintenance'] > 0) {
    $systemAlerts[] = [
        'type' => 'Maintenance',
        'message' => $systemHealth['trains_requiring_maintenance'] . ' trains require maintenance.',
        'severity' => 'Medium',
        'timestamp' => $currentDateStr . ' ' . $currentTimeStr
    ];
}

if ($systemHealth['avg_delay'] > 5) {
    $systemAlerts[] = [
        'type' => 'System Performance',
        'message' => 'Average train delay is ' . number_format($systemHealth['avg_delay'], 1) . ' minutes.',
        'severity' => $systemHealth['avg_delay'] > 10 ? 'High' : 'Medium',
        'timestamp' => $currentDateStr . ' ' . $currentTimeStr
    ];
}

// Simulate recent system events
$systemEvents = [
    [
        'type' => 'System',
        'message' => 'Daily system health check completed.',
        'timestamp' => $currentDate->modify('-1 hour')->format('Y-m-d H:i:s')
    ],
    [
        'type' => 'Maintenance',
        'message' => 'Scheduled maintenance for Train ' . array_rand(array_merge($activeTrains, $inactiveTrains)) . ' completed.',
        'timestamp' => $currentDate->modify('-3 hours')->format('Y-m-d H:i:s')
    ],
    [
        'type' => 'Network',
        'message' => 'Communication system update deployed.',
        'timestamp' => $currentDate->modify('-5 hours')->format('Y-m-d H:i:s')
    ],
    [
        'type' => 'Security',
        'message' => 'System security scan completed. No issues found.',
        'timestamp' => $currentDate->modify('-12 hours')->format('Y-m-d H:i:s')
    ],
    [
        'type' => 'Database',
        'message' => 'Database backup completed successfully.',
        'timestamp' => $currentDate->modify('-1 day')->format('Y-m-d H:i:s')
    ]
];

// Helper function to calculate estimated arrival time
function calculateEstimatedArrival($scheduledTime, $delayMinutes) {
    $time = new DateTime('2000-01-01T' . $scheduledTime);
    $time->modify('+' . (60 + $delayMinutes) . ' minutes'); // Assume 1 hour journey plus delay
    return $time->format('H:i:s');
}

// Start building the page content
ob_start();
?>

<div class="p-4">
    <div class="mb-4 px-2">
        <h1 class="text-xl font-medium text-dashboard-header">Remote Management</h1>
        <p class="text-dashboard-subtext mt-1">Monitor and manage train operations in real-time.</p>
    </div>
    
    <!-- System Health Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-dashboard-panel rounded shadow p-4">
            <div class="text-dashboard-subtext text-sm">System Status</div>
            <div class="text-dashboard-header text-2xl font-semibold mt-1 flex items-center">
                <span class="inline-block w-3 h-3 rounded-full bg-green-500 mr-2"></span>
                <?= $systemHealth['system_status'] ?>
            </div>
            <div class="text-dashboard-subtext text-sm mt-2">Last Updated: <?= $currentDateStr . ' ' . $currentTimeStr ?></div>
        </div>
        
        <div class="bg-dashboard-panel rounded shadow p-4">
            <div class="text-dashboard-subtext text-sm">Active Trains</div>
            <div class="text-dashboard-header text-2xl font-semibold mt-1">
                <?= $systemHealth['active_trains'] ?> / <?= $systemHealth['total_trains'] ?>
            </div>
            <div class="text-dashboard-subtext text-sm mt-2">
                <?= $systemHealth['trains_with_alerts'] ?> with alerts
            </div>
        </div>
        
        <div class="bg-dashboard-panel rounded shadow p-4">
            <div class="text-dashboard-subtext text-sm">Average Delay</div>
            <div class="text-dashboard-header text-2xl font-semibold mt-1">
                <?= number_format($systemHealth['avg_delay'], 1) ?> min
            </div>
            <div class="text-dashboard-subtext text-sm mt-2">
                <?= $systemHealth['trains_requiring_maintenance'] ?> trains need maintenance
            </div>
        </div>
        
        <div class="bg-dashboard-panel rounded shadow p-4">
            <div class="text-dashboard-subtext text-sm">Average Occupancy</div>
            <div class="text-dashboard-header text-2xl font-semibold mt-1">
                <?= number_format($systemHealth['avg_occupancy'], 1) ?>%
            </div>
            <div class="text-dashboard-subtext text-sm mt-2">Across all active trains</div>
        </div>
    </div>
    
    <!-- System Alerts and Events -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
        <!-- System Alerts -->
        <div class="bg-dashboard-panel rounded shadow p-4">
            <h2 class="text-dashboard-header text-lg mb-4">System Alerts</h2>
            <?php if (count($systemAlerts) > 0): ?>
                <div class="space-y-3">
                    <?php foreach ($systemAlerts as $alert): ?>
                        <div class="bg-dashboard-dark rounded p-3 border-l-4 <?= $alert['severity'] === 'High' ? 'border-red-500' : 'border-yellow-500' ?>">
                            <div class="flex justify-between">
                                <div class="font-medium"><?= htmlspecialchars($alert['type']) ?></div>
                                <div class="text-sm text-dashboard-subtext"><?= htmlspecialchars($alert['timestamp']) ?></div>
                            </div>
                            <div class="mt-1"><?= htmlspecialchars($alert['message']) ?></div>
                            <div class="mt-1 text-sm">
                                <span class="px-2 py-1 rounded-full <?= $alert['severity'] === 'High' ? 'bg-red-900 text-red-200' : 'bg-yellow-900 text-yellow-200' ?>">
                                    <?= htmlspecialchars($alert['severity']) ?> Priority
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="bg-dashboard-dark rounded p-4 text-center">
                    <p>No active system alerts</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Recent System Events -->
        <div class="bg-dashboard-panel rounded shadow p-4">
            <h2 class="text-dashboard-header text-lg mb-4">Recent System Events</h2>
            <div class="bg-dashboard-dark rounded p-3">
                <table class="w-full text-dashboard-text text-sm">
                    <thead>
                        <tr class="border-b border-gray-700">
                            <th class="text-left py-2">Event Type</th>
                            <th class="text-left py-2">Message</th>
                            <th class="text-right py-2">Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($systemEvents as $event): ?>
                            <tr class="border-b border-gray-700">
                                <td class="py-2"><?= htmlspecialchars($event['type']) ?></td>
                                <td class="py-2"><?= htmlspecialchars($event['message']) ?></td>
                                <td class="py-2 text-right"><?= htmlspecialchars($event['timestamp']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Active Trains -->
    <div class="bg-dashboard-panel rounded shadow p-4 mb-6">
        <h2 class="text-dashboard-header text-lg mb-4">Active Trains</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-dashboard-text text-sm">
                <thead>
                    <tr class="border-b border-gray-700">
                        <th class="text-left py-2 px-3">Train ID</th>
                        <th class="text-left py-2 px-3">Train Name</th>
                        <th class="text-left py-2 px-3">Current Location</th>
                        <th class="text-left py-2 px-3">Next Destination</th>
                        <th class="text-center py-2 px-3">Departure</th>
                        <th class="text-center py-2 px-3">Est. Arrival</th>
                        <th class="text-center py-2 px-3">Delay</th>
                        <th class="text-center py-2 px-3">Speed</th>
                        <th class="text-center py-2 px-3">Occupancy</th>
                        <th class="text-center py-2 px-3">Fuel</th>
                        <th class="text-center py-2 px-3">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($activeTrains as $train): ?>
                        <tr class="border-b border-gray-700 hover:bg-gray-700">
                            <td class="py-2 px-3"><?= htmlspecialchars($train['train_id']) ?></td>
                            <td class="py-2 px-3"><?= htmlspecialchars($train['train_name']) ?></td>
                            <td class="py-2 px-3"><?= htmlspecialchars($train['current_location']) ?></td>
                            <td class="py-2 px-3"><?= htmlspecialchars($train['next_destination']) ?></td>
                            <td class="py-2 px-3 text-center"><?= htmlspecialchars($train['scheduled_departure']) ?></td>
                            <td class="py-2 px-3 text-center"><?= htmlspecialchars($train['estimated_arrival']) ?></td>
                            <td class="py-2 px-3 text-center <?= $train['delay_minutes'] > 0 ? 'text-yellow-400' : 'text-green-400' ?>">
                                <?= $train['delay_minutes'] ?> min
                            </td>
                            <td class="py-2 px-3 text-center"><?= $train['speed'] ?> km/h</td>
                            <td class="py-2 px-3 text-center"><?= number_format($train['occupancy_rate'], 1) ?>%</td>
                            <td class="py-2 px-3 text-center">
                                <div class="w-full bg-gray-700 rounded-full h-2">
                                    <div class="bg-blue-500 h-2 rounded-full" style="width: <?= $train['fuel_level'] ?>%"></div>
                                </div>
                            </td>
                            <td class="py-2 px-3 text-center">
                                <?php if (count($train['alerts']) > 0): ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-900 text-yellow-200">
                                        <span class="w-2 h-2 rounded-full bg-yellow-400 mr-1"></span>
                                        Alert
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-900 text-green-200">
                                        <span class="w-2 h-2 rounded-full bg-green-400 mr-1"></span>
                                        Normal
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Train Details -->
    <h2 class="text-xl font-medium text-dashboard-header mb-4 px-2">Train Details & Alerts</h2>
    
    <div class="grid grid-cols-1 gap-4">
        <?php foreach ($activeTrains as $train): ?>
            <div class="bg-dashboard-panel rounded shadow">
                <div class="p-4 border-b border-gray-700 flex justify-between items-center">
                    <div>
                        <h2 class="text-dashboard-header text-lg">
                            Train <?= htmlspecialchars($train['train_id']) ?> - <?= htmlspecialchars($train['train_name']) ?>
                        </h2>
                        <div class="text-dashboard-subtext text-sm mt-1">
                            Route: <?= htmlspecialchars($train['current_location']) ?> → <?= htmlspecialchars($train['next_destination']) ?> | 
                            Departure: <?= htmlspecialchars($train['scheduled_departure']) ?> | 
                            Est. Arrival: <?= htmlspecialchars($train['estimated_arrival']) ?>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium <?= count($train['alerts']) > 0 ? 'bg-yellow-900 text-yellow-200' : 'bg-green-900 text-green-200' ?>">
                            <span class="w-2 h-2 rounded-full <?= count($train['alerts']) > 0 ? 'bg-yellow-400' : 'bg-green-400' ?> mr-1"></span>
                            <?= count($train['alerts']) > 0 ? 'Alert' : 'Normal' ?>
                        </div>
                        <div class="text-dashboard-subtext text-sm mt-1">Last Updated: <?= htmlspecialchars($train['last_update']) ?></div>
                    </div>
                </div>
                
                <div class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                        <div class="bg-dashboard-dark rounded p-3">
                            <div class="text-dashboard-subtext text-sm">Speed</div>
                            <div class="text-dashboard-header text-xl mt-1"><?= $train['speed'] ?> km/h</div>
                        </div>
                        
                        <div class="bg-dashboard-dark rounded p-3">
                            <div class="text-dashboard-subtext text-sm">Delay</div>
                            <div class="text-dashboard-header text-xl mt-1 <?= $train['delay_minutes'] > 0 ? 'text-yellow-400' : 'text-green-400' ?>">
                                <?= $train['delay_minutes'] ?> minutes
                            </div>
                        </div>
                        
                        <div class="bg-dashboard-dark rounded p-3">
                            <div class="text-dashboard-subtext text-sm">Occupancy</div>
                            <div class="text-dashboard-header text-xl mt-1"><?= number_format($train['occupancy_rate'], 1) ?>%</div>
                        </div>
                        
                        <div class="bg-dashboard-dark rounded p-3">
                            <div class="text-dashboard-subtext text-sm">Fuel Level</div>
                            <div class="mt-2">
                                <div class="w-full bg-gray-700 rounded-full h-4">
                                    <div class="bg-blue-500 h-4 rounded-full" style="width: <?= $train['fuel_level'] ?>%"></div>
                                </div>
                                <div class="text-right mt-1"><?= $train['fuel_level'] ?>%</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <h3 class="text-dashboard-subtext text-sm mb-2">Maintenance Status</h3>
                            <div class="bg-dashboard-dark rounded p-3 mb-4">
                                <div class="flex items-center">
                                    <span class="inline-block w-3 h-3 rounded-full <?= $train['maintenance_status'] === 'Normal' ? 'bg-green-500' : 'bg-yellow-500' ?> mr-2"></span>
                                    <span class="text-dashboard-header"><?= htmlspecialchars($train['maintenance_status']) ?></span>
                                </div>
                                <div class="mt-2">
                                    <div class="text-dashboard-subtext text-sm">Temperature: <?= $train['temperature'] ?>°C</div>
                                    <div class="text-dashboard-subtext text-sm mt-1">Next scheduled maintenance: <?= (new DateTime())->modify('+' . rand(5, 30) . ' days')->format('Y-m-d') ?></div>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <h3 class="text-dashboard-subtext text-sm mb-2">Active Alerts</h3>
                            <?php if (count($train['alerts']) > 0): ?>
                                <div class="bg-dashboard-dark rounded p-3 space-y-2">
                                    <?php foreach ($train['alerts'] as $alert): ?>
                                        <div class="border-l-4 <?= $alert['severity'] === 'High' ? 'border-red-500' : 'border-yellow-500' ?> pl-2">
                                            <div class="flex justify-between">
                                                <div class="font-medium"><?= htmlspecialchars($alert['type']) ?></div>
                                                <div class="text-xs text-dashboard-subtext"><?= htmlspecialchars($alert['timestamp']) ?></div>
                                            </div>
                                            <div class="mt-1 text-sm"><?= htmlspecialchars($alert['message']) ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="bg-dashboard-dark rounded p-3 text-center">
                                    <p>No active alerts for this train</p>
                                </div>
                            <?php endif; ?>
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
echo renderLayout('Remote Management - BookSL Train Dashboard', 'remoteManagement', $content);
?>
