<?php
// Include necessary files
require_once 'includes/db_connection.php';
require_once 'includes/data_service.php';
require_once 'includes/layout.php';

// Load data
$data = transformData();

// Get filter values from query parameters
$selectedYear = isset($_GET['year']) ? $_GET['year'] : 'all';
$selectedClass = isset($_GET['class']) ? $_GET['class'] : 'all';

// Get available years and classes
$availableYears = getAvailableYears();
$availableClasses = [];
foreach ($data as $entry) {
    if (!in_array($entry['class'], $availableClasses)) {
        $availableClasses[] = $entry['class'];
    }
}
sort($availableClasses);

// Filter data based on selected year and class
$filteredData = array_filter($data, function($entry) use ($selectedYear, $selectedClass) {
    $yearMatch = $selectedYear === 'all' || $entry['year'] == $selectedYear;
    $classMatch = $selectedClass === 'all' || $entry['class'] === $selectedClass;
    return $yearMatch && $classMatch;
});

// Calculate statistics
$totalRevenue = array_sum(array_column($filteredData, 'revenue'));
$totalPassengers = array_sum(array_column($filteredData, 'occupancy'));
$totalTrips = count($filteredData);
$avgRevenuePerTrip = $totalTrips > 0 ? $totalRevenue / $totalTrips : 0;
$avgOccupancyRate = $totalTrips > 0 ? array_sum(array_column($filteredData, 'occupancyRate')) / $totalTrips : 0;

// Group data by month for trend analysis
$monthlyData = [];
foreach ($filteredData as $entry) {
    $monthKey = $entry['year'] . '-' . str_pad($entry['month'], 2, '0', STR_PAD_LEFT);
    
    if (!isset($monthlyData[$monthKey])) {
        $monthlyData[$monthKey] = [
            'month' => $monthKey,
            'revenue' => 0,
            'passengers' => 0,
            'trips' => 0
        ];
    }
    
    $monthlyData[$monthKey]['revenue'] += $entry['revenue'];
    $monthlyData[$monthKey]['passengers'] += $entry['occupancy'];
    $monthlyData[$monthKey]['trips']++;
}

// Sort by month
ksort($monthlyData);

// Group data by class
$classSummary = [];
foreach ($filteredData as $entry) {
    $class = $entry['class'];
    
    if (!isset($classSummary[$class])) {
        $classSummary[$class] = [
            'class' => $class,
            'revenue' => 0,
            'passengers' => 0,
            'trips' => 0,
            'occupancyRate' => 0
        ];
    }
    
    $classSummary[$class]['revenue'] += $entry['revenue'];
    $classSummary[$class]['passengers'] += $entry['occupancy'];
    $classSummary[$class]['trips']++;
    $classSummary[$class]['occupancyRate'] += $entry['occupancyRate'];
}

// Calculate averages for class summary
foreach ($classSummary as &$class) {
    $class['avgRevenuePerTrip'] = $class['trips'] > 0 ? $class['revenue'] / $class['trips'] : 0;
    $class['avgOccupancyRate'] = $class['trips'] > 0 ? $class['occupancyRate'] / $class['trips'] : 0;
}

// Group data by route
$routeSummary = [];
foreach ($filteredData as $entry) {
    $route = $entry['from_city'] . ' - ' . $entry['to_city'];
    
    if (!isset($routeSummary[$route])) {
        $routeSummary[$route] = [
            'route' => $route,
            'revenue' => 0,
            'passengers' => 0,
            'trips' => 0
        ];
    }
    
    $routeSummary[$route]['revenue'] += $entry['revenue'];
    $routeSummary[$route]['passengers'] += $entry['occupancy'];
    $routeSummary[$route]['trips']++;
}

// Calculate average revenue per trip for routes
foreach ($routeSummary as &$route) {
    $route['avgRevenuePerTrip'] = $route['trips'] > 0 ? $route['revenue'] / $route['trips'] : 0;
}

// Sort routes by revenue (descending)
usort($routeSummary, function($a, $b) {
    return $b['revenue'] - $a['revenue'];
});

// Get top 5 routes by revenue
$topRoutes = array_slice($routeSummary, 0, 5);

// Sort data by date (descending) for recent sales
usort($filteredData, function($a, $b) {
    return strtotime($b['departure_date']) - strtotime($a['departure_date']);
});

// Get recent sales
$recentSales = array_slice($filteredData, 0, 10);

