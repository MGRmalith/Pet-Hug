<?php
   session_start();
   if (!isset($_SESSION['dr_id'])) {
       header("Location: doctorLogin.php");
       exit();
   }
   
   require '../connection.php'; // Include the database connection file
   include_once 'header_dr.php';
   
   $doctor_id = $_SESSION['dr_id']; // Assign doctor_id from session before using it in query
   

    // Fetch doctor details from the database
    $sql = "SELECT * FROM doctor WHERE dr_id = $doctor_id";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
    } else {
        $_SESSION['error_message1'] = "<p>Doctor not found or you do not have permission to edit this profile.</p>";
        exit();
    }

    // Handle form submission to update pet details
    if (isset($_POST["update"])) {
        $dr_name = $_POST['dr_name'];
        $dr_email = $_POST['dr_email'];
        $specialization = $_POST['specialization'];
        $dr_phone = $_POST['dr_phone'];

        // Validate the input
        if (empty($dr_name) || empty($specialization) || empty($dr_phone)) {
            $_SESSION['error_message1'] = "All fields is required.";
        } elseif (!preg_match("/^[0-9]{10}$/", $dr_phone)) {
            $_SESSION['error_message1'] = "Phone number must be 10 digits.";
        } elseif (!filter_var($dr_email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error_message1'] = "Invalid email format.";
        } else {
            $sql = "UPDATE doctor SET dr_name='$dr_name', dr_email='$dr_email', specialization='$specialization', dr_phone='$dr_phone' WHERE dr_id='$doctor_id'";

                if (mysqli_query($conn, $sql)) {
                    $_SESSION['success_message1'] = "Doctor details updated successfully.";
                    header("Location: doctor_profile.php");
                    exit();
                } else {
                    $_SESSION['error_message1'] = "Error updating pet: " . mysqli_error($conn);
                }
            }
    }elseif (isset($_POST["cancel"])) {
        // Redirect to doctor_profile.php if cancel is clicked
        header("Location: doctor_profile.php");
        exit();
    }

?>
    

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor profile form</title>
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
            if (!empty($row['doctor_image'])) {
                echo "<div class='dr_img'>";
                echo "<img src='" . htmlspecialchars($row['doctor_image']) . "' alt='Doctor Photo' width='100px' height='auto'>";
                echo "</div>";
            } else {
                // Use the default image if no photo exists
                echo "<div class='dr_img'>";
                echo "<img src='../images/17246491.png' alt='Default Photo' width='100px'>";
                echo "</div>";
            }
            ?>

            <div class="dr_name">
                <?php echo "<h2>" . htmlspecialchars($row['dr_name']). "</h2>"; ?>
                <?php echo "<h4>License No : " . htmlspecialchars($row['license_number']). "</h4>"; ?>
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
            <form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
                <label for="name">Name:</label>
                <input id="name" type="text" name="dr_name" value="<?php echo htmlspecialchars($row['dr_name']);?>"><br><br>
                <label for="email">Email:</label>
                <input id="email" type="email" name="dr_email" value="<?php echo htmlspecialchars($row['dr_email']);?>"><br><br>
                <label for="phone">Phone number:</label>
                <input id="phone" type="text" name="dr_phone" value="<?php echo htmlspecialchars($row['dr_phone']);?>" min="0"><br><br>
                <label for="specialization">Specialization</label>
                <input id="specialization" type="text" name="specialization" value="<?php echo htmlspecialchars($row['specialization']);?>"><br><br>

                <!-- Button to change password -->
                <a href="change_password.php" class="change-password-btn">Change Password</a>

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