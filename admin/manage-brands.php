<?php
$servername = 'localhost';
$username = 'root';
$psw = '';
$dbname = 'car_rental_db';

$conn = mysqli_connect($servername, $username, $psw, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Pagination settings
$limit = 4; // Number of records per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Add new brand
if (isset($_POST['add_brand'])) {
    $brand_name = mysqli_real_escape_string($conn, $_POST['brand_name']);
    $current_date = date('Y-m-d');
    
    $sql = "INSERT INTO brands (brand_name, adding_date, updation_date) VALUES ('$brand_name', '$current_date', '$current_date')";
    
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Brand added successfully');</script>";
    } else {
        echo "<script>alert('Error adding brand: " . mysqli_error($conn) . "');</script>";
    }
}

// Delete brand
if (isset($_GET['delete'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete']);
    
    $sql = "DELETE FROM brands WHERE id = '$id'";
    
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Brand deleted successfully');</script>";
    } else {
        echo "<script>alert('Error deleting brand: " . mysqli_error($conn) . "');</script>";
    }
}

// Search functionality
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// Fetch brands with pagination and search
$sql = "SELECT * FROM brands WHERE brand_name LIKE '%$search%' ORDER BY id LIMIT $start, $limit";
$result = mysqli_query($conn, $sql);

// Fetch total records for pagination
$total_sql = "SELECT COUNT(*) as total FROM brands WHERE brand_name LIKE '%$search%'";
$total_result = mysqli_query($conn, $total_sql);
$total_row = mysqli_fetch_assoc($total_result);
$total_records = $total_row['total'];
$total_pages = ceil($total_records / $limit);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Rental System | Manage Brands</title>
    <link rel="stylesheet" href="admin-style.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <!-- For navbar and sidebar -->
  <?php require('navbar.php');?>
    
        <div class="manage-brands">
            <h3>Add New Brand</h3>
            <form action="manage-brands.php" method="POST">
                <input type="text" name="brand_name" placeholder="Brand Name" required>
                <button type="submit" name="add_brand">Add Brand</button>
            </form>

            <h3>Listed Brands</h3>
            <div class="search-filter">
                <form action="manage-brands.php" method="GET">
                    <input type="text" name="search" placeholder="Search brands..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit">Search</button>
                </form>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Brand Name</th>
                            <th>Adding Date</th>
                            <th>Updation Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (mysqli_num_rows($result) > 0) {
                            $count = $start + 1; // Start counting from the current page
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<tr>";
                                echo "<td>" . $count . "</td>";
                                echo "<td>" . $row['brand_name'] . "</td>";
                                echo "<td>" . $row['adding_date'] . "</td>";
                                echo "<td>" . $row['updation_date'] . "</td>";
                                echo "<td>
                                        <a href='edit-brand.php?id=" . $row['id'] . "' class='edit-btn'><i class='fas fa-edit'></i></a>
                                        <a href='manage-brands.php?delete=" . $row['id'] . "' class='delete-btn' onclick='return confirm(\"Are you sure you want to delete this brand?\")'><i class='fas fa-trash-alt'></i></a>
                                      </td>";
                                echo "</tr>";
                                $count++;
                            }
                        } else {
                            echo "<tr><td colspan='5'>No brands found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <div class="pagination">
                <a href="?search=<?php echo urlencode($search); ?>&page=<?php echo ($page > 1) ? $page - 1 : 1; ?>" class="prev-btn">Previous</a>
                <span class="current-page">Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
                <a href="?search=<?php echo urlencode($search); ?>&page=<?php echo ($page < $total_pages) ? $page + 1 : $total_pages; ?>" class="prev-btn">Next</a>
            </div>
        </div>
    </div>
    <script>
    // Get the current page URL
    const currentLocation = window.location.pathname.split('/').pop();
    const navLinks = document.querySelectorAll('.sidebar a'); // Select the correct links

    navLinks.forEach(link => {
        if (link.getAttribute('href') === currentLocation) {
            link.classList.add('active'); // Add 'active' class if it matches
        }
    });
</script>
</body>
</html>

<?php
mysqli_close($conn);
?>
