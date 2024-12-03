<?php
session_start();

$errors = [];
$otp = "";

// Step 2: Handle OTP Submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submitOtp'])) {
    $otp = $_POST['otp'];

    // Validate OTP
    if ($_SESSION['otp'] && $_SESSION['otp_expiry'] > time()) {
        if ($otp == $_SESSION['otp']) {
            // OTP is valid, allow password reset
            header("Location: resetpassword.php");  // Redirect to password reset page
            exit;
        } else {
            $errors['otp'] = "Invalid OTP. Please try again.";
        }
    } else {
        $errors['otp'] = "OTP has expired. Please request a new one.";
        header("Location: forgotpassword.php");  // Redirect to forgot password page
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP</title>
    <link rel="stylesheet" href="style.css">
    <!-- Font Awesome link for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<div class="container">
    <div class="verify-otp-form">
        <h1>Verify OTP</h1>
        <form action="" method="post">
            <div class="input-group">
                <label for="otp">Enter OTP:</label>
                <div class="input-wrapper">
                <i class="fas fa-lock"></i>
                <input type="text" id="otp" name="otp" value="<?php echo $otp; ?>" placeholder="Enter OTP">
                <?php if (isset($errors['otp'])): ?>
                    <span class="error-message"><?php echo $errors['otp']; ?></span>
                <?php endif; ?>
            </div></div>

            <button type="submit" name="submitOtp">Verify OTP</button>
        </form>
    </div>
</div>

</body>
</html>
