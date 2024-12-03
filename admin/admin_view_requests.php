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
$limit = 3; // Number of records per page
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
        .manage-req{
            background-color: #ffffff;
            padding: 20px;
            padding-top: 8px; */
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
         /* Table Styles */
        .table-container {
            overflow-x: auto;
            margin-top: 0.1rem;
        }

        .manage-req table {
            width: 100%;
            border-collapse: collapse;
            /* margin-top: 20px; */
        }

        .manage-req th,
        .manage-req td {
            text-align: left;
            /* padding: 12px; */
            border-bottom: 1px solid #e0e0e0;
        }

        .manage-req th {
            background-color: #f8f9fa;
            color: #2c3e50;
            font-weight: bold;
        }

        .manage-req tr:hover {
            background-color: #f5f5f5;
        }
        
        .action-buttons {
            display: flex;
            align-items: center;
            gap: 10px;
            
        }
        .approve-btn, .reject-btn {
            margin: 0;
            margin-top: 5px;
            color: white;
            border: none;
            padding: 6px 16px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
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
    <!-- Navbar and Sidebar -->
   <?php require('navbar.php')?>
<div class="manage-req">
   <div class="toggle-view">
        <?php if ($view === 'history'): ?>
            <a href="admin_view_requests.php" class="btn btn-primary">View Pending Requests</a>
        <?php else: ?>
            <a href="admin_view_requests.php?view=history" class="btn btn-primary">View All Requests (History)</a>
        <?php endif; ?>
    </div>

    <div class="search-filter" style="margin-top: 20px; width:50%">
        <form action="admin_view_requests.php" method="GET">
            <input type="text" name="search" placeholder="Search by name, car brand, model, fuel type" value="<?php echo htmlspecialchars($search); ?>">
            <input type="hidden" name="view" value="<?php echo $view; ?>">
            <button class="submit" type="submit">Search</button>
        </form>
        </div>

        <form action="admin_action.php" method="POST" id="bulkActionForm">
    <?php if ($view === 'pending' && mysqli_num_rows($result) > 0): // Only show buttons if viewing pending requests and there are pending requests ?>
        <div class="action-buttons">
            <button type="button" class="approve-btn" onclick="submitBulkAction('approve')">Approve Selected</button>
            <button type="button" class="reject-btn" onclick="submitBulkAction('reject')">Reject Selected</button>
        </div>
    <?php endif; ?>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <?php if ($view === 'pending'): // Only show checkboxes if viewing pending requests ?>
                            <th><input type="checkbox" id="selectAll" onclick="toggleSelectAll(this)"> Select All</th>
                        <?php endif; ?>
                        <th>Request ID</th>
                        <th>User ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Car Brand</th>
                        <th>Car Model</th>
                        <th>Year</th>
                        <th>Kilometers Driven</th>
                        <th>Fuel Type</th>
                        <th>Photos</th>
                        <th>Status</th>
                        <?php if ($view === 'pending'): // Only show Actions column if viewing pending requests ?>
                            <th>Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <?php if ($view === 'pending'): // Only show checkboxes if viewing pending requests ?>
                                <td><input type="checkbox" name="request_ids[]" value="<?php echo htmlspecialchars($row['id']); ?>"></td>
                            <?php endif; ?>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['user_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['car_brand']); ?></td>
                            <td><?php echo htmlspecialchars($row['car_model']); ?></td>
                            <td><?php echo htmlspecialchars($row['year']); ?></td>
                            <td><?php echo htmlspecialchars($row['kilometers_driven']); ?></td>
                            <td><?php echo htmlspecialchars($row['fuel_type']); ?></td>
                            <td>
                                <?php
                                $photos_query = "SELECT * FROM car_photos WHERE request_id = " . intval($row['id']);
                                $photos_result = mysqli_query($conn, $photos_query);
                                if ($photos_result && mysqli_num_rows($photos_result) > 0) {
                                    while ($photo = mysqli_fetch_assoc($photos_result)) {
                                        echo '<img src="' . htmlspecialchars($photo['photo_path']) . '" alt="Car Photo" onclick="showModal(this)">';
                                    }
                                } else {
                                    echo "No photos available.";
                                }
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['status']); ?></td>

                            <?php if ($view === 'pending'): // Only show individual action buttons for pending requests ?>
                                <td>
                                    <button type="submit" formaction="admin_action.php?action=approve&id=<?php echo htmlspecialchars($row['id']); ?>" class="edit-btn"><i class="fa-solid fa-check"></i></button>
                                    <button type="submit" formaction="admin_action.php?action=reject&id=<?php echo htmlspecialchars($row['id']); ?>" class="delete-btn"><i class="fa-solid fa-xmark"></i></button>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p>No car rental requests found.</p>
    <?php endif; ?>
</form>
    <div class="pagination">
        <!-- Pagination links -->
        <a href="?search=<?php echo urlencode($search); ?>&view=<?php echo $view; ?>&page=<?php echo ($page > 1) ? $page - 1 : 1; ?>" class="prev-btn">Previous</a>
        <span class="current-page">Page <?php echo $page; ?></span>
        <a href="?search=<?php echo urlencode($search); ?>&view=<?php echo $view; ?>&page=<?php echo ($page < $total_pages) ? $page + 1 : $total_pages; ?>" class="next-btn">Next</a>
    </div>

    <div id="photoModal" class="modal">
        <span class="close" onclick="closeModal()">&times;</span>
        <img class="modal-content" id="modalImage">
    </div>
        </div>
    <script>
        function showModal(imgElement) {
            var modal = document.getElementById('photoModal');
            var modalImg = document.getElementById('modalImage');
            modal.style.display = "block";
            modalImg.src = imgElement.src;
        }

        function closeModal() {
            var modal = document.getElementById('photoModal');
            modal.style.display = "none";
        }

        function toggleSelectAll(selectAllCheckbox) {
            const checkboxes = document.querySelectorAll('input[name="request_ids[]"]');
            checkboxes.forEach(checkbox => checkbox.checked = selectAllCheckbox.checked);
        }

        function submitBulkAction(action) {
            const form = document.getElementById('bulkActionForm');
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'bulk_action';
            actionInput.value = action;
            form.appendChild(actionInput);
            form.submit();
        }
    </script>
</body>
</html>
