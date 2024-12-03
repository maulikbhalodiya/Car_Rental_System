<?php
session_start();

// Redirect to login page if the admin is not logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: adminlogin.php");
    exit();
}
require('db_connection.php');

// Pagination Setup
$limit = 4; // Number of users per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Handle user deletion
if (isset($_GET['delete_user'])) {
    $user_id = intval($_GET['delete_user']);
    $deleteQuery = "DELETE FROM customers WHERE customer_id = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("i", $user_id);
    if ($stmt->execute()) {
        header("Location: registered_users.php?msg=User+deleted+successfully");
        exit();
    } else {
        echo "Error deleting user: " . $conn->error;
    }
    $stmt->close();
}

// Handle delete selected users
if (isset($_POST['delete_selected'])) {
    if (!empty($_POST['selected_users'])) {
        foreach ($_POST['selected_users'] as $user_id) {
            $deleteQuery = "DELETE FROM customers WHERE customer_id = ?";
            $stmt = $conn->prepare($deleteQuery);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
        }
        header("Location: registered_users.php?msg=Selected+users+deleted+successfully");
        exit();
    }
}

// Handle search and city filter functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$citySort = isset($_GET['city_sort']) ? $_GET['city_sort'] : '';

// Fetch distinct city names for filtering
$cityQuery = "SELECT DISTINCT city FROM users ORDER BY city ASC";
$cityResult = $conn->query($cityQuery);

$cities = [];
if ($cityResult) {
    while ($row = $cityResult->fetch_assoc()) {
        $cities[] = $row['city'];
    }
}

// Prepare the base count query
$countQuery = "SELECT COUNT(*) AS total FROM users";
$conditions = [];
if ($search) {
    $conditions[] = "(fullname LIKE ? OR email LIKE ?)";
}
if ($citySort) {
    $conditions[] = "city = ?";
}

if (count($conditions) > 0) {
    $countQuery .= " WHERE " . implode(" AND ", $conditions);
}

// Prepare the statement
$countStmt = $conn->prepare($countQuery);
$searchTerm = '%' . $conn->real_escape_string($search) . '%';

if ($search && $citySort) {
    $countStmt->bind_param("sss", $searchTerm, $searchTerm, $citySort);
} elseif ($search) {
    $countStmt->bind_param("ss", $searchTerm, $searchTerm);
} elseif ($citySort) {
    $countStmt->bind_param("s", $citySort);
}
$countStmt->execute();
$countResult = $countStmt->get_result();
$totalRows = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);

// Prepare the base users query
$usersQuery = "SELECT id AS id, fullname AS name, email, phone, address, city, zip_code, created_at AS registration_date FROM users";
$conditions = [];
if ($search) {
    $conditions[] = "(fullname LIKE ? OR email LIKE ?)";
}
if ($citySort) {
    $conditions[] = "city = ?";
}

if (count($conditions) > 0) {
    $usersQuery .= " WHERE " . implode(" AND ", $conditions);
}

$usersQuery .= " LIMIT ? OFFSET ?";

// Prepare the statement
$stmt = $conn->prepare($usersQuery);
if ($citySort) {
    if ($search) {
        $stmt->bind_param("ssii", $searchTerm, $searchTerm, $limit, $offset);
    } else {
        $stmt->bind_param("sii", $citySort, $limit, $offset);
    }
} else {
    if ($search) {
        $stmt->bind_param("ssii", $searchTerm, $searchTerm, $limit, $offset);
    } else {
        $stmt->bind_param("ii", $limit, $offset);
    }
}
$stmt->execute();
$usersResult = $stmt->get_result();

// Check for query errors
if (!$usersResult) {
    die("Error fetching users: " . $conn->error);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Rental System | Manage Registered Users</title>
    <link rel="stylesheet" href="registered_users.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <!-- Navbar and sidebar -->
    <?php require('navbar.php'); ?>
    <div class="manage-users">
        <h3>Registered Users</h3>
        <?php
        if (isset($_GET['msg'])) {
            echo '<script>alert("' . htmlspecialchars($_GET['msg']) . '");</script>';
        }
        ?>
        <div class="button-group">
        <div class="search-filter" >
        <form method="GET" action="registered_users.php" >
            <input type="text" name="search" placeholder="Search by name or email" value="<?php echo htmlspecialchars($search); ?>">
            <select name="city_sort" onchange="this.form.submit()">
                <option value="">Select City</option>
                <?php foreach ($cities as $city): ?>
                    <option value="<?php echo htmlspecialchars($city); ?>" <?php echo $citySort === $city ? 'selected' : ''; ?>><?php echo htmlspecialchars($city); ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="search-btn">Search</button>
        </form>
        </div>
        <button id="delete-selected-btn" style="display:none;" onclick="document.getElementById('delete-selected-form').submit();" class="btn btn-danger"><i class="fas fa-trash"></i> Delete Selected</button>
        <form id="delete-selected-form" action="registered_users.php" method="POST" style="display:none;">
            <input type="hidden" name="selected_users[]" id="selected-users">
        </form></div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th><input type="checkbox" onclick="selectAll(this)"></th>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Address</th>
                        <th>City</th>
                        <th>Zip Code</th>
                        <th>Registration Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($usersResult->num_rows > 0): ?>
                        <?php $counter = 1; // Initialize counter ?>
                        <?php while($user = $usersResult->fetch_assoc()): ?>
                            <tr>
                                <td><input type="checkbox" name="selected_users[]" value="<?php echo htmlspecialchars($user['id']); ?>" onclick="toggleDeleteButton()"></td>
                                <td><?php echo $counter++; // Display the counter ?></td>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['phone']); ?></td>
                                <td><?php echo htmlspecialchars($user['address']); ?></td>
                                <td><?php echo htmlspecialchars($user['city']); ?></td>
                                <td><?php echo htmlspecialchars($user['zip_code']); ?></td>
                                <td><?php echo htmlspecialchars($user['registration_date']); ?></td>
                                <td>
                                    <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-success"><i class="fas fa-edit"></i></a>
                                    <a href="registered_users.php?delete_user=<?php echo $user['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this user?');"><i class="fas fa-trash-alt"></i></a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10">No registered users found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&city_sort=<?php echo urlencode($citySort); ?>">Previous</a>
            <?php endif; ?>
            <span class="current-page">Page <?php echo $page; ?> of <?php echo $totalPages; ?></span>
            <?php if ($page < $totalPages): ?>
                <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&city_sort=<?php echo urlencode($citySort); ?>">Next</a>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function toggleDeleteButton() {
            const deleteButton = document.getElementById('delete-selected-btn');
            const checkboxes = document.querySelectorAll('input[name="selected_users[]"]');
            deleteButton.style.display = Array.from(checkboxes).some(checkbox => checkbox.checked) ? 'block' : 'none';
        }

        function selectAll(checkbox) {
            const checkboxes = document.querySelectorAll('input[name="selected_users[]"]');
            checkboxes.forEach((cb) => {
                cb.checked = checkbox.checked;
            });
            toggleDeleteButton();
        }

        // Get the current page URL for making navbar highlight for current page
        const currentLocation = window.location.pathname.split('/').pop();
        const navLinks = document.querySelectorAll('.sidebar a');

        navLinks.forEach(link => {
            if (link.getAttribute('href') === currentLocation) {
                link.classList.add('active');
            }
        });
    </script>
</body>
</html>
