<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "Testing Database Connection and Data Retrieval\n";
echo "--------------------------------------------\n\n";

// Include the database connection file
require_once 'includes/db_connection.php';

// Get a database connection
$conn = getDbConnection();

if ($conn) {
    echo "Database Connection: SUCCESS\n\n";
    
    // Test query 1: Get train information
    echo "Train Information:\n";
    $stmt = $conn->query("SELECT train_id, train_name FROM trains ORDER BY train_id");
    $trains = $stmt->fetchAll();
    
    if (count($trains) > 0) {
        echo "Found " . count($trains) . " trains:\n";
        foreach ($trains as $train) {
            echo "- Train ID: " . $train['train_id'] . ", Name: " . $train['train_name'] . "\n";
        }
    } else {
        echo "No trains found in the database.\n";
    }
    
    echo "\n";
    
    // Test query 2: Get recent train journeys
    echo "Recent Train Journeys:\n";
    $stmt = $conn->query("SELECT j.journey_id, t.train_name, j.departure_city, j.arrival_city, j.journey_date, j.class, j.reserved_seats, j.total_seats, j.revenue 
                         FROM train_journeys j 
                         JOIN trains t ON j.train_id = t.train_id 
                         ORDER BY j.journey_date DESC LIMIT 5");
    $journeys = $stmt->fetchAll();
    
    if (count($journeys) > 0) {
        echo "Found " . count($journeys) . " recent journeys:\n";
        foreach ($journeys as $journey) {
            echo "- Journey ID: " . $journey['journey_id'] . 
                 ", Train: " . $journey['train_name'] . 
                 ", Route: " . $journey['departure_city'] . " to " . $journey['arrival_city'] . 
                 ", Date: " . $journey['journey_date'] . 
                 ", Class: " . $journey['class'] . 
                 ", Seats: " . $journey['reserved_seats'] . "/" . $journey['total_seats'] . 
                 ", Revenue: " . $journey['revenue'] . "\n";
        }
    } else {
        echo "No journeys found in the database.\n";
    }
    
    echo "\n";
    
    // Test query 3: Get passenger count
    echo "Passenger Information:\n";
    $stmt = $conn->query("SELECT COUNT(*) as passenger_count FROM passengers");
    $passengerCount = $stmt->fetchColumn();
    echo "Total passengers in database: " . $passengerCount . "\n";
    
    // Get a sample of passengers
    $stmt = $conn->query("SELECT rider_number, first_name, last_name, originating_station, most_visited_station FROM passengers ORDER BY rider_number LIMIT 5");
    $passengers = $stmt->fetchAll();
    
    if (count($passengers) > 0) {
        echo "Sample passengers:\n";
        foreach ($passengers as $passenger) {
            echo "- Rider Number: " . $passenger['rider_number'] . 
                 ", Name: " . $passenger['first_name'] . " " . $passenger['last_name'] . 
                 ", Origin: " . $passenger['originating_station'] . 
                 ", Most Visited: " . $passenger['most_visited_station'] . "\n";
        }
    }
    
} else {
    echo "Database Connection: FAILED\n";
    echo "Please check your database connection settings.\n";
}
?>
