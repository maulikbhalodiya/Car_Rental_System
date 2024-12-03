<?php
$servername = 'localhost';
$username = 'root';
$psw = '';
$dbname = 'car_rental_db';

$conn = mysqli_connect($servername, $username, $psw, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Delete selected vehicles
if (isset($_POST['delete_selected'])) {
    $ids = $_POST['vehicle_ids'];
    if (!empty($ids)) {
        $ids = array_map('intval', $ids); // Sanitize input
        $id_string = implode(',', $ids);
        $sql = "DELETE FROM vehicles WHERE id IN ($id_string)";
        
        if (mysqli_query($conn, $sql)) {
            echo "<script>alert('Vehicles deleted successfully');</script>";
        } else {
            echo "<script>alert('Error deleting vehicles: " . mysqli_error($conn) . "');</script>";
        }
    }
}

// Pagination setup
$limit = 4; // Number of records per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Search and filter
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$brand_filter = isset($_GET['brand_filter']) ? mysqli_real_escape_string($conn, $_GET['brand_filter']) : '';
$fuel_filter = isset($_GET['fuel_filter']) ? mysqli_real_escape_string($conn, $_GET['fuel_filter']) : '';
$model_year_filter = isset($_GET['model_year_filter']) ? mysqli_real_escape_string($conn, $_GET['model_year_filter']) : '';
$rent_min = isset($_GET['rent_min']) ? (int)$_GET['rent_min'] : 0;
$rent_max = isset($_GET['rent_max']) ? (int)$_GET['rent_max'] : 999999;

$sql = "SELECT v.*, b.brand_name FROM vehicles v 
        JOIN brands b ON v.brand_id = b.id 
        WHERE v.status = 1 ";

if (!empty($search)) {
    $sql .= "AND (v.vehicle_name LIKE '%$search%' OR b.brand_name LIKE '%$search%' OR v.fuel_type LIKE '%$search%') ";
}

if (!empty($brand_filter)) {
    $sql .= "AND v.brand_id = '$brand_filter' ";
}

if (!empty($fuel_filter)) {
    $sql .= "AND v.fuel_type = '$fuel_filter' ";
}

if (!empty($model_year_filter)) {
    $sql .= "AND v.model_year = '$model_year_filter' ";
}

if ($rent_min > 0 || $rent_max < 999999) {
    $sql .= "AND v.price_per_day BETWEEN $rent_min AND $rent_max ";
}

$sql .= "ORDER BY v.id LIMIT $start, $limit";
$result = mysqli_query($conn, $sql);

// Fetch total number of records for pagination
$total_sql = "SELECT COUNT(*) as total FROM vehicles WHERE status = 1 ";
if (!empty($search)) {
    $total_sql .= "AND (vehicle_name LIKE '%$search%' OR fuel_type LIKE '%$search%') ";
}
if (!empty($brand_filter)) {
    $total_sql .= "AND brand_id = '$brand_filter' ";
}
if (!empty($fuel_filter)) {
    $total_sql .= "AND fuel_type = '$fuel_filter' ";
}
if (!empty($model_year_filter)) {
    $total_sql .= "AND model_year = '$model_year_filter' ";
}
if ($rent_min > 0 || $rent_max < 999999) {
    $total_sql .= "AND price_per_day BETWEEN $rent_min AND $rent_max ";
}

$total_result = mysqli_query($conn, $total_sql);
$total_rows = mysqli_fetch_assoc($total_result)['total'];
$total_pages = ceil($total_rows / $limit);

// Fetch brands for dropdown
$brands_sql = "SELECT * FROM brands ORDER BY brand_name";
$brands_result = mysqli_query($conn, $brands_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Rental System | Manage Vehicles</title>
    <link rel="stylesheet" href="admin-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script>
        function updateCounter() {
            const checkboxes = document.querySelectorAll('input[name="vehicle_ids[]"]:checked');
            const selectedCount = checkboxes.length;
            document.getElementById('selected-count').innerText = selectedCount;
            document.getElementById('delete-selected-btn').style.display = selectedCount > 0 ? 'inline' : 'none';
        }
    </script>
</head>
<body>
     <!-- For navbar and sidebar -->
    <?php require('navbar.php');?>

        <div class="manage-vehicles">
            <a href="add-vehicle.php" class="btn btn-primary" name="add_vehicle">Add New Vehicle</a>
            <div class="search-filter" style="margin-top: 20px;">
                <form action="manage-vehicles.php" method="GET">
                    <input type="text" name="search" placeholder="Search vehicles..." value="<?php echo isset($search) ? htmlspecialchars($search) : ''; ?>">
                    
                    <select name="brand_filter">
                        <option value="">All Brands</option>
                        <?php
                        mysqli_data_seek($brands_result, 0);
                        while ($brand = mysqli_fetch_assoc($brands_result)) {
                            $selected = (isset($brand_filter) && $brand_filter == $brand['id']) ? 'selected' : '';
                            echo "<option value='" . $brand['id'] . "' $selected>" . htmlspecialchars($brand['brand_name']) . "</option>";
                        }
                        ?>
                    </select>

                    <select name="fuel_filter">
                        <option value="">All Fuel Types</option>
                        <option value="Petrol" <?php echo (isset($fuel_filter) && $fuel_filter == 'Petrol') ? 'selected' : ''; ?>>Petrol</option>
                        <option value="Diesel" <?php echo (isset($fuel_filter) && $fuel_filter == 'Diesel') ? 'selected' : ''; ?>>Diesel</option>
                        <option value="CNG" <?php echo (isset($fuel_filter) && $fuel_filter == 'CNG') ? 'selected' : ''; ?>>CNG</option>
                        <option value="Electric" <?php echo (isset($fuel_filter) && $fuel_filter == 'Electric') ? 'selected' : ''; ?>>Electric</option>
                    </select>

                    <select name="model_year_filter">
                        <option value="">All Model Years</option>
                        <?php
                        for ($year = date('Y'); $year >= 2015; $year--) {
                            $selected = (isset($model_year_filter) && $model_year_filter == $year) ? 'selected' : '';
                            echo "<option value='$year' $selected>$year</option>";
                        }
                        ?>
                    </select>

                    <div>
                        <input type="number" name="rent_min" placeholder="Min Rent (₹)" value="<?php echo isset($rent_min) ? htmlspecialchars($rent_min) : ''; ?>">
                        <input type="number" name="rent_max" placeholder="Max Rent (₹)" value="<?php echo isset($rent_max) ? htmlspecialchars($rent_max) : ''; ?>">
                    </div>

                    <button type="submit" class="btn btn-info">Search</button>
                </form>
            </div>
            <div>
            <h3>Approved Vehicles </h3> Items selected(<span id="selected-count">0</span>) 
                        <button type="submit" name="delete_selected" id="delete-selected-btn" class="btn btn-danger btn-sm" style="display:none;" onclick="return confirm('Are you sure you want to delete selected vehicles?')">Delete Selected</button>
                    </div>
            <div class="table-container">
                <form action="manage-vehicles.php" method="POST"> <!-- Wrap in form -->
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="select-all" onclick="toggle(this)"></th>
                                <th>#</th>
                                <th>Name</th>
                                <th>Brand</th>
                                <th>Price per Day</th>
                                <th>Rent Per Hour</th>
                                <th>Fuel Type</th>
                                <th>Model Year</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (mysqli_num_rows($result) > 0) {
                                $count = $start + 1; // Adjust count for pagination
                                while ($row = mysqli_fetch_assoc($result)) {
                                    echo "<tr>";
                                    echo "<td><input type='checkbox' name='vehicle_ids[]' value='" . $row['id'] . "' onclick='updateCounter()'></td>";
                                    echo "<td>" . $count . "</td>";
                                    echo "<td>" . htmlspecialchars($row['vehicle_name']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['brand_name']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['price_per_day']) . "₹</td>";
                                    echo "<td>" . htmlspecialchars($row['rent_per_hour']) . "₹</td>";
                                    echo "<td>" . htmlspecialchars($row['fuel_type']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['model_year']) . "</td>";
                                    echo "<td>
                                            <a href='view-vehicle.php?id=" . $row['id'] . "' class='btn btn-info btn-sm mr-1' title='View Details'><i class='fas fa-eye'></i></a>
                                            <a href='edit-vehicle.php?id=" . $row['id'] . "' class='edit-btn' title='Edit'><i class='fas fa-edit'></i></a>
                                            <button type='submit' name='delete_single' value='" . $row['id'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to delete this vehicle?\")'><i class='fas fa-trash-alt'></i></button>
                                        </td>";
                                    echo "</tr>";
                                    $count++;
                                }
                            } else {
                                echo "<tr><td colspan='9'>No approved vehicles found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </form> <!-- Close the form here -->
            </div>
            <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="manage-vehicles.php?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($brand_filter) ? '&brand_filter=' . $brand_filter : ''; ?><?php echo !empty($fuel_filter) ? '&fuel_filter=' . urlencode($fuel_filter) : ''; ?><?php echo !empty($model_year_filter) ? '&model_year_filter=' . $model_year_filter : ''; ?><?php echo $rent_min > 0 ? '&rent_min=' . $rent_min : ''; ?><?php echo $rent_max < 999999 ? '&rent_max=' . $rent_max : ''; ?>" class="prev-btn">Previous</a>
                    <?php endif; ?>
                    <span class="current-page">Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
                    <?php if ($page < $total_pages): ?>
                        <a href="manage-vehicles.php?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($brand_filter) ? '&brand_filter=' . $brand_filter : ''; ?><?php echo !empty($fuel_filter) ? '&fuel_filter=' . urlencode($fuel_filter) : ''; ?><?php echo !empty($model_year_filter) ? '&model_year_filter=' . $model_year_filter : ''; ?><?php echo $rent_min > 0 ? '&rent_min=' . $rent_min : ''; ?><?php echo $rent_max < 999999 ? '&rent_max=' . $rent_max : ''; ?>" class="next-btn">Next</a>
                    <?php endif; ?>
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

        function toggle(source) {
            const checkboxes = document.querySelectorAll('input[name="vehicle_ids[]"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = source.checked;
            });
            updateCounter();
        }
    </script>
</body>
</html>

<?php mysqli_close($conn); ?>
