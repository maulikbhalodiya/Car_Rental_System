<?php
session_start();
require('vendor/autoload.php'); // Ensure the correct path based on your setup
use Razorpay\Api\Api;
use setasign\Fpdi\Fpdi;

// Set Razorpay test mode keys
$razorpay_key_id = 'rzp_test_2xuCr5Gu8ys5Pr';
$razorpay_key_secret = 'aapkSTPDvH8y6ncT2GxseAs5';
$api = new Api($razorpay_key_id, $razorpay_key_secret);

// Get payment ID and order ID from the GET data
$payment_id = $_GET['payment_id'];
$order_id = $_GET['order_id'];
$booking_id = $_SESSION['booking_id'];

// Fetch payment details from Razorpay
try {
    $payment = $api->payment->fetch($payment_id);

    if ($payment && $payment->status == 'captured') {
        // Payment is successful; update booking status
        require('db_connection.php');

        $stmt = $conn->prepare("UPDATE booking_req SET status = 'Approved', payment_way = 'Razorpay' WHERE id = ?");
        $stmt->bind_param("i", $booking_id);

        if ($stmt->execute()) {
            $message = "Payment Successful! Your booking has been confirmed and is now Approved.";

            // Insert payment details
            $user_id = $_SESSION['user_id'];
            $vehicle_id = $_SESSION['car_id'];
            $amount = $payment->amount / 100;
            $payment_date = date("Y-m-d H:i:s");
            $payment_method = $payment->method;
            $payment_status = 'Completed';

            $stmt = $conn->prepare("
                INSERT INTO payments (booking_id, user_id, vehicle_id, amount, payment_date, payment_method, payment_status)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("iiidsss", $booking_id, $user_id, $vehicle_id, $amount, $payment_date, $payment_method, $payment_status);

            if ($stmt->execute()) {
                $message .= " Payment details have been successfully recorded.";
            } else {
                $message .= " Error recording payment details: " . $conn->error;
            }
        } else {
            $message = "Error updating booking: " . $conn->error;
        }

        $stmt->close();
        $conn->close();
    } else {
        $message = "Payment verification failed!";
    }
} catch (Exception $e) {
    $message = "Payment verification failed: " . $e->getMessage();
}

// Generate PDF
if (isset($payment) && $payment->status == 'captured') {
    if (isset($_POST['generate_pdf']) && $_POST['generate_pdf'] == 'yes') {
        // Fetch additional details (username, email, phone, vehicle name)
        require('db_connection.php');
        $stmt = $conn->prepare("
            SELECT u.fullname, u.email, u.phone, v.vehicle_name as vehicle_name 
            FROM users u 
            JOIN vehicles v ON v.id = ? 
            WHERE u.id = ?
        ");
        $stmt->bind_param("ii", $vehicle_id, $user_id);
        $stmt->execute();
        $stmt->bind_result($name, $email, $phone, $vehicle_name);
        $stmt->fetch();
        $stmt->close();
        $conn->close();

        // Create the PDF
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, 'Transaction Receipt', 1, 1, 'C');

        $pdf->SetFont('Arial', '', 12);
        $pdf->Ln(10);
        $pdf->Cell(0, 10, "Transaction ID: $payment_id", 0, 1);
        $pdf->Cell(0, 10, "Booking ID: $booking_id", 0, 1);
        $pdf->Cell(0, 10, "Name: $name", 0, 1);
        $pdf->Cell(0, 10, "Email: $email", 0, 1);
        $pdf->Cell(0, 10, "Phone: $phone", 0, 1);
        $pdf->Cell(0, 10, "Vehicle Name: $vehicle_name", 0, 1);
        $pdf->Cell(0, 10, "Amount: ₹$amount", 0, 1);
        $pdf->Cell(0, 10, "Payment Date: $payment_date", 0, 1);
        $pdf->Cell(0, 10, "Payment Method: $payment_method", 0, 1);
        $pdf->Cell(0, 10, "Payment Status: $payment_status", 0, 1);

        // Output PDF
        $file_name = "Transaction_$payment_id.pdf";
        $pdf->Output('D', $file_name);
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success</title>
    <script>
        // Redirect to car-listing.php after 5 seconds
        setTimeout(() => {
            alert('Your booking is done! Thank you for choosing our service.');
            window.location.href = 'car-listing.php';
        }, 5000);
    </script>
</head>
<body>
    <h2><?php echo $message; ?></h2>

    <!-- Form for PDF download -->
    <form method="POST">
        <label for="generate_pdf">Do you want a PDF for this transaction?</label>
        <select name="generate_pdf" id="generate_pdf" required>
            <option value="yes">Yes</option>
            <option value="no">No</option>
        </select>
        <button type="submit">Submit</button>
    </form>
</body>
</html>