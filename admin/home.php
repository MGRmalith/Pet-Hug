<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: adminLogin.php");
    exit();
}
include_once 'header_admin.php';
// Database connection
require '../connection.php';

// Admin ID from session
$admin_id = $_SESSION['admin_id'];

// Query to get the admin's name
$query = "SELECT admin_name FROM admin WHERE admin_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

$admin_name = $admin['admin_name']; // Assuming the admin's name is stored in 'admin_name'
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - PetHug</title>
    <link rel="stylesheet" href="../afterLoginUser_style/home.css" type="text/css">
</head>
<body>

<div class="container">
    <div class="about3">
    <img src="../images/WhatsApp Image 2024-10-29 at 16.20.42_50903714.jpg">
    <h2>Hello <?php echo htmlspecialchars($admin_name); ?>,</h2>
    <p>Manage all users, doctors, appointments, hostels, consultations, payments, and reports from here.</p>

    </div>
   
    <div class="cards">
        <div class="card">
            <img src="../images/user_management.png" alt="User Management">
            <h3>User Management</h3>
            <p>Manage registered users on the platform.</p>
            <a href="admin_user_management.php">Manage Users</a>
        </div>
        <div class="card">
            <img src="../images/doctor_management.png" alt="Doctor Management">
            <h3>Doctor Management</h3>
            <p>Manage registered doctors on the platform.</p>
            <a href="admin_doctor_management.php">Manage Doctors</a>
        </div>
        <div class="card">
            <img src="../images/appointment.png" alt="manage_appointments">
            <h3>Appointment Management</h3>
            <p>Manage appointments made by users.</p>
            <a href="admin_appointment_management.php">Manage Appointments</a>
        </div>
        <div class="card">
            <img src="../images/hostel.png" alt="manage_hostels">
            <h3>Hostel Management</h3>
            <p>Manage hostel requests for pet boarding.</p>
            <a href="hostel_management.php">Manage Hostels</a>
        </div>
        <div class="card">
            <img src="../images/consultation.png" alt="manage_consultations">
            <h3>Consultation Management</h3>
            <p>Manage consultation requests for pets.</p>
            <a href="admin_consultation_management.php">Manage Consultations</a>
        </div>
        <div class="card">
            <img src="../images/payments.png" alt="manage_payments">
            <h3>Calculate Payment</h3>
            <p>Calculate payments for services and doctors.</p>
            <a href="payment_management.php">Manage Payments</a>
        </div>
        <div class="card">
            <img src="../images/medical-record.png" alt="Get report">
            <h3>Get Reports</h3>
            <p>Generate and view reports for the platform.</p>
            <a href="generate_reports.php">Get Reports</a>
        </div>

       
        <div class="card">
            <img src="../images/message.png" alt="contact">
            <h3>Manage Contact Message</h3>
           
            <a href="admin_contact.php">Contact Messages</a>
        </div>
        <div class="card">
            <img src="../images/feedback.png" alt="feedback">
            <h3>Manage Feedback</h3>
            
            <a href="admin_feedback.php">Feedback</a>
        </div>

      
    </div>
</div>

</body>
</html>
<?php
include_once "../footer.php";

?>