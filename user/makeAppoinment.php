<?php
session_start();
include_once '../connection.php'; // Include the database connection file

if (!isset($_SESSION['user_id'])) {
    header("Location: userLogin.php");
    exit();
}


include_once 'header_user.php';

// Fetch pets and veterinarians for the dropdowns
$user_id = $_SESSION['user_id'];
$petQuery = "SELECT pet_id, pet_name FROM pet WHERE user_id = ?";
$petStmt = $conn->prepare($petQuery);
$petStmt->bind_param("i", $user_id);
$petStmt->execute();
$petResult = $petStmt->get_result();

$vetQuery = "SELECT dr_id, dr_name FROM doctor";
$vetStmt = $conn->prepare($vetQuery);
$vetStmt->execute();
$vetResult = $vetStmt->get_result();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pet_id = $_POST['pet_id'];
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];
    $reason = $_POST['reason'];
    $vet_id = $_POST['dr_id'];

    $datetime = date('Y-m-d H:i:s', strtotime("$appintment_date $appointment_time"));
    // Insert the appointment into the database
    $insertQuery = "INSERT INTO appointment (pet_id, user_id, doctor_id, appointment_time, appointment_reason) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param("iiiss", $pet_id, $user_id, $vet_id, $datetime, $reason);

    if ($stmt->execute()) {
        $success_message = "Appointment successfully booked!";
        header("Location:my_appointments.php");
        exit();
    } else {
        $error_message = "Error booking the appointment: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Make an Appointment</title>
    <link rel="stylesheet" href="../afterLoginUser_style/app_con_hos.css" type="text/css">
    
    <script>
        function validateForm() {
            var pet_id = document.forms["appointmentForm"]["pet_id"].value;
            var appointment_date = document.forms["appointmentForm"]["appointment_date"].value;
            var appointment_time = document.forms["appointmentForm"]["appointment_time"].value;
            var reason = document.forms["appointmentForm"]["appointment_reason"].value;
            var vet_id = document.forms["appointmentForm"]["dr_id"].value;

            if (pet_id == "" || appointment_date == "" || appointment_time == "" || reason == "" || vet_id == "") {
                alert("Please fill out all fields.");
                return false;
            }
            return true;
        }
    </script>
</head>
<body>

<div class="container">
    <h2>Make an Appointment</h2>

    <?php if (isset($success_message)) { echo "<div class='success'>$success_message</div>"; } ?>
    <?php if (isset($error_message)) { echo "<div class='error'>$error_message</div>"; } ?>

    <form name="appointmentForm" method="POST" onsubmit="return validateForm()">
        <div>
            <label for="pet_id">Select Pet:</label>
            <select name="pet_id" required>
                <option value="">-- Choose Pet --</option>
                <?php while ($pet = $petResult->fetch_assoc()) { ?>
                    <option value="<?php echo $pet['pet_id']; ?>"><?php echo $pet['pet_name']; ?></option>
                <?php } ?>
            </select>
        </div>
        <div>
            <label for="appointment_date">Appointment Date:</label>
            <input type="date" name="appointment_date" required>
        </div>
        <div>
            <label for="appointment_time">Appointment Time:</label>
            <input type="time" name="appointment_time" required>
        </div>
        <div>
            <label for="reason">Reason for Appointment:</label>
            <textarea name="reason" rows="4" required></textarea>
        </div>
        <div>
            <label for="dr_id">Select Veterinarian:</label>
            <select name="dr_id" required>
                <option value="">-- Choose Veterinarian --</option>
                <?php while ($vet = $vetResult->fetch_assoc()) { ?>
                    <option value="<?php echo $vet['dr_id']; ?>"><?php echo $vet['dr_name']; ?></option>
                <?php } ?>
            </select>
        </div>
        <div>
            <button type="submit">Book Appointment</button>
        </div>
    </form>
</div>

</body>
</html>
<?php
include_once "../footer.php";
?>
<?php $conn->close(); ?>