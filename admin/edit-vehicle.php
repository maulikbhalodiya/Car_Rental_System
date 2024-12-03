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
$sql = "SELECT * FROM vehicles WHERE id = $vehicle_id";
$result = mysqli_query($conn, $sql);
$vehicle = mysqli_fetch_assoc($result);

// If vehicle doesn't exist, redirect back
if (!$vehicle) {
    header("Location: manage-vehicles.php");
    exit();
}

// Handle form submission for editing vehicle
if (isset($_POST['update_vehicle'])) {
    $vehicle_name = mysqli_real_escape_string($conn, $_POST['vehicle_name']);
    $brand_id = mysqli_real_escape_string($conn, $_POST['brand_id']);
    $price_per_day = mysqli_real_escape_string($conn, $_POST['price_per_day']);
    $rent_per_hour = mysqli_real_escape_string($conn, $_POST['rent_per_hour']);
    $fuel_type = mysqli_real_escape_string($conn, $_POST['fuel_type']);
    $seating_capacity = mysqli_real_escape_string($conn, $_POST['seating_capacity']);
    $engine = mysqli_real_escape_string($conn, $_POST['engine']);
    $transmission = mysqli_real_escape_string($conn, $_POST['transmission']);
    $power = mysqli_real_escape_string($conn, $_POST['power']);
    $torque = mysqli_real_escape_string($conn, $_POST['torque']);
    $fuel_tank_capacity = mysqli_real_escape_string($conn, $_POST['fuel_tank_capacity']);
    $mileage = mysqli_real_escape_string($conn, $_POST['mileage']);
    $model_year = mysqli_real_escape_string($conn, $_POST['model_year']);
    $photos = [];

    // Handle photo uploads
    if (isset($_FILES['photos']['name']) && !empty($_FILES['photos']['name'][0])) {
        $photo_count = count($_FILES['photos']['name']);
        for ($i = 0; $i < $photo_count; $i++) {
            $photo_name = $_FILES['photos']['name'][$i];
            $photo_tmp = $_FILES['photos']['tmp_name'][$i];
            $photo_dest = 'images/vehicles/' . basename($photo_name);

            if (move_uploaded_file($photo_tmp, $photo_dest)) {
                $photos[] = $photo_name;
            }
        }
    }

    $photos_str = implode(',', $photos);
    $photos_sql = !empty($photos_str) ? ", photos = '$photos_str'" : '';

    // Update vehicle details in database
    $sql_update = "UPDATE vehicles 
                   SET vehicle_name = '$vehicle_name', brand_id = '$brand_id', price_per_day = '$price_per_day', 
                       rent_per_hour = '$rent_per_hour', fuel_type = '$fuel_type', seating_capacity = '$seating_capacity',
                       engine = '$engine', transmission = '$transmission', power = '$power', torque = '$torque',
                       fuel_tank_capacity = '$fuel_tank_capacity', mileage = '$mileage', model_year = '$model_year' 
                       $photos_sql
                   WHERE id = $vehicle_id";

    if (mysqli_query($conn, $sql_update)) {
        echo "<script>alert('Vehicle updated successfully'); window.location.href='manage-vehicles.php';</script>";
    } else {
        echo "<script>alert('Error updating vehicle: " . mysqli_error($conn) . "');</script>";
    }
}

