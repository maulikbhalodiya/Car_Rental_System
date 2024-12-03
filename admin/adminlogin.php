<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Rental Login Page</title>
    <!-- CSS file link -->
    <link rel="stylesheet" href="adminlogin.css">
    <!-- Font Awesome link for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<?php
session_start();
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

    // it will check for connection
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Sanitize user input to prevent SQL injection
    $username = mysqli_real_escape_string($conn, $username);
    $password = mysqli_real_escape_string($conn, $password);

    // Check if the user exists (Username) in the database
    $query = "SELECT * FROM admin WHERE UserName='$username'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) == 1) {
        // Fetch the user data
        $user = mysqli_fetch_assoc($result);
        // Hash the entered password and compare it with the stored hash (assuming MD5)
        if (md5($password) === $user['Password']) {
            // Redirect to the dashboard after successful login
            $_SESSION['admin_logged_in'] = true;
            header("Location: admindashboard.php");
            exit();
        } else {
            $login_error = "Invalid username or password.";
        }
    } else {
        $login_error = "Invalid username or password.";
    }

    mysqli_close($conn);
}
?>

<div class="container">
    <div class="login-form">
        <h1>Admin Log in</h1>
        <form action="" method="post">
            <div class="input-group">
                <label for="username">Username:</label>
                <div class="input-wrapper">
                    <i class="fas fa-user"></i>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" placeholder="Enter Username" required>
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
        </form>
    </div>
</div>

</body>
</html>