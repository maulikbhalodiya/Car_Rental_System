<?php session_start();
include('db_connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = $_POST['booking_id'];

    // Fetch the booking details
    $query = "SELECT * FROM booking_req WHERE id = '$booking_id' AND user_id = '{$_SESSION['user_id']}'";
    $result = $conn->query($query);
    $booking = $result->fetch_assoc();

    if ($booking) {
        $start_date = $booking['start_date'];
        $total_price = $booking['total_price'];
        $current_time = time();

        // Check cancellation rules
        if (strtotime($start_date) - $current_time > 86400) { // 86400 seconds = 24 hours
            // Free cancellation
            $updateQuery = "DELETE FROM booking_req WHERE id = '$booking_id'";
            $conn->query($updateQuery);
            echo "Booking cancelled successfully. No charges applied.";
        } elseif (strtotime($start_date) - $current_time <= 86400 && strtotime($start_date) - $current_time > 0) {
            // 50% fee cancellation
            $updateQuery = "DELETE FROM booking_req WHERE id = '$booking_id'";
            $conn->query($updateQuery);
            echo "Booking cancelled. A fee of 50% has been charged.";
        } else {
            echo "No refunds for same-day cancellations.";
        }
    } else {
        echo "Booking not found.";
    }
}
?>