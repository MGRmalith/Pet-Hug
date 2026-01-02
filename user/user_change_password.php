<?php
session_start();
include_once "../connection.php";

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: userLogin.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Fetch the current hashed password from the database
    $sql = "SELECT user_password FROM user WHERE user_id = '$user_id'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);

    // Verify current password using password_verify
    if (!password_verify($current_password, $row['user_password'])) {
        $_SESSION['error_message'] = "Current password is incorrect.";
    } elseif ($new_password !== $confirm_password) {
        $_SESSION['error_message'] = "New passwords do not match.";
    } else {
        // Hash the new password
        $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update with the new hashed password
        $update_sql = "UPDATE user SET user_password='$hashed_new_password' WHERE user_id='$user_id'";
        if (mysqli_query($conn, $update_sql)) {
            $_SESSION['success_message'] = "Password updated successfully.";
            header("Location: userProfile.php");
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

            <form action="user_change_password.php" method="post">
                <label for="current_password">Current Password:</label>
                <input type="password" id="current_password" name="current_password" required><br><br>
                <label for="new_password">New Password:</label>
                <input type="password" id="new_password" name="new_password" required><br><br>
                <label for="confirm_password">Confirm New Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required><br><br>

                <input type="submit" name="change_password" value="Change Password">
            </form>

            <a href="userProfile.php" id="cancel">Cancel</a>
        </div>
    </div>

</body>
</html>
 <?php
    $conn->close();
 ?>