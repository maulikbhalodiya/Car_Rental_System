<?php
session_start();
require 'vendor/autoload.php';  // Include PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'])) {
    $email = $_POST['email'];
    $otp = rand(100000, 999999); //6 digit otp generation

    // Store OTP in session
    $_SESSION['otp'] = $otp;
    $_SESSION['otp_expiry'] = time() + 60 * 2;  // 2 minutes validity

    $mail = new PHPMailer(true);
    try {
        //Server settings
        $mail->isSMTP();
         $mail->Host = 'smtp.gmail.com';
         $mail->SMTPAuth = true;
         $mail->Username = 'projectcp44@gmail.com';  // Your Gmail address
         $mail->Password = 'zmmg vcva urpi iwsy';    // Your generated app password
         $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
         $mail->Port = 587; //The port for TLS on Gmail's SMTP server.


        // Enable verbose debug output
        $mail->SMTPDebug = 0;  // Set to 0 when not debugging
        $mail->Debugoutput = 'html';  // Display debug as HTML

        // Recipients
        $mail->setFrom('projectcp44@gmail.com', 'From Maulik and team');
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Email Verification OTP';
        $mail->Body    = 'for Registration in Car Rental System Your OTP is: <b>' . $otp . '</b>';
        $mail->AltBody = 'Your OTP is: ' . $otp;

        $mail->send();
        echo 'OTP has been sent to your email!';
    } catch (Exception $e) {
        echo "Error: OTP could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
?>