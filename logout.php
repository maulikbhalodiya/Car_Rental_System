<?php
// Start the session
session_start();

// Destroy all session variables
session_unset();

// Destroy the session
session_destroy();

// Redirect to login page after a few seconds
header("Refresh: 3; url=login.php"); // Redirects after 3 seconds

// Message for the user
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout Successful</title>
    <link rel="stylesheet" href="style.css"> <!-- Link to a stylesheet (optional) -->
    <style>
        /* Quick CSS styling for the logout message */
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }

        .logout-container {
            text-align: center;
            background: #fff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .logout-container h1 {
            color: #27ae60;
            margin-bottom: 20px;
        }

        .logout-container p {
            font-size: 18px;
            margin-bottom: 20px;
            color: #333;
        }

        .logout-container a {
            color: #2980b9;
            text-decoration: none;
            font-weight: bold;
        }

        .logout-container a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="logout-container">
    <h1>You've been logged out!</h1>
    <p>You will be redirected to the login page shortly.</p>
    <p>If not, click <a href="index.php">here</a> to go to the home page.</p>
</div>

</body>
</html>
