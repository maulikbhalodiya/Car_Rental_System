<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: adminlogin.php");
    exit();
}

require 'db_connection.php';

// Determine whether to view pending requests or all requests
$view = isset($_GET['view']) && $_GET['view'] == 'history' ? 'history' : 'pending';

// Handle search query if provided
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// Pagination settings
$limit = 4; // Number of records per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Fetch car rental requests based on the selected view and search query
if ($view === 'history') {
            $sql = "SELECT * FROM car_rent_requests WHERE 
            status != 'Pending' AND (
            name LIKE '%$search%' OR 
            email LIKE '%$search%' OR
            fuel_type LIKE '%$search%' OR
            car_brand LIKE '%$search%' OR 
            car_model LIKE '%$search%')
            LIMIT $start, $limit"; // All requests except pending (history)
} else {
    $sql = "SELECT * FROM car_rent_requests WHERE 
            status = 'Pending' AND (
            fuel_type LIKE '%$search%' OR
            email LIKE '%$search%' OR
            name LIKE '%$search%' OR 
            car_brand LIKE '%$search%' OR 
            car_model LIKE '%$search%')
            LIMIT $start, $limit"; // Pending requests
}

// Fetch total records for pagination
$total_sql = "SELECT COUNT(*) as total FROM car_rent_requests WHERE 
               (status = 'Pending' AND (
               fuel_type LIKE '%$search%' OR
                email LIKE '%$search%' OR
               name LIKE '%$search%' OR 
               car_brand LIKE '%$search%' OR 
               car_model LIKE '%$search%')) OR 
               (name LIKE '%$search%' OR 
               fuel_type LIKE '%$search%' OR 
               car_brand LIKE '%$search%' OR 
               car_model LIKE '%$search%')";

$total_result = mysqli_query($conn, $total_sql);
$total_row = mysqli_fetch_assoc($total_result);
$total_records = $total_row['total'];
$total_pages = ceil($total_records / $limit);

$result = mysqli_query($conn, $sql);


