<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: adminLogin.php");
    exit();
}

require '../connection.php'; // Assuming this file handles the database connection
include_once 'header_admin.php';

$success_message = "";
$error_message = "";

// Function to assign a doctor to a hostel request
function assignDoctorToHostel($conn, $hostel_id) {
    // Find the doctor with the fewest assignments (appointments + consultations)
    $doctor_id = findDoctorWithFewestAssignments($conn);
    
    if ($doctor_id) {
        // Assign the doctor to the hostel request
        $update_doctor_sql = "UPDATE hostel SET dr_id = ?, status = 'Accepted' WHERE hostel_id = ?";
        $stmt = $conn->prepare($update_doctor_sql);
        $stmt->bind_param("ii", $doctor_id, $hostel_id);
        
        if ($stmt->execute()) {
            return true; // Doctor assigned successfully
        }
    }
    return false; // No doctor found or assignment failed
}

// Function to find the doctor with the fewest assignments
function findDoctorWithFewestAssignments($conn) {
    $sql = "
        SELECT d.dr_id
        FROM doctor d
        LEFT JOIN appointment a ON d.dr_id = a.doctor_id AND a.status = 'Accepted'
        LEFT JOIN consultation c ON d.dr_id = c.dr_id AND c.status = 'Accepted'
        LEFT JOIN hostel h ON d.dr_id = h.dr_id AND h.status = 'Accepted'
        GROUP BY d.dr_id
        ORDER BY 
            (COUNT(DISTINCT a.appointment_id) + COUNT(DISTINCT c.consultation_id) + COUNT(DISTINCT h.hostel_id)) ASC
        LIMIT 1"; // Get the doctor with the least assignments
    $result = $conn->query($sql);
    
    if ($result && $row = $result->fetch_assoc()) {
        return $row['dr_id']; // Return the doctor ID
    }
   return null; // No doctors available
}



// Handle actions for accepting, canceling requests, or sending reminders
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $request_id = $_POST['hostel_id'];
    $action = $_POST['action'];
    
    $user_id = 0;

    // Retrieve user_id for the notification message
    if ($action === 'accept' || $action === 'cancel' || $action === 'send_reminder') {
        $get_user_sql = "SELECT user_id FROM hostel WHERE hostel_id = ?";
        $get_user_stmt = $conn->prepare($get_user_sql);
        $get_user_stmt->bind_param("i", $request_id);
        $get_user_stmt->execute();
        $get_user_stmt->bind_result($user_id);
        $get_user_stmt->fetch();
        $get_user_stmt->close();
    }

    if ($action === 'accept') {
        $assigned_doctor_id = assignDoctorToHostel($conn, $request_id);
        if ($assigned_doctor_id) {
            $notification_title = "Hostel Request Accepted";
            $notification_message = "Your hostel request has been accepted. Doctor ID: " . $assigned_doctor_id;
            $success_message = "Request successfully accepted and doctor assigned.";
        } else {
            $error_message = "No available doctor to assign.";
        }
    } elseif ($action === 'cancel') {
        $update_sql = "UPDATE hostel SET status = 'Canceled' WHERE hostel_id = ?";
        $notification_title = "Hostel Request Cancelled";
        $notification_message = "Your hostel request has been cancelled.";
    } elseif ($action === 'send_reminder') {
        $update_sql = "UPDATE hostel SET reminder_sent = 1 WHERE hostel_id = ?";
        $notification_title = "Reminder Sent";
        $notification_message = "A reminder for your hostel request has been sent.";
    }

    // Updating the status of the hostel request
    if (isset($update_sql)) {
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("i", $request_id);
        
        if ($stmt->execute()) {
            // Insert notification into the notifications table
            $insert_notification_sql = "INSERT INTO notifications (recipient_type, recipient_id, title, message,service_type, service_id) VALUES (?, ?, ?, ?, 'hostel', ?)";
            $recipient_type = 'user'; // Assuming the recipient is a user
            $insert_notification_stmt = $conn->prepare($insert_notification_sql);
            $insert_notification_stmt->bind_param("sissi", $recipient_type, $user_id, $notification_title, $notification_message,$request_id);
            
            if ($insert_notification_stmt->execute()) {
                $success_message = "Request successfully updated and notification sent.";
            } else {
                $error_message = "Request updated, but error while sending notification: " . $insert_notification_stmt->error;
            }

            $insert_notification_stmt->close();
        } else {
            $error_message = "Error updating the request: " . $stmt->error;
        }
    }
}

