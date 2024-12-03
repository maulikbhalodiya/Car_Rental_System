<?php
session_start();  // Start the session to store user data

$login_error = "";
$username = $password = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Database connection
    $servername = 'localhost';
    $db_username = 'root';
    $db_password = '';
    $dbname = 'car_rental_db';
    $conn = mysqli_connect($servername, $db_username, $db_password, $dbname);

    // Check for connection errors
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    // require('db_connection.php');

    // Check if the user exists (email or phone number) in the database
    $query = "SELECT * FROM users WHERE email='$username' OR phone='$username'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) == 1) {
        // Fetch the user data
        $user = mysqli_fetch_assoc($result);

        // Verify the password
        if (password_verify($password, $user['password'])) {
            // Set session variables for the logged-in user
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['email'];
            $_SESSION['loggedin'] = true; // Set the logged-in status

            // Redirect to car_rent_listing.php after successful login
            header("Location: home.php");
            exit();
        } else {
            $login_error = "Invalid username or password1.";
        }
    } else {
        $login_error = "Invalid username or password.";
    }

    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Rental Login Page</title>
    <!-- CSS file link -->
    <link rel="stylesheet" href="style.css">
    <!-- Font Awesome link for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<div class="container">
    <div class="login-form">
        <h1>Sign in</h1>
        <form action="" method="post">
            <div class="input-group">
                <label for="username">Username (Email):</label>
                <div class="input-wrapper">
                    <i class="fas fa-user"></i>
                    <input type="text" id="username" name="username"  placeholder="Enter Email" required>
                </div>
            </div>
            <div class="input-group">
                <label for="password">Password:</label>
                <div class="input-wrapper">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="password" name="password" placeholder="Password" required>
                </div>
            </div>
            <button type="submit">Login</button>
            <?php if ($login_error): ?>
                <span class="error-message"><?php echo $login_error; ?></span>
            <?php endif; ?>
            <a href="register.php">For Register click here</a>
            <a href="forgotpassword.php">Forget Password</a>
        </form>
    </div>
</div>

</body>
</html>
