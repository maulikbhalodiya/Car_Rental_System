<?php
session_start();

// Redirect to login page if the admin is not logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: adminlogin.php");
    exit();
}

// Logout functionality
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: adminlogin.php");
    exit();
}

// CSRF Protection: Generate a token if not set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require('db_connection.php');

// Handle Delete Booking via POST
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_booking'])) {
    // Validate CSRF Token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token.");
    }

    // Get the booking ID from POST data
    $delete_booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;

    if ($delete_booking_id > 0) {
        // Start a transaction
        $conn->begin_transaction();

        try {
            // Fetch the vehicle_registration_id associated with the booking
            $fetchVehicleQuery = "SELECT vehicle_id FROM booking_req WHERE id = ?";
            if ($stmt_fetch = $conn->prepare($fetchVehicleQuery)) {
                $stmt_fetch->bind_param("i", $delete_booking_id);
                $stmt_fetch->execute();
                $result_fetch = $stmt_fetch->get_result();
                if ($vehicle = $result_fetch->fetch_assoc()) {
                    $vehicle_registration_id = $vehicle['vehicle_id'];
                } else {
                    throw new Exception("Booking not found.");
                }
                $stmt_fetch->close();
            } else {
                throw new Exception("Error preparing fetch vehicle statement.");
            }

            // Delete the booking
            $deleteQuery = "DELETE FROM booking_req WHERE id = ?";
            if ($stmt_delete = $conn->prepare($deleteQuery)) {
                $stmt_delete->bind_param("i", $delete_booking_id);
                if (!$stmt_delete->execute()) {
                    throw new Exception("Error deleting booking: " . $stmt_delete->error);
                }
                $stmt_delete->close();
            } else {
                throw new Exception("Error preparing delete statement.");
            }

        

            // Commit the transaction
            $conn->commit();

            echo "<script>alert('Booking deleted successfully and vehicle status updated to available.');</script>";
            echo "<script>window.location.href='manage_bookings.php';</script>";
            exit();
        } catch (Exception $e) {
            // Rollback the transaction on error
            $conn->rollback();
            echo "<script>alert('" . htmlspecialchars($e->getMessage()) . "');</script>";
        }
    } else {
        echo "<script>alert('Invalid booking ID for deletion.');</script>";
    }
}

// Filter Handling
$start_date_filter = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date_filter = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Pagination logic
$results_per_page = 5; // Number of bookings per page

// Get the current page from the URL (default is 1)
$current_page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($current_page - 1) * $results_per_page;

// Construct SQL WHERE conditions based on filters
$where_conditions = [];
if (!empty($start_date_filter)) {
    $where_conditions[] = "start_date >= '$start_date_filter'";
}
if (!empty($end_date_filter)) {
    $where_conditions[] = "end_date <= '$end_date_filter'";
}
if (!empty($status_filter)) {
    $where_conditions[] = "status = '$status_filter'";
}

// Combine the WHERE conditions
$where_clause = count($where_conditions) > 0 ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get the total number of bookings after applying filters
$totalBookingsQuery = "SELECT COUNT(id) AS total FROM booking_req $where_clause";
$result_total = $conn->query($totalBookingsQuery);
$total_row = $result_total->fetch_assoc();
$total_bookings = $total_row['total'];
$total_pages = ceil($total_bookings / $results_per_page);

