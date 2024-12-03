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

$errors = [];

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];  // Assuming the user is logged in

    // Fetch the user's email from the `users` table
    $sql = "SELECT email FROM users WHERE id = '$user_id'";
    $result = mysqli_query($conn, $sql);
    $user_email = '';
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $user_email = $row['email'];  // Store the user's email
    } else {
        // Handle the case where the email could not be found
        echo "Error fetching user email.";
        exit;
    }

    // Get form data
    $name = $_POST['name'];
    $address = $_POST['address'];
    $car_brand = $_POST['car_brand'];
    $car_model = $_POST['car_model'];
    $year = $_POST['year'];
    $kilometers_driven = $_POST['kilometers_driven'];
    $fuel_type = $_POST['fuel_type'];
    $photos = $_FILES['car_photos'];

    // Validate inputs
    if (empty($name)) {
        $errors['name'] = "Name is required.";
    }
    if (empty($address)) {
        $errors['address'] = "Address is required.";
    }
    if (empty($car_brand)) {
        $errors['car_brand'] = "Car brand is required.";
    }
    if (empty($car_model)) {
        $errors['car_model'] = "Car model is required.";
    }
    if (empty($year)) {
        $errors['year'] = "Year is required.";
    }
    if (empty($kilometers_driven)) {
        $errors['kilometers_driven'] = "Kilometers driven is required.";
    }
    if (empty($fuel_type)) {
        $errors['fuel_type'] = "Fuel type is required.";
    }
    if (empty($photos['name'][0])) {
        $errors['car_photos'] = "At least one car photo is required.";
    }

    // If no errors, insert the data into the database
    if (empty($errors)) {
        // Insert car rent request with the user's email
        $sql = "INSERT INTO car_rent_requests (user_id, email, name, address, car_brand, car_model, year, kilometers_driven, fuel_type) 
                VALUES ('$user_id', '$user_email', '$name', '$address', '$car_brand', '$car_model', '$year', '$kilometers_driven', '$fuel_type')";

        if (mysqli_query($conn, $sql)) {
            $request_id = mysqli_insert_id($conn);  // Get the last inserted request_id

            // Create uploads directory if it doesn't exist
            $upload_dir = "uploads/cars/";
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            // Handle multiple file uploads
            foreach ($_FILES['car_photos']['tmp_name'] as $key => $tmp_name) {
                $file_name = $_FILES['car_photos']['name'][$key];
                $file_tmp = $_FILES['car_photos']['tmp_name'][$key];
                $file_error = $_FILES['car_photos']['error'][$key];

                // Use the original file name
                $file_path = $upload_dir . basename($file_name);

                // Check for file upload errors
                if ($file_error === UPLOAD_ERR_OK) {
                    // Save the file to the server
                    if (move_uploaded_file($file_tmp, $file_path)) {
                        // Insert photo path into the database
                        $sql_photo = "INSERT INTO car_photos (request_id, photo_path) VALUES ('$request_id', '$file_path')";
                        mysqli_query($conn, $sql_photo);
                    } else {
                        $errors['car_photos'] = "Failed to upload photo: " . $file_name;
                    }
                } else {
                    $errors['car_photos'] = "Error uploading photo: " . $file_name;
                }
            }

            // If no errors in file upload
            if (empty($errors['car_photos'])) {
                echo "<script>alert('Car rental request submitted!');</script>";
            } else {
                echo "<script>alert('There were errors with some photos. Please try again.');</script>";
            }
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List Your Car for Rent</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Custom form styles */
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }

        .form-container {
            max-width: 700px;
            margin: 2rem auto;
            padding: 2rem;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .form-container h2 {
            text-align: center;
            color: #333;
            margin-bottom: 1.5rem;
            font-weight: bold;
        }

        .form-group label {
            font-weight: 500;
        }

        .form-control {
            border-radius: 6px;
        }

        .btn-primary {
            width: 100%;
            font-size: 1.1rem;
            font-weight: 600;
            padding: 10px;
            border-radius: 6px;
        }

        /* Error message styling */
        .error-message {
            color: red;
            font-size: 0.9rem;
            margin-top: -5px;
        }
    </style>
</head>
<body>
    <!-- Include Navigation Bar -->
    <?php include('header.php'); ?>

    <div class="container form-container">
        <h2>List Your Car for Rent</h2>
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="name">Name</label>
                    <input type="text" class="form-control" name="name" value="<?php echo isset($name) ? $name : ''; ?>" required>
                    <?php if (isset($errors['name'])): ?>
                        <span class="error-message"><?php echo $errors['name']; ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group col-md-6">
                    <label for="address">Address</label>
                    <textarea class="form-control" name="address" rows="2" required><?php echo isset($address) ? $address : ''; ?></textarea>
                    <?php if (isset($errors['address'])): ?>
                        <span class="error-message"><?php echo $errors['address']; ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="car_brand">Car Brand</label>
                    <input type="text" class="form-control" name="car_brand" value="<?php echo isset($car_brand) ? $car_brand : ''; ?>" required>
                    <?php if (isset($errors['car_brand'])): ?>
                        <span class="error-message"><?php echo $errors['car_brand']; ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group col-md-6">
                    <label for="car_model">Car Model</label>
                    <input type="text" class="form-control" name="car_model" value="<?php echo isset($car_model) ? $car_model : ''; ?>" required>
                    <?php if (isset($errors['car_model'])): ?>
                        <span class="error-message"><?php echo $errors['car_model']; ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="year">Year</label>
                    <input type="number" class="form-control" name="year" value="<?php echo isset($year) ? $year : ''; ?>" required>
                    <?php if (isset($errors['year'])): ?>
                        <span class="error-message"><?php echo $errors['year']; ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group col-md-6">
                    <label for="kilometers_driven">Kilometers Driven</label>
                    <input type="number" class="form-control" name="kilometers_driven" value="<?php echo isset($kilometers_driven) ? $kilometers_driven : ''; ?>" required>
                    <?php if (isset($errors['kilometers_driven'])): ?>
                        <span class="error-message"><?php echo $errors['kilometers_driven']; ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="fuel_type">Fuel Type</label>
                    <select class="form-control" name="fuel_type" required>
                        <option value="Petrol" <?php echo isset($fuel_type) && $fuel_type == 'Petrol' ? 'selected' : ''; ?>>Petrol</option>
                        <option value="Diesel" <?php echo isset($fuel_type) && $fuel_type == 'Diesel' ? 'selected' : ''; ?>>Diesel</option>
                        <option value="Electric" <?php echo isset($fuel_type) && $fuel_type == 'Electric' ? 'selected' : ''; ?>>Electric</option>
                    </select>
                    <?php if (isset($errors['fuel_type'])): ?>
                        <span class="error-message"><?php echo $errors['fuel_type']; ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group col-md-6">
                    <label for="car_photos">Upload Car Photos</label>
                    <input type="file" class="form-control-file" name="car_photos[]" multiple required>
                    <?php if (isset($errors['car_photos'])): ?>
                        <span class="error-message"><?php echo $errors['car_photos']; ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <button type="submit" class="btn btn-primary mt-4">Submit</button>
        </form>
    </div>

    <!-- Include Footer -->
    <?php include('footer.php'); ?>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.6.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
