<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require('db_connection.php');

$customer_id = $_SESSION['user_id'];

$query = "SELECT fullname, email, phone, address, city, zip_code, profile_pic FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $zip_code = $_POST['zip_code'];

    // Profile picture upload
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "uploads/profile/";
        $target_file = $target_dir . basename($_FILES["profile_pic"]["name"]);
        move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file);
        
        // Update with profile picture
        $updateQuery = "UPDATE users SET fullname = ?, email = ?, phone = ?, address = ?, city = ?, zip_code = ?, profile_pic = ? WHERE id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("sssssssi", $fullname, $email, $phone, $address, $city, $zip_code, $target_file, $customer_id);
    } else {
        // Update without profile picture
        $updateQuery = "UPDATE users SET fullname = ?, email = ?, phone = ?, address = ?, city = ?, zip_code = ? WHERE id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("ssssssi", $fullname, $email, $phone, $address, $city, $zip_code, $customer_id);
    }

    if ($stmt->execute()) {
        echo "<p class='message success'>Profile updated successfully.</p>";
    } else {
        echo "<p class='message error'>Error updating profile: " . $conn->error . "</p>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
         <!-- Include Bootstrap -->
         <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Font Awesome link for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="edit-profile.css">
    <title>Edit Profile</title>
</head>
<body>
    <header>
        <h2>Edit Profile</h2>
    </header>
    <div class="container">
        <div class="profile-picture">
            <img src="<?php echo htmlspecialchars($user['profile_pic']); ?>" alt="Profile Picture" />
        </div>
        <form action="edit-profile.php" method="POST" enctype="multipart/form-data">
            <label for="fullname">Full Name:</label>
            <input type="text" id="fullname" name="fullname" value="<?php echo htmlspecialchars($user['fullname']); ?>" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly required>

            <label for="phone">Phone:</label>
            <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>

            <label for="address">Address:</label>
            <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($user['address']); ?>" required>

            <label for="city">City:</label>
            <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($user['city']); ?>" required>

            <label for="zip_code">Zip Code:</label>
            <input type="text" id="zip_code" name="zip_code" value="<?php echo htmlspecialchars($user['zip_code']); ?>" required>

            <label for="profile_pic">Profile Picture:</label>
            <input type="file" id="profile_pic" name="profile_pic" accept="image/*">

            <div class="button-container">
                <button class="btn btn-success" type="submit">Save Changes</button>
                <a class="btn btn-primary" href="user-dashboard.php">Cancel</a>
            </div>
        </form>

    </div>
</body>
</html>
