<?php 
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: userLogin.php");
    exit();
}

include_once "../connection.php";
//header
include_once "header_user.php";

$user_id = $_SESSION['user_id'];



// Query to get the user's first name
$query = "SELECT user_first_name FROM user WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$user_first_name = $user['user_first_name']; // Assuming the user's name is stored in 'user_name'
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="../afterLoginUser_style/home.css" type="text/css">
    
</head>
<body>



<div class="container">
    <div class="about2">
    <img src="../images/WhatsApp Image 2024-10-29 at 16.20.41_006636d2.jpg">
        <h2>Hello <?php echo htmlspecialchars($user_first_name); ?>, Welcome to PetHug</h2>
        <p>Manage all your pet needs conveniently. Whether it's scheduling an appointment, requesting consultations, or finding a comfy place for your pet to stay, we have you covered.</p>
    </div>


    <div class="cards">
    <div class="card">
            <img src="../images/appointment.png" alt="My Appointments">
            <h3>My Appointments</h3>
            <p>View your upcoming appointments and manage them easily.</p>
            <a href="my_appointments.php" class="btn">View Appointments</a>
        </div>
        <div class="card">
            <img src="../images/consultation.png" alt="My Consultations">
            <h3>My Consultations</h3>
            <p>Review past consultations and schedule new ones for your pets.</p>
            <a href="my_consultations.php" class="btn">View Consultations</a>
        </div>
        <div class="card">
            <img src="../images/hostel.png" alt="My Hostel">
            <h3>My Hostel</h3>
            <p>See your pet's hostel bookings and manage stays.</p>
            <a href="my_hostel.php" class="btn">Manage Hostel</a>
        </div>
        <div class="card">
            <img src="../images/pets.png" alt="My Pets">
            <h3>My Pets</h3>
            <p>View and manage your pets’ profiles, health records, and more.</p>
            <a href="my_pets.php" class="btn">View Pets</a>
        </div>
        <div class="card">
            <img src="../images/payments.png" alt="My Payments">
            <h3>My Payments</h3>
            <p>Keep track of your payments and transactions for services.</p>
            <a href="bill.php" class="btn">View Payments</a>
        </div>

        <div class="card">
            <img src="../images/medical-record.png" alt="pet records">
            <h3>Medical Records</h3>
            <p></p>
            <a href="medical_records.php" class="btn">View records</a>
        </div>
    </div>
</div>

</body>
</html>

<?php
include_once "../footer.php";
?>
