<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: userLogin.php");
    exit();
}

include_once '../connection.php';
include_once 'header_user.php';

$user_id = $_SESSION['user_id'];

// Check if a notification was clicked to be viewed
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['notification_id'], $_POST['service_type'], $_POST['service_id'])) {
    $notification_id = intval($_POST['notification_id']);
    $service_type = $_POST['service_type'];
    $service_id = intval($_POST['service_id']);

    // Mark the notification as read
    $sql_mark_read = "UPDATE notifications SET is_read = 1 WHERE id = $notification_id";
    $conn->query($sql_mark_read);

    // Redirect based on service type
    switch ($service_type) {
        case 'appointment':
            header("Location: my_appointments.php?id=$service_id");
            break;
        case 'consultation':
            header("Location: my_consultations.php?id=$service_id");
            break;
        case 'hostel':
            header("Location: my_hostel.php?id=$service_id");
            break;
        case 'other':
        default:
            header("Location: user_dashboard.php"); // Default redirection
            break;
    }
    exit();
}

// Fetch unread notifications
$sql_unread_notifications = "SELECT * FROM notifications 
    WHERE recipient_id = $user_id 
    AND recipient_type = 'user' 
    AND is_read = 0 
    ORDER BY created_at DESC";
$result_unread_notifications = $conn->query($sql_unread_notifications);

// Fetch read notifications
$sql_read_notifications = "SELECT * FROM notifications 
    WHERE recipient_id = $user_id 
    AND recipient_type = 'user' 
    AND is_read = 1 
    ORDER BY created_at DESC";
$result_read_notifications = $conn->query($sql_read_notifications);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Notifications - PetHug</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #e0f7ff;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            margin: auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }
        h2 {
            border-bottom: 2px solid #007BFF;
            padding-bottom: 10px;
        }
        .notification {
            background-color: #e9f5ff;
            border-left: 4px solid #007BFF;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .notification:hover {
            background-color: #d0e7ff;
        }
        .notification p {
            margin: 5px 0;
        }
        .notification-time {
            font-size: 0.85em;
            color: #666;
        }
        .earlier-notification {
            border-left: 4px solid #007BFF;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .earlier-notification:hover {
            
            background-color: #d0e7ff;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Your Notifications</h2>

        <!-- Unread Notifications -->
        <?php if ($result_unread_notifications->num_rows > 0): ?>
            
            <?php while ($row = $result_unread_notifications->fetch_assoc()): ?>
                <div class="notification">
                    <p><strong><?php echo htmlspecialchars($row['title']); ?></strong></p>
                    <p><?php echo htmlspecialchars($row['message']); ?></p>
                    <p class="notification-time"><?php echo date('F j, Y, g:i a', strtotime($row['created_at'])); ?></p>
                    <form method="post" style="display: inline;">
                        <input type="hidden" name="notification_id" value="<?php echo $row['id']; ?>">
                        <input type="hidden" name="service_type" value="<?php echo $row['service_type']; ?>">
                        <input type="hidden" name="service_id" value="<?php echo $row['service_id']; ?>">
                        <button type="submit">View</button>
                    </form>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
        

        <!-- Read Notifications -->
        <?php if ($result_read_notifications->num_rows > 0): ?>
           
            <?php while ($row = $result_read_notifications->fetch_assoc()): ?>
                <div class="earlier-notification">
                    <p><strong><?php echo htmlspecialchars($row['title']); ?></strong></p>
                    <p><?php echo htmlspecialchars($row['message']); ?></p>
                    <p class="notification-time"><?php echo date('F j, Y, g:i a', strtotime($row['created_at'])); ?></p>
                    <form method="post" style="display: inline;">
                        <input type="hidden" name="notification_id" value="<?php echo $row['id']; ?>">
                        <input type="hidden" name="service_type" value="<?php echo $row['service_type']; ?>">
                        <input type="hidden" name="service_id" value="<?php echo $row['service_id']; ?>">
                        <button type="submit">View</button>
                    </form>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
include_once "../footer.php";   
$conn->close();
?>
