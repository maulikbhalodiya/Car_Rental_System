<?php
require('db_connection.php');

// Get the parameters from the GET request
$brand = isset($_GET['brand']) ? $_GET['brand'] : 'all';
$status = isset($_GET['status']) ? $_GET['status'] : 'all';
$start = isset($_GET['start']) ? $_GET['start'] : '';
$end = isset($_GET['end']) ? $_GET['end'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build the query to filter cars based on conditions
$query = "SELECT v.id, v.vehicle_name, b.brand_name, 
                 IF(br.start_date IS NOT NULL AND br.end_date IS NOT NULL, 'Booked', 'Available') AS status,
                 v.price_per_day AS rental_rate, br.start_date, br.end_date
          FROM vehicles AS v
          INNER JOIN brands AS b ON v.brand_id = b.id
          LEFT JOIN booking_req AS br ON v.id = br.vehicle_id
          WHERE 1";

if ($brand != 'all') {
    $query .= " AND v.brand_id = '$brand'";
}
if ($status != 'all') {
    if ($status === 'booked') {
        $query .= " AND br.start_date IS NOT NULL AND br.end_date >= CURDATE()"; // Only future bookings
    } else {
        $query .= " AND (br.start_date IS NULL OR br.end_date < CURDATE())"; // Only available cars
    }
}
if ($start != '') {
    $query .= " AND br.start_date >= '$start'";
}
if ($end != '') {
    $query .= " AND br.end_date <= '$end'";
}
if ($search != '') {
    $query .= " AND v.vehicle_name LIKE '%$search%'";
}

// Execute the query
$result = $conn->query($query);

// Return the result in JSON format (for API use)
$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}

echo json_encode($rows);
?>
