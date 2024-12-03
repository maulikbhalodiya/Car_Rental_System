<?php
session_start();
require('db_connection.php');

$isLoggedIn = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please log in to view earnings.'); window.location.href='login.php';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if the user owns any cars
$query = "SELECT car_id FROM car_owner WHERE owner_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    echo require('header.php'), "<div style='text-align:center' class='no-cars'><h3>You have no cars.</h3><a style='border: none; color: black; background: #3498db; border-radius: 8px; padding: 10px 28px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px; margin: 4px 2px; cursor: pointer;' href='home.php'>Back</a></div>";
    echo require('footer.php');
    exit();
}

// Fetch car list for filter options
$query = "SELECT car_owner.car_id, vehicles.vehicle_name 
          FROM car_owner 
          INNER JOIN vehicles ON car_owner.car_id = vehicles.id 
          WHERE car_owner.owner_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$car_result = $stmt->get_result();

$cars = [];
while ($row = $car_result->fetch_assoc()) {
    $cars[] = $row;
}

// Get total earnings data for all cars initially
$query = "
    SELECT MONTH(payment_date) AS month, SUM(amount) AS total_income
    FROM payments
    INNER JOIN car_owner ON payments.vehicle_id = car_owner.car_id
    WHERE car_owner.owner_id = ? AND payments.payment_status = 'Completed'
    GROUP BY MONTH(payment_date)
    ORDER BY MONTH(payment_date)";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$monthly_data = [];
while ($row = $result->fetch_assoc()) {
    $monthly_data[$row['month']] = $row['total_income'];
}

// Generate data for each month (1-12)
$income_data = [];
for ($i = 1; $i <= 12; $i++) {
    $income_data[] = isset($monthly_data[$i]) ? $monthly_data[$i] : 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Earnings - Car Rental System</title>
    <!-- Include Bootstrap -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="earnings.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- Navigation Bar -->
    <?php include('header.php'); ?>
    <div class="earnings-container">
        <h2>Your Monthly Earnings</h2>
        <div class="form-inline">
        <label for="carFilter">Filter by Car:  </label>
        <select class="col-3 form-control" id="carFilter" onchange="updateChart()">
            <option value="all">All</option>
            <?php foreach ($cars as $car): ?>
                <option value="<?php echo $car['car_id']; ?>"><?php echo htmlspecialchars($car['vehicle_name']); ?></option>
            <?php endforeach; ?>
        </select></div>
        <canvas id="earningsChart"></canvas>
    </div>

    <script>
        const initialData = <?php echo json_encode($income_data); ?>;
        const ctx = document.getElementById('earningsChart').getContext('2d');
        const earningsChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
                datasets: [{
                    label: 'Earnings (INR)',
                    data: initialData,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Total Income (INR)'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                }
            }
        });

        async function updateChart() {
            const carId = document.getElementById('carFilter').value;
            
            // Fetch new data based on the selected car
            const response = await fetch(`fetch_earnings.php?car_id=${carId}`);
            const newData = await response.json();

            // Update chart data and refresh
            earningsChart.data.datasets[0].data = newData;
            earningsChart.update();
        }
    </script>
    <!-- Footer -->
    <?php include('footer.php'); ?>
</body>
</html>
