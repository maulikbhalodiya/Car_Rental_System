<?php
$servername = 'localhost';
$username = 'root';
$psw = '';
$dbname = 'car_rental_db';

$conn = mysqli_connect($servername, $username, $psw, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get vehicle ID from URL
$vehicle_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch vehicle details
$sql = "SELECT v.*, b.brand_name FROM vehicles v 
        JOIN brands b ON v.brand_id = b.id 
        WHERE v.id = $vehicle_id";

$result = mysqli_query($conn, $sql);
$vehicle = mysqli_fetch_assoc($result);

// If vehicle doesn't exist, redirect back
if (!$vehicle) {
    header("Location: manage-vehicles.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Details | Car Rental System</title>
    <link rel="stylesheet" href="admin-style.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="sidebar">
        <h1>Car Rental System | Admin Panel</h1>
        <ul>
            <li><a href="admindashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="manage-brands.php"><i class="fas fa-car"></i> Brands</a></li>
            <li><a href="admin_view_requests.php"><i class="fas fa-file-alt"></i> View Pending Requests</a></li>
            <li><a href="manage-vehicles.php" class="active"><i class="fas fa-car"></i> Vehicles</a></li>
            <li><a href="#"><i class="fas fa-book"></i> Bookings </a></li>
            <li><a href="#"><i class="fas fa-comments"></i> Manage Testimonials</a></li>
            <li><a href="#"><i class="fas fa-envelope"></i> Manage Contact Queries</a></li>
            <li><a href="#"><i class="fas fa-users"></i> Registered Users</a></li>
            <li><a href="#"><i class="fas fa-address-book"></i> Update Contact Info</a></li>
            <li><a href="#"><i class="fas fa-user-plus"></i> Manage Subscribers</a></li>
        </ul>
    </div>
    <div class="content">
        <div class="top-bar">
            <h2>Dashboard</h2>
            <div class="user-info">
                <img src="admindp.png" alt="User Avatar">
                <span>Admin</span>
                <a href="adminlogin.php" class="logout-btn">Log Out</a>
            </div>
        </div>
        <div class="vehicle-details">
            <h3><?php echo htmlspecialchars($vehicle['vehicle_name']); ?> (<?php echo htmlspecialchars($vehicle['brand_name']); ?>)</h3>
            <table class="table table-striped">
                <tr>
                    <th>Type:</th>
                    <td><?php echo htmlspecialchars($vehicle['car_type']); ?></td>
                </tr>
                <tr>
                    <th>Price per Day (₹):</th>
                    <td><?php echo htmlspecialchars($vehicle['price_per_day']); ?></td>
                </tr>
                <tr>
                    <th>Rent per Hour (₹):</th>
                    <td><?php echo htmlspecialchars($vehicle['rent_per_hour']); ?></td>
                </tr>
                <tr>
                    <th>Fuel Type:</th>
                    <td><?php echo htmlspecialchars($vehicle['fuel_type']); ?></td>
                </tr>
                <tr>
                    <th>Seating Capacity:</th>
                    <td><?php echo htmlspecialchars($vehicle['seating_capacity']); ?></td>
                </tr>
                <tr>
                    <th>Engine:</th>
                    <td><?php echo htmlspecialchars($vehicle['engine']); ?></td>
                </tr>
                <tr>
                    <th>Transmission:</th>
                    <td><?php echo htmlspecialchars($vehicle['transmission']); ?></td>
                </tr>
                <tr>
                    <th>Power:</th>
                    <td><?php echo htmlspecialchars($vehicle['power']); ?></td>
                </tr>
                <tr>
                    <th>Torque:</th>
                    <td><?php echo htmlspecialchars($vehicle['torque']); ?></td>
                </tr>
                <tr>
                    <th>Fuel Tank Capacity:</th>
                    <td><?php echo htmlspecialchars($vehicle['fuel_tank_capacity']); ?></td>
                </tr>
                <tr>
                    <th>Mileage:</th>
                    <td><?php echo htmlspecialchars($vehicle['mileage']); ?></td>
                </tr>
                <tr>
                    <th>Model Year:</th>
                    <td><?php echo htmlspecialchars($vehicle['model_year']); ?></td>
                </tr>
                <tr>
                    <th>Photos:</th>
                    <td>
                        <?php
                        if (!empty($vehicle['photos'])) {
                            $photos = explode(',', $vehicle['photos']);
                            foreach ($photos as $photo) {
                                echo "<img src='vehicles/" . htmlspecialchars(trim($photo)) . "' width='100' height='100' alt='Car Image' class='thumbnail'>";
                            }
                        } else {
                            echo "No photos available";
                        }
                        ?>
                    </td>
                </tr>
            </table>
            <a href="manage-vehicles.php" class="btn btn-primary">Back to Manage Vehicles</a>
        </div>
    </div>
</body>
</html>

<?php
mysqli_close($conn);
?>
