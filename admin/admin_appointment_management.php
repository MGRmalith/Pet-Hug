<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: adminLogin.php");
    exit();
}
include_once 'header_admin.php';
require '../connection.php';

if (isset($_GET['cancel_success'])) {
    echo "<script>alert('Appointment cancelled successfully.');</script>";
} elseif (isset($_GET['cancel_error'])) {
    echo "<script>alert('Error cancelling appointment.');</script>";
}

// Fetch all appointments, sorted by date
$query = "SELECT *
FROM appointment
INNER JOIN doctor ON appointment.doctor_id = doctor.dr_id
ORDER BY appointment.appointment_time ASC";
$result = $conn->query($query);

// Handle sending reminders
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['send_reminder'])) {
    $appointment_id = $_POST['appointment_id'];
    
    // Fetch appointment details
    $appointmentQuery = "SELECT * FROM appointment WHERE appointment_id=?";
    $stmt = $conn->prepare($appointmentQuery);
    $stmt->bind_param("i", $appointment_id);
    $stmt->execute();
    $appointmentResult = $stmt->get_result();
    $appointment = $appointmentResult->fetch_assoc();

    $datetime = new DateTime($appointment['appointment_time']);
    $date = $datetime->format("Y-m-d"); 
    $time = $datetime->format("H:i:s");  
    
    if ($appointment) {
        // Determine the recipient type and notification message
        if ($appointment['status'] == 'Accepted') {
            $recipient_id = $appointment['user_id'];
            $recipient_type = 'user';
            $title = "Appointment Reminder";
            $message = "You have an appointment scheduled on " . $date . " at " . $time . ".";
        } elseif ($appointment['status'] == 'Pending') {
            $recipient_id = $appointment['doctor_id'];
            $recipient_type = 'doctor';
            $title = "Appointment Reminder to Doctor";
            $message = "You have a pending appointment on " . $date . " at " . $time . ". Please review.";
        } else {
            // For 'Cancelled' appointments, we can send a reminder to the user
            $recipient_id = $appointment['user_id'];
            $recipient_type = 'user';
            $title = "Cancelled Appointment Reminder";
            $message = "Your appointment scheduled on " . $date . " at " . $time . " has been cancelled.";
        }

        // Prepare and insert notification
        $insertNotification = "INSERT INTO notifications (recipient_type, recipient_id, title, message,service_type, service_id) VALUES (?, ?, ?, ?, 'appointment', ?)";
        $insertStmt = $conn->prepare($insertNotification);
        $insertStmt->bind_param("sissi", $recipient_type, $recipient_id, $title, $message, $appointment_id);

        if ($insertStmt->execute()) {
            // Update appointment to mark reminder as sent
            $updateQuery = "UPDATE appointment SET reminder_sent = 1 WHERE appointment_id = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("i", $appointment_id);
            $updateStmt->execute();

            if ($updateStmt->execute()) {
                echo "<script>alert('Reminder sent successfully.');
                </script>";
                header("Location: " . $_SERVER['PHP_SELF'] . "?sent_success=1");
                exit();
            } else {
                echo "<script>alert('Error sending reminder.');</script>";
                header("Location: " . $_SERVER['PHP_SELF'] . "?sent_error=1");
                exit();
            }
            $updateStmt->close();  // Close the update statement
        
        }
    
    }
}

// Handle appointment cancellation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cancel_appointment'])) {
    $appointment_id = $_POST['appointment_id'];
    
    // Update appointment to mark status as 'Cancelled'
    $updateQuery = "UPDATE appointment SET status = 'Canceled', reminder_sent = 0 WHERE appointment_id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("i", $appointment_id);
    
    if ($updateStmt->execute()) {
        // Redirect to the same page after cancellation
        header("Location: " . $_SERVER['PHP_SELF'] . "?cancel_success=1");
        exit();
    } else {
        // Handle failure
        header("Location: " . $_SERVER['PHP_SELF'] . "?cancel_error=1");
        exit();
    }

    $updateStmt->close(); // Close the prepared statement if needed
}

// Store all appointments for display
$acceptedAppointments = [];
$pendingAppointments = [];
$rejectedAppointments = [];

