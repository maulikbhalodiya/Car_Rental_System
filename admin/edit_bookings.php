<?php
session_start();

// Redirect to login page if the admin is not logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: adminlogin.php");
    exit();
}

// Include Composer's autoloader
require 'C:/xampp/htdocs/CRS/vendor/autoload.php'; // Adjust the path as needed

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require('db_connection.php');

// Initialize variables
$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;
$booking = null;

// Fetch the booking details if booking_id is valid
if ($booking_id > 0) {
    $bookingQuery = "SELECT id, user_id, vehicle_id, start_date, end_date, hours_booked, days_booked, total_price, payment_way, status  FROM booking_req WHERE id = ?";
    if ($stmt = $conn->prepare($bookingQuery)) {
        $stmt->bind_param("i", $booking_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $booking = $result->fetch_assoc();
        $stmt->close();
    } else {
        die("Error preparing the select statement.");
    }
} else {
    die("Invalid booking ID.");
}

// Fetch customer email and name based on customer_id
$customer_email = '';
$customer_name = 'Valued Customer';
if ($booking && isset($booking['user_id'])) {
    $customer_id = intval($booking['user_id']);
    $customerQuery = "SELECT email, fullname FROM users WHERE id = ?";
    if ($stmt_customer = $conn->prepare($customerQuery)) {
        $stmt_customer->bind_param("i", $customer_id);
        $stmt_customer->execute();
        $result_customer = $stmt_customer->get_result();
        if ($result_customer->num_rows > 0) {
            $customer = $result_customer->fetch_assoc();
            $customer_email = $customer['email'];
            $customer_name = $customer['fullname'];
        }
        $stmt_customer->close();
    }
}

$status = '';

// Update booking logic
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_booking'])) {
    // Collect and sanitize input data
    $customer_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : null;
    $vehicle_registration_id = isset($_POST['vehicle_id']) ? trim($_POST['vehicle_id']) : '';
    $pickup_date = isset($_POST['start_date']) ? trim($_POST['start_date']) : '';
    $return_date = isset($_POST['end_date']) ? trim($_POST['end_date']) : '';
    $total_amount = isset($_POST['total_price']) ? floatval($_POST['total_price']) : 0.0;
    $status = isset($_POST['status']) ? trim($_POST['status']) : '';
    $payment_way = isset($_POST['payment_way']) ? trim($_POST['payment_way']) : ''; 
}

// Define allowed status values
$allowed_status = ['Pending', 'Confirmed', 'Cancelled', 'Completed'];

