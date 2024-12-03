<?php
$servername = "localhost";   // Replace with your database server name if different
$username = "root";          // Replace with your database username
$password = "";              // Replace with your database password
$dbname = "car_rental_db";   // Replace with your database name

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>