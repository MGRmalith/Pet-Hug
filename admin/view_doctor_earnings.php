<?php
session_start();
include_once "../connection.php";
//header
include_once "header_admin.php"; 

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: adminLogin.php");
    exit();
}

$current_date = date('Y-m-d'); // Current date

// Get all doctors
$queryDoctors = "SELECT dr_id FROM doctor";
$doctorsResult = mysqli_query($conn, $queryDoctors);

if (!$doctorsResult) {
    die("Error fetching doctors: " . mysqli_error($conn));
}

// Loop through each doctor to calculate earnings
while ($doctor = mysqli_fetch_assoc($doctorsResult)) {
    $doctor_id = $doctor['dr_id'];

    // Sum of earnings from appointments for the specific doctor
    $query = "
        SELECT SUM(d.dr_fee) AS appointment_earnings
        FROM appointment a
        JOIN doctor d ON a.doctor_id = d.dr_id
        WHERE a.status = 'Completed' AND DATE(appointment_time) = ? AND a.doctor_id = ?
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $current_date, $doctor_id); // Bind current date and doctor ID
    $stmt->execute();
    $result = $stmt->get_result();
    $appointmentEarnings = 0;
    if ($result && $row = $result->fetch_assoc()) {
        $appointmentEarnings = $row['appointment_earnings'] ?? 0;
    }else {
        echo "Error fetching appointment earnings: " . $stmt->error;
    }

    // Sum of earnings from consultations for the specific doctor
    $query = "
        SELECT SUM(d.dr_fee) AS consultation_earnings
        FROM consultation c
        JOIN doctor d ON c.dr_id = d.dr_id
        WHERE c.status = 'Completed' AND DATE(consultation_time) = ? AND c.dr_id = ?
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $current_date, $doctor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $consultationEarnings = 0;
    if ($result && $row = $result->fetch_assoc()) {
        $consultationEarnings = $row['consultation_earnings'] ?? 0;
    } else {
        echo "Error fetching consultation earnings: " . $stmt->error;
    }

    // Sum of earnings from hostel supervision for the specific doctor
    $query = "
        SELECT SUM(dr_supervision_fee) AS hostel_earnings
        FROM hostel
        WHERE status = 'Completed' AND ? BETWEEN start_date AND end_date AND dr_id = ?
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $current_date, $doctor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $hostelEarnings = 0;
    if ($result && $row = $result->fetch_assoc()) {
        $hostelEarnings = $row['hostel_earnings'] ?? 0;
    }else {
        echo "Error fetching hostel earnings: " . $stmt->error;
    }

    // Total earnings for the doctor on the current date
    $totalEarnings = $appointmentEarnings + $consultationEarnings + $hostelEarnings;

    // Insert or update daily earnings for the specific doctor
    $insertQuery = "
        INSERT INTO doctor_daily_earnings (doctor_id, earnings_date, appointment_earnings, consultation_earnings, hostel_earnings, total_earnings)
        VALUES (?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
            appointment_earnings = VALUES(appointment_earnings),
            consultation_earnings = VALUES(consultation_earnings),
            hostel_earnings = VALUES(hostel_earnings),
            total_earnings = VALUES(total_earnings);
    ";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param("isdddd", $doctor_id, $current_date, $appointmentEarnings, $consultationEarnings, $hostelEarnings, $totalEarnings);
    //$stmt->execute();

    if (!$stmt->execute()) {
        echo "Error updating earnings: " . $stmt->error;
    }
}

// Search functionality
$search_doctor_id = null;
if (isset($_POST['search'])) {
    $search_doctor_id = $_POST['doctor_id'];

    // Fetch earnings data for the specified doctor_id
    $query = "
    SELECT earnings_date, appointment_earnings, consultation_earnings, hostel_earnings, total_earnings
    FROM doctor_daily_earnings
    WHERE doctor_id = ? 
    ORDER BY earnings_date DESC
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $search_doctor_id); 
    $stmt->execute();
    $result = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Doctor's Daily Earnings</title>
    <link rel="stylesheet" href="../afterLoginAdmin_style/view_doctor_earnings.css"> 
</head>
<body>

<div class="container">
    <h1>View Doctor's Daily Earnings</h1>

    <form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
        <label for="doctor_id">Enter Doctor ID:</label>
        <input type="number" id="doctor_id" name="doctor_id" required>
        <button type="submit" name="search">Search</button>
    </form>

    <?php
        if (isset($_POST['search']) && isset($result) && mysqli_num_rows($result) > 0) {
            echo "<h3>Earnings for Doctor ID: " . $search_doctor_id . "</h3>";
            echo "<table>
                    <tr>
                        <th>Date</th>
                        <th>Appointment Earnings (Rs.)</th>
                        <th>Consultation Earnings (Rs.)</th>
                        <th>Hostel Earnings (Rs.)</th>
                        <th>Total Earnings (Rs.)</th>
                    </tr>";
        
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>
                        <td>" . $row['earnings_date'] . "</td>
                        <td>" . number_format($row['appointment_earnings'], 2) . "</td>
                        <td>" . number_format($row['consultation_earnings'], 2) . "</td>
                        <td>" . number_format($row['hostel_earnings'], 2) . "</td>
                        <td>" . number_format($row['total_earnings'], 2) . "</td>
                      </tr>";
            }
            echo "</table>";
        } else if (isset($search_doctor_id)) {
            echo "<p>No earnings data found for Doctor ID: " . $search_doctor_id . "</p>";
        }        
    ?>
</div>

</body>
</html>

<?php include_once "../footer.php"; ?>
<?php $conn->close(); ?>    