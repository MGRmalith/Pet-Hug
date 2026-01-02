<?php
    session_start();
    if (!isset($_SESSION['dr_id'])) {
        header("Location: doctorLogin.php");
        exit();
    }
    
    require '../connection.php'; // Include the database connection file
    include_once 'header_dr.php';
    
    $doctor_id = $_SESSION['dr_id']; // Assign doctor_id from session before using it in query
    

    // Handle Appointment Report submission
    if (isset($_POST['submit_appointment'])) {
        $appointment_id = $_POST['appointment_id'];
        $pet_id = $_POST['pet_id'];
        $details = $_POST['details'];

        // Validation
        if (empty($appointment_id) || empty($pet_id) || empty($details)) {
            $_SESSION['error_message1'] = "All fields are required.";
        }else if (!filter_var($appointment_id, FILTER_VALIDATE_INT) || (!filter_var($pet_id, FILTER_VALIDATE_INT))) {
            $_SESSION['error_message1'] = "Invalid Appointment ID or Pet ID";
        }else{
            // Check if the appointment exists
            $checkStmt = $conn->prepare("SELECT * FROM appointment WHERE appointment_id = ?");
            $checkStmt->bind_param("i", $appointment_id);
            $checkStmt->execute();
            $result = $checkStmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();

                if ($row['pet_id'] != $pet_id) {
                    $_SESSION['error_message1'] = "Pet ID is wrong.";
                }else if (is_null($row['details'])) {                  // Only update if current details are NULL
                    $stmt = $conn->prepare("UPDATE appointment SET pet_id = ?, details = ? WHERE appointment_id = ?");
                    $stmt->bind_param("ssi", $pet_id, $details, $appointment_id); 
        
                    if ($stmt->execute()) {
                        $_SESSION['success_message1'] = "Appointment report updated successfully.";
                    } else {
                        $_SESSION['error_message1'] = "Error: " . $stmt->error;
                    }
                } else {
                    $_SESSION['error_message1'] = "Details already exist for this appointment.";
                }
            } else {
                $_SESSION['error_message1'] = "Appointment ID does not exist.";
            }
    
            $checkStmt->close();
            // Check if $stmt is defined before calling close
            if (isset($stmt)) {
                $stmt->close();
            }
        }
    }


    // Handle Consultation Report submission
    if (isset($_POST['submit_consultation'])) {
        $consultation_id = $_POST['consultation_id'];
        $pet_id = $_POST['pet_id'];
        $details = $_POST['details'];

        // Validation
        if (empty($consultation_id) || empty($pet_id) || empty($details)) {
            $_SESSION['error_message2'] = "All fields are required.";
        }else if (!filter_var($consultation_id, FILTER_VALIDATE_INT) || (!filter_var($pet_id, FILTER_VALIDATE_INT))) {
            $_SESSION['error_message2'] = "Invalid Appointment ID.";
        }else{
            // Check if the consultation exists
            $checkStmt = $conn->prepare("SELECT * FROM consultation WHERE consultation_id = ?");
            $checkStmt->bind_param("i", $consultation_id);
            $checkStmt->execute();
            $result = $checkStmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();

                if ($row['pet_id'] != $pet_id) {
                    $_SESSION['error_message2'] = "Pet ID is wrong.";
                }else if (is_null($row['details'])) {   // Only update if current details are NULL 
                    $stmt = $conn->prepare("UPDATE consultation SET pet_id = ?, details = ? WHERE consultation_id = ?");
                    $stmt->bind_param("ssi", $pet_id, $details, $consultation_id); 
        
                    if ($stmt->execute()) {
                        $_SESSION['success_message2'] = "Consultation report updated successfully.";
                    } else {
                        $_SESSION['error_message2'] = "Error: " . $stmt->error;
                    }
                } else {
                    $_SESSION['error_message2'] = "Details already exist for this consultation.";
                }

            } else {
                $_SESSION['error_message2'] = "Consultation ID does not exist.";
            }
    
            $checkStmt->close();
            // Check if $stmt is defined before calling close
            if (isset($stmt)) {
                $stmt->close();
            }
        }

        
    }

    // Handle Hostel Report submission
    if (isset($_POST['submit_hostel'])) {
        $hostel_id = $_POST['hostel_id'];
        $pet_id = $_POST['pet_id'];
        $details = $_POST['details'];

        // Validation
        if (empty($hostel_id) || empty($pet_id) || empty($details)) {
            $_SESSION['error_message3'] = "All fields are required.";
        }else if ((!filter_var($hostel_id, FILTER_VALIDATE_INT)) || (!filter_var($pet_id, FILTER_VALIDATE_INT))) {
            $_SESSION['error_message3'] = "Invalid Appointment ID.";
        }else{
            // Check if the hostel id exists
            $checkStmt = $conn->prepare("SELECT * FROM hostel WHERE hostel_id = ?");
            $checkStmt->bind_param("i", $hostel_id);
            $checkStmt->execute();
            $result = $checkStmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                
                if ($row['pet_id'] != $pet_id) {  // Only update if current details are NULL
                    $_SESSION['error_message3'] = "Pet ID is wrong.";
                }else if (is_null($row['details'])) { 
                    $stmt = $conn->prepare("UPDATE hostel SET pet_id = ?, details = ? WHERE hostel_id = ?");
                    $stmt->bind_param("ssi", $pet_id, $details, $hostel_id); 
        
                    if ($stmt->execute()) {
                        $_SESSION['success_message3'] = "Hostel report updated successfully.";
                    } else {
                        $_SESSION['error_message3'] = "Error: " . $stmt->error;
                    }
                } else {
                    $_SESSION['error_message3'] = "Details already exist for this hostel id.";
                }
            } else {
                $_SESSION['error_message3'] = "Hostel ID does not exist.";
            }
    
            $checkStmt->close();
            // Check if $stmt is defined before calling close
            if (isset($stmt)) {
                $stmt->close();
            }
        }
    }
    mysqli_close($conn); 

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report form</title>
    <link rel="stylesheet" href="../afterLoginDoctor_style/doctor_add_report.css">
