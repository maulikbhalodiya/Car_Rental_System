<?php
session_start();  
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {     
    header("Location: adminlogin.php");     
    exit(); 
}

require('db_connection.php');

// Get the filters and pagination parameters from the request
$brand = isset($_GET['brand']) ? $_GET['brand'] : 'all';
$status = isset($_GET['status']) ? $_GET['status'] : 'all';
$start = isset($_GET['start']) ? $_GET['start'] : '';
$end = isset($_GET['end']) ? $_GET['end'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$rowsPerPage = 5;

$offset = ($page - 1) * $rowsPerPage;

// Build the query with conditions based on the filters
$query = "SELECT v.id, v.vehicle_name, b.brand_name, 
                 IF(br.start_date IS NOT NULL AND br.end_date IS NOT NULL, 'Booked', 'Available') AS status,
                 v.price_per_day AS rental_rate, br.start_date, br.end_date
          FROM vehicles AS v
          INNER JOIN brands AS b ON v.brand_id = b.id
          LEFT JOIN booking_req AS br ON v.id = br.vehicle_id
          WHERE 1";

// Apply filters
if ($brand != 'all') {     
    $query .= " AND v.brand_id = '$brand'"; 
}
if ($status != 'all') {     
   if ($status === 'booked') {
       // Only cars with current or future bookings
       $query .= " AND br.start_date IS NOT NULL AND br.end_date >= CURDATE()";
   } elseif ($status === 'available') {
      // Ensure only cars with no current bookings are selected
      $query .= " AND v.id NOT IN (
          SELECT vehicle_id FROM booking_req
          WHERE CURDATE() BETWEEN start_date AND end_date
      )";
  }
  
  
}
   
if ($start != '') {     
    $query .= " AND br.start_date >= '$start'"; 
}
if ($end != '') {     
    $query .= " AND br.end_date <= '$end'"; 
}
if ($search != '') {     
    $query .= " AND v.vehicle_name LIKE '%$search%'"; 
}

// Get the total count for pagination
$countQuery = "SELECT COUNT(*) AS total 
               FROM vehicles v
               INNER JOIN brands b ON v.brand_id = b.id
               LEFT JOIN booking_req br ON v.id = br.vehicle_id 
               WHERE 1";

if ($brand != 'all') {     
    $countQuery .= " AND v.brand_id = '$brand'"; 
}
if ($status != 'all') {     
    if ($status === 'booked') {
        $countQuery .= " AND br.start_date IS NOT NULL AND br.end_date >= CURDATE()";
    } else {
        $countQuery .= " AND (br.start_date IS NULL OR br.end_date < CURDATE())";
    }
}
if ($start != '') {     
    $countQuery .= " AND br.start_date >= '$start'"; 
}
if ($end != '') {     
    $countQuery .= " AND br.end_date <= '$end'"; 
}
if ($search != '') {     
    $countQuery .= " AND v.vehicle_name LIKE '%$search%'"; 
}

// Execute the count query
$countResult = $conn->query($countQuery);
$totalRows = $countResult->fetch_assoc()['total'];
$total_pages = ceil($totalRows / $rowsPerPage);

// Add the LIMIT and OFFSET for pagination
$query .= " LIMIT $offset, $rowsPerPage";

