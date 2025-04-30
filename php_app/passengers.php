<?php
// Include necessary files
require_once 'includes/db_connection.php';
require_once 'includes/layout.php';

// Function to get passenger data from the database
function getPassengerData() {
    $conn = getDbConnection();
    if (!$conn) {
        return [];
    }

    try {
        $sql = "SELECT * FROM passengers ORDER BY rider_number";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('Error fetching passenger data: ' . $e->getMessage());
        return [];
    }
}

// Function to calculate KPIs from passenger data
function calculatePassengerKPIs($passengers) {
    if (empty($passengers)) {
        return [
            'totalPassengers' => 0,
            'avgSpendPerRide' => 0,
            'avgMonthlyTotal' => 0,
            'avgUsageFrequency' => 0,
            'percentUseOtherTrains' => 0,
            'topOriginatingStations' => [],
            'topDestinationStations' => [],
            'topSpendingPassengers' => [],
            'mostFrequentTravelers' => []
        ];
    }

    // Calculate basic KPIs
    $totalPassengers = count($passengers);
    $totalSpendPerRide = array_sum(array_column($passengers, 'spend_per_ride'));
    $totalMonthlySpend = array_sum(array_column($passengers, 'monthly_total'));
    $totalUsageFrequency = array_sum(array_column($passengers, 'usage_frequency'));
    $useOtherTrainsCount = count(array_filter($passengers, function($p) {
        return $p['uses_other_trains'] == true;
    }));

    // Calculate averages
    $avgSpendPerRide = $totalSpendPerRide / $totalPassengers;
    $avgMonthlyTotal = $totalMonthlySpend / $totalPassengers;
    $avgUsageFrequency = $totalUsageFrequency / $totalPassengers;
    $percentUseOtherTrains = ($useOtherTrainsCount / $totalPassengers) * 100;

    // Count originating stations
    $originatingStations = [];
    foreach ($passengers as $passenger) {
        $station = $passenger['originating_station'];
        if (!isset($originatingStations[$station])) {
            $originatingStations[$station] = 0;
        }
        $originatingStations[$station]++;
    }
    arsort($originatingStations);
    $topOriginatingStations = array_slice($originatingStations, 0, 5, true);

    // Count most visited stations
    $destinationStations = [];
    foreach ($passengers as $passenger) {
        $station = $passenger['most_visited_station'];
        if (!isset($destinationStations[$station])) {
            $destinationStations[$station] = 0;
        }
        $destinationStations[$station]++;
    }
    arsort($destinationStations);
    $topDestinationStations = array_slice($destinationStations, 0, 5, true);

    // Get top spending passengers
    $spendingPassengers = $passengers;
    usort($spendingPassengers, function($a, $b) {
        return $b['monthly_total'] - $a['monthly_total'];
    });
    $topSpendingPassengers = array_slice($spendingPassengers, 0, 5);

    // Get most frequent travelers
    $frequentTravelers = $passengers;
    usort($frequentTravelers, function($a, $b) {
        return $b['usage_frequency'] - $a['usage_frequency'];
    });
    $mostFrequentTravelers = array_slice($frequentTravelers, 0, 5);

    return [
        'totalPassengers' => $totalPassengers,
        'avgSpendPerRide' => $avgSpendPerRide,
        'avgMonthlyTotal' => $avgMonthlyTotal,
        'avgUsageFrequency' => $avgUsageFrequency,
        'percentUseOtherTrains' => $percentUseOtherTrains,
        'topOriginatingStations' => $topOriginatingStations,
        'topDestinationStations' => $topDestinationStations,
        'topSpendingPassengers' => $topSpendingPassengers,
        'mostFrequentTravelers' => $mostFrequentTravelers
    ];
}

// Function to get train usage distribution
function getTrainUsageDistribution($passengers) {
    $trainUsage = [];
    
    foreach ($passengers as $passenger) {
        if ($passenger['uses_other_trains'] && !empty($passenger['other_trains_used'])) {
            $trains = explode(';', $passenger['other_trains_used']);
            foreach ($trains as $train) {
                if (!isset($trainUsage[$train])) {
                    $trainUsage[$train] = 0;
                }
                $trainUsage[$train]++;
            }
        }
    }
    
    arsort($trainUsage);
    return $trainUsage;
}

// Get passenger data
$passengers = getPassengerData();
$kpis = calculatePassengerKPIs($passengers);
$trainUsage = getTrainUsageDistribution($passengers);

// Define colors for charts
$colors = ['#7e57c2', '#4e7fff', '#b39ddb', '#64b5f6', '#9575cd', '#5c6bc0', '#7986cb', '#4fc3f7'];

// Start building the page content
ob_start();
?>

