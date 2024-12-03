<?php
session_start();
require 'vendor/autoload.php';  // Include PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$errors = [];
$email = "";

// Step 1: Handle Email Submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submitEmail'])) {
    $email = $_POST['email'];

    // Check if email exists in the database
    $servername = 'localhost';
    $username = 'root';
    $psw = '';
    $dbname = 'car_rental_db';

    $conn = mysqli_connect($servername, $username, $psw, $dbname);

    if ($conn) {
        $sql = "SELECT * FROM users WHERE email='$email'";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {
            // Email exists, generate OTP and send email
            $otp = rand(100000, 999999);
            $_SESSION['otp'] = $otp;
            $_SESSION['otp_expiry'] = time() + 60 * 5;  // 5 minutes validity
            $_SESSION['reset_email'] = $email;

            // Send OTP using PHPMailer
            $mail = new PHPMailer(true);
            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'projectcp44@gmail.com';  // Your Gmail address
                $mail->Password = 'zmmg vcva urpi iwsy';    // Your generated app password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Recipients
                $mail->setFrom('projectcp44@gmail.com', 'Car Rental Support');
                $mail->addAddress($email);

                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Password Reset OTP';
                $mail->Body    = 'Your OTP for password reset is: <b>' . $otp . '</b>';
                $mail->AltBody = 'Your OTP for password reset is: ' . $otp;

                $mail->send();
                header("Location: verifyotp.php");  // Redirect to OTP verification page
                exit;
            } catch (Exception $e) {
                $errors['email'] = "Error: OTP could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            $errors['email'] = "Your email is not registered in our database.";
        }
    } else {
        $errors['email'] = "Connection failed: " . mysqli_connect_error();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<div class="container">
    <div class="forgot-password-form">
        <h1>Forgot Password</h1>
        <form action="" method="post">
            <div class="input-group">
                <label for="email">Registered Email:</label>
                <div class="input-wrapper">
                <i class="fas fa-envelope"></i>
                <input type="email" id="email" name="email" value="<?php echo $email; ?>" placeholder="Enter your registered email">
                <?php if (isset($errors['email'])): ?>
                    <span class="error-message"><?php echo $errors['email']; ?></span>
                <?php endif; ?>
            </div>
            </div>

            <button type="submit" name="submitEmail">Send OTP</button>
            <a href="register.php">Not a user? Register here</a>
        </form>
    </div>
</div>

</body>
</html>