// Validate status
if (!in_array($status, $allowed_status)) {
    echo "<script>alert('Invalid status value.');</script>";
} else {
    // Prepare the update query
    $updateQuery = "UPDATE bookings SET 
        customer_id = ?, 
        vehicle_registration_id = ?, 
        booking_date = ?, 
        pickup_date = ?, 
        return_date = ?, 
        total_amount = ?, 
        booking_status = ?, 
        payment_way = ?
        WHERE booking_id = ?";
    
    if ($stmt = $conn->prepare($updateQuery)) {
        // Bind parameters
        $stmt->bind_param(
            "issssdssi",
            $customer_id,
            $vehicle_registration_id,
            $booking_date,
            $pickup_date,
            $return_date,
            $total_amount,
            $status,
            $payment_way,
            $booking_id
        );

        // Execute the statement
        if ($stmt->execute()) {
            // Check if status changed to 'Confirmed' and was not 'Confirmed' before
            if ($status === 'Confirmed' && $booking['booking_status'] !== 'Confirmed') {
                // Fetch updated booking details
                $bookingQuery = "SELECT * FROM bookings WHERE booking_id = ?";
                if ($stmt_booking = $conn->prepare($bookingQuery)) {
                    $stmt_booking->bind_param("i", $booking_id);
                    $stmt_booking->execute();
                    $result_booking = $stmt_booking->get_result();
                    $updated_booking = $result_booking->fetch_assoc();
                    $stmt_booking->close();
                }

                // Fetch vehicle details
                $vehicleDetails = [];
                $vehicleQuery = "SELECT * FROM vehicles WHERE Registration_id = ?";
                if ($stmt_vehicle = $conn->prepare($vehicleQuery)) {
                    $stmt_vehicle->bind_param("s", $vehicle_registration_id);
                    $stmt_vehicle->execute();
                    $result_vehicle = $stmt_vehicle->get_result();
                    if ($result_vehicle->num_rows > 0) {
                        $vehicleDetails = $result_vehicle->fetch_assoc();
                    }
                    $stmt_vehicle->close();
                }

                // Send confirmation email
                if (!empty($customer_email)) {
                    $mail = new PHPMailer(true);

                    try {
                        // Server settings
                        $mail->isSMTP();
                        $mail->Host       = 'smtp.gmail.com';
                        $mail->SMTPAuth   = true;
                        $mail->Username   = 'carrentalservice147@gmail.com'; // Your Gmail address
                        $mail->Password   = 'ziwq ljxt zgiy vfhs'; // Your Gmail App Password
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Use TLS
                        $mail->Port       = 587; // TCP port to connect to

                        // Recipients
                        $mail->setFrom('carrentalservice147@gmail.com', 'Car Rental Service');
                        $mail->addAddress($customer_email, $customer_name); // Add a recipient

                        // Content
                        $mail->isHTML(true);
                        $mail->Subject = 'Your Car Rental Booking is Confirmed!';
                        
                        // Construct the email body
                        $emailBody = "
                            <h2>Booking Confirmation</h2>
                            <p>Dear {$customer_name},</p>
                            <p>We are pleased to inform you that your booking has been <strong>confirmed</strong>. Below are your booking details:</p>
                            <h3>Booking Details:</h3>
                            <ul>
                                <li><strong>Booking ID:</strong> {$updated_booking['booking_id']}</li>
                                <li><strong>Booking Date:</strong> {$updated_booking['booking_date']}</li>
                                <li><strong>Pickup Date:</strong> {$updated_booking['pickup_date']}</li>
                                <li><strong>Return Date:</strong> {$updated_booking['return_date']}</li>
                                <li><strong>Total Amount:</strong> ₹{$updated_booking['total_amount']}</li>
                                <li><strong>Payment Method:</strong> {$updated_booking['payment_way']}</li>
                                <li><strong>Booking Status:</strong> {$updated_booking['booking_status']}</li>
                            </ul>
                            <h3>Vehicle Details:</h3>
                            <ul>
                                <li><strong>Registration ID:</strong> {$vehicleDetails['Registration_id']}</li>
                                <li><strong>Vehicle Name:</strong> {$vehicleDetails['vehicle_name']}</li>
                                <li><strong>Car Type:</strong> {$vehicleDetails['car_type']}</li>
                                <li><strong>Seating Capacity:</strong> {$vehicleDetails['seating_capacity']}</li>
                                <li><strong>Price per Day:</strong> ₹{$vehicleDetails['price_per_day']}</li>
                                <li><strong>Fuel Type:</strong> {$vehicleDetails['fuel_type']}</li>
                                <li><strong>Mileage:</strong> {$vehicleDetails['mileage']} km/l</li>
                                <li><strong>Transmission:</strong> {$vehicleDetails['transmission']}</li>
                            </ul>
                            <p>If you have any questions or need further assistance, feel free to contact our support team.</p>
                            <p>Thank you for choosing our service!</p>
                            <p>Best Regards,<br>Car Rental Service Team</p>
                        ";

                        $mail->Body    = $emailBody;
                        $mail->AltBody = "Dear {$customer_name},\n\nYour booking (ID: {$updated_booking['booking_id']}) has been confirmed.\n\nBooking Details:\n- Booking Date: {$updated_booking['booking_date']}\n- Pickup Date: {$updated_booking['pickup_date']}\n- Return Date: {$updated_booking['return_date']}\n- Total Amount: ₹{$updated_booking['total_amount']}\n- Payment Method: {$updated_booking['payment_way']}\n- Booking Status: {$updated_booking['booking_status']}\n\nVehicle Details:\n- Registration ID: {$vehicleDetails['Registration_id']}\n- Vehicle Name: {$vehicleDetails['vehicle_name']}\n- Car Type: {$vehicleDetails['car_type']}\n- Seating Capacity: {$vehicleDetails['seating_capacity']}\n- Price per Day: ₹{$vehicleDetails['price_per_day']}\n- Fuel Type: {$vehicleDetails['fuel_type']}\n- Mileage: {$vehicleDetails['mileage']} km/l\n- Transmission: {$vehicleDetails['transmission']}\n\nThank you for choosing our service!\n\nBest Regards,\nCar Rental Service Team";

                        $mail->send();
                        echo "<script>alert('Booking updated successfully and confirmation email sent!');</script>";
                    } catch (Exception $e) {
                        // Log the error for debugging
                        error_log("Mailer Error: {$mail->ErrorInfo}");
                        echo "<script>alert('Booking updated successfully, but the confirmation email could not be sent.');</script>";
                    }
                } else {
                    echo "<script>alert('Booking updated successfully, but customer email not found.');</script>";
                }
            } else {
                echo "<script>alert('Successfully updated booking: " . htmlspecialchars($stmt->error) . "');</script>";
            }

            $stmt->close();
        } else {
            echo "<script>alert('Error preparing the update statement.');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Booking | Car Rental System</title>
    <link rel="stylesheet" href="edit_booking.css">
    <style>
        /* Basic styling for better visibility */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .content {
            background-color: #fff;
            padding: 20px 30px;
            border-radius: 5px;
            max-width: 800px;
            margin: auto;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
        }
        form table {
            width: 100%;
            border-collapse: collapse;
        }
        form table td {
            padding: 10px;
            vertical-align: top;
        }
        form table td:first-child {
            width: 30%;
            font-weight: bold;
        }
        input[type="text"],
        input[type="number"],
        input[type="date"],
        select {
            width: 95%;
            padding: 8px;
            margin-top: 5px;
            box-sizing: border-box;
        }
        button {
            padding: 10px 20px;
            background-color: #4285F4;
            border: none;
            color: #fff;
            cursor: pointer;
            border-radius: 3px;
            font-size: 16px;
        }
        button:hover {
            background-color: #357ae8;
        }
        .readonly {
            background-color: #e9e9e9;
        }
    </style>
</head>
<body>
    <div class="content">
        <h2>Edit Booking</h2>

        <?php if ($booking): ?>
            <form method="POST">
                <table>
                    <tr>
                        <td>Booking ID:</td>
                        <td><?php echo htmlspecialchars($booking['id']); ?></td>
                    </tr>
                    <tr>
                        <td>Customer ID:</td>
                        <td>
                            <input type="number" name="customer_id" value="<?php echo htmlspecialchars($booking['user_id']); ?>" required>
                        </td>
                    </tr>
                    <tr>
                        <td>Vehicle Registration ID:</td>
                        <td>
                            <input type="text" name="vehicle_registration_id" value="<?php echo htmlspecialchars($booking['vehicle_id']); ?>" required>
                        </td>
                    </tr>
                    <tr>
                        <td>Pickup Date:</td>
                        <td>
                            <input type="date" name="pickup_date" value="<?php echo htmlspecialchars($booking['start_date']); ?>" required>
                        </td>
                    </tr>
                    <tr>
                        <td>Return Date:</td>
                        <td>
                            <input type="date" name="return_date" value="<?php echo htmlspecialchars($booking['end_date']); ?>" required>
                        </td>
                    </tr>
                    <tr>
                        <td>Total Amount:</td>
                        <td>
                            <input type="number" step="0.01" name="total_amount" value="<?php echo htmlspecialchars($booking['total_price']); ?>" required>
                        </td>
                    </tr>
                    <tr>
                        <td>Status:</td>
                        <td>
                            <select name="status" required>
                                <option value="Pending" <?php echo ($booking['status'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                <option value="Confirmed" <?php echo ($booking['status'] == 'Confirmed') ? 'selected' : ''; ?>>Confirmed</option>
                                <option value="Cancelled" <?php echo ($booking['status'] == 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                <option value="Completed" <?php echo ($booking['status'] == 'Completed') ? 'selected' : ''; ?>>Completed</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>Payment Method:</td>
                        <td>
                            <input type="text" name="payment_way" value="<?php echo htmlspecialchars($booking['payment_way']); ?>" required>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" style="text-align: center;">
                            <button type="submit" name="update_booking">Update Booking</button>
                        </td>
                    </tr>
                </table>
            </form>
        <?php else: ?>
            <p>Booking not found.</p>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