<div class="p-4">
    <div class="mb-4 px-2">
        <h1 class="text-xl font-medium text-dashboard-header">Passenger Analytics Dashboard</h1>
    </div>
    
    <!-- Main Dashboard Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
        <!-- Key Metrics Panel -->
        <div class="bg-dashboard-panel rounded shadow p-4">
            <h2 class="text-dashboard-header text-lg mb-4">Key Metrics</h2>
            
            <div class="grid grid-cols-1 gap-4">
                <!-- Total Passengers -->
                <div class="bg-dashboard-dark rounded p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-dashboard-subtext text-sm">Total Passengers</p>
                            <p class="text-dashboard-header text-2xl font-bold"><?= number_format($kpis['totalPassengers']) ?></p>
                        </div>
                        <div class="bg-dashboard-purple rounded-full p-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
                
                <!-- Average Spend Per Ride -->
                <div class="bg-dashboard-dark rounded p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-dashboard-subtext text-sm">Avg. Spend Per Ride</p>
                            <p class="text-dashboard-header text-2xl font-bold"><?= number_format($kpis['avgSpendPerRide'], 2) ?> LKR</p>
                        </div>
                        <div class="bg-dashboard-blue rounded-full p-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
                
                <!-- Average Monthly Total -->
                <div class="bg-dashboard-dark rounded p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-dashboard-subtext text-sm">Avg. Monthly Spend</p>
                            <p class="text-dashboard-header text-2xl font-bold"><?= number_format($kpis['avgMonthlyTotal'], 2) ?> LKR</p>
                        </div>
                        <div class="bg-dashboard-light-purple rounded-full p-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>
                </div>
                
                <!-- Average Usage Frequency -->
                <div class="bg-dashboard-dark rounded p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-dashboard-subtext text-sm">Avg. Rides Per Month</p>
                            <p class="text-dashboard-header text-2xl font-bold"><?= number_format($kpis['avgUsageFrequency'], 1) ?></p>
                        </div>
                        <div class="bg-green-500 rounded-full p-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Passenger Distribution Panel -->
        <div class="bg-dashboard-panel rounded shadow p-4">
            <h2 class="text-dashboard-header text-lg mb-4">Passenger Distribution</h2>
            
            <div class="mb-4">
                <h3 class="text-dashboard-subtext text-sm mb-2">Other Train Usage</h3>
                <div class="bg-dashboard-dark rounded p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-dashboard-subtext">Use Other Trains</span>
                        <span class="text-dashboard-text"><?= number_format($kpis['percentUseOtherTrains'], 1) ?>%</span>
                    </div>
                    <div class="w-full bg-gray-700 rounded-full h-4">
                        <div 
                            class="bg-dashboard-purple h-4 rounded-full" 
                            style="width: <?= $kpis['percentUseOtherTrains'] ?>%"
                        ></div>
                    </div>
                </div>
            </div>
            
            <div>
                <h3 class="text-dashboard-subtext text-sm mb-2">Other Trains Used</h3>
                <div style="height: 220px;">
                    <canvas class="pie-chart" data-chart='<?= json_encode([
                        "labels" => array_keys($trainUsage),
                        "values" => array_values($trainUsage),
                        "colors" => array_slice($colors, 0, count($trainUsage))
                    ]) ?>'></canvas>
                </div>
            </div>
        </div>
        
        <!-- Station Analysis Panel -->
        <div class="bg-dashboard-panel rounded shadow p-4">
            <h2 class="text-dashboard-header text-lg mb-4">Station Analysis</h2>
            
            <div class="mb-4">
                <h3 class="text-dashboard-subtext text-sm mb-2">Top Originating Stations</h3>
                <div style="height: 180px;">
                    <canvas class="bar-chart" data-chart='<?= json_encode([
                        "labels" => array_keys($kpis['topOriginatingStations']),
                        "values" => array_values($kpis['topOriginatingStations']),
                        "label" => "Passengers",
                        "colors" => array_slice($colors, 0, count($kpis['topOriginatingStations']))
                    ]) ?>'></canvas>
                </div>
            </div>
            
            <div>
                <h3 class="text-dashboard-subtext text-sm mb-2">Top Destination Stations</h3>
                <div style="height: 180px;">
                    <canvas class="bar-chart" data-chart='<?= json_encode([
                        "labels" => array_keys($kpis['topDestinationStations']),
                        "values" => array_values($kpis['topDestinationStations']),
                        "label" => "Passengers",
                        "colors" => array_slice($colors, 0, count($kpis['topDestinationStations']))
                    ]) ?>'></canvas>
                </div>
            </div>
        </div>
        
        <!-- Top Passengers Panel -->
        <div class="bg-dashboard-panel rounded shadow p-4">
            <h2 class="text-dashboard-header text-lg mb-4">Top Passengers</h2>
            
            <div class="mb-4">
                <h3 class="text-dashboard-subtext text-sm mb-2">Top Spending Passengers</h3>
                <div class="bg-dashboard-dark rounded p-2">
                    <table class="w-full text-dashboard-text text-sm">
                        <thead>
                            <tr class="border-b border-gray-700">
                                <th class="text-left py-2">Passenger</th>
                                <th class="text-right py-2">Monthly Spend</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($kpis['topSpendingPassengers'] as $passenger): ?>
                                <tr class="border-b border-gray-700">
                                    <td class="py-2"><?= htmlspecialchars($passenger['first_name'] . ' ' . $passenger['last_name']) ?></td>
                                    <td class="py-2 text-right"><?= number_format($passenger['monthly_total'], 2) ?> LKR</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div>
                <h3 class="text-dashboard-subtext text-sm mb-2">Most Frequent Travelers</h3>
                <div class="bg-dashboard-dark rounded p-2">
                    <table class="w-full text-dashboard-text text-sm">
                        <thead>
                            <tr class="border-b border-gray-700">
                                <th class="text-left py-2">Passenger</th>
                                <th class="text-right py-2">Rides/Month</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($kpis['mostFrequentTravelers'] as $passenger): ?>
                                <tr class="border-b border-gray-700">
                                    <td class="py-2"><?= htmlspecialchars($passenger['first_name'] . ' ' . $passenger['last_name']) ?></td>
                                    <td class="py-2 text-right"><?= $passenger['usage_frequency'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


<?php
$content = ob_get_clean();

// Render the layout with the content
echo renderLayout('BookSL Train - Passenger Analytics', 'passengers', $content);
?>
