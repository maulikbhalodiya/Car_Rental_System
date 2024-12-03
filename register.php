<?php
session_start();
require 'vendor/autoload.php';  // Include PHPMailer

$errors = [];
$fullname = $email = $phone = $password = $repassword = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submitRegistration'])) {
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $repassword = $_POST['repassword'];
    $enteredOtp = $_POST['otp'];

    // Validate OTP
    if ($_SESSION['otp'] && $_SESSION['otp_expiry'] > time()) {
        if ($enteredOtp != $_SESSION['otp']) {
            $errors['otp'] = "Invalid OTP. Please try again.";
        }
    } else {
        $errors['otp'] = "OTP has expired. Please request a new one.";
    }
    
    // Validate full name
    if (empty($_POST["fullname"])) {
        $errors['fullname'] = "Full name is required.";
    } else {
        $fullname = htmlspecialchars($_POST["fullname"]);
    }
      // Validate phone
      if (empty($_POST["phone"])) {
        $errors['phone'] = "Phone number is required.";
    } elseif (!preg_match("/^[6-9][0-9]{9}$/", $_POST["phone"])) {
        $errors['phone'] = "Please Enter valid phone number";
    } else {
        $phone = htmlspecialchars($_POST["phone"]);
    }
    
    // Validate password
    if (empty($_POST["password"])) {
            $errors['password'] = "Password is required.";
    }elseif (!preg_match("/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{5,15}$/", $_POST["password"])) {
        $errors['password'] = "Please enter a strong password.";
    } else {
        $password = htmlspecialchars($_POST["password"]);
    }
    //match password with re-enterpass
    if ($password !== $repassword) $errors['repassword'] = "Passwords do not match.";



    if (empty($errors)) {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Store the data in the database
        $servername = 'localhost';
        $username = 'root';
        $psw = '';
        $dbname = 'car_rental_db';

        $conn = mysqli_connect($servername, $username, $psw, $dbname);

        if ($conn) {
            $sql = "INSERT INTO users (fullname, email, phone, password) VALUES ('$fullname', '$email', '$phone', '$hashed_password')";
            if (mysqli_query($conn, $sql)) {
                echo "<script>alert('Registration successful!');</script>";
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
        } else {
            die("Connection failed: " . $conn->connect_error);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Rental Register Page</title>
    <link rel="stylesheet" href="style.css">
    <!-- Font awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<div class="container">
    <div class="register-form">
        <h1>Sign up</h1>
        <form action="" method="post">
            <div class="input-group">
                <label for="fullname">Full Name:</label>
                <div class="input-wrapper">
                    <i class="fas fa-user"></i>
                    <input type="text" id="fullname" name="fullname" value="<?php echo $fullname; ?>" placeholder="Full Name" required>
                </div>
                <?php if (isset($errors['fullname'])): ?>
                    <span class="error-message"><?php echo $errors['fullname']; ?></span>
                <?php endif; ?>
            </div>

            <div class="input-group">
                <label for="email">Email:</label>
                <div class="input-wrapper">
                    <i class="fas fa-envelope"></i>
                    <input type="email" id="email" name="email" value="<?php echo $email; ?>" placeholder="Email" required>
                </div>
                <button type="button" class="verify-btn" id="sendOtp" onclick="sendOtpRequest()">Verify Email</button>
                <span id="otpStatus"></span>
                <?php if (isset($errors['email'])): ?>
                    <span class="error-message"><?php echo $errors['email']; ?></span>
                <?php endif; ?>
            </div>

            <div class="input-group" id="otpSection" style="display:none;">
                <label for="otp">Enter OTP:</label>
                <div class="input-wrapper">
                    <i class="fas fa-lock"></i>
                    <input type="text" id="otp" name="otp" placeholder="Enter OTP" required>
                </div>
                <?php if (isset($errors['otp'])): ?>
                    <span class="error-message"><?php echo $errors['otp']; ?></span>
                <?php endif; ?>
            </div>

            <div class="input-group">
                <label for="phone">Phone Number:</label>
                <div class="input-wrapper">
                    <i class="fas fa-phone"></i>
                    <input type="tel" id="phone" name="phone" value="<?php echo $phone; ?>" placeholder="Phone Number" required>
               </div>
                <?php if (isset($errors['phone'])): ?>
                    <span class="error-message"><?php echo $errors['phone']; ?></span>
                <?php endif; ?>
            </div>

            <div class="input-group">
                <label for="password">Password:</label>
                <div class="input-wrapper">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="password" name="password" placeholder="Password" required>
                </div>
                <?php if (isset($errors['password'])): ?>
                    <span class="error-message"><?php echo $errors['password']; ?></span>
                <?php endif; ?>
            </div>

            <div class="input-group">
                <label for="repassword">Re-enter Password:</label>
                <div class="input-wrapper">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="repassword" name="repassword" placeholder="Re-enter Password" required>
                </div>
                <?php if (isset($errors['repassword'])): ?>
                    <span class="error-message"><?php echo $errors['repassword']; ?></span>
                <?php endif; ?>
            </div>

            <button type="submit" name="submitRegistration">Register</button>
            <a href="login.php">Already a user? Click here</a>
         </form>
    </div>
</div>

<script>
function sendOtpRequest() {
    var email = document.getElementById("email").value;

    if (email) {
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "otpHandler.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4 && xhr.status == 200) {
                document.getElementById("otpStatus").innerHTML = xhr.responseText;
                document.getElementById("otpSection").style.display = "block";
            }
        };

        xhr.send("email=" + email);
    } else {
        alert("Please enter a valid email.");
    }
}
</script>

</body>
</html>
