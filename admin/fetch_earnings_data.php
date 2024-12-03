<?php
require('db_connection.php');
header('Content-Type: application/json');

// Get filter values
$carId = $_GET['car_id'] ?? 'all';
$fromDate = $_GET['from_date'] ?? '';
$toDate = $_GET['to_date'] ?? '';

$response = [
    'labels' => [],
    'earnings' => [],
    'most_booked_car' => [],
    'most_booked_user' => [],
    'error' => ''
];

try {
    // Validate date inputs
    if (!$fromDate || !$toDate) {
        throw new Exception('Both from_date and to_date are required.');
    }

    // Format and sanitize input dates
    $fromDate = date('Y-m-d', strtotime($fromDate));
    $toDate = date('Y-m-d', strtotime($toDate));

    // Determine if the date range is for a full month
    $fromMonth = date('Y-m', strtotime($fromDate));
    $toMonth = date('Y-m', strtotime($toDate));

    // If the selected range is exactly one month, group by month
    if ($fromMonth == $toMonth && $fromDate == date('Y-m-01', strtotime($fromDate)) && $toDate == date('Y-m-t', strtotime($fromDate))) {
        // Group by month
        $groupBy = "DATE_FORMAT(payment_date, '%Y-%m')"; // Group by month and year (e.g., '2025-01')
    } else {
        // Group by day
        $groupBy = "DATE_FORMAT(payment_date, '%Y-%m-%d')"; // Group by day (e.g., '2025-01-01')
    }

    // Main earnings query with refined grouping
    $query = "
        SELECT 
            $groupBy AS period, 
            SUM(amount) AS earnings 
        FROM 
            payments 
        INNER JOIN car_owner ON payments.vehicle_id = car_owner.car_id
        WHERE 
            payments.payment_status = 'Completed' 
            AND payment_date BETWEEN '$fromDate' AND '$toDate'
    ";

    if ($carId !== 'all') {
        $query .= " AND car_owner.car_id = " . intval($carId);
    }

    $query .= " GROUP BY period ORDER BY period ASC";

    // Execute main earnings query
    $result = $conn->query($query);
    if (!$result) {
        throw new Exception($conn->error);
    }

    while ($row = $result->fetch_assoc()) {
        $response['labels'][] = $row['period'];
        $response['earnings'][] = (float)$row['earnings'];
    }

    // Most booked car query with improved distinct counting
    $mostBookedQuery = "
        SELECT 
            $groupBy AS period, 
            vehicles.vehicle_name, 
            COUNT(DISTINCT payments.id) AS bookings 
        FROM 
            payments 
        INNER JOIN car_owner ON payments.vehicle_id = car_owner.car_id
        INNER JOIN vehicles ON car_owner.car_id = vehicles.id
        WHERE 
            payments.payment_status = 'Completed' 
            AND payment_date BETWEEN '$fromDate' AND '$toDate'
    ";

    if ($carId !== 'all') {
        $mostBookedQuery .= " AND car_owner.car_id = " . intval($carId);
    }

    $mostBookedQuery .= " GROUP BY period, vehicles.vehicle_name 
                          ORDER BY period ASC, bookings DESC";

    // Execute most booked car query
    $result = $conn->query($mostBookedQuery);
    if (!$result) {
        throw new Exception($conn->error);
    }

    // Collect the most booked car data
    $mostBooked = [];
    while ($row = $result->fetch_assoc()) {
        $key = $row['period'];
        if (!isset($mostBooked[$key])) {
            $mostBooked[$key] = [];
        }
        $mostBooked[$key][] = [
            'vehicle_name' => $row['vehicle_name'],
            'bookings' => (int)$row['bookings']
        ];
    }
    $response['most_booked_car'] = $mostBooked;

    // Most booked user query with refined distinct counting
    $mostBookedUserQuery = "
        SELECT 
            $groupBy AS period, 
            users.fullname AS user_name, 
            vehicles.vehicle_name, 
            COUNT(DISTINCT payments.id) AS bookings 
        FROM 
            payments 
        INNER JOIN car_owner ON payments.vehicle_id = car_owner.car_id
        INNER JOIN vehicles ON car_owner.car_id = vehicles.id
        INNER JOIN users ON payments.user_id = users.id
        WHERE 
            payments.payment_status = 'Completed' 
            AND payment_date BETWEEN '$fromDate' AND '$toDate'
    ";

    if ($carId !== 'all') {
        $mostBookedUserQuery .= " AND car_owner.car_id = " . intval($carId);
    }

    $mostBookedUserQuery .= " GROUP BY period, users.fullname, vehicles.vehicle_name
                              ORDER BY period ASC, bookings DESC";

    // Execute most booked user query
    $result = $conn->query($mostBookedUserQuery);
    if (!$result) {
        throw new Exception($conn->error);
    }

    // Collect the most booked user data
    $mostBookedUser = [];
    while ($row = $result->fetch_assoc()) {
        $key = $row['period'];
        if (!isset($mostBookedUser[$key])) {
            $mostBookedUser[$key] = [];
        }
        $mostBookedUser[$key][] = [
            'user_name' => $row['user_name'],
            'vehicle_name' => $row['vehicle_name'],
            'bookings' => (int)$row['bookings']
        ];
    }
    $response['most_booked_user'] = $mostBookedUser;

} catch (Exception $e) {
    $response['error'] = $e->getMessage();
}

echo json_encode($response);
?>
