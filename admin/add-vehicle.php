<?php
$servername = 'localhost';
$username = 'root';
$psw = '';
$dbname = 'car_rental_db';

$conn = mysqli_connect($servername, $username, $psw, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Add new vehicle
if (isset($_POST['add_vehicle'])) {
    $vehicle_name = mysqli_real_escape_string($conn, $_POST['vehicle_name']);
    $brand_id = mysqli_real_escape_string($conn, $_POST['brand_id']);
    $car_type = mysqli_real_escape_string($conn, $_POST['car_type']);
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
    
    // For handling multiple photo uploads
    $photos = implode(',', $_FILES['photos']['name']);
    
    $sql = "INSERT INTO vehicles (vehicle_name, brand_id, car_type, price_per_day, rent_per_hour, fuel_type, seating_capacity, engine, transmission, power, torque, fuel_tank_capacity, mileage, photos, model_year) 
            VALUES ('$vehicle_name', '$brand_id', '$car_type', '$price_per_day', '$rent_per_hour', '$fuel_type', '$seating_capacity', '$engine', '$transmission', '$power', '$torque', '$fuel_tank_capacity', '$mileage', '$photos', $model_year)";
    
    if (mysqli_query($conn, $sql)) {
        // Save photos to the server
        foreach ($_FILES['photos']['tmp_name'] as $key => $tmp_name) {
            $photo_name = $_FILES['photos']['name'][$key];
            $photo_tmp = $_FILES['photos']['tmp_name'][$key];
            move_uploaded_file($photo_tmp, "C:/xampp/htdocs/Car_Rental/images/vehicles/" . $photo_name);
        }
        
        echo "<script>alert('Vehicle added successfully');</script>";
    } else {
        echo "<script>alert('Error adding vehicle: " . mysqli_error($conn) . "');</script>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <title>Add New Vehicle</title>
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 20px;
        }
        .form-group {
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <h3>Add New Vehicle</h3>
        <form action="add-vehicle.php" method="POST" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <input type="text" class="form-control" name="vehicle_name" placeholder="Vehicle Name" required>
                </div>
                <div class="form-group col-md-6">
                    <select name="brand_id" class="form-control" required>
                        <option value="">Select Brand</option>
                        <?php
                        $brands_sql = "SELECT * FROM brands ORDER BY brand_name";
                        $brands_result = mysqli_query($conn, $brands_sql);
                        while ($brand = mysqli_fetch_assoc($brands_result)) {
                            echo "<option value='" . $brand['id'] . "'>" . $brand['brand_name'] . "</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-4">
                    <select name="car_type" class="form-control" required>
                        <option value="">Select Car Type</option>
                        <option value="SUV">SUV</option>
                        <option value="Sedan">Sedan</option>
                        <option value="Hatchback">Hatchback</option>
                        <option value="Sports">Sports</option>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <input type="number" class="form-control" name="price_per_day" placeholder="Price per Day" step="0.01" min="0" required>
                </div>
                <div class="form-group col-md-4">
                    <input type="number" class="form-control" name="rent_per_hour" placeholder="Rent per Hour (₹)" min="0" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-4">
                    <select name="fuel_type" class="form-control" required>
                        <option value="">Select Fuel Type</option>
                        <option value="Petrol">Petrol</option>
                        <option value="Diesel">Diesel</option>
                        <option value="CNG">CNG</option>
                        <option value="Electric">Electric</option>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <input type="number" class="form-control" name="model_year" placeholder="Model Year" min="2015" max="<?php echo date('Y'); ?>" required>
                </div>
                <div class="form-group col-md-4">
                    <input type="number" class="form-control" name="seating_capacity" placeholder="Seating Capacity" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-4">
                    <input type="text" class="form-control" name="engine" placeholder="Engine (e.g., 2.0L)" required>
                </div>
                <div class="form-group col-md-4">
                    <select name="transmission" class="form-control" required>
                        <option value="">Select Transmission</option>
                        <option value="Manual">Manual</option>
                        <option value="Automatic">Automatic</option>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <input type="text" class="form-control" name="power" placeholder="Power (e.g., 140bhp)" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-4">
                    <input type="text" class="form-control" name="torque" placeholder="Torque (e.g., 200Nm)" required>
                </div>
                <div class="form-group col-md-4">
                    <input type="text" class="form-control" name="fuel_tank_capacity" placeholder="Fuel Tank Capacity (e.g., 50L)" required>
                </div>
                <div class="form-group col-md-4">
                    <input type="text" class="form-control" name="mileage" placeholder="Mileage (e.g., 12.5 Kmpl)" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-12">
                    <input type="file" class="form-control-file" name="photos[]" multiple required>
                </div>
            </div>
            <button type="submit" name="add_vehicle" class="btn btn-primary">Add Vehicle</button>
            <a href="manage-vehicles.php" class="btn btn-secondary">Back</a>
            <button type="reset" class="btn btn-warning">Clear Details</button>
        </form>
    </div>
</body>
</html>
