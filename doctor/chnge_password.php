<?php
session_start();
include_once "../connection.php";

// Redirect if not logged in
if (!isset($_SESSION['dr_id'])) {
    header("Location: doctorLogin.php");
    exit();
}

$dr_id = $_SESSION['dr_id'];

if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Fetch the current hashed password from the database
    $sql = "SELECT dr_password FROM doctor WHERE dr_id = '$dr_id'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);

    // Verify current password using password_verify
    if (!password_verify($current_password, $row['dr_password'])) {
        $_SESSION['error_message'] = "Current password is incorrect.";
    } elseif ($new_password !== $confirm_password) {
        $_SESSION['error_message'] = "New passwords do not match.";
    } else {
        // Hash the new password
        $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update with the new hashed password
        $update_sql = "UPDATE doctor SET dr_password='$hashed_new_password' WHERE dr_id='$dr_id'";
        if (mysqli_query($conn, $update_sql)) {
            $_SESSION['success_message'] = "Password updated successfully.";
            header("Location: doctor_profile.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Error updating password: " . mysqli_error($conn);
        }
    }
}
$conn->close(); 
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

            <form action="change_password.php" method="post">
                <label for="current_password">Current Password:</label>
                <input type="password" id="current_password" name="current_password" required><br><br>
                <label for="new_password">New Password:</label>
                <input type="password" id="new_password" name="new_password" required><br><br>
                <label for="confirm_password">Confirm New Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required><br><br>

                <input type="submit" name="change_password" value="Change Password">
            </form>

            <a href="doctor_profile.php" id="cancel">Cancel</a>
        </div>
    </div>

</body>
</html>
