<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: userLogin.php");
    exit();
}

include_once '../connection.php';
include_once 'header_user.php';

$user_id = $_SESSION['user_id'];

// Fetch appointments based on status
$query = "SELECT a.appointment_id, a.appointment_time, a.appointment_reason, a.status, p.pet_name, d.dr_name, p.pet_image 
          FROM appointment a
          JOIN pet p ON a.pet_id = p.pet_id
          JOIN doctor d ON a.doctor_id = d.dr_id
          WHERE a.user_id = ? 
          ORDER BY a.appointment_time ASC";

$stmt = $conn->prepare($query);
if ($stmt === false) {
    die("Error preparing the SQL statement: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Handle appointment actions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $appointment_id = $_POST['appointment_id'];

    if (isset($_POST['cancel_appointment'])) {
        $cancelQuery = "UPDATE appointment SET status='Canceled' WHERE appointment_id = ? AND user_id = ? AND status='Pending'";
        $cancelStmt = $conn->prepare($cancelQuery);
        $cancelStmt->bind_param("ii", $appointment_id, $user_id);

        if ($cancelStmt->execute()) {
            $cancel_message = "Appointment successfully cancelled!";
            header("location: my_appointments.php");
            exit();
        } else {
            $error_message = "Error cancelling appointment.";
        }
    }

    if (isset($_POST['reschedule_appointment'])) {
        $new_date = $_POST['date'];
        $new_time = $_POST['time'];
        $new_details = $_POST['details'];
        $new_datetime = $new_date . " " . $new_time;

        $rescheduleQuery = "UPDATE appointment SET appointment_time = ?, appointment_reason = ?, status = 'Pending' 
                            WHERE appointment_id = ? AND user_id = ? AND status='Canceled'";
        $rescheduleStmt = $conn->prepare($rescheduleQuery);
        $rescheduleStmt->bind_param("ssii",$new_datetime , $new_details, $appointment_id, $user_id);

        if ($rescheduleStmt->execute()) {
            $success_message = "Appointment successfully rescheduled!";
            header("location: my_appointments.php");
            exit();
        } else {
            $error_message = "Error rescheduling appointment.";
        }
    }
    if(isset($_POST['delete_appointment'])) {

        $deleteQuery = "DELETE FROM appointment WHERE appointment_id = ? AND user_id = ?";
        $deleteStmt = $conn->prepare($deleteQuery);
        $deleteStmt->bind_param("ii", $appointment_id, $user_id);
        if ($deleteStmt->execute()) {
            $success_message = "Appointment successfully deleted!";
            header("location: my_appointments.php");
            exit();
        } else {
            $error_message = "Error deleting appointment.";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Appointments</title>
    <style>

        body {
            background-color: #e0f7ff;
             font-family: 'Arial', sans-serif; 
             color: #333; 
             margin: 0; 
             padding: 0; 
            }

        .container { 
            max-width: 1200px; 
            margin: 50px auto; 
            padding: 20px; 
            background-color: #fff; 
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); 
            border-radius: 10px; 
            
        }
        .about{
            position: relative;
            width: 100%;
            margin: 0 auto;
            
        }
        .about-image{
            display: block;
            margin-left: auto;
            margin-right: auto;
            width: 100%;
            position: relative;
            opacity: 0.9;
            border-radius: 10px;   
            filter: brightness(70%);
            
        }
        .container h2 { 
            position: absolute;
            color:  #333;
            top: 6vh;
            width: 100%;
            text-align: center; 
            font-size: 44px;
            z-index: 1;
        }
        .about-text{
            position: absolute;
            top: 16vh; 
            color: black;
            font-size: 20px;
            left: 2vw;
        }
        .container h3{
            width: 100%;
            text-align: center; 
            margin-top: 40px; 
            font-size: 28px;
            color: #007bff;
        }

        .table-container {
            max-height: 400px;
            overflow-y: auto;
        }

        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px; 
            margin-bottom: 20px;
        }
        
        table, th, td { 
            border: 1px solid #ccc; 
        }

        th, td { 
            padding: 10px; 
            text-align: center; 
        }

        th { 
            background-color: #007bff; 
            color: white; 
            position: sticky;
            top: 0;
            z-index: 1;
        }

        td img { 
            max-width: 50px; 
            height: auto; 
            border-radius: 50%; 
        }

        .container button { 
            padding: 10px 15px; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer; 
        }

        .cancel-btn { 
            background-color: #ff4500; 
            color: white; 
        }

        .pay-btn { 
            padding: 10px 15px; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer; 
            background-color: #28a745; 
            color: white;
        }

        .reschedule-btn { 
            background-color: #ff6347; 
            color: white; 
        }

        .message { 
            text-align: center; 
            margin-bottom: 20px; 
            color: #555; 
            font-size: 1.2em; 
        }

        .new-appointment-btn {
            margin-top: 20px;
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 10px;
            font-size: 22px;
        }

        .new-appointment-btn:hover {
            background-color: #0056b3;
        }

        .create-btn-container { 
            position: absolute;
            width: 100%;
            text-align: center; 
            top: 40vh; 
        }
        .delete-btn {
            background-color: #dc3545;
            color: white;
            margin-top: 3px;
        }

        /* Modal styles */
        .modal {
            display: none; 
            position: fixed; 
            z-index: 1; 
            left: 0; 
            top: 0; 
            width: 100%; 
            height: 100%; 
            overflow: auto; 
            background-color: rgb(0,0,0); 
            background-color: rgba(0,0,0,0.4); 
            padding-top: 60px; 
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%; 
            max-width: 500px; 
        }


        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
    <script>
        function confirmCancel() {
            return confirm("Are you sure you want to cancel this appointment?");
        }

        function openModal(appointmentId, currentDate, currentTime, currentDetails) {
            document.getElementById("appointment_id").value = appointmentId;
            document.getElementById("date").value = currentDate;
            document.getElementById("time").value = currentTime;
            document.getElementById("details").value = currentDetails;
            document.getElementById("myModal").style.display = "block";
        }

        function closeModal() {
            document.getElementById("myModal").style.display = "none";
        }

        window.onclick = function(event) {
            var modal = document.getElementById("myModal");
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</head>
<body>

<div class="container">
    <div class="about">
    <h2>Your Appointments</h2>
    <img class="about-image" src="../images/dog-1861839_1280.jpg" alt="Pet" >
    <p class="about-text">Welcome to the PetHug, where taking care of your pet’s health is simple and convenient. From this page, you can quickly create new appointments with our dedicated veterinary team or manage existing ones to fit your schedule. Whether your pet needs a check-up, a vaccination, or a specialized consultation, our streamlined system makes it easy to stay on top of every health appointment. Book with confidence and manage with flexibility—all in one place.</p>    
    <div class="create-btn-container">
        <a href="makeAppointment.php" class="new-appointment-btn">Create New Appointment</a>
    </div>
    </div>
    
    
    <?php if (isset($cancel_message)) { echo "<div class='success'>$cancel_message</div>"; } ?>
    <?php if (isset($error_message)) { echo "<div class='error'>$error_message</div>"; } ?>

    <?php
// Add the 'Accepted' status to the statuses array.
$statuses = ['Pending', 'Accepted', 'Canceled'];

// Loop through the statuses to fetch and display appointments
foreach ($statuses as $status) {
    $queryByStatus = "SELECT a.appointment_id, a.appointment_time, a.appointment_reason, a.status, p.pet_name, d.dr_name, p.pet_image 
                      FROM appointment a
                      JOIN pet p ON a.pet_id = p.pet_id
                      JOIN doctor d ON a.doctor_id = d.dr_id
                      WHERE a.user_id = ? AND a.status = ?
                      ORDER BY a.appointment_time ASC";

    $stmtByStatus = $conn->prepare($queryByStatus);
    $stmtByStatus->bind_param("is", $user_id, $status);
    $stmtByStatus->execute();
    $resultByStatus = $stmtByStatus->get_result();

    if ($resultByStatus->num_rows > 0) { ?>
        <h3><?php echo $status; ?> Appointments</h3>
        <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Appointment ID</th>
                    <th>Pet</th>
                    <th>Doctor</th>
                    <th>Appointment Date</th>
                    <th>Time</th>
                    <th>Details</th>
                    <?php if ( $status !== 'Accepted') { ?>
                    <th>Actions</th>
                    <?php } ?>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $resultByStatus->fetch_assoc()) { 
                    $datetime = new DateTime($row['appointment_time']);
                    $date = $datetime->format("Y-m-d"); 
                    $time = $datetime->format("H:i:s"); 
                ?>
                    <tr>
                        <td><?php echo $row['appointment_id']; ?></td>
                        <td><img src="<?php echo $row['pet_image']; ?>" alt="<?php echo $row['pet_name']; ?>"><?php echo $row['pet_name']; ?></td>
                        <td><?php echo $row['dr_name']; ?></td>
                        <td><?php echo $date; ?></td>
                        <td><?php echo $time; ?></td>
                        <td><?php echo $row['appointment_reason']; ?></td>
                        <?php if ($status === 'Pending') { ?>
                        <td>
                            <form method="POST" onsubmit="return confirmCancel();">
                                <input type="hidden" name="appointment_id" value="<?php echo $row['appointment_id']; ?>">
                                <button type="submit" name="cancel_appointment" class="cancel-btn">Cancel</button>
                            </form>
                        </td>
                        <?php } elseif ($status === 'Canceled') { ?>
                        <td>
                        <button onclick="openModal('<?php echo $row['appointment_id']; ?>', '<?php echo $date; ?>', '<?php echo $time; ?>', '<?php echo $row['appointment_reason']; ?>')" class="reschedule-btn">Reschedule</button>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="appointment_id" value="<?php echo $row['appointment_id']; ?>">
                            <button type="submit" name="delete_appointment" class="delete-btn">Delete</button>
                        </form>
                        </td>
                        <?php } ?>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        </div>
        <?php } else { ?>
        <h3>No <?php echo $status; ?> Appointments Found</h3>
    <?php }
}
?>    
</div>

<!-- Reschedule Modal -->
<div id="myModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>Reschedule Appointment</h2><br>
        <form method="POST">
            <input type="hidden" name="appointment_id" id="appointment_id">
            <label for="date">New Date:</label>
            <input type="date" name="date" id="date" required><br><br>
            <label for="time">New Time:</label>
            <input type="time" name="time" id="time" required><br><br>
            <label for="details">New Details:</label>
            <textarea name="details" id="details" required></textarea><br><br>
            <button type="submit" class="pay-btn" name="reschedule_appointment">Reschedule Appointment</button>
        </form>
    </div>
</div>

</body>
</html>
<?php
include_once "../footer.php";
?>