// Fetch the bookings for the current page with filters applied
$bookingsQuery = "SELECT id, user_id, vehicle_id, start_date, end_date, hours_booked, days_booked, total_price, payment_way, status FROM booking_req $where_clause LIMIT ?, ?";
$stmt = $conn->prepare($bookingsQuery);
$stmt->bind_param("ii", $offset, $results_per_page);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    die("Error fetching bookings: " . htmlspecialchars($conn->error));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Rental System | Manage Bookings</title>
    <link rel="stylesheet" href="admin_style.css">
    <link rel="stylesheet" href="manage_bookings.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .booking-table table {
            width: 100%;
            border-collapse: collapse;
        }
        .booking-table th, .booking-table td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: center;
        }
        .booking-table th {
            background-color: #f2f2f2;
        }
        .pagination {
            margin-top: 20px;
            text-align: center;
        }
        .pagination a {
            margin: 0 5px;
            padding: 10px;
            text-decoration: none;
            background-color: #3498db;
            color: white;
            border-radius: 5px;
        }
        .pagination .current-page {
            margin: 0 5px;
            padding: 10px;
            text-decoration: none;
            border-radius: 5px;
            background-color: #1f242d;
            color: white;
}
        .pagination a.active {
            background-color: #3498db;
            color: white;
        }
        .filters {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .filters form {
            display: flex;
            gap: 15px;
        }
        .filters input, .filters select, .filters button {
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        .filters button {
            background-color: #4CAF50;
            color: white;
            cursor: pointer;
        }
        .filters button:hover {
            background-color: #45a049;
        }
        .filters .clear-button {
            background-color: #f44336;
        }
        .filters .clear-button:hover {
            background-color: #d32f2f;
        }
    </style>
</head>
<body>

   <?php require('navbar.php')?>

    <div class="filters">
        <!-- Filters Form -->
        <form method="GET" action="manage_bookings.php">
            

            <label for="start_date">Start Date:</label>
            <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($start_date_filter); ?>">

            <label for="end_date">End Date:</label>
            <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($end_date_filter); ?>">

            <select id="status" name="status">
                <option value="">All Statuses</option>
                <option value="Pending" <?php echo ($status_filter == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                <option value="Approved" <?php echo ($status_filter == 'Approved') ? 'selected' : ''; ?>>Approved</option>
                <option value="Rejected" <?php echo ($status_filter == 'Rejected') ? 'selected' : ''; ?>>Rejected</option>
            </select>

            <button type="submit">Apply Filters</button>
        </form>

        <!-- Clear Filters Button -->
        <form method="GET" action="manage_bookings.php" class="clear-filters-form">
            <button type="submit" name="clear_filters" class="clear-button">Clear Filters</button>
        </form>
    </div>

    <div class="booking-table">
        <h2>Manage Bookings</h2>
        <table>
            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>Customer ID</th>
                    <th>Vehicle Registration ID</th>
                    <th>Pickup Date</th>
                    <th>Return Date</th>
                    <th>Total Amount</th>
                    <th>Status</th>
                    <th>Payment Method</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $counter = ($current_page - 1) * $results_per_page + 1;
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        // Sanitize output
                        $booking_id = htmlspecialchars($row['id']);
                        $customer_id = htmlspecialchars($row['user_id']);
                        $vehicle_registration_id = htmlspecialchars($row['vehicle_id']);
                        $pickup_date = htmlspecialchars($row['start_date']);
                        $return_date = htmlspecialchars($row['end_date']);
                        $total_amount = htmlspecialchars($row['total_price']);
                        $status = htmlspecialchars($row['status']);
                        $payment_method = htmlspecialchars($row['payment_way']);
                ?>
                    <tr>
                        <td><?php echo $counter++; ?></td>
                        <td><?php echo $customer_id; ?></td>
                        <td><?php echo $vehicle_registration_id; ?></td>
                        <td><?php echo $pickup_date; ?></td>
                        <td><?php echo $return_date; ?></td>
                        <td><?php echo $total_amount; ?></td>
                        <td><?php echo $status; ?></td>
                        <td><?php echo $payment_method; ?></td>
                        <td>
                            <form method="POST" action="manage_bookings.php" onsubmit="return confirm('Are you sure you want to delete this booking?');">
                                <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                <button type="submit" name="delete_booking">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php
                    }
                } else {
                    echo "<tr><td colspan='9'>No bookings found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <div class="pagination">
        <?php if ($current_page > 1): ?>
            <a href="manage_bookings.php?page=<?php echo $current_page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($start_date_filter) ? '&start_date=' . urlencode($start_date_filter) : ''; ?><?php echo !empty($end_date_filter) ? '&end_date=' . urlencode($end_date_filter) : ''; ?><?php echo !empty($status_filter) ? '&status=' . urlencode($status_filter) : ''; ?>" class="prev-btn">Previous</a>
        <?php endif; ?>

        <span class="current-page">Page <?php echo $current_page; ?> of <?php echo $total_pages; ?></span>

        <?php if ($current_page < $total_pages): ?>
            <a href="manage_bookings.php?page=<?php echo $current_page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($start_date_filter) ? '&start_date=' . urlencode($start_date_filter) : ''; ?><?php echo !empty($end_date_filter) ? '&end_date=' . urlencode($end_date_filter) : ''; ?><?php echo !empty($status_filter) ? '&status=' . urlencode($status_filter) : ''; ?>" class="next-btn">Next</a>
        <?php endif; ?>
    </div>

</body>
</html>