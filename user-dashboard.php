<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the user is logged in
$isLoggedIn = isset($_SESSION['user_id']) && $_SESSION['loggedin'] === true;

// Change 'customer_id' to 'user_id' to match the login script
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require('db_connection.php');

$user_id = $_SESSION['user_id']; // Changed from customer_id to user_id

// Fetch user information
$userQuery = "SELECT fullname, email, phone, address, city, zip_code, created_at, profile_pic FROM users WHERE id = ?";
$stmt = $conn->prepare($userQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$userResult = $stmt->get_result();
$user = $userResult->fetch_assoc();

// Check if user exists before proceeding
if (!$user) {
    header("Location: login.php"); // Redirect if user not found
    exit();
}

// Fetch rental history
$rentalQuery = "
    SELECT 
        b.id AS booking_id,
        v.vehicle_name,
        b.start_date AS pickup_date,
        b.end_date AS return_date,
        b.status AS booking_status,
        b.total_price AS total_amount
    FROM 
        booking_req b
    LEFT JOIN 
        vehicles v ON b.vehicle_id = v.id
    WHERE 
        b.user_id = ?
    ORDER BY 
        b.created_at DESC
";
$stmt = $conn->prepare($rentalQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$rentalResult = $stmt->get_result();

// Fetch favorites
$favoritesQuery = "
    SELECT 
        f.id AS favorite_id,
        v.vehicle_name,
        v.id AS vehicle_id
    FROM 
        favorites f
    JOIN 
        vehicles v ON f.vehicle_id = v.id
    WHERE 
        f.user_id = ?
    ORDER BY 
        f.date_added DESC
";
$stmt = $conn->prepare($favoritesQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$favoritesResult = $stmt->get_result();

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="user-dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<!-- Navigation bar -->
<?php include('header.php'); ?>

<section class="profile-section">
    <div class="profile-card">
        <div class="profile-header">
            <?php
            // Get the profile picture or use the default if it's not set
            $profilePic = isset($user['profile_pic']) ? htmlspecialchars($user['profile_pic']) : 'uploads/profile/profile.png';
            ?>
            <img src="<?php echo $profilePic; ?>" alt="User Image" class="profile-pic">
            <h2><?php echo htmlspecialchars($user['fullname']); ?></h2>
            <p>Member since <?php echo date("F Y", strtotime($user['created_at'])); ?></p>
            <a href="edit-profile.php" class="btn-edit">Edit Profile</a>
    </div>



        <div class="profile-details">
            <div class="profile-info">
                <h3>Personal Information</h3>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone']); ?></p>
                <p><strong>Address:</strong> 
                    <?php 
                        echo htmlspecialchars($user['address']) . ", ";
                        echo htmlspecialchars($user['city']) . ", ";
                        echo htmlspecialchars($user['zip_code']);
                    ?>
                </p>
            </div>

            <div class="profile-history">
                <h3>Rental History</h3>
                <?php if ($rentalResult->num_rows > 0): ?>
                    <ul class="rental-list">
                        <?php while($rental = $rentalResult->fetch_assoc()): ?>
                            <li>
                                <p><strong>Booking ID:</strong> <?php echo htmlspecialchars($rental['booking_id']); ?></p>
                                <p><strong>Car:</strong> <?php echo htmlspecialchars($rental['vehicle_name'] ?? 'N/A'); ?></p>
                                <p><strong>Pickup Date:</strong> <?php echo date("d M Y", strtotime($rental['pickup_date'])); ?></p>
                                <p><strong>Return Date:</strong> <?php echo date("d M Y", strtotime($rental['return_date'])); ?></p>
                                <p><strong>Booking Status:</strong> <?php echo htmlspecialchars($rental['booking_status']); ?></p>
                                <p><strong>Total Amount:</strong> ₹<?php echo number_format($rental['total_amount'], 2); ?></p>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                <?php else: ?>
                    <p>No rental history available.</p>
                <?php endif; ?>
            </div>

            <div class="profile-favorites">
                <h3>Favorites</h3>
                <?php if ($favoritesResult->num_rows > 0): ?>
                    <ul class="favorites-list">
                        <?php while($favorite = $favoritesResult->fetch_assoc()): ?>
                            <li>
                                <p><strong>Car:</strong> <?php echo htmlspecialchars($favorite['vehicle_name']); ?></p>
                                <form action="collection_details.php" method="GET">
                                    <input type="hidden" name="Registration_id" value="<?php echo htmlspecialchars($favorite['vehicle_id']); ?>">
                                    <button type="submit" class="btn btn-primary">View Details</button>
                                </form>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                <?php else: ?>
                    <p>No favorites available.</p>
                <?php endif; ?>
            </div>

            <div class="profile-settings">
                <h3>Account Settings</h3>
                <a href="change_password.php" class="btn btn-warning">Change Password</a>
                <a href="delete_account.php" class="btn btn-danger">Delete Account</a>
                <form action="logout.php" method="POST">
                    <button type="submit" class="btn btn-secondary"><i class="fas fa-sign-out-alt"></i> Logout</button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php include('footer.php'); ?>

</body>
</html>
