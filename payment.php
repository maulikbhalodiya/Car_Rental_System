<?php 
session_start();

// Include the Razorpay PHP SDK
require 'vendor/autoload.php';

use Razorpay\Api\Api;

// Set Razorpay test mode keys
$razorpay_key_id = 'rzp_test_2xuCr5Gu8ys5Pr';
$razorpay_key_secret = 'aapkSTPDvH8y6ncT2GxseAs5';

// Initialize Razorpay API
$api = new Api($razorpay_key_id, $razorpay_key_secret);

// Fetch the booking details from the database
$booking_id = $_GET['booking_id']; // Assuming booking_id is passed in the URL

require('db_connection.php');

// Fetch booking details along with car name and user name
$stmt = $conn->prepare("
    SELECT 
        br.total_price, 
        br.start_date, 
        br.end_date, 
        br.start_time, 
        br.end_time, 
        br.days_booked, 
        br.hours_booked, 
        v.vehicle_name, 
        u.fullname 
    FROM 
        booking_req br
    JOIN 
        vehicles v ON br.vehicle_id = v.id
    JOIN 
        users u ON br.user_id = u.id
    WHERE 
        br.id = ?
");
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('Invalid booking ID or booking not found.'); window.location.href='view-details.php?id=" . htmlspecialchars($_GET['id']) . "';</script>";
    exit();
}

$booking = $result->fetch_assoc();
$stmt->close();
$conn->close();

// Convert the amount to paise (as required by Razorpay)
$amount_in_paise = $booking['total_price'] * 100; // Convert INR to paise

// Generate a unique receipt ID
$receipt_id = uniqid();

// Create an order with Razorpay
$orderData = [
    'receipt'         => $receipt_id,
    'amount'          => $amount_in_paise, // Amount in paise
    'currency'        => 'INR',
    'payment_capture' => 1 // Automatically capture payment
];

$razorpayOrder = $api->order->create($orderData);
$orderId = $razorpayOrder['id'];

// Store order ID and booking ID in session for later verification
$_SESSION['razorpay_order_id'] = $orderId;
$_SESSION['booking_id'] = $booking_id;

// Prepare user details for prefill
$user_name = htmlspecialchars($booking['fullname']);
$user_email = htmlspecialchars($_SESSION['user_email'] ?? ''); // Ensure user_email is stored in session
$user_contact = htmlspecialchars($_SESSION['user_phone'] ?? ''); // Ensure user_phone is stored in session

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Car Rental Payment</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .payment-container {
            text-align: center;
            padding: 30px;
            background-color: #fff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            max-width: 500px;
            width: 90%;
        }
        .payment-container h2 {
            color: #333;
            margin-bottom: 10px;
        }
        .payment-container p {
            color: #555;
            font-size: 16px;
            margin: 5px 0;
        }
        .booking-details {
            text-align: left;
            margin: 20px 0;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 8px;
        }
        .booking-details h3 {
            margin-bottom: 10px;
            color: #444;
        }
        .booking-details p {
            margin: 5px 0;
        }
        .razorpay-button-container {
            margin-top: 20px;
        }
        .razorpay-button-container button {
            background-color: #5cb85c;
            color: #fff;
            border: none;
            padding: 12px 25px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .razorpay-button-container button:hover {
            background-color: #4cae4c;
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <h2>Complete Your Payment</h2>
        <p>Total Amount: <strong>₹<?php echo number_format($booking['total_price'], 2); ?></strong></p>
        
        <div class="booking-details">
            <h3>Booking Details</h3>
            <p><strong>Car Name:</strong> <?php echo htmlspecialchars($booking['vehicle_name']); ?></p>
            <p><strong>Booked By:</strong> <?php echo $user_name; ?></p>
            <?php if (isset($booking['booking_type']) && ($booking['booking_type'] === 'day' || $booking['days_booked'])): ?>
    <p><strong>Start Date:</strong> <?php echo date('d M Y', strtotime($booking['start_date'])); ?></p>
    <p><strong>End Date:</strong> <?php echo date('d M Y', strtotime($booking['end_date'])); ?></p>
    <p><strong>Days Booked:</strong> <?php echo htmlspecialchars($booking['days_booked']); ?></p>
<?php endif; ?>

<?php if (isset($booking['booking_type']) && ($booking['booking_type'] === 'hour' || $booking['hours_booked'])): ?>
    <p><strong>Date:</strong> <?php echo date('d M Y', strtotime($booking['start_date'])); ?></p>
    <p><strong>Start Time:</strong> <?php echo date('h:i A', strtotime($booking['start_time'])); ?></p>
    <p><strong>End Time:</strong> <?php echo date('h:i A', strtotime($booking['end_time'])); ?></p>
    <p><strong>Hours Booked:</strong> <?php echo htmlspecialchars($booking['hours_booked']); ?></p>
<?php endif; ?>

        </div>
        
        <!-- Razorpay payment button form -->

        <!-- Razorpay payment button script -->
        <div class="razorpay-button-container">
            <button id="payButton">Pay Now</button>
        </div>
    </div>

    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
        // Set up the payment options
        var options = {
            "key": "<?php echo $razorpay_key_id; ?>", 
            "amount": "<?php echo $amount_in_paise; ?>", // Amount in paise
            "currency": "INR",
            "name": "Car Rental System",
            "description": "Booking Payment",
            "image": "logo.png", // Add your logo file here
            "order_id": "<?php echo $orderId; ?>", 
          "handler": function (response) {
            var payment_id = response.razorpay_payment_id;
            var order_id = response.razorpay_order_id;

            // Send payment details to your server for verification
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'payment_success.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function () {
                // Once the server processes the payment, redirect the user
                if (xhr.status === 200) {
                    // Redirect to success page
                    window.location.href = 'payment_success.php?payment_id=' + payment_id + '&order_id=' + order_id;
                } else {
                    alert('Payment failed. Please try again.');
                }
            };
            xhr.send('razorpay_payment_id=' + payment_id + '&razorpay_order_id=' + order_id);
        }
    };

        // Open Razorpay payment on button click
        document.getElementById('payButton').onclick = function () {
            var rzp1 = new Razorpay(options);
            rzp1.open();
        };
    </script>
</body>
</html>
