<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Redirect to login page
    header("Location: adminlogin.php");
    exit();
}
require('db_connection.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Earnings Reports</title>
    <link rel="stylesheet" href="admin-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Chart.js Library -->
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background-color: #f4f4f9;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        .filter-container {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            align-items: center;
        }
        .chart-container {
            margin-top: 30px;
        }
        .most-booked-container {
            margin-top: 30px;
        }
        .month-header {
            font-size: 20px;
            font-weight: bold;
            margin-top: 20px;
            text-decoration: underline;
        }
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .report-table th, .report-table td {
            padding: 8px;
            text-align: left;
            border: 1px solid #ddd;
        }
        .report-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .report-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .report-table tr:hover {
            background-color: #f1f1f1;
        }
    </style>
</head>
<body>
<?php require('navbar.php')?>
<div class="container">
    <h2>Admin Earnings Reports</h2>
    
    <div class="filter-container">
        <label for="carFilter">Select Car:</label>
        <select id="carFilter">
            <option value="all">All Cars</option>
            <?php
            // Fetch cars from the database
            $carQuery = $conn->query("SELECT id, vehicle_name FROM vehicles");
            if ($carQuery->num_rows > 0) {
                while ($car = $carQuery->fetch_assoc()) {
                    echo "<option value='{$car['id']}'>{$car['vehicle_name']}</option>";
                }
            } else {
                echo "<option disabled>No cars found</option>";
            }
            ?>
        </select>
        <label for="fromDate">From:</label>
        <input type="date" id="fromDate" value="<?php echo date('Y-m-01'); ?>">
        <label for="toDate">To:</label>
        <input type="date" id="toDate" value="<?php echo date('Y-m-d'); ?>">
        <button id="applyFilters">Apply Filter</button>
    </div>

    <div class="chart-container">
        <canvas id="earningsChart"></canvas>
    </div>

    <div class="most-booked-container">
        <h3>Most Booked Car Report</h3>
        <div id="mostBookedReport"></div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const earningsChartCtx = document.getElementById("earningsChart").getContext("2d");
    let earningsChart;

    // Function to load earnings data and create the chart
    function loadEarningsData(carId = "all", fromDate, toDate) {
        fetch(`fetch_earnings_data.php?car_id=${carId}&from_date=${fromDate}&to_date=${toDate}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error("Error from server:", data.error);
                    return;
                }

                const labels = data.labels;
                const earningsData = data.earnings;
                const mostBookedData = data.most_booked_car;
                const mostBookedUserData = data.most_booked_user;

                // Display chart data
                if (earningsChart) {
                    earningsChart.destroy(); // Destroy previous chart instance
                }

                earningsChart = new Chart(earningsChartCtx, {
                    type: "line",
                    data: {
                        labels: labels,
                        datasets: [{
                            label: "Earnings (INR)",
                            data: earningsData,
                            borderColor: "rgba(75, 192, 192, 1)",
                            backgroundColor: "rgba(75, 192, 192, 0.2)",
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top'
                            }
                        }
                    }
                });

                // Generate the Most Booked Car report, ensuring all ties are shown
                const mostBookedContainer = document.getElementById("mostBookedReport");
                mostBookedContainer.innerHTML = "<strong>Most Booked Cars and Users:</strong>";

                let mostBookedText = '';
                let currentMonth = '';

                for (let key in mostBookedData) {
                    const cars = mostBookedData[key];
                    const userEntries = mostBookedUserData[key];
                    
                    if (cars && userEntries) {
                        if (currentMonth !== key) {
                            if (currentMonth !== '') {
                                mostBookedText += "</table>";
                            }
                            mostBookedText += `<div class="month-header">${key}</div>`;
                            mostBookedText += `
                                <table class="report-table">
                                    <thead>
                                        <tr>
                                            <th>Most Booked Car</th>
                                            <th>Bookings</th>
                                            <th>Most Booked User</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                            `;
                            currentMonth = key;
                        }

                        cars.forEach((carEntry, index) => {
                            const userEntry = userEntries[index] || { user_name: "N/A" };
                            mostBookedText += `
                                <tr>
                                    <td>${carEntry.vehicle_name}</td>
                                    <td>${carEntry.bookings} bookings</td>
                                    <td>${userEntry.user_name}</td>
                                </tr>
                            `;
                        });
                    }
                }

                mostBookedText += "</tbody></table>";
                mostBookedContainer.innerHTML += mostBookedText;
            })
            .catch(error => console.error("Error loading earnings data:", error));
    }

    // Initial load with default dates
    const fromDate = document.getElementById("fromDate").value;
    const toDate = document.getElementById("toDate").value;
    loadEarningsData("all", fromDate, toDate);

    // Apply filters
    document.getElementById("applyFilters").addEventListener("click", function () {
        const carId = document.getElementById("carFilter").value;
        const fromDate = document.getElementById("fromDate").value;
        const toDate = document.getElementById("toDate").value;
        loadEarningsData(carId, fromDate, toDate);
    });
});
</script>

</body>
</html>