if (!$result) {
    die("Error fetching requests: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - View Requests</title>
    <link rel="stylesheet" href="admin-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        /* Additional styling */
        .action-buttons {
            display: none;
            margin-bottom: 15px;
        }

        .action-buttons.active {
            display: block;
        }

        .selected-count {
            font-weight: bold;
            margin-right: 20px;
        }

        img {
            width: 50px;
            height: auto;
            margin-right: 5px;
        }
        table {
               width: 100%;
               border-collapse: collapse;
         }
         table, th, td {
               border: 1px solid #aaa;
         }
         th, td {
               padding: 10px;
               text-align: left;
         }
         .approve-btn, .reject-btn {
               padding: 5px 10px;
               margin-right: 5px;
               border: none;
               cursor: pointer;
               color: #fff;
         }
         .approve-btn {
               background-color: #28a745;
         }
         .reject-btn {
               background-color: #dc3545;
         }
         img {
               max-width: 100px;
               height: auto;
               margin: 5px;
               cursor: pointer;
         }
         .pagination {
               margin-top: 20px;
         }
         .pagination a {
               padding: 10px;
               margin: 0 5px;
               background-color: #007bff;
               color: white;
               text-decoration: none;
               border-radius: 5px;
         }
         .modal {
               display: none;
               position: fixed;
               z-index: 1;
               padding-top: 60px;
               left: 0;
               top: 0;
               width: 100%;
               height: 100%;
               overflow: auto;
               background-color: rgba(0, 0, 0, 0.8);
         }
         .modal-content {
               margin: auto;
               display: block;
               width: 80%;
               max-width: 700px;
         }
         .close {
               position: absolute;
               top: 15px;
               right: 35px;
               color: white;
               font-size: 40px;
               font-weight: bold;
               cursor: pointer;
         }
    </style>
</head>
<body>
<div class="sidebar">
        <h1>Car Rental System | Admin Panel</h1>
        <ul>
            <li><a href="admindashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="manage-brands.php"><i class="fas fa-car"></i> Brands</a></li>
            <li><a href="admin_view_requests.php" class="active"><i class="fas fa-file-alt"></i> View Pending Requests</a></li>
            <li><a href="manage-vehicles.php"><i class="fas fa-car"></i> Vehicles</a></li>
            <li><a href="#"><i class="fas fa-book"></i> Bookings</a></li>
            <li><a href="#"><i class="fas fa-comments"></i> Manage Testimonials</a></li>
            <li><a href="#"><i class="fas fa-envelope"></i> Manage Contact Queries</a></li>
            <li><a href="#"><i class="fas fa-users"></i> Registered Users</a></li>
            <li><a href="#"><i class="fas fa-address-book"></i> Update Contact Info</a></li>
            <li><a href="#"><i class="fas fa-user-plus"></i> Manage Subscribers</a></li>
        </ul>
    </div>

    
    <div class="content">
    <div class="top-bar">
    <h2><?php echo $view === 'history' ? 'Car Rental Request History' : 'Pending Car Rental Requests'; ?></h2>
         <div class="user-info">
            <img src="admindp.png" alt="User Avatar">
            <span>Admin</span>
            <a href="adminlogin.php" class="logout-btn">Log Out</a>
         </div>
</div>

<div class="view-requests">
    <h1>Car Rental Requests</h1>
    <div class="view-buttons">
            <a href="admin_view_requests.php?view=pending" class="btn btn-primary <?php echo $view === 'pending' ? 'active' : ''; ?>">Pending Requests</a>
            <a href="admin_view_requests.php?view=history" class="btn btn-secondary <?php echo $view === 'history' ? 'active' : ''; ?>">History</a>
      </div><br>
      <div class="search-filter">
            <form action="admin_view_requests.php" method="GET">
                <input type="text" name="search" placeholder="Search by name, car brand, model, fuel type" value="<?php echo htmlspecialchars($search); ?>">
                <input type="hidden" name="view" value="<?php echo $view; ?>">
                <button type="submit">Search</button>
            </form>
        </div>

    <!-- Action buttons and selected count -->
    <div class="action-buttons">
        <span class="selected-count">Selected: <span id="selectedCount">0</span></span>
        <button class="btn btn-success" id="approveSelected" onclick="submitMultipleAction('approve')">Approve Selected</button>
        <button class="btn btn-danger" id="rejectSelected" onclick="submitMultipleAction('reject')">Reject Selected</button>
    </div>
         
    <?php if (mysqli_num_rows($result) > 0): ?>
        <form id="multipleRequestsForm" action="admin_action.php" method="POST">
            <table class="table-container">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAll" onclick="selectAllRequests(this)"></th>
                        <th>Request ID</th>
                        <th>Vehicle Name</th>
                        <th>Car Type</th>
                        <th>Price per Day</th>
                        <th>Rent per Hour</th>
                        <th>Fuel Type</th>
                        <th>Seating Capacity</th>
                        <th>Photos</th>
                        <th>Status</th>
                        <th>View</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><input type="checkbox" class="selectRequest" name="request_ids[]" value="<?php echo htmlspecialchars($row['id']); ?>" onclick="updateSelectedCount()"></td>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['vehicle_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['car_type']); ?></td>
                            <td><?php echo htmlspecialchars($row['price_per_day']); ?></td>
                            <td><?php echo htmlspecialchars($row['rent_per_hour']); ?></td>
                            <td><?php echo htmlspecialchars($row['fuel_type']); ?></td>
                            <td><?php echo htmlspecialchars($row['seating_capacity']); ?></td>
                            <td>
                                <img src="userIMG/<?php echo htmlspecialchars($row['photos']); ?>" onclick="openModal(this)">
                                <img src="userIMG/<?php echo htmlspecialchars($row['photos1']); ?>" onclick="openModal(this)">
                                <img src="userIMG/<?php echo htmlspecialchars($row['photos2']); ?>" onclick="openModal(this)">
                                <img src="userIMG/<?php echo htmlspecialchars($row['photos3']); ?>" onclick="openModal(this)">
                            </td>
                            <td><?php echo htmlspecialchars($row['status']); ?></td>
                            <td>
                              <a href="view-user-vehicle.php?id=<?php echo htmlspecialchars($row['id']); ?>" class="btn btn-info btn-sm" title="View Details"><i class="fas fa-eye"></i></a>
                            </td>
                            <td>
                                <button type="submit" formaction="admin_action.php?action=approve&id=<?php echo htmlspecialchars($row['id']); ?>" class="approve-btn">Approve</button>
                                <button type="submit" formaction="admin_action.php?action=reject&id=<?php echo htmlspecialchars($row['id']); ?>" class="reject-btn">Reject</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </form>

        <!-- Pagination -->
        <div class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="admin_view_requests.php?page=<?php echo $i; ?>&view=<?php echo $view; ?>&search=<?php echo $search; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
        </div>
    <?php else: ?>
        <p>No car rental requests found.</p>
    <?php endif; ?>
</div>

<!-- Modal for viewing images -->
<div id="imageModal" class="modal">
    <span class="close" onclick="closeModal()">&times;</span>
    <img class="modal-content" id="modalImage">
</div>

<script>
    function selectAllRequests(checkbox) {
        var checkboxes = document.querySelectorAll('.selectRequest');
        checkboxes.forEach(function(cb) {
            cb.checked = checkbox.checked;
        });
        updateSelectedCount();
    }

    function updateSelectedCount() {
        var selected = document.querySelectorAll('.selectRequest:checked').length;
        document.getElementById('selectedCount').innerText = selected;
        document.querySelector('.action-buttons').classList.toggle('active', selected > 0);
    }

    function submitMultipleAction(action) {
        var form = document.getElementById('multipleRequestsForm');
        form.action = 'admin_action.php?action=' + action;
        form.submit();
    }

    function openModal(img) {
        var modal = document.getElementById("imageModal");
        var modalImg = document.getElementById("modalImage");
        modal.style.display = "block";
        modalImg.src = img.src;
    }

    function closeModal() {
        var modal = document.getElementById("imageModal");
        modal.style.display = "none";
    }
</script>
</body>
</html>
