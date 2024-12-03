<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// $servername = "localhost";
// $username = "root";
// $password = "";
// $dbname = "car_rental_db";

// $conn = new mysqli($servername, $username, $password, $dbname);
// if ($conn->connect_error) {
//     die("Connection failed: " . $conn->connect_error);
// }
require('db_connection.php');

$customer_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $deleteQuery = "DELETE FROM customers WHERE customer_id = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("i", $customer_id);

    if ($stmt->execute()) {
        session_destroy();
        echo "<p class='message success'>Account deleted successfully. Redirecting to homepage...</p>";
        header("refresh:2;url=index.php");
    } else {
        echo "<p class='message error'>Error deleting account: " . $conn->error . "</p>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="edit_profile.css">
    <title>Delete Account</title>
</head>
<body>
    <header>
        <h2>Delete Account</h2>
    </header>
    <div class="container">
        <p>Are you sure you want to delete your account? This action cannot be undone.</p>
        <form action="delete_account.php" method="POST">
            <button type="submit">Delete Account</button>
        </form>
        <a href="profile.php" class="btn-cancel">Cancel</a>
    </div>
</body>
</html>
