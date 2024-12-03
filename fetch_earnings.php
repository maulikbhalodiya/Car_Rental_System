<?php
require('db_connection.php');
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]); // No data if the user is not logged in
    exit();
}

$user_id = $_SESSION['user_id'];
$car_id = isset($_GET['car_id']) && $_GET['car_id'] !== 'all' ? (int)$_GET['car_id'] : null;
$view = isset($_GET['view']) ? $_GET['view'] : 'monthly';
$month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');

$year = date('Y');
$selectedMonth = date('m');

if ($view === 'daily' && $month) {
    list($year, $selectedMonth) = explode('-', $month);
}

$queryBase = "
    SELECT %s(payment_date) AS period, SUM(amount) AS total_income
    FROM payments
    INNER JOIN car_owner ON payments.vehicle_id = car_owner.car_id
    WHERE car_owner.owner_id = ? AND payments.payment_status = 'Completed' %s
    GROUP BY period ORDER BY period
";

$periodColumn = $view === 'daily' ? 'DAY' : 'MONTH';
$additionalCondition = "";

if ($view === 'daily') {
    $additionalCondition = "AND MONTH(payment_date) = ? AND YEAR(payment_date) = ?";
}

$query = sprintf($queryBase, $periodColumn, $additionalCondition);
$stmt = $conn->prepare($query);

if ($view === 'daily') {
    $stmt->bind_param("iii", $user_id, $selectedMonth, $year);
} else {
    $stmt->bind_param("i", $user_id);
}

$stmt->execute();
$result = $stmt->get_result();

$income_data = [];
while ($row = $result->fetch_assoc()) {
    $income_data[$row['period']] = $row['total_income'];
}

$maxPeriods = $view === 'daily' ? date('t', strtotime("$year-$selectedMonth-01")) : 12;
$finalData = [];

for ($i = 1; $i <= $maxPeriods; $i++) {
    $finalData[] = isset($income_data[$i]) ? $income_data[$i] : 0;
}

echo json_encode($finalData); // Return the earnings data as a JSON array
?>
