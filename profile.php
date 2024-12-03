<?php
// Start the session
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the user is logged in
$isLoggedIn = isset($_SESSION['customer_id']);

// Check if the user is logged in
if (!isset($_SESSION['customer_id'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}

// Database connection parameters
// $servername = "localhost";
// $username = "root"; // Your DB username
// $password = "";     // Your DB password
// $dbname = "car_rental_db"; // Your DB name

// // Create a new MySQLi connection
// $conn = new mysqli($servername, $username, $password, $dbname);

// // Check the connection
// if ($conn->connect_error) {
//     die("Connection failed: " . $conn->connect_error);
// }
require('db_connection.php');

// Fetch user information from the customers table
$customer_id = $_SESSION['customer_id'];

// Prepare and execute the query to fetch user data
$userQuery = "SELECT fullname, email, phone, address, city, zip_code, created_at, profile_pic FROM customers WHERE customer_id = ?";
$stmt = $conn->prepare($userQuery);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$userResult = $stmt->get_result();

// Check if user exists
if ($userResult->num_rows === 0) {
    echo "User not found.";
    exit();
}

$user = $userResult->fetch_assoc();

// Fetch rental history from bookings table, joining with vehicles to get car details
$rentalQuery = "
    SELECT 
        b.booking_id,
        v.vehicle_name,
        b.pickup_date,
        b.return_date,
        b.booking_status,
        b.total_amount,
        b.payment_status,
        c.chauffeurs_id,
        c.chauffeurs_name,
        c.age,
        c.photo
    FROM 
        bookings b
    LEFT JOIN 
        vehicles v ON b.vehicle_registration_id = v.Registration_id
    LEFT JOIN
        chauffeurs c ON b.chauffeurs_id = c.chauffeurs_id
    WHERE 
        b.customer_id = ?
    ORDER BY 
        b.booking_date DESC
";

// Prepare the rental query
$stmt = $conn->prepare($rentalQuery);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $customer_id);

// Execute the rental query
if (!$stmt->execute()) {
    die("Execution failed: " . $stmt->error);
}

// Get the result
$rentalResult = $stmt->get_result();

// Check for errors in fetching data
if ($rentalResult === false) {
    die("Error fetching results: " . $conn->error);
}

// Fetch favorites from favorites table, joining with vehicles to get car details
$favoritesQuery = "
    SELECT 
        f.favorite_id,
        v.vehicle_name,
        v.Registration_id
    FROM 
        favorites f
    JOIN 
        vehicles v ON f.vehicle_registration_id = v.Registration_id
    WHERE 
        f.customer_id = ?
    ORDER BY 
        f.date_added DESC
";
$stmt = $conn->prepare($favoritesQuery);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$favoritesResult = $stmt->get_result();

// Fetch hired chauffeurs separately
$hiredChauffeursQuery = "
    SELECT 
        b.booking_id,
        c.chauffeurs_id,
        c.chauffeurs_name,
        c.age,
        c.photo
    FROM 
        bookings b
    JOIN 
        chauffeurs c ON b.chauffeurs_id = c.chauffeurs_id
    WHERE 
        b.customer_id = ? AND 
        b.booking_status = 'Confirmed' AND
        b.chauffeurs_id IS NOT NULL
    ORDER BY 
        b.booking_date DESC
";

$stmt = $conn->prepare($hiredChauffeursQuery);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$hiredChauffeursResult = $stmt->get_result();

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="profile.css">
    <!-- Add FontAwesome for the logout icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Bootstrap CSS for styling messages -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<!-- Navigation bar -->
<?php include('header.php'); ?>

<section class="profile-section">
    <div class="profile-card">

        <!-- Display success or error messages -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success">
                <?php 
                    echo $_SESSION['message']; 
                    unset($_SESSION['message']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                    echo $_SESSION['error']; 
                    unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <div class="profile-header">
            <img src="<?php echo htmlspecialchars($user['profile_pic']); ?>" alt="User Image" class="profile-pic">
            <h2><?php echo htmlspecialchars($user['fullname']); ?></h2>
            <p>Member since <?php echo date("F Y", strtotime($user['created_at'])); ?></p>
            <a href="edit_profile.php" class="btn-edit">Edit Profile</a>
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
                    <p>Number of bookings: <?php echo $rentalResult->num_rows; ?></p>
                    <ul class="rental-list">
                        <?php while($rental = $rentalResult->fetch_assoc()): ?>
                            <li>
                                <p><strong>Booking ID:</strong> <?php echo htmlspecialchars($rental['booking_id']); ?></p>
                                <p><strong>Car:</strong> <?php echo htmlspecialchars($rental['vehicle_name'] ?? 'N/A'); ?></p>
                                <p><strong>Pickup Date:</strong> <?php echo date("d M Y", strtotime($rental['pickup_date'])); ?></p>
                                <p><strong>Return Date:</strong> <?php echo date("d M Y", strtotime($rental['return_date'])); ?></p>
                                <p><strong>Booking Status:</strong> <?php echo htmlspecialchars($rental['booking_status']); ?></p>
                                <p><strong>Total Amount:</strong> $<?php echo number_format($rental['total_amount'], 2); ?></p>
                                <p><strong>Payment Status:</strong> <?php echo htmlspecialchars($rental['payment_status']); ?></p>
                                <?php if (!empty($rental['chauffeurs_name'])): ?>
                                    <p><strong>Chauffeur:</strong> <?php echo htmlspecialchars($rental['chauffeurs_name']); ?></p>
                                <?php endif; ?>
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
                             <input type="hidden" name="Registration_id" value="<?php echo htmlspecialchars($favorite['Registration_id']); ?>">
                             <button type="submit" class="btn btn-primary">View Details</button>
                           </form>
                       </li>
                      <?php endwhile; ?>
                   </ul>
                <?php else: ?>
               <p>No favorites available.</p>
              <?php endif; ?>
            </div>
 
            <div class="profile-hired-chauffeurs">
                <h3>Hired Chauffeurs</h3>
                <?php if ($hiredChauffeursResult->num_rows > 0): ?>
                    <ul class="hired-chauffeurs-list">
                        <?php while($chauffeur = $hiredChauffeursResult->fetch_assoc()): ?>
                            <li>
                                <p><strong>Chauffeur:</strong> <?php echo htmlspecialchars($chauffeur['chauffeurs_name']); ?></p>
                                <p><strong>Age:</strong> <?php echo htmlspecialchars($chauffeur['age']); ?></p>
                                <img src="<?php echo htmlspecialchars($chauffeur['photo']); ?>" alt="Chauffeur Photo" class="chauffeur-pic">
                                <form action="dehire_chauffeur.php" method="POST">
                                    <input type="hidden" name="chauffeurs_id" value="<?php echo htmlspecialchars($chauffeur['chauffeurs_id']); ?>">
                                    <button type="submit" class="btn btn-danger">Dehire</button>
                                </form>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                <?php else: ?>
                    <p>No hired chauffeurs.</p>
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
