<?php
session_start(); // Start the session to check if the user is logged in

// Check if the user is logged in
$isLoggedIn = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Rental System</title>
    <!-- CSS file link -->
    <link rel="stylesheet" href="home.css">
     <!-- Include Bootstrap -->
     <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Font Awesome link for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');
    </style>
</head>
<body>
    <!-- Navigation Bar -->
   <?php include('header.php'); ?>
    <!-- Hero Section -->
    <section class="hero">
        <img src="images/home page/home img3.jpg" alt="Luxury Car">
        <div class="hero-text">
            <h1>Find The Best Car For You</h1>
            <p>Drive Your Way - Easy, Fast, and Reliable Car Rentals at Your Fingertips!</p>
            <a href="car-listing.php" class="btn">Browse Cars</a>
        </div>
    </section>

<?php include('footer.php')?>
<script>
    // Get the current page URL
    const currentLocation = window.location.pathname.split('/').pop();
    const navLinks = document.querySelectorAll('.nav-link');

    navLinks.forEach(link => {
        if (link.getAttribute('href') === currentLocation) {
            link.classList.add('active');
        }
    });
</script>
</body>
</html>