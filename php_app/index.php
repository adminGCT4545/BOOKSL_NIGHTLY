<?php
// Include necessary files
require_once 'includes/db_connection.php';
require_once 'includes/data_service.php';
require_once 'includes/layout.php';

// Get filter values from query parameters
$selectedYear = isset($_GET['year']) ? $_GET['year'] : 'all';
$selectedMetric = isset($_GET['metric']) ? $_GET['metric'] : 'occupancy';

// Load data
$data = transformData();
$availableYears = getAvailableYears();
$upcomingDepartures = getUpcomingDepartures(5);

// Filter data based on selected year
$filteredData = $selectedYear === 'all' 
    ? $data 
    : array_filter($data, function($entry) use ($selectedYear) {
        return $entry['year'] == $selectedYear;
    });

// Calculate summary statistics
$summaryStats = calculateSummaryStats($filteredData);

// Prepare data for charts
function prepareTrainMetricData($summaryStats, $selectedMetric) {
    $result = [];
    
    if ($summaryStats && isset($summaryStats['trainStats'])) {
        foreach ($summaryStats['trainStats'] as $train => $stats) {
            $value = 0;
            
            if ($selectedMetric === 'occupancy') {
                $value = $stats['avgOccupancyRate'];
            } else if ($selectedMetric === 'delay') {
                $value = $stats['avgDelay'];
            } else if ($selectedMetric === 'revenue') {
                $value = $stats['totalRevenue'];
            }
            
            $result[] = [
                'train' => $train,
                'value' => $value
            ];
        }
    }
    
    return $result;
}

function prepareDelayTrendData($summaryStats) {
    $result = [];
    $allMonths = [];
    
    // Combine all months from all trains to get a complete list
    if ($summaryStats && isset($summaryStats['trainStats'])) {
        foreach ($summaryStats['trainStats'] as $train => $stats) {
            foreach ($stats['delayTrend'] as $item) {
                $allMonths[$item['month']] = true;
            }
        }
    }
    
    // Create data points for all months and trains
    $sortedMonths = array_keys($allMonths);
    sort($sortedMonths);
    
    foreach ($sortedMonths as $month) {
        $dataPoint = ['month' => $month];
        
        if ($summaryStats && isset($summaryStats['trainStats'])) {
            foreach ($summaryStats['trainStats'] as $train => $stats) {
                $found = false;
                foreach ($stats['delayTrend'] as $item) {
                    if ($item['month'] === $month) {
                        $dataPoint[$train] = isset($item['avgDelay']) ? $item['avgDelay'] : 0;
                        $found = true;
                        break;
                    }
                }
                
                if (!$found) {
                    $dataPoint[$train] = 0;
                }
            }
        }
        
        $result[] = $dataPoint;
    }
    
    return $result;
}

function prepareCityPairData($summaryStats) {
    $result = [];
    
    if ($summaryStats && isset($summaryStats['cityPairStats'])) {
        foreach ($summaryStats['cityPairStats'] as $cityPair => $stats) {
            $result[] = [
                'cityPair' => $cityPair,
                'avgDelay' => $stats['avgDelay'],
                'totalRevenue' => $stats['totalRevenue']
            ];
        }
        
        // Sort by average delay
        usort($result, function($a, $b) {
            return $b['avgDelay'] - $a['avgDelay'];
        });
    }
    
    return array_slice($result, 0, 5); // Top 5 city pairs by delay
}

function prepareClassData($summaryStats) {
    $result = [];
    
    if ($summaryStats && isset($summaryStats['classStats'])) {
        foreach ($summaryStats['classStats'] as $class => $stats) {
            $result[] = [
                'name' => $class,
                'value' => $stats['totalRevenue']
            ];
        }
    }
    
    return $result;
}

// Prepare chart data
$trainMetricData = prepareTrainMetricData($summaryStats, $selectedMetric);
$delayTrendData = prepareDelayTrendData($summaryStats);
$cityPairData = prepareCityPairData($summaryStats);
$classData = prepareClassData($summaryStats);

