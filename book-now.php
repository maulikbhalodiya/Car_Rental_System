<?php 
session_start(); 
include('db_connection.php'); 

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) { 
    echo "<script>alert('Please log in to book a car.'); window.location.href='login.php';</script>"; 
    exit(); 
}

$user_id = $_SESSION['user_id'];
$userQuery = "SELECT fullname, email, phone FROM users WHERE id = '$user_id'";
$userResult = $conn->query($userQuery);
$user = $userResult->fetch_assoc();

$vehicle_id = $_GET['id']; 

// Fetch vehicle pricing details
$vehicleQuery = "SELECT price_per_day, rent_per_hour, vehicle_name FROM vehicles WHERE id = '$vehicle_id'";
$vehicleResult = $conn->query($vehicleQuery);
$vehicle = $vehicleResult->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') { 
    $bookingType = $_POST['booking_type']; 

    if ($bookingType === 'day') { 
        $start_date = $_POST['start_date']; 
        $end_date = $_POST['end_date']; 

        // Check for overlapping bookings (daily)
        $overlapQuery = "SELECT * FROM booking_req 
                         WHERE vehicle_id = '$vehicle_id' 
                         AND ((start_date <= '$end_date' AND end_date >= '$start_date')) 
                         AND status != 'Cancelled'"; // Avoid cancelled bookings

        $overlapResult = $conn->query($overlapQuery);

        if ($overlapResult->num_rows > 0) {
            echo "<script>alert('The car is already booked during the selected time period. Please choose a different time.'); window.history.back();</script>"; 
            exit(); // Stop further processing if overlap found
        }

        // Calculate days booked
        $days_booked = (strtotime($end_date) - strtotime($start_date)) / (60 * 60 * 24) + 1; 
        $total_price = $days_booked * $vehicle['price_per_day'];

        $insertQuery = "INSERT INTO booking_req (user_id, vehicle_id, start_date, end_date, start_time, end_time, days_booked, total_price, status) 
                        VALUES ('$user_id', '$vehicle_id', '$start_date', '$end_date', '00:00:00', '23:59:59', '$days_booked', '$total_price', 'Pending')"; 

        if ($conn->query($insertQuery) === TRUE) { 
            header("Location: payment.php?booking_id=" . $conn->insert_id); 
            exit(); 
        } else { 
            error_log("Error: " . $conn->error); 
            echo "<script>alert('Error: " . $conn->error . "'); window.history.back();</script>"; 
            exit(); 
        } 
    } elseif ($bookingType === 'hour') { 
        $date = $_POST['date']; 
        $start_time = $_POST['start_time']; 
        $end_time = $_POST['end_time']; 

        // Combine date and time for proper datetime formatting
        $start_datetime = $date . ' ' . $start_time;
        $end_datetime = $date . ' ' . $end_time;

        // Check for overlapping bookings (hourly)
        $overlapQuery = "SELECT * FROM booking_req 
                         WHERE vehicle_id = '$vehicle_id' 
                         AND '$start_datetime' < end_datetime 
                         AND '$end_datetime' > start_datetime 
                         AND status != 'Cancelled'"; 

        $overlapResult = $conn->query($overlapQuery);

        if ($overlapResult->num_rows > 0) {
            echo "<script>alert('The car is already booked during the selected time period. Please choose a different time.'); window.history.back();</script>"; 
            exit(); // Stop further processing if overlap found
        }

        // Calculate hours booked
        $hours_booked = (strtotime($end_datetime) - strtotime($start_datetime)) / 3600;

        // Validate hours booked
        if ($hours_booked <= 0) { 
            echo "<script>alert('End time must be after start time.'); window.history.back();</script>"; 
            exit(); 
        }

        $total_price = $hours_booked * $vehicle['rent_per_hour']; 

        // Insert booking request
        $insertQuery = "INSERT INTO booking_req (user_id, vehicle_id, start_date, end_date, start_time, end_time, hours_booked, total_price, status)
                        VALUES ('$user_id', '$vehicle_id', '$date', '$date', '$start_time', '$end_time', '$hours_booked', '$total_price', 'Pending')";

        if ($conn->query($insertQuery) === TRUE) { 
            header("Location: payment.php?booking_id=" . $conn->insert_id); 
            exit(); 
        } else { 
            error_log("Error: " . $conn->error); 
            echo "<script>alert('Error: " . $conn->error . "'); window.history.back();</script>"; 
            exit(); 
        } 
    } 
}
?> 


<!DOCTYPE html> 
<html lang="en"> 
<head> 
    <meta charset="UTF-8"> 
    <title>Book Now</title> 
</head> 
<body> 
    <h2>Book Vehicle: <?php echo htmlspecialchars($vehicle['vehicle_name']); ?></h2> 
    <form method="post" action=""> 
        <label for="booking_type">Booking Type:</label> 
        <select name="booking_type" id="booking_type" required> 
            <option value="day">Daily</option> 
            <option value="hour">Hourly</option> 
        </select>

        <div id="daily_booking" style="display: none;"> 
            <label for="start_date">Start Date:</label> 
            <input type="date" name="start_date" id="start_date"> 
            <label for="end_date">End Date:</label> 
            <input type="date" name="end_date" id="end_date"> 
        </div>

        <div id="hourly_booking" style="display: none;"> 
            <label for="date">Date:</label> 
            <input type="date" name="date" id="date"> 
            <label for="start_time">Start Time:</label> 
            <input type="time" name="start_time" id="start_time"> 
            <label for="end_time">End Time:</label> 
            <input type="time" name="end_time" id="end_time"> 
        </div>

        <button type="submit">Proceed to Payment</button> 
    </form>

    <script>
        document.getElementById('booking_type').addEventListener('change', function () {
            if (this.value === 'day') {
                document.getElementById('daily_booking').style.display = 'block';
                document.getElementById('hourly_booking').style.display = 'none';
            } else if (this.value === 'hour') {
                document.getElementById('daily_booking').style.display = 'none';
                document.getElementById('hourly_booking').style.display = 'block';
            }
        });
    </script>
</body> 
</html>
