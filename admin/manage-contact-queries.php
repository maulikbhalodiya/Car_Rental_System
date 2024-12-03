<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: adminlogin.php");
    exit();
}

// Database connection
require('db_connection.php');

// Pagination Setup
$limit = 10; // Number of queries per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Set filter to show specific status (pending/resolved)
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$filterQuery = "";
if ($filter === 'pending') {
    $filterQuery = "WHERE status = 'Pending'";
} elseif ($filter === 'resolved') {
    $filterQuery = "WHERE status = 'Resolved'";
}

// Fetch total count for pagination
$countQuery = "SELECT COUNT(*) AS total FROM contact_queries $filterQuery";
$countResult = $conn->query($countQuery);
$totalRows = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);

// Handle query status update
if (isset($_POST['update_status'])) {
    $query_id = intval($_POST['query_id']);
    $new_status = $conn->real_escape_string($_POST['status']);

    $valid_statuses = ['Pending', 'Reviewed', 'Resolved'];
    if (!in_array($new_status, $valid_statuses)) {
        die("Invalid status value.");
    }

    $updateQuery = "UPDATE contact_queries SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($updateQuery);
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("si", $new_status, $query_id);  // 'si' for string and integer
    if ($stmt->execute()) {
        header("Location: manage-contact-queries.php?msg=Status+updated+successfully");
        exit();
    } else {
        echo "Error updating status: " . $stmt->error;
    }
    $stmt->close();
}

// Handle delete selected queries
if (isset($_POST['delete_selected'])) {
    if (!empty($_POST['selected_queries'])) {
        foreach ($_POST['selected_queries'] as $query_id) {
            $deleteQuery = "DELETE FROM contact_queries WHERE id = ?";
            $stmt = $conn->prepare($deleteQuery);
            $stmt->bind_param("i", $query_id);
            $stmt->execute();
        }
        header("Location: manage-contact-queries.php?msg=Selected+queries+deleted+successfully");
        exit();
    }
}

// Handle reply to query using PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/phpmailer/phpmailer/src/Exception.php';
require __DIR__ . '/vendor/phpmailer/phpmailer/src/PHPMailer.php';
require __DIR__ . '/vendor/phpmailer/phpmailer/src/SMTP.php';

if (isset($_POST['reply_query'])) {
    $query_id = intval($_POST['query_id']);
    $reply_message = trim($_POST['reply_message']);
    $recipient_email = $conn->real_escape_string($_POST['recipient_email']);

    // Fetch the original query for reference
    $originalQuery = $conn->query("SELECT message FROM contact_queries WHERE id = $query_id")->fetch_assoc()['message'];

    $mail = new PHPMailer(true); // Create a new PHPMailer instance

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Set the SMTP server to send through
        $mail->SMTPAuth = true;
        $mail->Username = 'carrentalservice147@gmail.com';  // Your Gmail address
        $mail->Password = 'ziwq ljxt zgiy vfhs';    // Your generated app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587; 

        // Recipients
        $mail->setFrom('carrentalservice147@gmail.com', 'Car Rental Service');
        $mail->addAddress($recipient_email); 

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Reply to your contact query';
        $mail->Body = "<p>Dear Customer,</p><p>Original Query: $originalQuery</p><p>Reply: $reply_message</p><p>Best Regards,<br>Car Rental Service</p>";

        // Send the email
        $mail->send();

        // Update status to Resolved and set reply timestamp
        $updateQuery = "UPDATE contact_queries SET status = 'Resolved', reply = ?, reply_timestamp = NOW() WHERE id = ?";
        $stmt = $conn->prepare($updateQuery);
        if ($stmt === false) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("si", $reply_message, $query_id);
        $stmt->execute();
        $stmt->close();

        header("Location: manage-contact-queries.php?msg=Reply+sent+successfully");
        exit();
    } catch (Exception $e) {
        echo "Error sending email: {$mail->ErrorInfo}";
    }
}

// Fetch all contact queries with filter
$queriesQuery = "SELECT id, name, email, message, status, submitted_at AS date_submitted, reply, reply_timestamp FROM contact_queries $filterQuery ORDER BY date_submitted DESC";
$queriesResult = $conn->query($queriesQuery);

