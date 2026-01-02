<?php 
    session_start();
    if (!isset($_SESSION['dr_id'])) {
        header("Location: doctorLogin.php");
        exit();
    }
    
    require '../connection.php'; // Include the database connection file
    include_once 'header_dr.php';
    
    $doctor_id = $_SESSION['dr_id']; // Assign doctor_id from session before using it in query
    


    $upcomingAppointments = []; // Initialize with an empty array if there's no data
    $relevantPets = []; // Initialize with an empty array if there's no data
    // Get the current date
    $currentDateTime = date('Y-m-d H:i:s');


 // Handle button actions
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $action = $_POST['action'];

    switch ($action) {
        case "save_notes":
            $consultation_id = $_POST['consultation_id'];
            $dr_notes = $_POST['details'];
            $updateQuery = "UPDATE consultation SET details = ?, status = 'completed' WHERE consultation_id = ?";
            break;
        case "update_notes":
            $appointment_id = $_POST['appointment_id'];
            $details = $_POST['details'];
            $updateQuery = "UPDATE appointment SET details = ?, status = 'Completed' WHERE appointment_id = ?";
            break;
        default:
            $updateQuery = ""; // No action matched
            break;
    }

    if (!empty($updateQuery)) {
        $updateStmt = $conn->prepare($updateQuery);
        
        if ($action === "save_notes") {
            // Bind parameters for `save_notes`
            $updateStmt->bind_param("si", $dr_notes, $consultation_id);
        } else if ($action === "update_notes") {
            // Bind parameters for `update_notes`
            $updateStmt->bind_param("si", $details, $appointment_id);
        }

        if ($updateStmt->execute()) {
            header("Location: doctor_dashboard.php");
            exit();
        } else {
            $error_message = "Error updating records. Please try again.";
        }
    } else {
        $error_message = "No valid action provided.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="../afterLoginUser_style/dashboard.css">
</head>
<body>

    <?php
            if(isset($_SESSION['success_message'])) {
                echo '<p style="color:green;">'.$_SESSION['success_message'].'</p>';
                unset($_SESSION['success_message']);
            }
    ?>

    <div class="dashboard">

        <!--welcome Note-->
        <div class="welcome-note">
            <h1>Welcome, Dr.
            <?php 
                $sql_name = "SELECT dr_name FROM doctor WHERE dr_id='$doctor_id'";
                $result_name = mysqli_query($conn, $sql_name);

                // Check if the query was successful and returned a row
                if ($result_name) {
                    $row = mysqli_fetch_assoc($result_name); // Fetch the row
                    echo htmlspecialchars($row['dr_name']); // Output the first_name
                } else {
                    die("Query failed: " . mysqli_error($conn));
                }
                ?>
            </h1><br>
        </div>
            
        <!--upcoming appoinments-->
        <section class="upcoming-appointments-consultations">
            <h2>Upcoming Appointments & Consultations</h2><br>

            <div class="upcoming-appoinments">
                <h3>Appointments</h3>
                <?php

                // Fetch upcoming appointments from the database
                $sql_appointments = "SELECT 
                            a.appointment_id,
                            a.appointment_reason,
                            a.appointment_time,
                            p.pet_id,
                            p.pet_name,
                            d.dr_name
                        FROM appointment a
                        INNER JOIN pet p ON a.pet_id = p.pet_id
                        INNER JOIN doctor d ON a.doctor_id = d.dr_id
                        WHERE a.doctor_id = '$doctor_id' AND a.appointment_time>='$currentDateTime' AND a.status='Accepted'
                        ORDER BY a.appointment_time ASC
                        LIMIT 3";

                $result_appointments = mysqli_query($conn, $sql_appointments);

                if (mysqli_num_rows($result_appointments) > 0) {
                    echo "<table border=1>
                    <tr>
                        <th>Appointment id</th>
                        <th>Pet id</th>
                        <th>Pet name</th>
                        <th>Appointment time</th>
                        <th>Appointment reason</th>
                        <th>Details</th>
                        <th>Actions</th>
                    </tr>";

                    while ($row = mysqli_fetch_assoc($result_appointments)) {?>

                <td><?php echo $row['appointment_id']; ?></td>
                <td><?php echo $row['pet_id']; ?></td>
                <td><?php echo $row['pet_name']; ?></td>
                <td><?php echo $row['appointment_time']; ?></td>
                <td><?php echo $row['appointment_reason']; ?></td>
                <td>
                  <a href="../Doctor/doctor_view_report.php?appointment_id=<?php echo $row['appointment_id']; ?>&pet_id=<?php echo $row['pet_id']; ?>" class="ShowNotes-btn">
                  Show Notes
                  </a>
                </td>

                <td>
                   <form method="POST" style="display:inline;">
                      <input type="hidden" name="appointment_id" value="<?php echo $row['appointment_id']; ?>">
                      <input type="hidden" name="action" value="update_notes">
                      <textarea name="details" id="details_<?php echo $row['appointment_id']; ?>" placeholder="Enter notes..." required></textarea>
                     <button type="submit" class="update-notes-btn">Save Notes</button>
                   </form>
                </td>
                            
                    </tr>

                    <?php
                        }

                        echo "</table><br><br>";
                } else {
                    echo "<p>No upcoming Appoinments found.<p>";
                }
                ?>
            </div><br>
            
            <div class="upcoming-consultations">
                <h3>Consultations</h3>
                <?php

                // Fetch upcoming consultations from the database
                $sql_consultations = "SELECT 
                            c.consultation_id,
                            c.consultation_reason,
                            c.created_at,
                            p.pet_id,
                            p.pet_name,
                            d.dr_name,
                            u.user_phone,
                            u.user_first_name
                        FROM consultation c
                        INNER JOIN pet p ON c.pet_id = p.pet_id
                        INNER JOIN doctor d ON c.dr_id = d.dr_id
                        INNER JOIN user u ON c.user_id = u.user_id
                        WHERE c.dr_id = '$doctor_id' AND c.created_at>='$currentDateTime' AND c.status='Accepted'
                        ORDER BY c.created_at ASC
                        LIMIT 3";

                $result_consultations = mysqli_query($conn, $sql_consultations);

                if (mysqli_num_rows($result_consultations) > 0) {
                    echo "<table border=1>
                    <tr>
                        <th>Consultation id</th>
                        <th>Pet id</th>
                        <th>Pet name</th>
                        <th>Consultation time</th>
                        <th>Consultation reason</th>
                        <th>Details </th>
                        <th>Actions</th>
                    </tr>";

                    while ($row = mysqli_fetch_assoc($result_consultations)) {?>

              <tr>
                <td><?php echo $row['consultation_id']; ?></td>
                <td><?php echo $row['pet_id']; ?></td>
                <td><?php echo $row['pet_name']; ?></td>
                <td><?php echo $row['created_at']; ?></td>
                <td><?php echo $row['consultation_reason']; ?></td>
                <td>
                  <a href="../Doctor/doctor_view_report.php?consultation_id=<?php echo $row['consultation_id']; ?>&pet_id=<?php echo $row['pet_id']; ?>" class="ShowNotes-btn">
                  Show Notes
                  </a>
                </td>
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="consultation_id" value="<?php echo $row['consultation_id']; ?>">
                        <input type="hidden" name="action" value="save_notes">
                        <textarea name="details" id="details_<?php echo $row['consultation_id']; ?>" placeholder="Enter notes..." required></textarea>
                        <button type="submit" class="update-notes-btn">Save Notes</button>
                    </form>
                 <a 
                    href="https://wa.me/<?php echo $row['user_phone']; ?>?text=<?php echo urlencode("Hello " . $row['user_first_name'] . ", regarding your pet " . $row['pet_name'] . ": "); ?>" 
                    target="_blank" 
                    class="whatsapp-btn">
                    <button type="button">WhatsApp</button>
                </a>
                </td>
            </tr>
            <?php
                    }

                    echo "</table><br><br>";
                }else{
                    echo "<p>No upcoming consultations found.<p>";
                }
                ?>
            </div>

    </div>
</body>
</html>


<!--footer-->
<?php include_once "../footer.php"?>

<?php $conn->close(); ?>
            