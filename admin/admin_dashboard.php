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

    // Fetch pending counts for each category
    $appointment_count_query = "SELECT COUNT(*) AS pending_count FROM appointment WHERE status = 'Pending'";
    $consultation_count_query = "SELECT COUNT(*) AS pending_count FROM consultation WHERE status = 'Pending'";
    $hostel_count_query = "SELECT COUNT(*) AS pending_count FROM hostel WHERE status = 'Pending'";
    $payment_count_query = "SELECT COUNT(*) AS pending_count FROM bill WHERE status = 'Pending'";

    $appointment_result = $conn->query($appointment_count_query);
    $consultation_result = $conn->query($consultation_count_query);
    $hostel_result = $conn->query($hostel_count_query);
    $payment_result = $conn->query($payment_count_query);

    $appointment_pending_count = $appointment_result->fetch_assoc()['pending_count'];
    $consultation_pending_count = $consultation_result->fetch_assoc()['pending_count'];
    $hostel_pending_count = $hostel_result->fetch_assoc()['pending_count'];
    $payment_pending_count = $payment_result->fetch_assoc()['pending_count'];

    // Query to fetch today's messages
    $message_query = "SELECT * FROM contact_form WHERE DATE(created_at) = CURDATE()";
    $message_result = $conn->query($message_query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="../afterLoginAdmin_style/admin_dashboard.css">
</head>
<body>

    <div class="container">
        <h2>Admin Dashboard</h2>
        <div class="dashboard-stats">
            <p>Pending Appointments: <strong><?php echo $appointment_pending_count; ?></strong></p>
            <p>Pending Consultations: <strong><?php echo $consultation_pending_count; ?></strong></p>
            <p>Pending Hostel Requests: <strong><?php echo $hostel_pending_count; ?></strong></p>
            <p>Pending Payments: <strong><?php echo $payment_pending_count; ?></strong></p>
        </div>

        <div class="today-messages">
            <h3>Messages Received Today</h3>
            <?php if ($message_result->num_rows > 0): ?>
                <ul>
                    <?php while($message = $message_result->fetch_assoc()): ?>
                        <li>
                            <strong><?php echo $message['name']; ?> &nbsp; (<?php echo $message['email']; ?>):&nbsp;</strong> &nbsp;<?php echo $message['message']; ?> <span><?php echo date('H:i', strtotime($message['created_at'])); ?></span>
                            &nbsp;<a href="mailto:<?php echo $message['email']; ?>" class="email-button">Reply via Email</a>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>No messages received today.</p>
            <?php endif; ?>
        </div>
    </div>

 
</body>
</html>

<!--footer-->
<?php include_once "../footer.php"?>

<?php $conn->close(); ?>