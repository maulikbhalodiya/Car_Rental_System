<?php
session_start();

// Check if car_id is set in the URL
if (isset($_GET['car_id'])) {
    // Store car_id in the session
    $_SESSION['car_id'] = intval($_GET['car_id']);

    // Redirect to view-details.php with car_id in the URL
    header("Location: view-details.php?id=" . $_SESSION['car_id']);
    exit();
} else {
    // If car_id is not set, redirect back to the car listing page
    header("Location: car-listing.php");
    exit();
}
?>
