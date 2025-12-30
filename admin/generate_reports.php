<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: adminLogin.php");
    exit();
}

include_once 'header_admin.php';
require '../connection.php'; // Database connection

// Function to get the monthly summary
function getMonthlySummary($conn) {
    $summary = [];

    // Users count
    $result = $conn->query("SELECT COUNT(*) AS user_count FROM user WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())");
    $summary['users'] = $result->fetch_assoc()['user_count'];

    // Doctors count
    $result = $conn->query("SELECT COUNT(*) AS doctor_count FROM doctor WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())");
    $summary['doctors'] = $result->fetch_assoc()['doctor_count'];

    // Pets count
    $result = $conn->query("SELECT COUNT(*) AS pet_count FROM pet WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())");
    $summary['pets'] = $result->fetch_assoc()['pet_count'];

    // Appointments count
    $result = $conn->query("SELECT COUNT(*) AS appointment_count FROM appointment WHERE MONTH(appointment_time) = MONTH(CURRENT_DATE()) AND YEAR(appointment_time) = YEAR(CURRENT_DATE())");
    $summary['appointments'] = $result->fetch_assoc()['appointment_count'];

    // Consultations count
    $result = $conn->query("SELECT COUNT(*) AS consultation_count FROM consultation WHERE MONTH(consultation_time) = MONTH(CURRENT_DATE()) AND YEAR(consultation_time) = YEAR(CURRENT_DATE())");
    $summary['consultations'] = $result->fetch_assoc()['consultation_count'];

    // Hostels count
    $result = $conn->query("SELECT COUNT(*) AS hostel_count FROM hostel WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())");
    $summary['hostels'] = $result->fetch_assoc()['hostel_count'];

    // Payments total
    $result = $conn->query("SELECT SUM(amount) AS total_payments FROM bill WHERE MONTH(date) = MONTH(CURRENT_DATE()) AND YEAR(date) = YEAR(CURRENT_DATE())");
    $summary['payments'] = $result->fetch_assoc()['total_payments'] ?? 0;

    return $summary;
}

// Initialize variables
$reportData = [];
$reportType = '';
$fields = [];

// Generate Report Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_report'])) {
    // Retrieve the report type
    $reportType = $_POST['report_type'];

    switch ($reportType) {
        case 'consultations':
            $query = "SELECT consultation_id, consultation_time, consultation.user_id, pet_id, dr_id, consultation.status, consultation_fee, bill.amount FROM consultation JOIN bill ON consultation.bill_id = bill.bill_id";
            break;

        case 'appointments':
            $query = "SELECT appointment_id, appointment_time, appointment.user_id, pet_id, doctor_id, appointment.status, appointment_fee, bill.amount FROM appointment JOIN bill ON appointment.bill_id = bill.bill_id";
            break;

        case 'hostels':
            $query = "SELECT hostel_id, h.user_id, pet_id, start_date, end_date, h.status, hostel_fee, bill.amount FROM hostel AS h JOIN bill ON h.bill_id = bill.bill_id";
            break;

        case 'admin':
            $query = "SELECT admin_id, admin_name, admin_email, admin_phone, admin_address, created_at FROM admin";
            break;

        case 'users':
            $query = "SELECT user_id, user_email, user_phone, user_first_name, user_last_name, created_at FROM user";
            break;

        case 'doctors':
            $query = "SELECT d.dr_id, dr_name, earnings_date, appointment_earnings, consultation_earnings, hostel_earnings, total_earnings FROM doctor_daily_earnings JOIN doctor d ON doctor_daily_earnings.doctor_id = d.dr_id";
            break;

        default:
            echo "Invalid report type.";
            exit;
    }

    $result = $conn->query($query);

    if ($result) {
        // Store the result in an array for display
        while ($row = $result->fetch_assoc()) {
            $reportData[] = $row;
        }

        // Store report data and fields in session for later use
        if (!empty($reportData)) {
            $_SESSION['reportData'] = $reportData;
            $_SESSION['reportType'] = $reportType;
            $_SESSION['fields'] = array_keys($reportData[0]);
        }
    } else {
        echo "Error generating report: " . $conn->error;
    }
}

