<?php
require_once 'db_connection.php';
require_once 'db_interceptor.php';

/**
 * Transform data from the database into a format usable by the dashboard
 * @return array Transformed train data
 */
function transformData() {
    $conn = getLoggingDbConnection();
    if (!$conn) {
        return getFallbackData();
    }

    try {
        // Query to join train_journeys with trains and train_schedules
        $sql = "
            SELECT 
                j.journey_id, 
                j.train_id, 
                t.train_name, 
                j.departure_city, 
                j.arrival_city, 
                j.journey_date, 
                j.class, 
                s.scheduled_time, 
                j.is_delayed, 
                CASE WHEN j.is_delayed THEN s.default_delay_minutes ELSE 0 END as delay_minutes,
                j.total_seats, 
                j.reserved_seats, 
                j.revenue
            FROM 
                train_journeys j
            JOIN 
                trains t ON j.train_id = t.train_id
            JOIN 
                train_schedules s ON j.train_id = s.train_id
            ORDER BY 
                j.journey_date
        ";

        $stmt = executeQuery($conn, $sql);
        $entries = [];
        $id = 1;

        while ($row = $stmt->fetch()) {
            $scheduledTime = substr($row['scheduled_time'], 0, 8); // Format: HH:MM:SS
            $delayMinutes = (int)$row['delay_minutes'];
            $actualTime = calculateActualTime($scheduledTime, $delayMinutes);
            $date = new DateTime($row['journey_date']);
            
            $entries[] = [
                'id' => $id++,
                'train_id' => (string)$row['train_id'],
                'train_name' => $row['train_name'],
                'departure_date' => $row['journey_date'],
                'from_city' => $row['departure_city'],
                'to_city' => $row['arrival_city'],
                'class' => $row['class'],
                'scheduled_time' => $scheduledTime,
                'actual_time' => $actualTime,
                'delay_minutes' => $delayMinutes,
                'capacity' => (int)$row['total_seats'],
                'occupancy' => (int)$row['reserved_seats'],
                'revenue' => (float)$row['revenue'],
                'occupancyRate' => ((int)$row['reserved_seats'] / (int)$row['total_seats']) * 100,
                'date' => $date,
                'year' => (int)$date->format('Y'),
                'month' => (int)$date->format('n'),
                'quarter' => ceil((int)$date->format('n') / 3)
            ];
        }

        return $entries;
    } catch (PDOException $e) {
        error_log('Database query failed: ' . $e->getMessage());
        return getFallbackData();
    }
}

/**
 * Calculate actual time based on scheduled time and delay
 * @param string $scheduledTime Scheduled time in HH:MM:SS format
 * @param int $delayMinutes Delay in minutes
 * @return string Actual time in HH:MM:SS format
 */
function calculateActualTime($scheduledTime, $delayMinutes) {
    $scheduled = new DateTime('2000-01-01T' . $scheduledTime);
    $scheduled->modify("+{$delayMinutes} minutes");
    return $scheduled->format('H:i:s');
}

/**
 * Get all available years in the data
 * @return array Array of years
 */
function getAvailableYears() {
    $conn = getLoggingDbConnection();
    if (!$conn) {
        // Extract years from fallback data
        $fallbackData = getFallbackData();
        $years = array_unique(array_column($fallbackData, 'year'));
        sort($years);
        return $years;
    }

    try {
        $sql = "
            SELECT DISTINCT EXTRACT(YEAR FROM journey_date) as year
            FROM train_journeys
            ORDER BY year
        ";
        
        $stmt = executeQuery($conn, $sql);
        $years = [];
        
        while ($row = $stmt->fetch()) {
            $years[] = (int)$row['year'];
        }
        
        return $years;
    } catch (PDOException $e) {
        error_log('Error getting available years: ' . $e->getMessage());
        
        // Extract years from fallback data
        $fallbackData = getFallbackData();
        $years = array_unique(array_column($fallbackData, 'year'));
        sort($years);
        return $years;
    }
}

