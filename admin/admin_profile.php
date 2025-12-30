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
    
    // Fetch admin details from the database
    $sql = "SELECT * FROM admin WHERE admin_id = $admin_id";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
    } else {
        $_SESSION['error_message1'] = "<p>Admin not found or you do not have permission to edit this profile.</p>";
        exit();
    }

    // Handle form submission to update pet details
    if (isset($_POST["update"])) {
        $admin_name = $_POST['admin_name'];
        $admin_email = $_POST['admin_email'];
        $admin_phone = $_POST['admin_phone'];
        $admin_address = $_POST['admin_address'];

        // Validate the input
        if (empty($admin_name) || empty($admin_email) ||empty($admin_phone) || empty($admin_address)) {
            $_SESSION['error_message1'] = "All fields is required.";
        } elseif (!preg_match("/^[0-9]{10}$/", $admin_phone)) {
            $_SESSION['error_message1'] = "Phone number must be 10 digits.";
        } elseif (!filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error_message1'] = "Invalid email format.";
        } else {
            $sql = "UPDATE admin SET admin_name='$admin_name', admin_email='$admin_email', admin_phone='$admin_phone', admin_address='$admin_address' WHERE admin_id='$admin_id'";

                if (mysqli_query($conn, $sql)) {
                    $_SESSION['success_message1'] = "Admin details updated successfully.";
                    header("Location: admin_profile.php");
                    exit();
                } else {
                    $_SESSION['error_message1'] = "Error updating pet: " . mysqli_error($conn);
                }
            }
    }elseif (isset($_POST["cancel"])) {
        // Redirect to admin_profile.php if cancel is clicked
        header("Location: admin_profile.php");
        exit();
    }

?>
    

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin profile form</title>
    <link rel="stylesheet" href="../afterLoginDoctor_style/dr_profile.css">
</head>
<body>

    <!-- Elements to display error and success messages -->
    <?php
            if(isset($_SESSION['error_message1'])) {
                echo '<p style="color:red;">'.$_SESSION['error_message1'].'</p>';
                unset($_SESSION['error_message1']);
            }
            if(isset($_SESSION['success_message1'])) {
                echo '<p style="color:green;">'.$_SESSION['success_message1'].'</p>';
                unset($_SESSION['success_message1']);
            }
    ?>

    <div class="profile-container">
        <div class="profile">
        <?php
            // Check if the admin has a photo in the database
            if (!empty($row['admin_image'])) {
                echo "<div class='dr_img'>";
                echo "<img src='" . htmlspecialchars($row['admin_image']) . "' alt='Admin Photo' width='100px' height='auto'>";
                echo "</div>";
            } else {
                // Use the default image if no photo exists
                echo "<div class='dr_img'>";
                echo "<img src='../images/17246491.png' alt='Default Photo' width='100px'>";
                echo "</div>";
            }
            ?>
            <div class="dr_name">
                    <?php echo "<h2>" . htmlspecialchars($row['admin_name']) . "</h2>"; ?>
            </div>
        </div>

        <?php
            if (isset($_SESSION['success_message'])) {
                        echo '<p style="color:green;">' . $_SESSION['success_message'] . '</p>';
                        unset($_SESSION['success_message']);
            }
        ?>

        <div class="form">
            <h2>Edit Profile</h2>
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                <label for="name">Name:</label>
                <input id="name" type="text" name="admin_name" value="<?php echo htmlspecialchars($row['admin_name']);?>"><br><br>
                <label for="email">Email:</label>
                <input id="email" type="email" name="admin_email" value="<?php echo htmlspecialchars($row['admin_email']);?>"><br><br>
                <label for="phone">Phone number:</label>
                <input id="phone" type="text" name="admin_phone" value="<?php echo htmlspecialchars($row['admin_phone']);?>" min="0"><br><br>
                <label for="address">Phone number:</label>
                <input id="address" type="text" name="admin_address" value="<?php echo htmlspecialchars($row['admin_address']);?>"><br><br>

                <!-- Button to change password -->
                <a href="admin_change_password.php" class="change-password-btn">Change Password</a>

                <div class="btns">
                    <!--submit button-->
                    <input id="submit" type="submit" name="update" value="Update">
                    <!-- Cancel button as a submit button -->
                    <input id="cancel" type="submit" name="cancel" value="Cancel">
                </div>
            </form>
        </div>
    </div>
    
    
</body>
</html>

<!-- footer -->
<?php include_once"../footer.php";?>

<?php $conn->close(); ?>