<?php
session_start();
$errors = [];
$new_password = $confirm_password = "";

// Step 3: Handle Password Reset Submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submitReset'])) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($new_password)) {
        $errors['new_password'] = "New password is required.";
    }
    if ($new_password !== $confirm_password) {
        $errors['confirm_password'] = "Passwords do not match.";
    }

    if (empty($errors)) {
        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

        // Update the password in the database
        $servername = 'localhost';
        $username = 'root';
        $psw = '';
        $dbname = 'car_rental_db';

        $conn = mysqli_connect($servername, $username, $psw, $dbname);

        if ($conn) {
            $email = $_SESSION['reset_email'];
            $sql = "UPDATE users SET password='$hashed_password' WHERE email='$email'";

            if (mysqli_query($conn, $sql)) {
                echo "<script>alert('Password reset successful!'); window.location.href = 'login.php';</script>";
                session_destroy();  // Clear session data
            } else {
                echo "Error updating record: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="style.css">
    <!-- Font Awesome link for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<div class="container">
    <div class="reset-password-form">
        <h1>Reset Password</h1>
        <form action="" method="post">
            <div class="input-group">
                <label for="new_password">New Password:</label>
                <div class="input-wrapper">
                <i class="fas fa-lock"></i>
                <input type="password" id="new_password" name="new_password" placeholder="Enter new password">
                <?php if (isset($errors['new_password'])): ?>
                    <span class="error-message"><?php echo $errors['new_password']; ?></span>
                <?php endif; ?>
            </div></div>

            <div class="input-group">
                <label for="confirm_password">Confirm Password:</label>
                <div class="input-wrapper">
                <i class="fas fa-lock"></i>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password">
                <?php if (isset($errors['confirm_password'])): ?>
                    <span class="error-message"><?php echo $errors['confirm_password']; ?></span>
                <?php endif; ?>
            </div></div>

            <button type="submit" name="submitReset">Reset Password</button>
        </form>
    </div>
</div>

</body>
</html>