if (!$queriesResult) {
    die("Error fetching contact queries: " . $conn->error);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Rental System | Manage Contact Queries</title>
    <link rel="stylesheet" href="admin-style.css">
    <link rel="stylesheet" href="manage-contact-queries.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script>
        // Toggle visibility for status update, delete, and reply forms
        function toggleAction(queryId, action) {
            let updateForm = document.getElementById(`updateForm_${queryId}`);
            let replyForm = document.getElementById(`replyForm_${queryId}`);
            
            if (action === 'update') {
                updateForm.style.display = updateForm.style.display === 'none' ? 'block' : 'none';
                replyForm.style.display = 'none';
            } else if (action === 'reply') {
                replyForm.style.display = replyForm.style.display === 'none' ? 'block' : 'none';
                updateForm.style.display = 'none';
            }
        }

        function toggleDeleteButton() {
            const deleteButton = document.getElementById('delete-selected-btn');
            const checkboxes = document.querySelectorAll('input[name="selected_queries[]"]');
            deleteButton.style.display = Array.from(checkboxes).some(checkbox => checkbox.checked) ? 'block' : 'none';
        }

        function selectAll(checkbox) {
            const checkboxes = document.querySelectorAll('input[name="selected_queries[]"]');
            checkboxes.forEach((cb) => {
                cb.checked = checkbox.checked;
            });
            toggleDeleteButton();
        }
    </script>
</head>
<body>
<?php require('navbar.php'); ?>

<div class="manage-contact-queries">
    <h3>Contact Queries</h3>
    <?php
        if (isset($_GET['msg'])) {
            echo '<script>alert("' . htmlspecialchars($_GET['msg']) . '");</script>';
        }
    ?>
    <div class="button-group">
        <a href="?filter=pending" class="<?php echo $filter === 'pending' ? 'active' : ''; ?> btn btn-primary">Pending</a>
        <a href="?filter=resolved" class="<?php echo $filter === 'resolved' ? 'active' : ''; ?> btn btn-primary">Resolved</a>

        <button id="delete-selected-btn" style="display:none;" onclick="document.getElementById('delete-selected-form').submit();" class="delete-btn"><i class="fas fa-trash"></i> Delete Selected</button>
    </div>
    <form id="delete-selected-form" action="manage-contact-queries.php" method="POST" style="display:none;">
        <input type="hidden" name="selected_queries[]" id="selected-queries">
    </form>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th><input type="checkbox" onclick="selectAll(this)"></th>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Message</th>
                    <th>Status</th>
                    <th>Date Submitted</th>
                    <th>Reply</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($queriesResult->num_rows > 0): ?>
                    <?php while($query = $queriesResult->fetch_assoc()): ?>
                        <tr>
                            <td><input type="checkbox" name="selected_queries[]" value="<?php echo htmlspecialchars($query['id']); ?>" onclick="toggleDeleteButton()"></td>
                            <td><?php echo htmlspecialchars($query['id']); ?></td>
                            <td><?php echo htmlspecialchars($query['name']); ?></td>
                            <td><?php echo htmlspecialchars($query['email']); ?></td>
                            <td><?php echo htmlspecialchars($query['message']); ?></td>
                            <td><?php echo htmlspecialchars($query['status']); ?></td>
                            <td><?php echo htmlspecialchars($query['date_submitted']); ?></td>
                            <td>
                                <?php if (!empty($query['reply'])): ?>
                                    <p>Reply: <?php echo htmlspecialchars($query['reply']); ?></p>
                                    <p>Time: <?php echo htmlspecialchars($query['reply_timestamp']); ?></p>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button onclick="toggleAction(<?php echo $query['id']; ?>, 'update')" class="edit-btn"><i class="fas fa-edit"></i></button><!--edit btn-->
                                <button onclick="toggleAction(<?php echo $query['id']; ?>, 'reply')" class="reply-btn"><i class="fas fa-reply"></i></button><!--reply btn-->
                                <form action="manage-contact-queries.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="query_id" value="<?php echo $query['id']; ?>">
                                    <button type="submit" name="delete_query" class="btn btn-danger"><i class="fas fa-trash"></i> </button> <!--delete btn-->
                                </form>

                                <!-- Hidden forms for update and reply -->
                                <form id="updateForm_<?php echo $query['id']; ?>" action="manage-contact-queries.php" method="POST" style="display:none;">
                                    <input type="hidden" name="query_id" value="<?php echo $query['id']; ?>">
                                    <select name="status" required>
                                        <option value="Pending" <?php if($query['status'] == 'Pending') echo 'selected'; ?>>Pending</option>
                                        <option value="Resolved" <?php if($query['status'] == 'Resolved') echo 'selected'; ?>>Resolved</option>
                                    </select>
                                    <button type="submit" name="update_status" class="edit-btn"><i class="fas fa-save"></i></button>
                                </form>

                                <form id="replyForm_<?php echo $query['id']; ?>" action="manage-contact-queries.php" method="POST" style="display:none;">
                                    <input type="hidden" name="query_id" value="<?php echo $query['id']; ?>">
                                    <input type="hidden" name="recipient_email" value="<?php echo $query['email']; ?>">
                                    <textarea name="reply_message" placeholder="Enter your reply here..." required></textarea>
                                    <button type="submit" name="reply_query" class="reply-btn"><i class="fas fa-paper-plane"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9">No contact queries found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

  <!-- Pagination -->
  <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?filter=<?php echo $filter; ?>&page=<?php echo $page - 1; ?>">Previous</a>
            <?php endif; ?>
            <span class="current-page">Page <?php echo $page; ?> of <?php echo $totalPages; ?></span>
            <?php if ($page < $totalPages): ?>
                <a href="?filter=<?php echo $filter; ?>&page=<?php echo $page + 1; ?>">Next</a>
            <?php endif; ?>
        </div>
        </div>
<script>
    // Get the current page URL for makee navbar higlight for current page
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
