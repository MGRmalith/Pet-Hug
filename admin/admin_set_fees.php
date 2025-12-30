<?php
session_start();

include_once "../connection.php";
//header
include_once 'header_admin.php';

// Check if the admin is logged in (you can adjust this as needed)
if (!isset($_SESSION['admin_id'])) {
    header("Location: adminLogin.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];

$appointment_id = isset($_GET['appointment_id']) ? $_GET['appointment_id'] : '';
$consultation_id = isset($_GET['consultation_id']) ? $_GET['consultation_id'] : '';
$hostel_id = isset($_GET['hostel_id']) ? $_GET['hostel_id'] : '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check which form was submitted and update the corresponding fee
    if (isset($_POST['update_appointment_fee'])) {
        $appointment_id = $_POST['appointment_id'];
        $appointment_fee = $_POST['appointment_fee'];

        // Check if the appointment is approved
        $stmt = $conn->prepare("SELECT status FROM appointment WHERE appointment_id = ?");
        $stmt->bind_param("i", $appointment_id);
        $stmt->execute();
        $stmt->bind_result($status);
        $stmt->fetch();
        $stmt->close();
        
        if ($status === 'Accepted') {
            // Proceed to update the fee
            $stmt = $conn->prepare("UPDATE appointment SET appointment_fee = ? WHERE appointment_id = ?");
            $stmt->bind_param("di", $appointment_fee, $appointment_id);
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Appointment fee updated successfully!";
            } else {
                $_SESSION['error_message'] = "Update failed: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $_SESSION['error_message'] = "Update failed: Appointment is not approved. So can't update appointment fee.";
        }
    }

    if (isset($_POST['update_consultation_fee'])) {
        $consultation_id = $_POST['consultation_id'];
        $consultation_fee = $_POST['consultation_fee'];

        // Check if the consultation is approved
        $stmt = $conn->prepare("SELECT status FROM consultation WHERE consultation_id = ?");
        $stmt->bind_param("i", $consultation_id);
        $stmt->execute();
        $stmt->bind_result($status);
        $stmt->fetch();
        $stmt->close();
        
        if ($status === 'Accepted') {
            // Proceed to update the fee
            $stmt = $conn->prepare("UPDATE consultation SET consultation_fee = ? WHERE consultation_id = ?");
            $stmt->bind_param("di", $consultation_fee, $consultation_id);
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Consultation fee updated successfully!";
            } else {
                $_SESSION['error_message'] = "Update failed: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $_SESSION['error_message'] = "Update failed: Consultation is not approved. So can't update consultation fee.";
        }
    }

    if (isset($_POST['update_hostel_fee'])) {
        $hostel_id = $_POST['hostel_id'];
        $hostel_fee = $_POST['hostel_fee'];

        // Check if the hostel is approved
        $stmt = $conn->prepare("SELECT status FROM hostel WHERE hostel_id = ?");
        $stmt->bind_param("i", $hostel_id);
        $stmt->execute();
        $stmt->bind_result($status);
        $stmt->fetch();
        $stmt->close();
        
        if ($status === 'Accepted') {
            // Proceed to update the fee
            $stmt = $conn->prepare("UPDATE hostel SET hostel_fee = ? WHERE hostel_id = ?");
            $stmt->bind_param("di", $hostel_fee, $hostel_id);
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Hostel fee updated successfully!";
            } else {
                $_SESSION['error_message'] = "Update failed: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $_SESSION['error_message'] = "Update failed: Hostel request is not approved. So can't update hostel fee.";
        }
    }

    if (isset($_POST['update_doctor_fee'])) {
        $doctor_id = $_POST['doctor_id'];
        $doctor_fee = $_POST['doctor_fee'];

        $stmt = $conn->prepare("UPDATE doctor SET dr_fee = ? WHERE dr_id = ?");
        $stmt->bind_param("di", $doctor_fee, $doctor_id);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Doctor fee updated successfully!";
        } else {
            $_SESSION['error_message'] = "Update failed: " . $stmt->error;
        }
        $stmt->close();
    }

    if (isset($_POST['update_hospital_fee'])) {
        $hospital_id = $_POST['hospital_id'];
        $hospital_fee = $_POST['hospital_fee'];

        $stmt = $conn->prepare("UPDATE hospital SET hospital_fee = ? WHERE hospital_id = ?");
        $stmt->bind_param("di", $hospital_fee, $hospital_id);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Hospital fee updated successfully!";
        } else {
            $_SESSION['error_message'] = "Update failed: " . $stmt->error;
        }
        $stmt->close();
    }

    if (isset($_POST['update_supervision_fee'])) {
        $hostel_id = $_POST['hostel_id'];
        $dr_supervision_fee = $_POST['dr_supervision_fee'];

        $stmt = $conn->prepare("SELECT status FROM hostel WHERE hostel_id = ?");
        $stmt->bind_param("i", $hostel_id);
        $stmt->execute();
        $stmt->bind_result($status);
        $stmt->fetch();
        $stmt->close();
        
        if ($status === 'Accepted') {
            $stmt = $conn->prepare("UPDATE hostel SET dr_supervision_fee = ? WHERE hostel_id = ?");
            $stmt->bind_param("di", $dr_supervision_fee, $hostel_id);
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Doctor supervision fee updated successfully!";
            } else {
                $_SESSION['error_message'] = "Update failed: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $_SESSION['error_message'] = "Update failed: Hostel request is not approved. So can't update supervision fee.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin - Update Fees</title>
    <link rel="stylesheet" href="../afterLoginAdmin_style/admin_set_fees.css">
</head>
<body>

    <div class="set_fee">
        <h1 id="title">Admin - Update Fees</h1>

        <?php
            if(isset($_SESSION['error_message'])) {
                echo '<p style="color:red;">'.$_SESSION['error_message'].'</p>';
                unset($_SESSION['error_message']);
            }
            if(isset($_SESSION['success_message'])) {
                echo '<p style="color:green;">'.$_SESSION['success_message'].'</p>';
                unset($_SESSION['success_message']);
            }
        ?>

        <div class="update-fees-container">

        <!-- Update Appointment Fee -->
        <form action="<?php echo $_SERVER['PHP_SELF']?>" method="POST">
            <h3>Update Appointment Fee</h3><br>
            <label for="appointment_id">Appointment ID:</label>
            <input id="appointment_id" type="number" name="appointment_id" value="<?php echo isset($appointment_id) ? $appointment_id : ''; ?>" required>
            <label for="appointment_fee">Appointment Fee:</label>
            <input id="appointment_fee" type="number" step="0.01" name="appointment_fee" required>
            <button type="submit" name="update_appointment_fee">Update Appointment Fee</button>
        </form>


            <!-- Update Consultation Fee -->
            <form action="<?php echo $_SERVER['PHP_SELF']?>" method="POST">
                <h3>Update Consultation Fee</h3><br>
                <label for="consultation_id">Consultation ID:</label>
                <input id="consultation_id" type="number" name="consultation_id" value="<?php echo isset($consultation_id) ? $consultation_id : ''; ?>" required>
                <label for="consultation_fee">Consultation Fee:</label>
                <input id="consultation_fee" type="number" step="0.01" name="consultation_fee" required>
                <button type="submit" name="update_consultation_fee">Update Consultation Fee</button>
            </form>

            <!-- Update Hostel Fee -->
            <form action="<?php echo $_SERVER['PHP_SELF']?>" method="POST">
                <h3>Update Hostel Fee</h3><br>
                <label for="hostel_id">Hostel ID:</label>
                <input id="hostel_id" type="number" name="hostel_id" value="<?php echo isset($hostel_id) ? $hostel_id : '';?>" required>
                <label for="hostel_fee">Hostel Fee:</label>
                <input id="hostel_fee" type="number" step="0.01" name="hostel_fee" required>
                <button type="submit" name="update_hostel_fee">Update Hostel Fee</button>
            </form>

            <!-- Update Doctor Fee -->
            <form action="<?php echo $_SERVER['PHP_SELF']?>" method="POST">
                <h3>Update Doctor Fee</h3><br>
                <label for="doctor_id">Doctor ID:</label>
                <input id="doctor_id" type="number" name="doctor_id" required>
                <label for="doctor_fee">Doctor Fee:</label>
                <input id="doctor_fee" type="number" step="0.01" name="doctor_fee" required>
                <button type="submit" name="update_doctor_fee">Update Doctor Fee</button>
            </form>

            <!-- Update Hospital Fee -->
            <form action="<?php echo $_SERVER['PHP_SELF']?>" method="POST">
                <h3>Update Hospital Fee</h3><br>
                <label for="hospital_id">Hospital ID:</label>
                <input id="hospital_id" type="number" name="hospital_id" required>
                <label for="hospital_fee">Hospital Fee:</label>
                <input id="hospital_fee" type="number" step="0.01" name="hospital_fee" required>
                <button type="submit" name="update_hospital_fee">Update Hospital Fee</button>
            </form>

            <!-- Update Doctor_supervision Fee -->
            <form action="<?php echo $_SERVER['PHP_SELF']?>" method="POST">
                <h3>Update Doctor Supervision Fee</h3><br>
                <label for="doctor_sup_fee">Doctor Supervision Fee For Hostel (daily):</label>
                <input id="doctor_sup_fee" type="number" step="0.01" name="dr_supervision_fee" required>
                <button type="submit" name="update_supervision_fee">Update Doctor Fee</button>
            </form>

        </div>
    </div>

</body>
</html>

<!--footer-->
<?php include_once '../footer.php';?>

<?php $conn->close(); ?>
