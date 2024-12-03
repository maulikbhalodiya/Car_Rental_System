<?php
session_start();
require 'db_connection.php';
// Check if the user is logged in
$isLoggedIn = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if the user is not logged in
    header('Location: login.php');
    exit;
}
?>
<!-- about-us.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Car Rental System</title>
    <link rel="stylesheet" href="about-us.css">
</head>
<body>
    <!-- Navigation Bar -->
    <?php include('header.php'); ?>

    <div class="about-us-container container">
        <h1 class="page-title">About Us</h1>

        <!-- Mission Section -->
        <section class="section-container mission-section">
            <div class="section-title">
                <h2>Our Mission</h2>
            </div>
            <p>Our mission is to provide reliable, affordable, and convenient car rental services to our customers. We strive to ensure every customer experiences hassle-free and enjoyable travel solutions.</p>
        </section>

        <!-- Values Section -->
        <section class="section-container values-section">
            <div class="section-title">
                <h2>Our Values</h2>
            </div>
            <div class="values-list">
                <div class="value-item">
                    <h3>Customer Satisfaction</h3>
                    <p>We prioritize our customers’ needs and strive to exceed their expectations.</p>
                </div>
                <div class="value-item">
                    <h3>Quality</h3>
                    <p>We maintain a high standard for all our vehicles to ensure safety and comfort.</p>
                </div>
                <div class="value-item">
                    <h3>Transparency</h3>
                    <p>We offer fair pricing and no hidden charges.</p>
                </div>
            </div>
        </section>

        <!-- Meet the Team Section -->
        <section class="section-container team-section">
            <div class="section-title">
                <h2>Meet the Team</h2>
            </div>
            <p>We are a team of dedicated professionals committed to making car rentals easier and more accessible. Our team is here to assist you with every step of your rental journey.</p>

            <div class="team-grid">
                <!-- Example team member. Repeat for each team member -->
                <div class="team-member">
                    <img src="team-member.jpg" alt="Team Member" class="team-photo">
                    <h3>Maulik Bhalodiya</h3>
                    <p>CEO & Founder</p>
                </div>
                <div class="team-member">
                    <img src="team-member.jpg" alt="Team Member" class="team-photo">
                    <h3>Tushar Umretiya</h3>
                    <p>Co-Founder</p>
                </div>
                <div class="team-member">
                    <img src="team-member.jpg" alt="Team Member" class="team-photo">
                    <h3>Bharatsinh Solanki</h3>
                    <p>CTO</p>
                </div>
            </div>
        </section>
    </div>

    <!-- Footer -->
    <?php include('footer.php'); ?>
</body>
</html>