// Prepare chart data
$monthlyRevenueData = [
    'labels' => array_column($monthlyData, 'month'),
    'values' => array_column($monthlyData, 'revenue'),
    'label' => 'Monthly Revenue (LKR)'
];

$monthlyPassengersData = [
    'labels' => array_column($monthlyData, 'month'),
    'values' => array_column($monthlyData, 'passengers'),
    'label' => 'Monthly Passengers'
];

$classPieData = [
    'labels' => array_column($classSummary, 'class'),
    'values' => array_column($classSummary, 'revenue')
];

$routeRevenueData = [
    'labels' => array_column($topRoutes, 'route'),
    'values' => array_column($topRoutes, 'revenue'),
    'label' => 'Revenue by Route (LKR)'
];

// Define colors
$colors = ['#7e57c2', '#4e7fff', '#b39ddb', '#64b5f6', '#9575cd', '#5c6bc0', '#7986cb', '#4fc3f7'];

// Start building the page content
ob_start();
?>

<div class="p-4">
    <div class="mb-4 px-2 flex justify-between items-center">
        <div>
            <h1 class="text-xl font-medium text-dashboard-header">Ticket Sales</h1>
            <p class="text-dashboard-subtext mt-1">Analyze ticket sales, revenue, and passenger statistics.</p>
        </div>
        <div class="flex items-center space-x-4">
            <form id="filterForm" method="GET" class="flex items-center space-x-4">
                <div>
                    <label for="classSelect" class="text-dashboard-subtext mr-2 text-sm">Class:</label>
                    <select 
                        id="classSelect"
                        name="class"
                        class="bg-dashboard-dark text-dashboard-text border border-gray-700 rounded px-2 py-1 text-sm"
                    >
                        <option value="all" <?= $selectedClass === 'all' ? 'selected' : '' ?>>All Classes</option>
                        <?php foreach ($availableClasses as $class): ?>
                            <option value="<?= htmlspecialchars($class) ?>" <?= $selectedClass === $class ? 'selected' : '' ?>><?= htmlspecialchars($class) ?></option>
                        <?php endforeach; ?>
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
    
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-dashboard-panel rounded shadow p-4">
            <div class="text-dashboard-subtext text-sm">Total Revenue</div>
            <div class="text-dashboard-header text-2xl font-semibold mt-1"><?= number_format($totalRevenue) ?> LKR</div>
            <div class="text-dashboard-subtext text-sm mt-2">Avg. per Trip: <?= number_format($avgRevenuePerTrip) ?> LKR</div>
        </div>
        
        <div class="bg-dashboard-panel rounded shadow p-4">
            <div class="text-dashboard-subtext text-sm">Total Passengers</div>
            <div class="text-dashboard-header text-2xl font-semibold mt-1"><?= number_format($totalPassengers) ?></div>
            <div class="text-dashboard-subtext text-sm mt-2">Avg. Occupancy: <?= number_format($avgOccupancyRate, 1) ?>%</div>
        </div>
        
        <div class="bg-dashboard-panel rounded shadow p-4">
            <div class="text-dashboard-subtext text-sm">Total Trips</div>
            <div class="text-dashboard-header text-2xl font-semibold mt-1"><?= number_format($totalTrips) ?></div>
            <div class="text-dashboard-subtext text-sm mt-2">Across <?= count($routeSummary) ?> Routes</div>
        </div>
        
        <div class="bg-dashboard-panel rounded shadow p-4">
            <div class="text-dashboard-subtext text-sm">Avg. Revenue per Passenger</div>
            <div class="text-dashboard-header text-2xl font-semibold mt-1">
                <?= $totalPassengers > 0 ? number_format($totalRevenue / $totalPassengers, 2) : 0 ?> LKR
            </div>
            <div class="text-dashboard-subtext text-sm mt-2">Based on total ticket sales</div>
        </div>
    </div>
    
    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
        <div class="bg-dashboard-panel rounded shadow p-4">
            <h2 class="text-dashboard-header text-lg mb-4">Monthly Revenue Trend</h2>
            <div style="height: 300px;">
                <canvas class="bar-chart" data-chart='<?= json_encode($monthlyRevenueData) ?>'></canvas>
            </div>
        </div>
        
        <div class="bg-dashboard-panel rounded shadow p-4">
            <h2 class="text-dashboard-header text-lg mb-4">Monthly Passenger Trend</h2>
            <div style="height: 300px;">
                <canvas class="bar-chart" data-chart='<?= json_encode($monthlyPassengersData) ?>'></canvas>
            </div>
        </div>
    </div>
    
    <!-- Revenue by Class and Route -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
        <div class="bg-dashboard-panel rounded shadow p-4">
            <h2 class="text-dashboard-header text-lg mb-4">Revenue by Class</h2>
            <div style="height: 300px;">
                <canvas class="pie-chart" data-chart='<?= json_encode([
                    "labels" => $classPieData['labels'],
                    "values" => $classPieData['values'],
                    "colors" => array_slice($colors, 0, count($classPieData['labels']))
                ]) ?>'></canvas>
            </div>
        </div>
        
        <div class="bg-dashboard-panel rounded shadow p-4">
            <h2 class="text-dashboard-header text-lg mb-4">Top Routes by Revenue</h2>
            <div style="height: 300px;">
                <canvas class="bar-chart" data-chart='<?= json_encode($routeRevenueData) ?>'></canvas>
            </div>
        </div>
    </div>
    
    <!-- Tables Section -->
    <div class="grid grid-cols-1 gap-4">
        <!-- Class Performance Table -->
        <div class="bg-dashboard-panel rounded shadow p-4">
            <h2 class="text-dashboard-header text-lg mb-4">Class Performance</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-dashboard-text text-sm">
                    <thead>
                        <tr class="border-b border-gray-700">
                            <th class="text-left py-2 px-3">Class</th>
                            <th class="text-right py-2 px-3">Total Revenue</th>
                            <th class="text-right py-2 px-3">Avg. Revenue per Trip</th>
                            <th class="text-right py-2 px-3">Total Passengers</th>
                            <th class="text-right py-2 px-3">Avg. Occupancy</th>
                            <th class="text-right py-2 px-3">Total Trips</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($classSummary as $class): ?>
                            <tr class="border-b border-gray-700 hover:bg-gray-700">
                                <td class="py-2 px-3"><?= htmlspecialchars($class['class']) ?></td>
                                <td class="py-2 px-3 text-right"><?= number_format($class['revenue']) ?> LKR</td>
                                <td class="py-2 px-3 text-right"><?= number_format($class['avgRevenuePerTrip']) ?> LKR</td>
                                <td class="py-2 px-3 text-right"><?= number_format($class['passengers']) ?></td>
                                <td class="py-2 px-3 text-right"><?= number_format($class['avgOccupancyRate'], 1) ?>%</td>
                                <td class="py-2 px-3 text-right"><?= number_format($class['trips']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Recent Sales Table -->
        <div class="bg-dashboard-panel rounded shadow p-4">
            <h2 class="text-dashboard-header text-lg mb-4">Recent Ticket Sales</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-dashboard-text text-sm">
                    <thead>
                        <tr class="border-b border-gray-700">
                            <th class="text-left py-2 px-3">Date</th>
                            <th class="text-left py-2 px-3">Train</th>
                            <th class="text-left py-2 px-3">Route</th>
                            <th class="text-center py-2 px-3">Class</th>
                            <th class="text-right py-2 px-3">Occupancy</th>
                            <th class="text-right py-2 px-3">Revenue</th>
                            <th class="text-right py-2 px-3">Avg. Ticket Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentSales as $sale): ?>
                            <tr class="border-b border-gray-700 hover:bg-gray-700">
                                <td class="py-2 px-3"><?= htmlspecialchars($sale['departure_date']) ?></td>
                                <td class="py-2 px-3"><?= htmlspecialchars($sale['train_name']) ?> (<?= htmlspecialchars($sale['train_id']) ?>)</td>
                                <td class="py-2 px-3"><?= htmlspecialchars($sale['from_city']) ?> → <?= htmlspecialchars($sale['to_city']) ?></td>
                                <td class="py-2 px-3 text-center"><?= htmlspecialchars($sale['class']) ?></td>
                                <td class="py-2 px-3 text-right"><?= number_format($sale['occupancy']) ?> (<?= number_format($sale['occupancyRate'], 1) ?>%)</td>
                                <td class="py-2 px-3 text-right"><?= number_format($sale['revenue']) ?> LKR</td>
                                <td class="py-2 px-3 text-right"><?= number_format($sale['occupancy'] > 0 ? $sale['revenue'] / $sale['occupancy'] : 0, 2) ?> LKR</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

// Render the layout with the content
echo renderLayout('Ticket Sales - BookSL Train Dashboard', 'ticketSales', $content);
?>