/**
 * Get upcoming departures
 * @param int $count Number of departures to return
 * @return array Array of upcoming departures
 */
function getUpcomingDepartures($count = 5) {
    $conn = getLoggingDbConnection();
    if (!$conn) {
        // Use the first few entries from fallback data as upcoming departures
        $fallbackData = getFallbackData();
        $departures = array_slice($fallbackData, 0, $count);
        
        return array_map(function($entry) {
            return [
                'train_id' => $entry['train_id'],
                'train_name' => $entry['train_name'],
                'from_city' => $entry['from_city'],
                'to_city' => $entry['to_city'],
                'scheduled_time' => $entry['scheduled_time'],
                'actual_time' => $entry['actual_time'],
                'delay_minutes' => $entry['delay_minutes'],
                'departure_date' => $entry['departure_date']
            ];
        }, $departures);
    }

    try {
        $sql = "
            SELECT DISTINCT ON (j.train_id, j.departure_city, j.arrival_city)
                j.train_id,
                t.train_name,
                j.departure_city as from_city,
                j.arrival_city as to_city,
                s.scheduled_time,
                j.is_delayed,
                CASE WHEN j.is_delayed THEN s.default_delay_minutes ELSE 0 END as delay_minutes,
                j.journey_date as departure_date
            FROM 
                train_journeys j
            JOIN 
                trains t ON j.train_id = t.train_id
            JOIN 
                train_schedules s ON j.train_id = s.train_id
            ORDER BY 
                j.train_id, j.departure_city, j.arrival_city, j.journey_date
            LIMIT :count
        ";
        
        $stmt = executeQuery($conn, $sql, ['count' => $count]);
        
        $departures = [];
        
        while ($row = $stmt->fetch()) {
            $scheduledTime = substr($row['scheduled_time'], 0, 8);
            $delayMinutes = (int)$row['delay_minutes'];
            
            $departures[] = [
                'train_id' => $row['train_id'],
                'train_name' => $row['train_name'],
                'from_city' => $row['from_city'],
                'to_city' => $row['to_city'],
                'scheduled_time' => $scheduledTime,
                'actual_time' => calculateActualTime($scheduledTime, $delayMinutes),
                'delay_minutes' => $delayMinutes,
                'departure_date' => $row['departure_date']
            ];
        }
        
        return $departures;
    } catch (PDOException $e) {
        error_log('Error getting upcoming departures: ' . $e->getMessage());
        
        // Use the first few entries from fallback data as upcoming departures
        $fallbackData = getFallbackData();
        $departures = array_slice($fallbackData, 0, $count);
        
        return array_map(function($entry) {
            return [
                'train_id' => $entry['train_id'],
                'train_name' => $entry['train_name'],
                'from_city' => $entry['from_city'],
                'to_city' => $entry['to_city'],
                'scheduled_time' => $entry['scheduled_time'],
                'actual_time' => $entry['actual_time'],
                'delay_minutes' => $entry['delay_minutes'],
                'departure_date' => $entry['departure_date']
            ];
        }, $departures);
    }
}

/**
 * Calculate summary statistics from the data
 * @param array $data Train data
 * @return array Summary statistics
 */
