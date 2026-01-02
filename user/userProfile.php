<?php
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header("Location: userLogin.php");
        exit();
    }
    include_once 'header_user.php';
    // Database connection
    require '../connection.php';
    
    // User ID from session
    $user_id = $_SESSION['user_id'];
    
    // Fetch user details from the database
    $sql = "SELECT * FROM user WHERE user_id = $user_id";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
    } else {
        $_SESSION['error_message1'] = "<p>User not found or you do not have permission to edit this profile.</p>";
        exit();
    }

    // Handle form submission to update pet details
    if (isset($_POST["update"])) {
        $user_first_name = $_POST['user_first_name'];
        $user_last_name = $_POST['user_last_name'];
        $user_email = $_POST['user_email'];
        $user_phone = $_POST['user_phone'];
        $user_address = $_POST['user_address'];

        // Validate the input
        if (empty($user_first_name) || empty($user_last_name) || empty($user_email) ||empty($user_phone) || empty($user_address)) {
            $_SESSION['error_message1'] = "All fields is required.";
        } elseif (!preg_match("/^[0-9]{10}$/", $user_phone)) {
            $_SESSION['error_message1'] = "Phone number must be 10 digits.";
        } elseif (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error_message1'] = "Invalid email format.";
        } else {
            $sql = "UPDATE user SET user_first_name='$user_first_name', user_last_name='$user_last_name', user_email='$user_email', user_phone='$user_phone', user_address='$user_address' WHERE user_id='$user_id'";

                if (mysqli_query($conn, $sql)) {
                    $_SESSION['success_message1'] = "User details updated successfully.";
                    header("Location: userProfile.php");
                    exit();
                } else {
                    $_SESSION['error_message1'] = "Error updating pet: " . mysqli_error($conn);
                }
            }
    }elseif (isset($_POST["cancel"])) {
        // Redirect to userProfile.php if cancel is clicked
        header("Location: userProfile.php");
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
            // Check if the user has a photo in the database
            if (!empty($row['user_image'])) {
                echo "<div class='dr_img'>";
                echo "<img src='" . htmlspecialchars($row['user_image']) . "' alt='user Photo' width='100px' height='auto'>";
                echo "</div>";
            } else {
                // Use the default image if no photo exists
                echo "<div class='dr_img'>";
                echo "<img src='../images/17246491.png' alt='Default Photo' width='100px'>";
                echo "</div>";
            }
            ?>
            <div class="dr_name">
                    <?php echo "<h2>" . htmlspecialchars($row['user_first_name']) . " " . htmlspecialchars($row['user_last_name']) . "</h2>"; ?>
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
                <label for="first_name">First Name:</label>
                <input id="first_name" type="text" name="user_first_name" value="<?php echo htmlspecialchars($row['user_first_name']);?>"><br><br>
                <label for="last_name">Last Name:</label>
                <input id="last_name" type="text" name="user_last_name" value="<?php echo htmlspecialchars($row['user_last_name']);?>"><br><br>
                <label for="email">Email:</label>
                <input id="email" type="email" name="user_email" value="<?php echo htmlspecialchars($row['user_email']);?>"><br><br>
                <label for="phone">Phone number:</label>
                <input id="phone" type="text" name="user_phone" value="<?php echo htmlspecialchars($row['user_phone']);?>" min="0"><br><br>
                <label for="address">Address:</label>
                <input id="address" type="text" name="user_address" value="<?php echo htmlspecialchars($row['user_address']);?>"><br><br>

                <!-- Button to change password -->
                <a href="user_change_password.php" class="change-password-btn">Change Password</a>

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
<?php include_once "../footer.php";?>

<?php $conn->close(); ?>