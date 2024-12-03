<?php
session_start();
require 'db_connection.php';
require 'vendor/autoload.php'; // Include PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: adminlogin.php");
    exit();
}

// Define function to send mail notification
function sendStatusMail($email, $name, $status) {
    $mail = new PHPMailer(true);
    
    try {
        //Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Set the SMTP server to send through
        $mail->SMTPAuth = true;
        $mail->Username = 'projectcp44@gmail.com'; // SMTP username
        $mail->Password = 'zmmg vcva urpi iwsy'; // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587; // TCP port to connect to

        //Recipients
        $mail->setFrom('projectcp44@gmail.com', 'Car Rental Team');
        $mail->addAddress($email, $name); // Add a recipient

        // Content
        $mail->isHTML(false); // Set email format to plain text
        $mail->Subject = "Car Rental Request " . ucfirst($status);
        $mail->Body    = "Dear $name,\n\nYour car rental request has been $status.\n\nRegards,\nCar Rental Team";

        $mail->send();
    } catch (Exception $e) {
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
    }
}

// Handle bulk actions
if (isset($_POST['bulk_action']) && isset($_POST['request_ids'])) {
    $action = $_POST['bulk_action'];
    $requestIds = $_POST['request_ids'];

    foreach ($requestIds as $id) {
        $id = intval($id);
        $query = "SELECT * FROM car_rent_requests WHERE id = $id";
        $result = mysqli_query($conn, $query);
        $row = mysqli_fetch_assoc($result);

        if ($row) {
            $email = $row['email'];
            $name = $row['name'];

            // Update status and send email
            if ($action === 'approve') {
                $updateQuery = "UPDATE car_rent_requests SET status = 'Approved' WHERE id = $id";
                mysqli_query($conn, $updateQuery);
                sendStatusMail($email, $name, 'approved');
            } elseif ($action === 'reject') {
                $updateQuery = "UPDATE car_rent_requests SET status = 'Rejected' WHERE id = $id";
                mysqli_query($conn, $updateQuery);
                sendStatusMail($email, $name, 'rejected');
            }
        }
    }

    header("Location: admin_view_requests.php?view=pending&message=Bulk action completed successfully.");
    exit();
}

// Handle individual approve/reject actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = intval($_GET['id']);

    $query = "SELECT * FROM car_rent_requests WHERE id = $id";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);

    if ($row) {
        $email = $row['email'];
        $name = $row['name'];

        if ($action === 'approve') {
            $updateQuery = "UPDATE car_rent_requests SET status = 'Approved' WHERE id = $id";
            mysqli_query($conn, $updateQuery);
            sendStatusMail($email, $name, 'approved');
        } elseif ($action === 'reject') {
            $updateQuery = "UPDATE car_rent_requests SET status = 'Rejected' WHERE id = $id";
            mysqli_query($conn, $updateQuery);
            sendStatusMail($email, $name, 'rejected');
        }
    }

    header("Location: admin_view_requests.php?view=pending&message=Action completed successfully.");
    exit();
}

header("Location: admin_view_requests.php?view=pending&message=No action performed.");
exit();
?>