function calculateSummaryStats($data) {
    $stats = [
        'totalRevenue' => 0,
        'averageOccupancyRate' => 0,
        'averageDelay' => 0,
        'trainStats' => [],
        'cityPairStats' => [],
        'classStats' => []
    ];
    
    // Initialize train stats
    foreach (['101', '102', '103', '104', '105', '106', '107', '108'] as $train) {
        $stats['trainStats'][$train] = [
            'count' => 0,
            'totalRevenue' => 0,
            'avgOccupancyRate' => 0,
            'avgDelay' => 0,
            'delayTrend' => []
        ];
    }
    
    // Aggregate data
    foreach ($data as $entry) {
        // Overall stats
        $stats['totalRevenue'] += $entry['revenue'];
        $stats['averageOccupancyRate'] += $entry['occupancyRate'];
        $stats['averageDelay'] += $entry['delay_minutes'];
        
        // Train specific stats
        $train = $entry['train_id'];
        if (isset($stats['trainStats'][$train])) {
            $stats['trainStats'][$train]['count'] += 1;
            $stats['trainStats'][$train]['totalRevenue'] += $entry['revenue'];
            $stats['trainStats'][$train]['avgOccupancyRate'] += $entry['occupancyRate'];
            $stats['trainStats'][$train]['avgDelay'] += $entry['delay_minutes'];
            
            // Track delay by month for trend analysis
            $monthKey = $entry['year'] . '-' . str_pad($entry['month'], 2, '0', STR_PAD_LEFT);
            $found = false;
            
            foreach ($stats['trainStats'][$train]['delayTrend'] as $key => $trend) {
                if ($trend['month'] === $monthKey) {
                    $stats['trainStats'][$train]['delayTrend'][$key]['delay'] += $entry['delay_minutes'];
                    $stats['trainStats'][$train]['delayTrend'][$key]['count'] += 1;
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                $stats['trainStats'][$train]['delayTrend'][] = [
                    'month' => $monthKey,
                    'delay' => $entry['delay_minutes'],
                    'count' => 1
                ];
            }
        }
        
        // City pair stats
        $cityPair = $entry['from_city'] . '-' . $entry['to_city'];
        if (!isset($stats['cityPairStats'][$cityPair])) {
            $stats['cityPairStats'][$cityPair] = [
                'count' => 0,
                'totalRevenue' => 0,
                'avgDelay' => 0
            ];
        }
        $stats['cityPairStats'][$cityPair]['count'] += 1;
        $stats['cityPairStats'][$cityPair]['totalRevenue'] += $entry['revenue'];
        $stats['cityPairStats'][$cityPair]['avgDelay'] += $entry['delay_minutes'];
        
        // Class stats
        if (!isset($stats['classStats'][$entry['class']])) {
            $stats['classStats'][$entry['class']] = [
                'count' => 0,
                'totalRevenue' => 0,
                'avgOccupancyRate' => 0
            ];
        }
        $stats['classStats'][$entry['class']]['count'] += 1;
        $stats['classStats'][$entry['class']]['totalRevenue'] += $entry['revenue'];
        $stats['classStats'][$entry['class']]['avgOccupancyRate'] += $entry['occupancyRate'];
    }
    
    // Calculate averages
    $dataCount = count($data);
    if ($dataCount > 0) {
        $stats['averageOccupancyRate'] = $stats['averageOccupancyRate'] / $dataCount;
        $stats['averageDelay'] = $stats['averageDelay'] / $dataCount;
    }
    
    // Calculate averages for train stats
    foreach ($stats['trainStats'] as $train => $trainStat) {
        $count = $trainStat['count'];
        if ($count > 0) {
            $stats['trainStats'][$train]['avgOccupancyRate'] = $trainStat['avgOccupancyRate'] / $count;
            $stats['trainStats'][$train]['avgDelay'] = $trainStat['avgDelay'] / $count;
            
            // Calculate average delay by month
            foreach ($stats['trainStats'][$train]['delayTrend'] as $key => $month) {
                $stats['trainStats'][$train]['delayTrend'][$key]['avgDelay'] = $month['delay'] / $month['count'];
            }
            
            // Sort delay trend by month
            usort($stats['trainStats'][$train]['delayTrend'], function($a, $b) {
                return strcmp($a['month'], $b['month']);
            });
        }
    }
    
    // Calculate averages for city pair stats
    foreach ($stats['cityPairStats'] as $cityPair => $cityPairStat) {
        $count = $cityPairStat['count'];
        if ($count > 0) {
            $stats['cityPairStats'][$cityPair]['avgDelay'] = $cityPairStat['avgDelay'] / $count;
        }
    }
    
    // Calculate averages for class stats
    foreach ($stats['classStats'] as $class => $classStat) {
        $count = $classStat['count'];
        if ($count > 0) {
            $stats['classStats'][$class]['avgOccupancyRate'] = $classStat['avgOccupancyRate'] / $count;
        }
    }
    
    return $stats;
}

/**
 * Fallback data in case database connection fails
 * @return array Fallback data
 */
function getFallbackData() {
    error_log('Using fallback data due to database connection issues');
    
    // Return a small subset of mock data
    return [
        [ 
            'id' => 1, 
            'train_id' => '101', 
            'train_name' => 'Perali Express', 
            'departure_date' => '2025-01-05', 
            'from_city' => 'Colombo', 
            'to_city' => 'Kandy', 
            'class' => 'First', 
            'scheduled_time' => '06:00:00', 
            'actual_time' => '06:05:00', 
            'delay_minutes' => 5, 
            'capacity' => 50, 
            'occupancy' => 45, 
            'revenue' => 22500.00, 
            'occupancyRate' => 90, 
            'date' => new DateTime('2025-01-05'), 
            'year' => 2025, 
            'month' => 1, 
            'quarter' => 1 
        ],
        [ 
            'id' => 2, 
            'train_id' => '102', 
            'train_name' => 'Udarata Menike', 
            'departure_date' => '2025-01-05', 
            'from_city' => 'Colombo', 
            'to_city' => 'Ella', 
            'class' => 'Second', 
            'scheduled_time' => '07:30:00', 
            'actual_time' => '07:38:00', 
            'delay_minutes' => 8, 
            'capacity' => 80, 
            'occupancy' => 65, 
            'revenue' => 19500.00, 
            'occupancyRate' => 81.25, 
            'date' => new DateTime('2025-01-05'), 
            'year' => 2025, 
            'month' => 1, 
            'quarter' => 1 
        ],
        [ 
            'id' => 3, 
            'train_id' => '103', 
            'train_name' => 'Kandy Intercity', 
            'departure_date' => '2025-01-06', 
            'from_city' => 'Kandy', 
            'to_city' => 'Ella', 
            'class' => 'Third', 
            'scheduled_time' => '14:00:00', 
            'actual_time' => '14:00:00', 
            'delay_minutes' => 0, 
            'capacity' => 120, 
            'occupancy' => 95, 
            'revenue' => 14250.00, 
            'occupancyRate' => 79.17, 
            'date' => new DateTime('2025-01-06'), 
            'year' => 2025, 
            'month' => 1, 
            'quarter' => 1 
        ],
        [ 
            'id' => 4, 
            'train_id' => '104', 
            'train_name' => 'Ella Odyssey', 
            'departure_date' => '2025-01-06', 
            'from_city' => 'Colombo', 
            'to_city' => 'Kandy', 
            'class' => 'Second', 
            'scheduled_time' => '16:30:00', 
            'actual_time' => '16:45:00', 
            'delay_minutes' => 15, 
            'capacity' => 80, 
            'occupancy' => 78, 
            'revenue' => 23400.00, 
            'occupancyRate' => 97.5, 
            'date' => new DateTime('2025-01-06'), 
            'year' => 2025, 
            'month' => 1, 
            'quarter' => 1 
        ],
        [ 
            'id' => 5, 
            'train_id' => '105', 
            'train_name' => 'Rajarata Express', 
            'departure_date' => '2024-06-15', 
            'from_city' => 'Colombo', 
            'to_city' => 'Ella', 
            'class' => 'Third', 
            'scheduled_time' => '08:15:00', 
            'actual_time' => '08:25:00', 
            'delay_minutes' => 10, 
            'capacity' => 120, 
            'occupancy' => 81, 
            'revenue' => 12478.04, 
            'occupancyRate' => 67.5, 
            'date' => new DateTime('2024-06-15'), 
            'year' => 2024, 
            'month' => 6, 
            'quarter' => 2 
        ]
    ];
}
?>
