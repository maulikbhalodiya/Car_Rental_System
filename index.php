<?php
require('db_connection.php');

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Determine the date range based on user selection
$timeRange = isset($_GET['timeRange']) ? $_GET['timeRange'] : '1'; // Default to 1 day
$startDate = '';
$endDate = date('Y-m-d H:i:s');

switch ($timeRange) {
    case '7':
        $startDate = date('Y-m-d H:i:s', strtotime('-7 days'));
        break;
    case '30':
        $startDate = date('Y-m-d H:i:s', strtotime('-30 days'));
        break;
    case '1':
    default:
        $startDate = date('Y-m-d H:i:s', strtotime('-1 day'));
        break;
}

// Fetch data based on the selected date range
$sql = "SELECT start_date, total_price FROM booking_req WHERE status = 'Pending' AND start_date BETWEEN '$startDate' AND '$endDate'";
$result = $conn->query($sql);

$data = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

// Close connection
$conn->close();

// Prepare data for Chart.js
$labels = [];
$prices = [];
foreach ($data as $item) {
    $labels[] = date('Y-m-d', strtotime($item['start_date'])); // Format date
    $prices[] = (float)$item['total_price']; // Convert to float
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Real-Time Graph</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        canvas {
            max-width: 600px;
            margin: auto;
        }
        .controls {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="controls">
    <label for="timeRange">Select Time Range:</label>
    <select id="timeRange" onchange="updateChart()">
        <option value="1" <?php if ($timeRange == '1') echo 'selected'; ?>>Last 1 Day</option>
        <option value="7" <?php if ($timeRange == '7') echo 'selected'; ?>>Last 7 Days</option>
        <option value="30" <?php if ($timeRange == '30') echo 'selected'; ?>>Last 30 Days</option>
    </select>
</div>

<canvas id="myChart"></canvas>

<script>
    const labels = <?php echo json_encode($labels); ?>; // PHP array to JavaScript
    const prices = <?php echo json_encode($prices); ?>; // PHP array to JavaScript

    const ctx = document.getElementById('myChart').getContext('2d');
    let myChart = new Chart(ctx, {
        type: 'bar', // Change this to 'line' or any other type if needed
        data: {
            labels: labels,
            datasets: [{
                label: 'Total Price',
                data: prices,
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    function updateChart() {
        const timeRange = document.getElementById('timeRange').value;
        window.location.href = '?timeRange=' + timeRange; // Redirect to the same page with the selected time range
    }
</script>

</body>
</html>