// Export Report Logic
if (isset($_GET['export']) && !empty($_SESSION['reportData'])) {
    // Start output buffering to use PHP's built-in Excel export functionality
    ob_start(); // Start output buffering

    $reportData = $_SESSION['reportData'];
    $fields = $_SESSION['fields'];
    $reportType = $_SESSION['reportType'];

    // Output report results in Excel format (HTML)
    echo "<table border='1'><tr>";

    // Get column names
    foreach ($fields as $field) {
        echo "<th>{$field}</th>";
    }
    echo "</tr>";

    // Fetch and output data
    foreach ($reportData as $row) {
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>{$value}</td>";
        }
        echo "</tr>";
    }
    
    echo "</table>";

    // Capture the buffered output
    $reportOutput = ob_get_clean(); // Get the output buffer and clean it

    // Set the headers for the browser to download the report as an Excel file
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=\"report_{$reportType}.xls\"");
    
    // Output the generated report
    echo $reportOutput;

    // Unset session variables after exporting
    unset($_SESSION['reportData']);
    unset($_SESSION['reportType']);
    unset($_SESSION['fields']);

    // End the script and send the response
    exit; 
}

// Get the summary data
$summary = getMonthlySummary($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Advanced Reporting</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Basic styling for the admin interface */
        body {
            font-family: 'Arial', sans-serif;
            background-color:  #e0f7ff;; /* Soft light blue background */
            margin: 0;
            padding: 0;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: auto;
            margin-top: 50px;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        h1, h2 {
            color: #03045e ; /* Light blue for headings */
            margin-bottom: 20px;
        }

        .summary-section {
            background: #e0f7ff; /* Light cyan background for summary */
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .summary-items {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: space-between;
        }

        .summary-item {
            background: #007bff; /* Blue background for summary items */
            color: white;
            font-weight: 600;
            padding: 20px;
            border-radius: 8px;
            flex: 1 1 150px; /* Grow, shrink, and set base width */
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: background-color 0.3s ease-in-out;
        }

        .summary-item:hover {
            background: #0056b3  ; /* Darker blue on hover */
        }

        .filters {
            margin-bottom: 30px;
            background-color: #ffffff;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .filters label {
            font-weight: bold;
            color: #333;
        }

        .filters select,
        .filters button {
            padding: 12px 18px;
            border: 1px solid #007bff; /* Light blue border */
            border-radius: 5px;
            font-size: 16px;
            margin-top: 10px;
        }

        .filters select:focus,
        .filters button:focus {
            outline: none;
            border-color: #0056b3;
        }

        .filters button {
            background-color: #007bff;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s ease-in-out;
        }

        .filters button:hover {
            background-color: #0056b3;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            text-align: left;
        }

        th, td {
            padding: 12px;
            border: 1px solid #ddd;
        }

        th {
            background-color: #007bff;
            color: white;
        }

        td {
            background-color: #f9f9f9;
        }

        a.button {
            display: inline-block;
            padding: 12px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
            transition: background-color 0.3s ease;
        }

        a.button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Advanced Reporting</h1>

    <div class="summary-section">
        <h2>Monthly Summary</h2>
        <div class="summary-items">
            <div class="summary-item">
                <p>Total Users</p>
                <p><?= $summary['users'] ?></p>
            </div>
            <div class="summary-item">
                <p>Total Doctors</p>
                <p><?= $summary['doctors'] ?></p>
            </div>
            <div class="summary-item">
                <p>Total Pets</p>
                <p><?= $summary['pets'] ?></p>
            </div>
            <div class="summary-item">
                <p>Total Appointments</p>
                <p><?= $summary['appointments'] ?></p>
            </div>
            <div class="summary-item">
                <p>Total Consultations</p>
                <p><?= $summary['consultations'] ?></p>
            </div>
            <div class="summary-item">
                <p>Total Hostel Requests</p>
                <p><?= $summary['hostels'] ?></p>
            </div>
            <div class="summary-item">
                <p>Total Payments</p>
                <p><?= number_format($summary['payments'], 2) ?></p>
            </div>
        </div>
    </div>

    <div class="filters">
        <form method="POST" action="">
            <label for="report_type">Select Report Type:</label>
            <select name="report_type" id="report_type" required>
                <option value="appointments">Appointments</option>
                <option value="consultations">Consultations</option>
                <option value="hostels">Hostels</option>
                <option value="users">Users</option>
                <option value="doctors">Doctors</option>
                <option value="admin">Admin</option>
            </select>
            <button type="submit" name="generate_report">Generate Report</button>
        </form>
    </div>

    <?php if (isset($_SESSION['reportData'])): ?>
        <h2>Generated Report</h2>
        <table>
            <thead>
                <tr>
                    <?php foreach ($_SESSION['fields'] as $field): ?>
                        <th><?= ucfirst(str_replace('_', ' ', $field)) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($_SESSION['reportData'] as $row): ?>
                    <tr>
                        <?php foreach ($row as $column): ?>
                            <td><?= htmlspecialchars($column) ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <a href="?export=true" class="button">Export Report</a>
    <?php endif; ?>
</div>

</body>
</html>

<?php
include_once "../footer.php";
?>