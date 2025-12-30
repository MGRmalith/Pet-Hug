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


// Handle deletion of feedback
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM feedback WHERE feedback_id = ?");
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        $message = "Feedback deleted successfully!";
    } else {
        $message = "Error deleting feedback: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch all feedback
$sql = "SELECT feedback_id, rating, feedback_text, created_at FROM feedback ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Feedback</title>
   <style> 
   body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    background-color: #e0f7ff;
}

h1 {
    
    color: #03045e;
    padding: 20px 10px;
    text-align: center;
}

main {
    padding: 20px;
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
    background-color: #fff;
    margin-bottom: 20px;
}

table th, table td {
    padding: 10px;
    text-align: left;
    border: 1px solid #ddd;
}

table th {
    background-color: #007bff;
    color: white;
}

table tr:nth-child(even) {
    background-color: #f9f9f9;
}

.delete-btn {
    color: #ff4d4d;
    text-decoration: none;
    font-weight: bold;
}

.delete-btn:hover {
    text-decoration: underline;
}



   </style>
</head>
<body>
    <div class="container">
    <header>
        <h1>Manage Feedback</h1>
    </header>
    <main>
        <?php if (isset($message)) { ?>
            <p class="message"><?= $message; ?></p>
        <?php } ?>

        <?php if ($result->num_rows > 0) { ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Rating</th>
                        <th>Feedback</th>
                        <th>Submitted On</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><?= $row['feedback_id']; ?></td>
                            <td><?= $row['rating']; ?>/5</td>
                            <td><?= $row['feedback_text']; ?></td>
                            <td><?= date("F d, Y h:i A", strtotime($row['created_at'])); ?></td>
                            <td>
                                <a href="manage_feedback.php?delete_id=<?= $row['feedback_id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this feedback?');">Delete</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <p>No feedback available.</p>
        <?php } ?>
    </main>
    </div>
    
</body>
</html>



<?php $conn->close(); ?>

<?php include_once '../footer.php'; ?>
