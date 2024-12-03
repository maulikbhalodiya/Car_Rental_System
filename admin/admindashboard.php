<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
   // Redirect to login page
   header("Location: adminlogin.php");
   exit();
}
require 'db_connection.php';
// Query to get the total number of users
$userQuery = "SELECT COUNT(*) AS total_users FROM users";
$userResult = mysqli_query($conn, $userQuery);
$userData = mysqli_fetch_assoc($userResult);
$totalUsers = $userData['total_users'];

// Query to get the total number of brands
$brandQuery = "SELECT COUNT(*) AS total_brands FROM brands";
$brandResult = mysqli_query($conn, $brandQuery);
$brandData = mysqli_fetch_assoc($brandResult);
$totalBrands = $brandData['total_brands'];

// Query to get the total number of vehicles
$vehicleQuery = "SELECT COUNT(*) AS total_vehicles FROM vehicles";
$vehicleResult = mysqli_query($conn, $vehicleQuery);
$vehicleData = mysqli_fetch_assoc($vehicleResult);
$totalVehicles = $vehicleData['total_vehicles'];

// Query to get the total number of bookings
$bookingQuery = "SELECT COUNT(*) AS total_bookings FROM booking_req";
$bookingResult = mysqli_query($conn, $bookingQuery);
$bookingData = mysqli_fetch_assoc($bookingResult);
$totalBookings = $bookingData['total_bookings'];

// Query to get the total number of queries
$queryQuery = "SELECT COUNT(*) AS total_queries FROM contact_queries";
$queryResult = mysqli_query($conn, $queryQuery);
$queryData = mysqli_fetch_assoc($queryResult);
$totalQueries = $queryData['total_queries'];


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Rental System | Admin Panel</title>
    <link rel="stylesheet" href="admin-style.css">
    <link rel="stylesheet" href="admin_view_requests.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <!-- Navbar and sidebar -->
        <?php require('navbar.php');?>
    
        <div class="dashboard-cards">
            <div class="dashboard-card card-1">
                <h3><?php echo $totalUsers; ?></h3>
                <p>Reg Users</p>
                <div class="details"><a href="registered_users.php">Full Details →</a></div>
            </div>
            <div class="dashboard-card card-2">
                <h3><?php echo $totalBrands; ?></h3>
                <p>Listed Brands</p>
                <div class="details"><a href="manage-brands.php">Full Details →</a></div>
            </div>
            <div class="dashboard-card card-3">
                <h3><?php echo $totalVehicles; ?></h3>
                <p>Listed Vehicles</p>
                <div class="details"><a href="manage-vehicles.php">Full Details →</a></div>
            </div>
            <div class="dashboard-card card-4">
                <h3><?php echo $totalBookings; ?></h3>
                <p>Total Bookings</p>
                <div class="details"><a href="#">Full Details →</a></div>
            </div>
        
            <!-- <div class="dashboard-card card-5">
                <h3>3</h3>
                <p>Subscribers</p>
                <div class="details"><a href="#">Full Details →</a></div>
            </div> -->
            <div class="dashboard-card card-7">
                <h3><?php echo $totalQueries; ?></h3>
                <p>Queries</p>
                <div class="details"><a href="manage-contact-queries.php">Full Details →</a></div>
            </div>
            <!-- <div class="dashboard-card card-7">
                <h3>3</h3>
                <p>Testimonials</p>
                <div class="details"><a href="#">Full Details →</a></div>
            </div> -->
        </div>
    </div>
    <script>
    // Get the current page URL
    const currentLocation = window.location.pathname.split('/').pop();
    const navLinks = document.querySelectorAll('.sidebar a'); // Select the correct links

    navLinks.forEach(link => {
        if (link.getAttribute('href') === currentLocation) {
            link.classList.add('active'); // Add 'active' class if it matches
        }
    });
</script>

</body>
</html>