// Define colors for charts
$trainColors = [
    '101' => '#7e57c2',
    '102' => '#4e7fff',
    '103' => '#b39ddb',
    '104' => '#64b5f6',
    '105' => '#9575cd',
    '106' => '#5c6bc0',
    '107' => '#7986cb',
    '108' => '#4fc3f7'
];

$colors = ['#7e57c2', '#4e7fff', '#b39ddb', '#64b5f6', '#9575cd', '#5c6bc0', '#7986cb', '#4fc3f7'];

// Start building the page content
ob_start();
?>

<div class="p-4">
    <div class="mb-4 px-2 flex justify-between items-center">
        <h1 class="text-xl font-medium text-dashboard-header">Train Operations Dashboard</h1>
        <div class="flex items-center space-x-4">
            <form id="filterForm" method="GET" class="flex items-center space-x-4">
                <div>
                    <label for="metricSelect" class="text-dashboard-subtext mr-2 text-sm">Metric:</label>
                    <select 
                        id="metricSelect"
                        name="metric"
                        class="bg-dashboard-dark text-dashboard-text border border-gray-700 rounded px-2 py-1 text-sm"
                    >
                        <option value="occupancy" <?= $selectedMetric === 'occupancy' ? 'selected' : '' ?>>Occupancy Rate</option>
                        <option value="delay" <?= $selectedMetric === 'delay' ? 'selected' : '' ?>>Delay</option>
                        <option value="revenue" <?= $selectedMetric === 'revenue' ? 'selected' : '' ?>>Revenue</option>
                    </select>
                </div>
                <div>
                    <label for="yearSelect" class="text-dashboard-subtext mr-2 text-sm">Year:</label>
                    <select 
                        id="yearSelect"
                        name="year"
                        class="bg-dashboard-dark text-dashboard-text border border-gray-700 rounded px-2 py-1 text-sm"
                    >
                        <option value="all" <?= $selectedYear === 'all' ? 'selected' : '' ?>>All Years</option>
                        <?php foreach ($availableYears as $year): ?>
                            <option value="<?= $year ?>" <?= $selectedYear == $year ? 'selected' : '' ?>><?= $year ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Main Dashboard Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
        <!-- Train Schedule & Status Panel -->
        <div class="bg-dashboard-panel rounded shadow p-4">
            <h2 class="text-dashboard-header text-lg mb-4">Train Schedule & Status</h2>
            
            <div class="mb-4">
                <h3 class="text-dashboard-subtext text-sm mb-2">Upcoming Departures</h3>
                <div class="bg-dashboard-dark rounded p-2">
                    <table class="w-full text-dashboard-text text-sm">
                        <thead>
                            <tr class="border-b border-gray-700">
                                <th class="text-left py-2">Train</th>
                                <th class="text-left py-2">Route</th>
                                <th class="text-right py-2">Time</th>
                                <th class="text-right py-2">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($upcomingDepartures, 0, 5) as $train): ?>
                                <tr>
                                    <td class="py-2"><?= htmlspecialchars($train['train_name']) ?></td>
                                    <td class="py-2"><?= htmlspecialchars($train['from_city']) ?> → <?= htmlspecialchars($train['to_city']) ?></td>
                                    <td class="py-2 text-right"><?= htmlspecialchars($train['scheduled_time']) ?></td>
                                    <td class="py-2 text-right <?= $train['delay_minutes'] > 0 ? 'text-yellow-400' : 'text-green-400' ?>">
                                        <?= $train['delay_minutes'] > 0 ? "Delayed {$train['delay_minutes']}m" : 'On Time' ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="mb-4">
                <h3 class="text-dashboard-subtext text-sm mb-2">Delay by Train</h3>
                <div style="height: 180px;">
                    <canvas class="bar-chart" data-chart='<?= json_encode([
                        "labels" => array_column(array_filter($trainMetricData, function($item) use ($selectedMetric) {
                            return $selectedMetric === 'delay';
                        }), 'train'),
                        "values" => array_column(array_filter($trainMetricData, function($item) use ($selectedMetric) {
                            return $selectedMetric === 'delay';
                        }), 'value'),
                        "label" => "Avg. Delay (minutes)",
                        "colors" => array_map(function($train) use ($trainColors) {
                            return $trainColors[$train['train']] ?? '#7e57c2';
                        }, array_filter($trainMetricData, function($item) use ($selectedMetric) {
                            return $selectedMetric === 'delay';
                        }))
                    ]) ?>'></canvas>
                </div>
            </div>
            
            <div class="mt-4">
                <h3 class="text-dashboard-subtext text-sm mb-2">Route Performance</h3>
                <div class="bg-dashboard-dark rounded p-2">
                    <table class="w-full text-dashboard-text text-sm">
                        <thead>
                            <tr class="border-b border-gray-700">
                                <th class="text-left py-2">Route</th>
                                <th class="text-right py-2">Avg. Delay</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($summaryStats['cityPairStats'] as $cityPair => $stats): ?>
                                <tr>
                                    <td class="py-2"><?= htmlspecialchars($cityPair) ?></td>
                                    <td class="py-2 text-right"><?= number_format($stats['avgDelay'], 1) ?> min</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Ticket Sales & Revenue Panel -->
        <div class="bg-dashboard-panel rounded shadow p-4">
            <h2 class="text-dashboard-header text-lg mb-4">Ticket Sales & Revenue</h2>
            
            <div class="mb-4">
                <h3 class="text-dashboard-subtext text-sm mb-2">Revenue by Train</h3>
                <div style="height: 180px;">
                    <canvas class="bar-chart" data-chart='<?= json_encode([
                        "labels" => array_column(array_filter($trainMetricData, function($item) use ($selectedMetric) {
                            return $selectedMetric === 'revenue';
                        }), 'train'),
                        "values" => array_column(array_filter($trainMetricData, function($item) use ($selectedMetric) {
                            return $selectedMetric === 'revenue';
                        }), 'value'),
                        "label" => "Revenue (LKR)",
                        "colors" => array_map(function($train) use ($trainColors) {
                            return $trainColors[$train['train']] ?? '#7e57c2';
                        }, array_filter($trainMetricData, function($item) use ($selectedMetric) {
                            return $selectedMetric === 'revenue';
                        }))
                    ]) ?>'></canvas>
                </div>
            </div>
            
            <div class="mb-4">
                <h3 class="text-dashboard-subtext text-sm mb-2">Revenue by Class</h3>
                <div style="height: 180px;">
                    <canvas class="pie-chart" data-chart='<?= json_encode([
                        "labels" => array_column($classData, 'name'),
                        "values" => array_column($classData, 'value'),
                        "colors" => array_slice($colors, 0, count($classData))
                    ]) ?>'></canvas>
                </div>
            </div>
            
            <div class="mt-4">
                <h3 class="text-dashboard-subtext text-sm mb-2">Revenue Summary</h3>
                <div class="bg-dashboard-dark rounded p-3">
                    <div class="flex justify-between mb-2">
                        <span class="text-dashboard-subtext">Total Revenue</span>
                        <span class="text-dashboard-text"><?= number_format($summaryStats['totalRevenue']) ?> LKR</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-dashboard-subtext">Avg. Revenue per Trip</span>
                        <span class="text-dashboard-text">
                            <?= number_format($summaryStats['totalRevenue'] / count($filteredData)) ?> LKR
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Occupancy & Capacity Panel -->
        <div class="bg-dashboard-panel rounded shadow p-4">
            <h2 class="text-dashboard-header text-lg mb-4">Occupancy & Capacity</h2>
            
            <div class="mb-6">
                <h3 class="text-dashboard-subtext text-sm mb-2">Overall Occupancy Rate</h3>
                <div class="w-full bg-gray-700 rounded-full h-4 mb-4">
                    <div 
                        class="bg-green-400 h-4 rounded-full" 
                        style="width: <?= $summaryStats['averageOccupancyRate'] ?>%"
                    ></div>
                </div>
                
                <div class="bg-dashboard-dark rounded">
                    <div class="grid grid-cols-2 border-b border-gray-700">
                        <div class="p-2 text-dashboard-subtext">Average Occupancy</div>
                        <div class="p-2 text-dashboard-text text-right"><?= number_format($summaryStats['averageOccupancyRate'], 1) ?>%</div>
                    </div>
                    <?php foreach ($summaryStats['trainStats'] as $train => $stats): ?>
                        <div class="grid grid-cols-2 border-b border-gray-700">
                            <div class="p-2 text-dashboard-subtext">Train <?= htmlspecialchars($train) ?></div>
                            <div class="p-2 text-dashboard-text text-right"><?= number_format($stats['avgOccupancyRate'], 1) ?>%</div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="mt-4">
                <h3 class="text-dashboard-subtext text-sm mb-2">Occupancy by Train</h3>
                <div style="height: 180px;">
                    <canvas class="bar-chart" data-chart='<?= json_encode([
                        "labels" => array_column(array_filter($trainMetricData, function($item) use ($selectedMetric) {
                            return $selectedMetric === 'occupancy';
                        }), 'train'),
                        "values" => array_column(array_filter($trainMetricData, function($item) use ($selectedMetric) {
                            return $selectedMetric === 'occupancy';
                        }), 'value'),
                        "label" => "Occupancy Rate (%)",
                        "colors" => array_map(function($train) use ($trainColors) {
                            return $trainColors[$train['train']] ?? '#7e57c2';
                        }, array_filter($trainMetricData, function($item) use ($selectedMetric) {
                            return $selectedMetric === 'occupancy';
                        }))
                    ]) ?>'></canvas>
                </div>
            </div>
        </div>
        
        <!-- Performance Metrics Panel -->
        <div class="bg-dashboard-panel rounded shadow p-4">
            <h2 class="text-dashboard-header text-lg mb-4">Performance Metrics</h2>
            
            <div class="mb-6">
                <h3 class="text-dashboard-subtext text-sm mb-2">Delay Trend</h3>
                <div style="height: 180px;">
                    <?php
                    // Prepare data for line chart
                    $lineChartData = [
                        'labels' => array_column($delayTrendData, 'month'),
                        'datasets' => []
                    ];
                    
                    foreach ($summaryStats['trainStats'] as $train => $stats) {
                        $values = [];
                        foreach ($delayTrendData as $dataPoint) {
                            $values[] = isset($dataPoint[$train]) ? $dataPoint[$train] : 0;
                        }
                        
                        $lineChartData['datasets'][] = [
                            'label' => "Train $train",
                            'values' => $values,
                            'color' => $trainColors[$train] ?? null
                        ];
                    }
                    ?>
                    <canvas class="line-chart" data-chart='<?= json_encode($lineChartData) ?>'></canvas>
                </div>
            </div>
            
            <div class="mt-4">
                <h3 class="text-dashboard-subtext text-sm mb-2">Key Performance Indicators</h3>
                <div class="bg-dashboard-dark rounded">
                    <div class="grid grid-cols-2 border-b border-gray-700">
                        <div class="p-2 text-dashboard-subtext">On-Time Performance</div>
                        <div class="p-2 text-dashboard-text text-right">
                            <?= number_format(count(array_filter($filteredData, function($train) {
                                return $train['delay_minutes'] === 0;
                            })) / count($filteredData) * 100, 1) ?>%
                        </div>
                    </div>
                    <div class="grid grid-cols-2 border-b border-gray-700">
                        <div class="p-2 text-dashboard-subtext">Avg. Delay</div>
                        <div class="p-2 text-dashboard-text text-right"><?= number_format($summaryStats['averageDelay'], 1) ?> min</div>
                    </div>
                    <div class="grid grid-cols-2 border-b border-gray-700">
                        <div class="p-2 text-dashboard-subtext">Total Trips</div>
                        <div class="p-2 text-dashboard-text text-right"><?= count($filteredData) ?></div>
                    </div>
                    <div class="grid grid-cols-2">
                        <div class="p-2 text-dashboard-subtext">Total Passengers</div>
                        <div class="p-2 text-dashboard-text text-right">
                            <?= number_format(array_sum(array_column($filteredData, 'occupancy'))) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

// Render the layout with the content
echo renderLayout('BookSL Train Dashboard', 'dashboard', $content);
?>