// Execute the data query
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Car Status</title>
    <link rel="stylesheet" href="admin-style.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background-color: #f4f4f9;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
            padding-top:15px;
            background-color: #fff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        .filter-container, .search-container {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 20px;
            align-items: center;
        }
        .table-container {
            overflow-x: auto;
            margin-top: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        .pagination-container {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
        .status-available {
            color: green;
            font-weight: bold;
        }
        .status-booked {
            color: red;
            font-weight: bold;
        }
        .filter-container .form-control {
            max-width: 200px;
        }
        .pagination{
            padding-top:0px;
        }
    </style>
</head>
<body>

<?php require('navbar.php'); ?>

<div class="container">
    <h2>Car Status</h2>

    <!-- Filters Section -->
    <div class="filter-container">
        <div>
            <label for="brandFilter">Brand:</label>
            <select id="brandFilter" class="form-control" onchange="applyFilters()">
                <option value="all">All Brands</option>
                <?php
                $brandQuery = $conn->query("SELECT DISTINCT b.id, b.brand_name FROM brands AS b INNER JOIN vehicles AS v ON v.brand_id = b.id");
                while ($brandRow = $brandQuery->fetch_assoc()) {
                    echo "<option value='{$brandRow['id']}' " . ($brand == $brandRow['id'] ? 'selected' : '') . ">{$brandRow['brand_name']}</option>";
                }
                ?>
            </select>
        </div>
        <div>
            <label for="statusFilter">Status:</label>
            <select id="statusFilter" class="form-control" onchange="applyFilters()">
                <option value="all" <?php echo $status == 'all' ? 'selected' : ''; ?>>All</option>
                <option value="booked" <?php echo $status == 'booked' ? 'selected' : ''; ?>>Booked</option>
                <option value="available" <?php echo $status == 'available' ? 'selected' : ''; ?>>Available</option>
            </select>
        </div>
        <div>
            <label for="timePeriodFilter">Period:</label>
            <div style="display: flex; gap: 10px;">
                <input type="date" id="startDate" class="form-control" value="<?php echo $start; ?>" onchange="applyFilters()">
                <input type="date" id="endDate" class="form-control" value="<?php echo $end; ?>" onchange="applyFilters()">
            </div>
        </div>
        <div>
            <label for="searchBox">Search:</label>
            <input type="text" id="searchBox" class="form-control" placeholder="Car name..." value="<?php echo $search; ?>" onchange="applyFilters()">
        </div>
    </div>

    <!-- Table Section -->
<div class="table-container">
    <table id="carStatusTable" class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Car Name</th>
                <th>Brand</th>
                <th>Status</th>
                <th>Rental Rate</th>
                <th>Booking Start</th>
                <th>Booking End</th>
                <th>Details</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                $counter = $offset + 1; // Start counter from the correct number based on page
                while ($row = $result->fetch_assoc()) {
                    // Convert to IST and format date
                    $startDate = new DateTime($row['start_date'], new DateTimeZone('UTC'));
                    $startDate->setTimezone(new DateTimeZone('Asia/Kolkata'));
                    $startDateFormatted = $startDate->format('d-m-Y');

                    $endDate = new DateTime($row['end_date'], new DateTimeZone('UTC'));
                    $endDate->setTimezone(new DateTimeZone('Asia/Kolkata'));
                    $endDateFormatted = $endDate->format('d-m-Y');

                    // Determine the status
                    $statusClass = ($row['status'] == 'Available') ? 'status-available' : 'status-booked';
                    $bookingId = $row['id']; // Assuming 'id' is the booking ID
                    
                    echo "<tr>
                            <td>{$counter}</td>
                            <td>{$row['vehicle_name']}</td>
                            <td>{$row['brand_name']}</td>
                            <td class='{$statusClass}'>{$row['status']}</td>
                            <td>{$row['rental_rate']}</td>
                            <td>{$startDateFormatted}</td>
                            <td>{$endDateFormatted}</td>
                            <td><a href='booking_details.php?booking_id={$bookingId}' class='btn btn-info'>Details</a></td>
                          </tr>";
                    $counter++;
                }
            } else {
                echo "<tr><td colspan='8'>No results found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<div class="pagination">
    <a href="?brand=<?php echo urlencode($brand); ?>&status=<?php echo urlencode($status); ?>&start=<?php echo urlencode($start); ?>&end=<?php echo urlencode($end); ?>&search=<?php echo urlencode($search); ?>&page=<?php echo ($page > 1) ? $page - 1 : 1; ?>" class="prev-btn">Previous</a>
    <span class="current-page">Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
    <a href="?brand=<?php echo urlencode($brand); ?>&status=<?php echo urlencode($status); ?>&start=<?php echo urlencode($start); ?>&end=<?php echo urlencode($end); ?>&search=<?php echo urlencode($search); ?>&page=<?php echo ($page < $total_pages) ? $page + 1 : $total_pages; ?>" class="prev-btn">Next</a>
</div>

</div>

<script>
// Function to apply filters and reload the page
function applyFilters() {
    var brand = document.getElementById('brandFilter').value;
    var status = document.getElementById('statusFilter').value;
    var start = document.getElementById('startDate').value;
    var end = document.getElementById('endDate').value;
    var search = document.getElementById('searchBox').value;
    window.location.href = `admin_car_status.php?page=1&brand=${brand}&status=${status}&start=${start}&end=${end}&search=${search}`;
}
</script>

</body>
</html>
