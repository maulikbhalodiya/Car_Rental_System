<?php
session_start(); // Start the session to check if the user is logged in

// Check if the user is logged in
$isLoggedIn = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;

// Database connection
require('db_connection.php');

// Fetch brands for the dropdown
$brandSql = "SELECT * FROM brands";
$brandResult = $conn->query($brandSql);

// Base query to fetch vehicles with average rating
$sql = "SELECT v.*, b.brand_name, 
        (SELECT AVG(rating) FROM vehicle_ratings WHERE vehicle_id = v.id) AS avg_rating 
        FROM vehicles v 
        LEFT JOIN brands b ON v.brand_id = b.id 
        WHERE v.availability = 1";

// Apply filters
$filters = [];

// Brand filter
if (!empty($_GET['brand'])) {
    $brand = intval($_GET['brand']);
    $filters[] = "v.brand_id = '$brand'";
}

// Fuel type filter
if (!empty($_GET['fuel_type'])) {
    $fuel_type = $conn->real_escape_string($_GET['fuel_type']);
    $filters[] = "v.fuel_type = '$fuel_type'";
}

// Name filter
if (!empty($_GET['car_name'])) {
    $name = $conn->real_escape_string($_GET['car_name']);
    $filters[] = "v.vehicle_name LIKE '%$name%'";
}

// Year filter
if (!empty($_GET['year'])) {
    $year = intval($_GET['year']);
    $filters[] = "v.model_year = '$year'";
}

// Price range filter
if (!empty($_GET['min_price'])) {
    $min_price = intval($_GET['min_price']);
    $filters[] = "v.rent_per_hour >= '$min_price'";
}

if (!empty($_GET['max_price'])) {
    $max_price = intval($_GET['max_price']);
    $filters[] = "v.rent_per_hour <= '$max_price'";
}

// Add filters to SQL query if any
if (!empty($filters)) {
    $sql .= " AND " . implode(" AND ", $filters);
}

// Pagination settings
$recordsPerPage = 4;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $recordsPerPage;

// Update the base SQL query for pagination
$sql .= " LIMIT $offset, $recordsPerPage";

// Execute the query
$result = $conn->query($sql);

// Get total records for pagination
$totalRecordsSql = "SELECT COUNT(*) as total FROM vehicles WHERE availability = 1";
$totalRecordsResult = $conn->query($totalRecordsSql);
$totalRecords = $totalRecordsResult->fetch_assoc()['total'];
$totalPages = ceil($totalRecords / $recordsPerPage);

// Fetch recently listed cars (limit to 2 for this example)
$recentSql = "SELECT v.*, b.brand_name FROM vehicles v LEFT JOIN brands b ON v.brand_id = b.id WHERE v.availability = 1 ORDER BY v.id DESC LIMIT 2";
$recentResult = $conn->query($recentSql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Listing</title>
    <link rel="stylesheet" href="home.css"> <!-- For header and footer-->
    <link rel="stylesheet" href="car-listing.css"> <!-- for car listing  -->
    <!-- Include Bootstrap -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- font awesome link for icons used -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <?php include('header.php');?>
    <header class="header">
        <h1>Car Listing</h1>
        <p>Home > Car Listing</p>
    </header>

    <div class="container">
        <!-- Search Section -->
        <div class="search-section">
            <h2>Find Your Car</h2>
            <form class="search-form" method="GET" action="">
                <select name="brand">
                    <option value="">Select Brand</option>
                    <?php
                    if ($brandResult->num_rows > 0) {
                        while ($brandRow = $brandResult->fetch_assoc()) {
                            echo '<option value="' . htmlspecialchars($brandRow['id']) . '">' . htmlspecialchars($brandRow['brand_name']) . '</option>';
                        }
                    }
                    ?>
                </select>
                <select name="fuel_type">
                    <option value="">Select Fuel Type</option>
                    <option value="Petrol">Petrol</option>
                    <option value="Diesel">Diesel</option>
                    <option value="CNG">CNG</option>
                </select>
                <input type="text" name="car_name" placeholder="Car Name" />
                <input type="number" name="year" placeholder="Year" />
                <input type="number" name="min_price" placeholder="Min Price" />
                <input type="number" name="max_price" placeholder="Max Price" />
                <button type="submit">Search Car</button>
                <button type="reset" onclick="location.href='car-listing.php'">Clear All Filters</button>
            </form>

            <div class="recently-listed">
                <h3>Recently Listed Cars</h3>
                <?php
                if ($recentResult->num_rows > 0) {
                    while ($recentRow = $recentResult->fetch_assoc()) {
                        $recentPhotoArray = explode(',', $recentRow['photos']);
                        $recentFirstPhoto = trim($recentPhotoArray[0]);
                ?>
                    <div class="recent-car">
                        <img src="images/vehicles/<?php echo htmlspecialchars($recentFirstPhoto); ?>" alt="<?php echo htmlspecialchars($recentRow['vehicle_name']); ?>">
                        <div class="recent-car-info">
                            <h4 class="recent-car-name"><?php echo htmlspecialchars($recentRow['vehicle_name']); ?></h4>
                            <p class="recent-car-price">₹<?php echo htmlspecialchars($recentRow['rent_per_hour']); ?> Per Hour</p>
                        </div>
                    </div>
                <?php
                    }
                } else {
                    echo "<p>No recent listings available.</p>";
                }
                ?>
            </div>
        </div>

        <!-- Car Listings Section -->
        <div class="car-listings">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $photoArray = explode(',', $row['photos']);
                    $firstPhoto = trim($photoArray[0]);
            ?>
                <div class="car-item">
                    <div class="car-image">
                        <img src="images/vehicles/<?php echo htmlspecialchars($firstPhoto); ?>" alt="<?php echo htmlspecialchars($row['vehicle_name']); ?>">
                    </div>
                    <div class="car-details">
                        <h3 class="car-name"><?php echo htmlspecialchars($row['vehicle_name']); ?> (<?php echo htmlspecialchars($row['model_year']); ?>)</h3>
                        <div class="car-info">
                            <span>₹<?php echo htmlspecialchars($row['rent_per_hour']); ?> Per Hour</span>
                            <span><?php echo htmlspecialchars($row['seating_capacity']); ?> Seat</span>
                            <span><?php echo htmlspecialchars($row['model_year']); ?> Model</span>
                            <span><?php echo htmlspecialchars($row['fuel_type']); ?></span>
                        </div>
                        <div class="car-rating">
                            <span>Rating: <?php echo round($row['avg_rating'], 1) ?: 'No ratings yet'; ?>/5</span>
                        </div>
                        <a href="set_car_session.php?car_id=<?php echo htmlspecialchars($row['id']); ?>" class="view-details">View Details</a>
                    </div>
                </div>
            <?php
                }
            } else {
                echo "<p>No cars available at the moment.</p>";
            }
            ?>
        </div>
    </div>

    <!-- Pagination -->
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?php echo $page - 1; ?>" class="prev">Previous</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?php echo $i; ?>" class="<?php echo ($i == $page) ? 'active' : ''; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
            <a href="?page=<?php echo $page + 1; ?>" class="next">Next</a>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <?php include('footer.php');?>

</body>
</html>