</head>
<body>


    <div class="reports_container">
    <h1 id="title">Add Reports</h1>
        <div class="dv">
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
            <h2 class="h">Appointment Report</h2>
            <form action="<?php echo $_SERVER['PHP_SELF']?>" method="post">
                <label class="i1" for="appointmentid">AppointmentID :</label>
                <input id="appointmentid" type="text" name="appointment_id" placeholder="Appointment ID">

                <label class="i2" for="pet_id_appointment">Pet ID :</label>
                <input id="pet_id_appointment" type="text" name="pet_id" placeholder="Pet ID">

                <label class="i3" for="details_appointment">Details :</label></td>
                <input id="details_appointment" type="text" name="details" placeholder="Details">        
                
                <input id="submit" type="submit" name="submit_appointment" value="Update">    
            </form>
        </div>

        <div class="dv">
        <?php
            if(isset($_SESSION['error_message2'])) {
                echo '<p style="color:red;">'.$_SESSION['error_message2'].'</p>';
                unset($_SESSION['error_message2']);
            }
            if(isset($_SESSION['success_message2'])) {
                echo '<p style="color:green;">'.$_SESSION['success_message2'].'</p>';
                unset($_SESSION['success_message2']);
            }
        ?>
            <h2 class="h">Consultation Report</h2>
            <form action="<?php echo $_SERVER['PHP_SELF']?>" method="post">
                <label class="i1" for="consultationid">Consultation ID :</label>
                <input id="consultationid" type="text" name="consultation_id" placeholder="Consultation ID">

                <label class="i2" for="pet_id_consultation">Pet ID :</label>
                <input id="pet_id_consultation" type="text" name="pet_id" placeholder="Pet ID">

                <label class="i3" for="details_consultation">Details :</label></td>
                <input id="details_consultation" type="text" name="details" placeholder="Details">        
                
                <input id="submit" type="submit" name="submit_consultation" value="Update">    
            </form>
        </div>

        <div class="dv">
        <?php
            if(isset($_SESSION['error_message3'])) {
                echo '<p style="color:red;">'.$_SESSION['error_message3'].'</p>';
                unset($_SESSION['error_message3']);
            }
            if(isset($_SESSION['success_message3'])) {
                echo '<p style="color:green;">'.$_SESSION['success_message3'].'</p>';
                unset($_SESSION['success_message3']);
            }
        ?>
            <h2 class="h">Hostel Report</h2>
            <form action="<?php echo $_SERVER['PHP_SELF']?>" method="post">
                <label class="i1" for="hostalid">Hostel ID :</label>
                <input id="hostelid" type="text" name="hostel_id" placeholder="Hostel ID">

                <label class="i2" for="pet_id_hostel">Pet ID :</label>
                <input id="pet_id_hostel" type="text" name="pet_id" placeholder="Pet ID">

                <label class="i3" for="details_hostel">Details :</label></td>
                <input id="details_hostel" type="text" name="details" placeholder="Details">        
                
                <input id="submit" type="submit" name="submit_hostel" value="Update">    
            </form>
        </div>
    </div>
    
</body>
</html>

<!-- footer -->
<?php include_once "../footer.php"?>