// Fetch all hostel requests ordered by start date with user and pet names
$fetch_sql = "
    SELECT 
        hostel.hostel_id, 
        hostel.start_date, 
        hostel.end_date, 
        hostel.status, 
        hostel.reminder_sent,
        user.user_first_name AS user_name, 
        pet.pet_name AS pet_name
    FROM 
        hostel
    JOIN 
        user ON hostel.user_id = user.user_id
    JOIN 
        pet ON hostel.pet_id = pet.pet_id
    ORDER BY 
        hostel.start_date DESC";

$stmt = $conn->prepare($fetch_sql);
$stmt->execute();
$result = $stmt->get_result();

// Prepare arrays for each status type
$pending_requests = [];
$accepted_requests = [];
$cancelled_requests = [];

while ($row = $result->fetch_assoc()) {
    if ($row['status'] === 'Pending') {
        $pending_requests[] = $row;
    } elseif ($row['status'] === 'Accepted') {
        $accepted_requests[] = $row;
    } elseif ($row['status'] === 'Canceled') {
        $cancelled_requests[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hostel Management</title>
    <style>
        /* Basic CSS for styling */
        body {
            background-color: #e0f7ff;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1200px;
            margin: auto;
            padding: 20px;
            margin-top: 50px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .container h2 {
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
        .section {
            margin-bottom: 30px;
        }
        .message {
            text-align: center;
            color: green;
        }
        .error-message {
            text-align: center;
            color: red;
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
        table,th, td {
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
        .container button {
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 3px;

        }
        .accept-btn {
            background-color: #28a745;
            color: white;
        }
        .cancel-btn {
            background-color: #ff4500;
            color: white;
        }
        .reminder-btn {
            background-color: #3498db;
            color: white;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Hostel Management</h2>

    <?php if ($success_message) { ?>
        <p class="message"><?php echo $success_message; ?></p>
    <?php } ?>
    <?php if ($error_message) { ?>
        <p class="error-message"><?php echo $error_message; ?></p>
    <?php } ?>

    <!-- Pending Requests Section -->
    <div class="section">
        <h3>Pending Requests</h3>
        <table>
            <thead>
                <tr>
                    <th>Request ID</th>
                    <th>User Name</th>
                    <th>Pet Name</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pending_requests as $row) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['hostel_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['pet_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['start_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['end_date']); ?></td>
                        <td>
                            <form method="POST" action="" style="display:inline-block;">
                                <input type="hidden" name="hostel_id" value="<?php echo $row['hostel_id']; ?>">
                                <button type="submit" class="accept-btn" name="action" value="accept">Accept</button>
                                <button type="submit" class="cancel-btn" name="action" value="cancel">Cancel</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- Accepted Requests Section -->
    <div class="section">
        <h3>Accepted Requests</h3>
        <table>
            <thead>
                <tr>
                    <th>Request ID</th>
                    <th>User Name</th>
                    <th>Pet Name</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($accepted_requests as $row) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['hostel_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['pet_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['start_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['end_date']); ?></td>
                        <td>
                            <?php if (!$row['reminder_sent']) { ?>
                                <form method="POST" action="" style="display:inline-block;">
                                    <input type="hidden" name="hostel_id" value="<?php echo $row['hostel_id']; ?>">
                                    <button type="submit" class="reminder-btn" name="action" value="send_reminder">Send Reminder</button>
                                </form>
                            <?php } else { ?>
                                <span>Already reminded</span>
                            <?php } ?>

                            <!-- Check if the fee is set -->
                            <?php if (empty($row['hostel_fee'])) { ?>
                                <form action="admin_set_fees.php" method="GET" style="display:inline-block;">
                                    <input type="hidden" name="hostel_id" value="<?php echo $row['hostel_id']; ?>">
                                    <button type="submit" class="reminder-btn" style="background-color: #28a745;">Set Fee</button>
                                </form>
                            <?php } else { ?>
                                <span>Fee Set</span>
                            <?php } ?>
                            </td>
                        </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- Cancelled Requests Section -->
    <div class="section">
        <h3>Cancelled Requests</h3>
        <table>
            <thead>
                <tr>
                    <th>Request ID</th>
                    <th>User Name</th>
                    <th>Pet Name</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cancelled_requests as $row) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['hostel_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['pet_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['start_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['end_date']); ?></td>
                        <td>
                            <?php if (!$row['reminder_sent']) { ?>
                                <form method="POST" action="" style="display:inline-block;">
                                    <input type="hidden" name="hostel_id" value="<?php echo $row['hostel_id']; ?>">
                                    <button type="submit" class="reminder-btn" name="action" value="send_reminder">Send Reminder</button>
                                </form>
                            <?php } else { ?>
                                <span>Already reminded</span>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>

<?php
include '../footer.php';
?>