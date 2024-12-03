<?php
session_start();
include('db_connection.php');

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please log in to view the invoice.'); window.location.href='login.php';</script>";
    exit();
}

$booking_id = $_GET['booking_id'] ?? null;

if ($booking_id) {
    $bookingQuery = "SELECT br.*, v.vehicle_name, p.amount, p.payment_date, p.payment_status 
                     FROM booking_req br 
                     JOIN vehicles v ON br.vehicle_id = v.id 
                     JOIN payments p ON br.id = p.booking_id 
                     WHERE br.id = '$booking_id' AND br.user_id = '{$_SESSION['user_id']}'";
    $bookingResult = $conn->query($bookingQuery);
    $booking = $bookingResult->fetch_assoc();
}
?>

<!DOCTYPE html>
<html>
<head><title>Booking Invoice</title></head>
<body>
    <h1>Booking Invoice</h1>
    <p><strong>Vehicle:</strong> <?php echo htmlspecialchars($booking['vehicle_name']); ?></p>
    <p><strong>Days Booked:</strong> <?php echo htmlspecialchars($booking['days_booked']); ?></p>
    <p><strong>Total Price:</strong> ₹<?php echo number_format($booking['amount'], 2); ?></p>
    <p><strong>Payment Status:</strong> <?php echo htmlspecialchars($booking['payment_status']); ?></p>
    <p><strong>Payment Date:</strong> <?php echo htmlspecialchars($booking['payment_date']); ?></p>
    <button onclick="location.href='home.php'">Go to Home</button>
</body>
</html>
