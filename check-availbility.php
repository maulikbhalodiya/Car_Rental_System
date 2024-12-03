<?php
include('db_connection.php');

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $carId = $_POST['car_id'];
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];

    // Query to check if there is any booking that overlaps with the selected dates
    $query = "SELECT * FROM bookings 
              WHERE car_id = ? 
              AND ((start_date <= ? AND end_date >= ?) OR (start_date <= ? AND end_date >= ?))";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('issss', $carId, $endDate, $startDate, $startDate, $endDate);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Car is already booked in the selected date range
        echo json_encode(['available' => false]);
    } else {
        // Car is available
        echo json_encode(['available' => true]);
    }

    $stmt->close();
    $conn->close();
}
?>
