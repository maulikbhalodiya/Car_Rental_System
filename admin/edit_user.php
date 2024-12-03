<?php
session_start();

// Redirect to login page if the admin is not logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: adminlogin.php");
    exit();
}
//databse connection
include('db_connection.php');

// Fetch user details
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("User not found.");
}

// Update user details
if (isset($_POST['update_user'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $zip_code = mysqli_real_escape_string($conn, $_POST['zip_code']);

    $update_sql = "UPDATE users SET fullname = ?, email = ?, phone = ?, address = ?, city = ?, zip_code = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ssssssi", $name, $email, $phone, $address, $city, $zip_code, $user_id);

    if ($update_stmt->execute()) {
        echo "<script>alert('User updated successfully');</script>";
        header("Location: registered_users.php?msg=User+updated+successfully");
        exit();
    } else {
        echo "Error updating user: " . $conn->error;
    }

    $update_stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User | Car Rental System</title>
    <link rel="stylesheet" href="admin-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
    .edit-user-form {
        background: #fff;
        padding: 30px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        margin-top: 20px;
        max-width: 600px;
    }

    .edit-user-form h3 {
        margin-bottom: 20px;
        font-size: 22px;
    }

    .edit-user-form input[type="text"],
    .edit-user-form input[type="email"] {
        width: 100%;
        padding: 10px;
        margin-bottom: 20px;
        border: 1px solid #ccc;
        border-radius: 5px;
        font-size: 16px;
    }

    .edit-user-form button {
        background: #333;
        color: #fff;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 16px;
    }

    .edit-user-form button:hover {
        background: #555;
    }
    </style>
</head>
<body>
<?php require('navbar.php');?>
        <div class="edit-user-form">
            <h3>Update User Details</h3>
            <form action="edit_user.php?id=<?php echo $user_id; ?>" method="POST">
                <input type="text" name="name" placeholder="Full Name" value="<?php echo htmlspecialchars($user['fullname']); ?>" required>
                <input type="email" name="email" placeholder="Email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                <input type="text" name="phone" placeholder="Phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                <input type="text" name="address" placeholder="Address" value="<?php echo htmlspecialchars($user['address']); ?>" required>
                <input type="text" name="city" placeholder="City" value="<?php echo htmlspecialchars($user['city']); ?>" required>
                <input type="text" name="zip_code" placeholder="Zip Code" value="<?php echo htmlspecialchars($user['zip_code']); ?>" required>
                <button type="submit" name="update_user">Update User</button>
                <a class="btn btn-primary" type="button" href="registered_users.php">Cancel</a>
            </form>
        </div>
    </div>
</body>
</html>
