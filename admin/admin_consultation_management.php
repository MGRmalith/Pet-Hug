<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: adminLogin.php");
    exit();
}

include_once 'header_admin.php';
require '../connection.php';

// Feedback messages
if (isset($_GET['cancel_success'])) {
    echo "<script>alert('Consultation cancelled successfully.');</script>";
} elseif (isset($_GET['cancel_error'])) {
    echo "<script>alert('Error cancelling consultation.');</script>";
} elseif (isset($_GET['sent_success'])) {
    echo "<script>alert('Reminder sent successfully.');</script>";
} elseif (isset($_GET['sent_error'])) {
    echo "<script>alert('Error sending reminder.');</script>";
}

// Fetch all consultations, sorted by date
$query = "SELECT * FROM consultation ORDER BY consultation_time ASC";
$result = $conn->query($query);

// Check if query execution was successful
if (!$result) {
    die("Error fetching consultations: " . $conn->error);
}

// Store all consultations for display
$acceptedConsultations = [];
$pendingConsultations = [];
$cancelledConsultations = [];

// Classify consultations based on their status
while ($row = $result->fetch_assoc()) {
    switch ($row['status']) {
        case 'Accepted':
            $acceptedConsultations[] = $row;
            break;
        case 'Pending':
            $pendingConsultations[] = $row;
            break;
        case 'Canceled':
            $cancelledConsultations[] = $row;
            break;
    }
}

// Handle sending reminders
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['send_reminder'])) {
    $consultation_id = $_POST['consultation_id'];

    // Fetch consultation details
    $consultationQuery = "SELECT * FROM consultation WHERE consultation_id=?";
    $stmt = $conn->prepare($consultationQuery);
    $stmt->bind_param("i", $consultation_id);
    $stmt->execute();
    $consultationResult = $stmt->get_result();
    $consultation = $consultationResult->fetch_assoc();

    if ($consultation) {
        // Determine the recipient type and notification message
        $recipient_id = 0;
        $recipient_type = '';
        $title = '';
        $message = '';

        if ($consultation['status'] === 'Pending') {
            // Notify doctor
            $recipient_id = $consultation['dr_id'];
            $recipient_type = 'doctor';
            $title = "Pending Consultation Reminder";
            $message = "You have a pending consultation regarding " . $consultation['reason'] . ".";
        } elseif ($consultation['status'] === 'Accepted') {
            // Notify user
            $recipient_id = $consultation['user_id'];
            $recipient_type = 'user';
            $title = "Appointment Accepted Reminder";
            $message = "Your consultation is confirmed.";
        } elseif ($consultation['status'] === 'Canceled') {
            // Notify user about cancellation
            $recipient_id = $consultation['user_id'];
            $recipient_type = 'user';
            $title = "Canceled Consultation Reminder";
            $message = "Your consultation has been canceled.";
        }

        // Insert notification
        $insertNotification = "INSERT INTO notifications (recipient_type, recipient_id, title, message,service_type, service_id) VALUES (?, ?, ?, ?, 'consultation', ?)";
        $insertStmt = $conn->prepare($insertNotification);
        $insertStmt->bind_param("sissi", $recipient_type, $recipient_id, $title, $message, $consultation_id);

        if ($insertStmt->execute()) {
            // Update consultation to mark reminder as sent
            $updateQuery = "UPDATE consultation SET reminder_sent = 1 WHERE consultation_id = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("i", $consultation_id);
            if ($updateStmt->execute()) {
                // Successful reminder action
                header("Location: " . $_SERVER['PHP_SELF'] . "?sent_success=1");
                exit();
            } else {
                // Log error if update fails
                echo "<script>alert('Error updating reminder status: " . $updateStmt->error . "');</script>";
                header("Location: " . $_SERVER['PHP_SELF'] . "?sent_error=1");
                exit();
            }
            $updateStmt->close();
        } else {
            // Log error if insertion fails
            echo "<script>alert('Error sending reminder: " . $insertStmt->error . "');</script>";
            header("Location: " . $_SERVER['PHP_SELF'] . "?sent_error=1");
            exit();
        }

        $insertStmt->close();
    }
}

