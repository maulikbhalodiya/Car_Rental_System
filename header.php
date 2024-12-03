<html>
<head>
    
    <!-- Font Awesome link for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="home.css"> <!-- Your CSS file -->
</head>
<body>
<header>
    <div class="top-bar">
        <div class="contact-info">
            <i class="fas fa-envelope"></i> projectcp@44gmail.com |   <i class="fas fa-phone"></i> 9726920463
        </div>
        <div class="profile">
            <i class="fas fa-user profile-icon"></i>
            <span><?php //echo $username; ?></span> <!-- Display actual username -->
            <div class="profile-dropdown">
                <a href="user-dashboard.php">Profile</a>
                <a href="earnings.php">Earning</a>
                <?php if (!$isLoggedIn): ?>
                <a href="login.php" class="login-btn">Login/Register</a>
            <?php else: ?>
                <a href="logout.php" class="login-btn">Logout</a>
            <?php endif; ?>
            </div>
        </div>
    </div>
    <nav class="navbar">
        <div class="logo">
            <a href="home.php">Car Rental System</a>
        </div>
        <div class="menu-toggle">
            <i class="fas fa-bars"></i>
        </div>
        <ul class="nav-links">
            <li><a href="home.php" class="nav-link">Home</a></li>
            <li><a href="car-listing.php" class="nav-link">Car Listing</a></li>
            <li><a href="car_rent_listing.php" class="nav-link">Rent your car</a></li>
            <!-- <li><a href="faq.php" class="nav-link">FAQs</a></li> -->
            <li><a href="contactus.php" class="nav-link">Contact Us</a></li>
            <li><a href="aboutus.php" class="nav-link">About Us</a></li>
        </ul>
    </nav>
</header>
<script>
    // Toggle mobile menu
    document.querySelector('.menu-toggle').addEventListener('click', function() {
        const navbar = document.querySelector('.navbar');
        navbar.classList.toggle('active');
    });

    // Close the dropdown when clicking outside
    window.addEventListener('click', function(event) {
        const profileDropdown = document.querySelector('.profile-dropdown');
        if (!event.target.closest('.profile') && profileDropdown.style.display === 'block') {
            profileDropdown.style.display = 'none';
        }
    });

    // Toggle dropdown on profile icon click
    document.querySelector('.profile').addEventListener('click', function() {
        const profileDropdown = document.querySelector('.profile-dropdown');
        profileDropdown.style.display = profileDropdown.style.display === 'block' ? 'none' : 'block';
    });
</script>
</body>
</html>