// Fetch brands for dropdown
$brands_sql = "SELECT * FROM brands ORDER BY brand_name";
$brands_result = mysqli_query($conn, $brands_sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Vehicle | Car Rental System</title>
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
            <h2>Edit Vehicle</h2>
            <div class="user-info">
                <span class="admin-icon"><i class="fas fa-user-shield"></i></span>
                <span>Admin</span>
                <a href="adminlogin.php" class="logout-btn">Log Out</a>
            </div>
        </div>
        <div class="edit-vehicle-form">
            <form action="edit-vehicle.php?id=<?php echo $vehicle_id; ?>" method="POST" enctype="multipart/form-data">
            <div class="edit-vehicle-form">
    <form action="edit-vehicle.php?id=<?php echo $vehicle_id; ?>" method="POST" enctype="multipart/form-data">
        <div class="row">
            <div class="form-group col-md-6">
                <label for="vehicle_name">Vehicle Name:</label>
                <input type="text" class="form-control" name="vehicle_name" value="<?php echo htmlspecialchars($vehicle['vehicle_name']); ?>" required>
            </div>
            <div class="form-group col-md-6">
                <label for="brand_id">Brand:</label>
                <select class="form-control" name="brand_id" required>
                    <?php while ($brand = mysqli_fetch_assoc($brands_result)): ?>
                        <option value="<?php echo $brand['id']; ?>" <?php echo ($vehicle['brand_id'] == $brand['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($brand['brand_name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-4">
                <label for="price_per_day">Price per Day (₹):</label>
                <input type="number" class="form-control" name="price_per_day" value="<?php echo htmlspecialchars($vehicle['price_per_day']); ?>" required>
            </div>
            <div class="form-group col-md-4">
                <label for="rent_per_hour">Rent per Hour (₹):</label>
                <input type="number" class="form-control" name="rent_per_hour" value="<?php echo htmlspecialchars($vehicle['rent_per_hour']); ?>" required>
            </div>
            <div class="form-group col-md-4">
                <label for="fuel_type">Fuel Type:</label>
                <select class="form-control" name="fuel_type" required>
                    <option value="Petrol" <?php echo ($vehicle['fuel_type'] == 'Petrol') ? 'selected' : ''; ?>>Petrol</option>
                    <option value="Diesel" <?php echo ($vehicle['fuel_type'] == 'Diesel') ? 'selected' : ''; ?>>Diesel</option>
                    <option value="CNG" <?php echo ($vehicle['fuel_type'] == 'CNG') ? 'selected' : ''; ?>>CNG</option>
                    <option value="Electric" <?php echo ($vehicle['fuel_type'] == 'Electric') ? 'selected' : ''; ?>>Electric</option>
                </select>
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-4">
                <label for="seating_capacity">Seating Capacity:</label>
                <input type="number" class="form-control" name="seating_capacity" value="<?php echo htmlspecialchars($vehicle['seating_capacity']); ?>" required>
            </div>
            <div class="form-group col-md-4">
                <label for="engine">Engine:</label>
                <input type="text" class="form-control" name="engine" value="<?php echo htmlspecialchars($vehicle['engine']); ?>" required>
            </div>
            <div class="form-group col-md-4">
                <label for="transmission">Transmission:</label>
                <select class="form-control" name="transmission" required>
                    <option value="Manual" <?php echo ($vehicle['transmission'] == 'Manual') ? 'selected' : ''; ?>>Manual</option>
                    <option value="Automatic" <?php echo ($vehicle['transmission'] == 'Automatic') ? 'selected' : ''; ?>>Automatic</option>
                </select>
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-4">
                <label for="power">Power (HP):</label>
                <input type="number" class="form-control" name="power" value="<?php echo htmlspecialchars($vehicle['power']); ?>" required>
            </div>
            <div class="form-group col-md-4">
                <label for="torque">Torque (Nm):</label>
                <input type="number" class="form-control" name="torque" value="<?php echo htmlspecialchars($vehicle['torque']); ?>" required>
            </div>
            <div class="form-group col-md-4">
                <label for="fuel_tank_capacity">Fuel Tank Capacity (Liters):</label>
                <input type="number" class="form-control" name="fuel_tank_capacity" value="<?php echo htmlspecialchars($vehicle['fuel_tank_capacity']); ?>" required>
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-4">
                <label for="mileage">Mileage (kmpl):</label>
                <input type="text" class="form-control" name="mileage" value="<?php echo htmlspecialchars($vehicle['mileage']); ?>" required>
            </div>
            <div class="form-group col-md-4">
                <label for="model_year">Model Year:</label>
                <input type="number" class="form-control" name="model_year" value="<?php echo htmlspecialchars($vehicle['model_year']); ?>" required>
            </div>
            <div class="form-group col-md-4">
    <label for="photos">Current Photos:</label>
    <?php
    // Check if there are any photos
    if (!empty($vehicle['photos'])) {
        // Assuming 'photos' column contains a comma-separated list of image file names
        $photos = explode(',', $vehicle['photos']); // Split the image filenames
        foreach ($photos as $photo) {
            // Check if the file exists before displaying
            $image_path = 'vehicles/' . htmlspecialchars($photo);
            if (file_exists($image_path)) {
                // Display the image
                echo '<img src="' . $image_path . '" alt="Vehicle Image" style="width: 150px; height: auto; margin-right: 10px;">';
            } else {
                // Display a placeholder or message if the image file does not exist
                echo '<p>Image not found: ' . htmlspecialchars($photo) . '</p>';
            }
               }
            } else {
               echo '<p>No images uploaded for this vehicle.</p>';
            }
            ?>
         </div>

         <div class="form-group col-md-4">
            <label for="photos">Upload New Photos:</label>
            <input type="file" class="form-control-file" name="photos[]" multiple>
            <small>Leave blank if no change to photos.</small>
         </div>

<div class="buttons">
        <button type="submit" name="update_vehicle" class="btn btn-success">Update Vehicle</button>
        <a href="manage-vehicles.php" class="btn btn-primary">Cancel</a></div>
    </form>
</div>

    </div>
</body>
</html>

<?php
mysqli_close($conn);
?>
