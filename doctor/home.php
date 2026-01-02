<?php
session_start();
if (!isset($_SESSION['dr_id'])) {
    header("Location: doctorLogin.php");
    exit();
}

require '../connection.php'; // Include the database connection file
include_once 'header_dr.php';

$doctor_id = $_SESSION['dr_id']; // Assign doctor_id from session before using it in query

$query = "SELECT dr_name FROM doctor WHERE dr_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$result = $stmt->get_result();
$doctor = $result->fetch_assoc();

if ($doctor) {
    $doctor_name = $doctor['dr_name']; // Fetch the doctor's name if available
} else {
    $doctor_name = "Doctor"; // Fallback if no doctor is found
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Home - PetHug</title>
    <link rel="stylesheet" href="../afterLoginUser_style/home.css" type="text/css">
  
</head>
<body>

<div class="container">
    <div class="about">
        <img src="../images/banner_wellness2.jpg" alt="doctor">
        <h2>Welcome,  <?php echo htmlspecialchars($doctor_name); ?></h2>
        <p>Manage your appointments, consultations, earnings, and reports efficiently with PetHug.</p>
    </div>
    

    <div class="cards">
        <div class="card">
            <img src="../images/appointment.png" alt="Appointments">
            <h3>Assigned Appointments</h3>
            <p>View and manage your upcoming and past appointments.</p>
            <a href="assigned_appointments.php">Go to Appointments</a>
        </div>
        <div class="card">
            <img src="../images/consultation.png" alt="Consultation">
            <h3>Give Consultation</h3>
            <p>Provide quality consultations and track patient history.</p>
            <a href="give_consultation.php">Provide Consultation</a>
        </div>
        <div class="card">
            <img src="../images/wallet.png" alt="Earnings">
            <h3>Check Earnings</h3>
            <p>View and track your earnings from consultations and treatments.</p>
            <a href="doctor_earnings.php">View Earnings</a>
        </div>
        <div class="card">
            <img src="../images/reports.png" alt="Reports">
            <h3>Get Reports</h3>
            <p>Generate detailed reports of your activity and consultations.</p>
            <a href="generate_doctor_report.php">Generate Reports</a>
        </div>
    </div>
</div>

</body>
</html>

<?php
include_once "../footer.php";
?>