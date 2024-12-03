<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: adminlogin.php");
    exit();
}

require('db_connection.php');

// Get the booking_id from the URL
$booking_id = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;

// Fetch the booking details along with vehicle and user information
$query = "SELECT v.vehicle_name, b.brand_name, br.start_date, br.end_date, br.total_price, br.status AS booking_status,
                 u.fullname AS user_name, u.phone AS user_phone, u.email AS user_email, br.payment_way
          FROM booking_req AS br
          INNER JOIN vehicles AS v ON br.vehicle_id = v.id
          INNER JOIN brands AS b ON v.brand_id = b.id
          INNER JOIN users AS u ON br.user_id = u.id
          WHERE br.id = $booking_id";

$result = $conn->query($query);

if ($result->num_rows > 0) {
    $booking = $result->fetch_assoc();
} else {
    // If no booking is found, redirect back to the car status page
    header("Location: admin_car_status.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Booking Details</title>
    <link rel="stylesheet" href="admin-style.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
            body {
            font-family: Arial, sans-serif;
            margin: 0;
            background-color: #f4f4f9;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
    </style>
</head>
<body>

<?php require('navbar.php'); ?>

<div class="container">
    <h2>Booking Details</h2>

    <table class="table table-bordered">
        <tr>
            <th>Car Name</th>
            <td><?php echo htmlspecialchars($booking['vehicle_name']); ?></td>
        </tr>
        <tr>
            <th>Brand</th>
            <td><?php echo htmlspecialchars($booking['brand_name']); ?></td>
        </tr>
        <tr>
            <th>Booking Period</th>
            <td><?php echo htmlspecialchars($booking['start_date']) . ' to ' . htmlspecialchars($booking['end_date']); ?></td>
        </tr>
        <tr>
            <th>Total Price</th>
            <td><?php echo htmlspecialchars($booking['total_price']); ?></td>
        </tr>
        <tr>
            <th>Booking Status</th>
            <td><?php echo htmlspecialchars($booking['booking_status']); ?></td>
        </tr>
        <tr>
            <th>User Name</th>
            <td><?php echo htmlspecialchars($booking['user_name']); ?></td>
        </tr>
        <tr>
            <th>User Phone</th>
            <td><?php echo htmlspecialchars($booking['user_phone']); ?></td>
        </tr>
        <tr>
            <th>User Email</th>
            <td><?php echo htmlspecialchars($booking['user_email']); ?></td>
        </tr>
        <tr>
            <th>Payment Type</th>
            <td><?php echo htmlspecialchars($booking['payment_way']); ?></td>
        </tr>
    </table>

    <a href="admin_car_status.php" class="btn btn-primary">Back to Car Status</a>
</div>

</body>
</html>