// Handle consultation cancellation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cancel_consultation'])) {
    $consultation_id = $_POST['consultation_id'];

    // Update consultation to mark status as 'Cancelled'
    $updateQuery = "UPDATE consultation SET status = 'Canceled', reminder_sent = 0 WHERE consultation_id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("i", $consultation_id);

    if ($updateStmt->execute()) {
        header("Location: " . $_SERVER['PHP_SELF'] . "?cancel_success=1");
        exit();
    } else {
        header("Location: " . $_SERVER['PHP_SELF'] . "?cancel_error=1");
        exit();
    }

    $updateStmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Consultation Management - PetHug</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #e0f7ff;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1200px;
            margin: 10px auto;
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
        h3{
            text-align: center;
            font-size: 24px;
            color: #007bff;
            margin-top: 20px;
        }
        .table-container {
            max-height: 400px;
            overflow-y: auto;
        
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
    function confirmCancel(consultation_id) {
        if (confirm("Are you sure you want to cancel this consultation?")) {
            document.getElementById("consultation_id").value = consultation_id; // Set the ID
            document.getElementById("cancelForm").submit(); // Submit the cancel form
        }
    }
    </script>
</head>
<body>

<div class="container">
    <h2>Consultation Management</h2><br>

    <!-- Accepted Consultations -->
    <h3>Accepted Consultations</h3>
    <div class="table-container">
        <table>
            <tr>
                <th>Consultation ID</th>
                <th>Date</th>
                <th>Reason</th>
                <th>User ID</th>
                <th>Pet ID</th>
                <th>Doctor ID</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($acceptedConsultations as $row) { ?>
                <tr>
                    <td><?php echo $row['consultation_id']; ?></td>
                    <td><?php echo $row['created_at']; ?></td>
                    <td><?php echo $row['consultation_reason']; ?></td>
                    <td><?php echo $row['user_id']; ?></td>
                    <td><?php echo $row['pet_id']; ?></td>
                    <td><?php echo $row['dr_id']; ?></td>
                    <td>
                        <button class="cancel-btn" onclick="confirmCancel('<?php echo $row['consultation_id']; ?>')">Cancel</button>
                        <form method="POST" style="display:inline-block;">
                            <input type="hidden" name="consultation_id" value="<?php echo $row['consultation_id']; ?>">
                            <?php if (!$row['reminder_sent']) { ?>
                                <button type="submit" name="send_reminder" class="reminder-btn">Send Reminder to User</button>
                            <?php } ?>
                        </form>
                        <!-- Check if the fee is set -->
                        <?php if (empty($row['consultation_fee'])) { ?>
                            <form action="admin_set_fees.php" method="GET" style="display:inline-block;">
                                <input type="hidden" name="consultation_id" value="<?php echo $row['consultation_id']; ?>">
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

    <!-- Pending Consultations -->
    <h3>Pending Consultations</h3>
    <div class="table-container">
        <table>
            <tr>
                <th>Consultation ID</th>
                <th>Date</th>
                <th>Reason</th>
                <th>User ID</th>
                <th>Pet ID</th>
                <th>Doctor ID</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($pendingConsultations as $row) { ?>
                <tr>
                    <td><?php echo $row['consultation_id']; ?></td>
                    <td><?php echo $row['created_at']; ?></td>
                    <td><?php echo $row['consultation_reason']; ?></td>
                    <td><?php echo $row['user_id']; ?></td>
                    <td><?php echo $row['pet_id']; ?></td>
                    <td><?php echo $row['dr_id']; ?></td>
                    <td>
                        <button class="cancel-btn" onclick="confirmCancel('<?php echo $row['consultation_id']; ?>')">Cancel</button>
                        <form method="POST" style="display:inline-block;">
                            <input type="hidden" name="consultation_id" value="<?php echo $row['consultation_id']; ?>">
                            <?php if (!$row['reminder_sent']) { ?>
                                <button type="submit" name="send_reminder" class="reminder-btn">Send Reminder to Doctor</button>
                            <?php } ?>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div><br><br>

    <!-- Cancelled Consultations -->
    <h3>Cancelled Consultations</h3>
    <div class="table-container">
        <table>
            <tr>
                <th>Consultation ID</th>
                <th>Date</th>
                <th>Reason</th>
                <th>User ID</th>
                <th>Pet ID</th>
                <th>Doctor ID</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($cancelledConsultations as $row) { ?>
                <tr>
                    <td><?php echo $row['consultation_id']; ?></td>
                    <td><?php echo $row['created_at']; ?></td>
                    <td><?php echo $row['consultation_reason']; ?></td>
                    <td><?php echo $row['user_id']; ?></td>
                    <td><?php echo $row['pet_id']; ?></td>
                    <td><?php echo $row['dr_id']; ?></td>
                    <td>
                        <form method="POST" style="display:inline-block;">
                            <input type="hidden" name="consultation_id" value="<?php echo $row['consultation_id']; ?>">
                            <?php if (!$row['reminder_sent']) { ?>
                                <button type="submit" name="send_reminder" class="reminder-btn">Send Reminder to User</button>
                            <?php } else { ?>
                                <span>Reminder already sent</span>
                            <?php } ?>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
</div>

<form id="cancelForm" method="POST" style="display: none;">
    <input type="hidden" name="consultation_id" id="consultation_id">
    <input type="hidden" name="cancel_consultation" value="1"> <!-- Hidden input to indicate cancellation -->
</form>

</body>
</html>
<?php
include_once '../footer.php';
?>