<?php
session_start();
$isLoggedIn = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;

$userId = $isLoggedIn ? $_SESSION['user_id'] : null; 
// After user login

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if user is logged in before processing the form
    if (!$isLoggedIn) {
        echo "<script>alert('Please login first'); window.location.href='login.php';</script>";
        exit;
    }

    // Database connection
     include 'db_connection.php';

      // Get form data
      $name = trim($_POST['fullName']);
      $email = trim($_POST['email']);
      $subject = trim($_POST['subject']); // New subject field
      $message = trim($_POST['message']);
  
      // Prepare and bind
      $stmt = $conn->prepare("INSERT INTO contact_queries (user_id, name, email, subject, message, status) VALUES (?, ?, ?, ?, ?, 'Pending')");
      $stmt->bind_param("issss", $userId, $name, $email, $subject, $message);
  
      if ($stmt->execute()) {
          echo "<script>alert('Message sent successfully!');</script>";
      } else {
          echo "<script>alert('Error: " . $stmt->error . "');</script>";
      }
  
      $stmt->close();
      $conn->close();
  }
  ?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Car Rental System</title>
    <!-- Include Bootstrap -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Font Awesome link for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="home.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');
    </style>
</head>
<body>

    <!-- Navigation Bar -->
    <?php include('header.php'); ?>

    <div class="container mt-5">
    <h2>Contact Us</h2>
    <form action="contactus.php" method="POST" class="mt-4">
        <div class="form-group">
            <label for="fullName">Full Name:</label>
            <input type="text" class="form-control" id="fullName" name="fullName" required>
        </div>
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($_SESSION['username']) ? $_SESSION['username'] : ''; ?>" readonly required>
        </div>
        <div class="form-group">
            <label for="subject">Subject:</label>
            <input type="text" class="form-control" id="subject" name="subject" required>
        </div>
        <div class="form-group">
            <label for="message">Message:</label>
            <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Send Message</button>
    </form>
</div>

    <!-- Footer -->
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
