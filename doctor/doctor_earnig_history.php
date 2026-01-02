<?php
session_start();

include_once "../connection.php";
include_once 'header_dr.php';

// Check if doctor is logged in
if (!isset($_SESSION['dr_id'])) {
    header("Location: doctorLogin.php");
    exit();
}

$doctor_id = $_SESSION['dr_id'];

// Default to last month
$month = date('m', strtotime('last month'));
$year = date('Y', strtotime('last month'));

// Check if the user selected a specific month and year
if (isset($_POST['month']) && isset($_POST['year'])) {
    $month = $_POST['month'];
    $year = $_POST['year'];
}

// Query to get the total earnings for the specified month and year
$sql = "
    SELECT 
        doctor_id,
        SUM(appointment_earnings) AS total_appointment_earnings,
        SUM(consultation_earnings) AS total_consultation_earnings,
        SUM(hostel_earnings) AS total_hostel_earnings,
        SUM(total_earnings) AS monthly_total_earnings
    FROM 
        doctor_daily_earnings
    WHERE 
        doctor_id = ?
        AND YEAR(earnings_date) = ?
        AND MONTH(earnings_date) = ?
    GROUP BY 
        doctor_id
";

// Prepare and execute the query for monthly totals
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $doctor_id, $year, $month);
$stmt->execute();
$result = $stmt->get_result();
$earnings = $result->fetch_assoc();

// Query to get daily earnings for each day in the specified month and year
$sql_daily = "
    SELECT 
        earnings_date,
        appointment_earnings,
        consultation_earnings,
        hostel_earnings,
        total_earnings
    FROM 
        doctor_daily_earnings
    WHERE 
        doctor_id = ?
        AND YEAR(earnings_date) = ?
        AND MONTH(earnings_date) = ?
    ORDER BY 
        earnings_date
";

$stmt_daily = $conn->prepare($sql_daily);
$stmt_daily->bind_param("iii", $doctor_id, $year, $month);
$stmt_daily->execute();
$daily_result = $stmt_daily->get_result();

// Close the statements and database connection
$stmt->close();
$stmt_daily->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Monthly Earnings</title>
    <link rel="stylesheet" href="../afterLoginDoctor_style/doctor_earning_history.css">
</head>
<body>
<div class="container_earnings">
<h2>Your Earnings for the Month</h2>

<form method="post">
    <label for="month">Select Month:</label>
    <select name="month" id="month">
        <?php for ($m = 1; $m <= 12; $m++): ?>
            <option value="<?php echo $m; ?>" <?php echo ($m == $month) ? 'selected' : ''; ?>>
                <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
            </option>
        <?php endfor; ?>
    </select>
    <label for="year">Select Year:</label>
    <select name="year" id="year">
        <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
            <option value="<?php echo $y; ?>" <?php echo ($y == $year) ? 'selected' : ''; ?>>
                <?php echo $y; ?>
            </option>
        <?php endfor; ?>
    </select>
    <button type="submit">View Earnings</button>
</form>

<div class="table1">
    <?php if ($earnings): ?>
    <table>
        <tr>
            <th>Appointment Earnings</th>
            <td><?php echo number_format($earnings['total_appointment_earnings'], 2); ?></td>
        </tr>
        <tr>
            <th>Consultation Earnings</th>
            <td><?php echo number_format($earnings['total_consultation_earnings'], 2); ?></td>
        </tr>
        <tr>
            <th>Hostel Earnings</th>
            <td><?php echo number_format($earnings['total_hostel_earnings'], 2); ?></td>
        </tr>
        <tr>
            <th>Total Monthly Earnings</th>
            <td style="color : #2b78bb;" ><strong><?php echo number_format($earnings['monthly_total_earnings'], 2); ?></strong></td>
        </tr>
    </table>
    <?php else: ?>
    <p style="text-align: center;">No earnings data available for the selected month and year.</p>
    <?php endif; ?>
</div>

<!--Daily earnings for month-->
<div class="table2">
    <h3>Daily Earnings for <?php echo date('F Y', mktime(0, 0, 0, $month, 1, $year)); ?></h3>

    <?php if ($daily_result->num_rows > 0): ?>
    <table>
        <tr>
            <th>Date</th>
            <th>Appointment Earnings</th>
            <th>Consultation Earnings</th>
            <th>Hostel Earnings</th>
            <th>Total Daily Earnings</th>
        </tr>
        <?php while ($daily = $daily_result->fetch_assoc()): ?>
        <tr>
            <td><?php echo date('Y-m-d', strtotime($daily['earnings_date'])); ?></td>
            <td><?php echo number_format($daily['appointment_earnings'], 2); ?></td>
            <td><?php echo number_format($daily['consultation_earnings'], 2); ?></td>
            <td><?php echo number_format($daily['hostel_earnings'], 2); ?></td>
            <td><?php echo number_format($daily['total_earnings'], 2); ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
    <?php else: ?>
    <p>No daily earnings data available for the selected month.</p>
    <?php endif; ?>
</div>
</div>

</body>
</html>

<?php
include_once "../footer.php";
?>