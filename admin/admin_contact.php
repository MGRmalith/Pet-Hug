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


// Handle deletion of messages
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM contact_form WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        $message = "Message deleted successfully.";
    } else {
        $message = "Error deleting message: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch all contact messages
$sql = "SELECT id, name, email, subject, message, created_at FROM contact_form ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Contact Messages</title>
   <style> 
   body {
    font-family: Arial, sans-serif;
    background-color: #e0f7ff;
    margin: 0;
    padding: 0;
}

h1 {
    text-align: center;
    color: #03045e;
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

table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px auto;
    background-color: #fff;
}

table th, table td {
    text-align: left;
    padding: 10px;
    border: 1px solid #ddd;
}

table th {
    background-color: #007BFF;
    color: #fff;
}

.notification {
    text-align: center;
    color: green;
}

.btn {
    text-decoration: none;
    padding: 5px 10px;
    color: #fff;
    background-color: #28a745;
    border-radius: 5px;
    font-size: 12px;
}

.btn.delete {
    background-color: #dc3545;
}

.btn:hover {
    opacity: 0.8;
}

.btn.delete:hover {
    opacity: 0.8;
}

   </style>
</head>
<body>
    <div class="container">
    <h1>Contact Messages Management</h1>

    <?php if (isset($message)): ?>
        <p class="notification"><?php echo $message; ?></p>
    <?php endif; ?>

    <table border="1" cellpadding="10" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Subject</th>
                <th>Message</th>
                <th>Received At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['subject']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($row['message'])); ?></td>
                        <td><?php echo $row['created_at']; ?></td>
                        <td>
                            <a href="mailto:<?php echo $row['email']; ?>?subject=Re: <?php echo rawurlencode($row['subject']); ?>" class="btn">Reply</a>
                            <a href="?delete_id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this message?')" class="btn delete">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">No messages found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php $conn->close(); // Close the database connection ?>
    </div>
</body>
</html>

<?php include_once "../footer.php"?>
