<?php
session_start();
include_once "../connection.php";

// Redirect if not logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: adminLogin.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];

if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Fetch the current hashed password from the database
    $sql = "SELECT admin_password FROM admin WHERE admin_id = '$admin_id'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);

    // Verify current password using password_verify
    if (!password_verify($current_password, $row['admin_password'])) {
        $_SESSION['error_message'] = "Current password is incorrect.";
    } elseif ($new_password !== $confirm_password) {
        $_SESSION['error_message'] = "New passwords do not match.";
    } else {
        // Hash the new password
        $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update with the new hashed password
        $update_sql = "UPDATE admin SET admin_password='$hashed_new_password' WHERE admin_id='$admin_id'";
        if (mysqli_query($conn, $update_sql)) {
            $_SESSION['success_message'] = "Password updated successfully.";
            header("Location: admin_profile.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Error updating password: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <link rel="stylesheet" href="../afterLoginDoctor_style/change_password.css">
</head>
<body>
    <div class="container">
        <div class="form">
            <h2>Change Password</h2>
            
            <?php
                if (isset($_SESSION['error_message'])) {
                    echo '<p style="color:red;">' . $_SESSION['error_message'] . '</p>';
                    unset($_SESSION['error_message']);
                }
            ?>

            <form action="admin_change_password.php" method="post">
                <label for="current_password">Current Password:</label>
                <input type="password" id="current_password" name="current_password" required><br><br>
                <label for="new_password">New Password:</label>
                <input type="password" id="new_password" name="new_password" required><br><br>
                <label for="confirm_password">Confirm New Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required><br><br>

                <input type="submit" name="change_password" value="Change Password">
            </form>

            <a href="admin_profile.php" id="cancel">Cancel</a>
        </div>
    </div>

</body>
</html>
 <?php
    $conn->close();
 ?>