// Classify appointments based on their status
while ($row = $result->fetch_assoc()) {
    switch ($row['status']) { 
        case 'Accepted':
            $acceptedAppointments[] = $row;
            break;
        case 'Pending':
            $pendingAppointments[] = $row;
            break;
        case 'Canceled':
            $rejectedAppointments[] = $row;
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Appointment Management - PetHug</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #e0f7ff;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1200px;
            margin: auto;
            margin-top: 50px;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            color: #333;
            font-size: 30px;
        }
        .table-container {
            max-height: 400px;
            overflow-y: auto;
        }
        h3{
            text-align: center;
            font-size: 24px;
            color: #007bff;
            margin-top: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px; 
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
       
        .cancel-btn {
             padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 3px;
            background-color: #ff4500;
            color: white;
        }
        .reminder-btn {
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 3px;
            background-color: #3498db;
            color: white;
        }
    </style>
    <script>
    function confirmCancel(appointment_id) {
        if (confirm("Are you sure you want to cancel this appointment?")) {
            document.getElementById("appointment_id").value = appointment_id; // Set the ID
            document.getElementById("cancelForm").submit(); // Submit the cancel form
        }
    }
</script>
</head>
<body>

<div class="container">
    <h2>Appointment Management</h2><br>

    <!-- Accepted Appointments -->
    <h3>Accepted Appointments</h3>
    <div class="table-container">
        <table>
            <tr>
                <th>Appointment ID</th>
                <th>Date</th>
                <th>Time</th>
                <th>Details</th>
                <th>User ID</th>
                <th>Pet ID</th>
                <th>Doctor ID</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($acceptedAppointments as $row) { 
                $datetime = new DateTime($row['appointment_time']);
                $date = $datetime->format("Y-m-d"); 
                $time = $datetime->format("H:i:s");                     
            ?>
                <tr>
                    <td><?php echo $row['appointment_id']; ?></td>
                    <td><?php echo $date; ?></td>
                    <td><?php echo $time; ?></td>
                    <td><?php echo $row['appointment_reason']; ?></td>
                    <td><?php echo $row['user_id']; ?></td>
                    <td><?php echo $row['pet_id']; ?></td>
                    <td><?php echo $row['dr_id']; ?></td>
                    <td>
                    <button class="cancel-btn" onclick="confirmCancel('<?php echo $row['appointment_id']; ?>')">Cancel</button>
                        <form method="POST" style="display:inline-block;">
                            <input type="hidden" name="appointment_id" value="<?php echo $row['appointment_id']; ?>">
                            <?php if (!$row['reminder_sent']) { ?>
                                <button type="submit" name="send_reminder" class="reminder-btn">Send Reminder</button>
                            <?php } ?>
                        </form>
                        <!-- Check if the fee is set -->
                        <?php if (empty($row['appointment_fee'])) { ?>
                            <form action="admin_set_fees.php" method="GET" style="display:inline-block;">
                                <input type="hidden" name="appointment_id" value="<?php echo $row['appointment_id']; ?>">
                                <button type="submit" class="reminder-btn" style="background-color: #28a745;">Set Fee</button>
                            </form>
                        <?php } else { ?>
                            <span>Fee Set</span>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div><br><br>

    <!-- Pending Appointments -->
    <h3>Pending Appointments</h3>
    <div class="table-container">
        <table>
            <tr>
                <th>Appointment ID</th>
                <th>Date</th>
                <th>Time</th>
                <th>Details</th>
                <th>User ID</th>
                <th>Pet ID</th>
                <th>Doctor ID</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($pendingAppointments as $row) { 
                $datetime = new DateTime($row['appointment_time']);
                $date = $datetime->format("Y-m-d"); 
                $time = $datetime->format("H:i:s");  
                
            ?>
                <tr>
                    <td><?php echo $row['appointment_id']; ?></td>
                    <td><?php echo $date; ?></td>
                    <td><?php echo $time; ?></td>
                    <td><?php echo $row['appointment_reason']; ?></td>
                    <td><?php echo $row['user_id']; ?></td>
                    <td><?php echo $row['pet_id']; ?></td>
                    <td><?php echo $row['dr_id']; ?></td>
                    <td>
                    <button class="cancel-btn" onclick="confirmCancel('<?php echo $row['appointment_id']; ?>')">Cancel</button>
                        <form method="POST" style="display:inline-block;">
                            <input type="hidden" name="appointment_id" value="<?php echo $row['appointment_id']; ?>">
                            <?php if (!$row['reminder_sent']) { ?>
                                <button type="submit" name="send_reminder" class="reminder-btn">Send Reminder</button>
                            <?php } ?>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div><br><br>

    <!-- Rejected Appointments -->
    <h3>Rejected Appointments</h3>
    <div class="table-container">
        <table>
            <tr>
                <th>Appointment ID</th>
                <th>Date</th>
                <th>Time</th>
                <th>Details</th>
                <th>User ID</th>
                <th>Pet ID</th>
                <th>Doctor ID</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($rejectedAppointments as $row) { 
                $datetime = new DateTime($row['appointment_time']);
                $date = $datetime->format("Y-m-d"); 
                $time = $datetime->format("H:i:s");      
            ?>
                <tr>
                    <td><?php echo $row['appointment_id']; ?></td>
                    <td><?php echo $date; ?></td>
                    <td><?php echo $time; ?></td>
                    <td><?php echo $row['appointment_reason']; ?></td>
                    <td><?php echo $row['user_id']; ?></td>
                    <td><?php echo $row['pet_id']; ?></td>
                    <td><?php echo $row['dr_id']; ?></td>
                    <td>
                        
                        <form method="POST" style="display:inline-block;">
                            <input type="hidden" name="appointment_id" value="<?php echo $row['appointment_id']; ?>">
                            <?php if (!$row['reminder_sent']) { ?>
                                <button type="submit" name="send_reminder" class="reminder-btn">Send Reminder</button>
                            <?php } 
                            else { 
                            echo "Reminder already sent"; }  ?>
                            
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
</div>

<form id="cancelForm" method="POST" style="display: none;">
    <input type="hidden" name="appointment_id" id="appointment_id">
    <input type="hidden" name="cancel_appointment" value="1"> <!-- Hidden input to indicate cancellation -->
</form>

</body>
</html>
<?php
include_once '../footer.php';
?>