<?php
session_start();
include_once "../connection.php";

//header
 include_once 'header_admin.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: adminLogin.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];

// Get the current time
$current_time = date("Y-m-d H:i:s");
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    var_dump($_POST);
}


// If the admin confirms a payment, update the status to "Confirmed"
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_payment']) && isset($_POST['user_id'])) {
    $bill_id = $_POST['bill_id'];
    $user_id = $_POST['user_id'];

    // Collect activity IDs
    $appointment_ids = [];
    $consultation_ids = [];
    $hostel_ids = [];

    $sql = "SELECT appointment_id
        FROM appointment 
        WHERE user_id = $user_id AND appointment_time <= '$current_time' AND bill_id IS NULL AND status='Accepted'";
    $result = mysqli_query($conn,$sql);

    if ($result->num_rows > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $appointment_ids[] = $row['appointment_id'];
        }
    }

    $sql = "SELECT consultation_id
        FROM consultation 
        WHERE user_id = $user_id AND consultation_time <= '$current_time' AND bill_id IS NULL AND status='Accepted'";
    $result = mysqli_query($conn,$sql);

    if ($result->num_rows > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $consultation_ids[] = $row['consultation_id'];
        }
    }

    $sql = "SELECT hostel_id
        FROM hostel 
        WHERE user_id = $user_id AND end_date <= '$current_time' AND bill_id IS NULL AND status='Accepted'";
    $result = mysqli_query($conn,$sql);

    if ($result->num_rows > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $hostel_ids[] = $row['hostel_id'];
        }
    }

    $stmt = $conn->prepare("UPDATE bill SET status = 'Confirmed', admin_id = ? WHERE bill_id = ?");
    $stmt->bind_param("ii",$admin_id, $bill_id);

    if ($stmt->execute()) {
            foreach ($appointment_ids as $appointment_id) {
                $stmt = $conn->prepare("UPDATE appointment SET bill_id = ?, admin_id = ?, status = 'Completed' WHERE appointment_id = ? AND user_id = ?");
                $stmt->bind_param("iiii", $bill_id, $admin_id, $appointment_id, $user_id);
                $stmt->execute();
            }
            
            foreach ($consultation_ids as $consultation_id) {
                $stmt = $conn->prepare("UPDATE consultation SET bill_id = ?, admin_id = ?, status = 'Completed' WHERE consultation_id = ? AND user_id = ?");
                $stmt->bind_param("iiii", $bill_id, $admin_id, $consultation_id, $user_id);
                $stmt->execute();
            }
                
            foreach ($hostel_ids as $hostel_id) {
                $stmt = $conn->prepare("UPDATE hostel SET bill_id = ?, admin_id = ?, status = 'Completed' WHERE hostel_id = ? AND user_id = ?");
                $stmt->bind_param("iiii", $bill_id, $admin_id, $hostel_id, $user_id);
                $stmt->execute();
            }
        echo "<p class='success'>Payment for Bill ID $bill_id has been confirmed!</p>";
    } else {
        echo "<p class='error'>Error: " . $stmt->error . "</p>";
    }
    $stmt->close();
}

// If the admin rejects a payment, update the status to "Rejected"
elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reject_payment']) && isset($_POST['user_id'])) {
    $bill_id = $_POST['bill_id'];
    $user_id = $_POST['user_id'];

    $stmt = $conn->prepare("UPDATE bill SET status = 'Rejected', admin_id = ? WHERE bill_id = ?");
    $stmt->bind_param("ii", $admin_id, $bill_id);

    if ($stmt->execute()) {
        echo "<p class='error'>Payment for Bill ID $bill_id has been rejected!</p>";
    } else {
        echo "<p class='error'>Error: " . $stmt->error . "</p>";
    }
    $stmt->close();
}

// Fetch all pending payments for both bank transfer and cash payments
$result = $conn->query("SELECT * FROM bill WHERE status = 'Pending' AND (method = 'Online Bank Transfer')");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Payment Processing</title>
    <link rel="stylesheet" href="../afterLoginAdmin_style/admin_payment_confirmation.css">

</head>
<body>

    <div class="container">
        <h1>Pending Payments (Bank Transfer)</h1>

        <?php if ($result->num_rows > 0): ?>
            <table border="1">
                <tr>
                    <th>Bill ID</th>
                    <th>User ID</th>
                    <th>Amount (Rs.)</th>
                    <th>Date</th>
                    <th>Method</th>
                    <th>transaction_reference</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>

                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['bill_id']; ?></td>
                        <td><?php echo $row['user_id']; ?></td>
                        <td><?php echo $row['amount']; ?></td>
                        <td><?php echo $row['date']; ?></td>
                        <td><?php echo $row['method']; ?></td>
                        <td><?php echo $row['transaction_reference']; ?></td>
                        <td><?php echo $row['status']; ?></td>
                        <td>
                            <!-- Confirmation Form -->
                            <form action="" method="post" style="display:inline;">
                                <input type="hidden" name="bill_id" value="<?php echo $row['bill_id']; ?>">
                                <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
                                <input type="submit" name="confirm_payment" value="Confirm">
                            </form>
                            <!-- Rejection Form -->
                            <form action="" method="post" style="display:inline;">
                                <input type="hidden" name="bill_id" value="<?php echo $row['bill_id']; ?>">
                                <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
                                <input type="submit" name="reject_payment" value="Reject" style="background-color:red; color:white;">
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p class="error">No pending payments for Bank Transfer</p>
        <?php endif; ?>
    </div>

    <!--footer-->
    <?php include_once '../footer.php';?>
</body>
</html>

<?php $conn->close(); ?>
