<?php 
   session_start();
   if (!isset($_SESSION['user_id'])) {
       header("Location: userLogin.php");
       exit();
   }
   
   $user_id = $_SESSION['user_id'];

   include_once "../connection.php";
   //header
   include_once "header_user.php";
   

    $upcomingAppointments = []; // Initialize with an empty array if there's no data
    $relevantPets = []; // Initialize with an empty array if there's no data
    // Get the current date
    $currentDateTime = date('Y-m-d H:i:s');
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
            <h1>Welcome, 
            <?php 
                $sql_name = "SELECT user_first_name FROM user WHERE user_id='$user_id'";
                $result_name = mysqli_query($conn, $sql_name);

                // Check if the query was successful and returned a row
                if ($result_name) {
                    $row = mysqli_fetch_assoc($result_name); // Fetch the row
                    echo htmlspecialchars($row['user_first_name']); // Output the first_name
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
                        WHERE a.user_id = '$user_id' AND a.appointment_time>='$currentDateTime' AND a.status='Accepted'
                        ORDER BY a.appointment_time ASC
                        LIMIT 3";

                $result_appointments = mysqli_query($conn, $sql_appointments);

                if (mysqli_num_rows($result_appointments) > 0) {
                    echo "<table border=1>
                    <tr>
                        <th>Appointment id</th>
                        <th>Pet id</th>
                        <th>Pet name</th>
                        <th>Appointment reason</th>
                        <th>Appointment time</th>
                        <th>Doctor name</th>
                    </tr>";

                    while ($row = mysqli_fetch_assoc($result_appointments)) {

                        echo "<tr>
                            <td>" . $row['appointment_id'] . "</td>
                            <td>" . $row['pet_id'] . "</td>
                            <td>" . $row['pet_name'] . "</td>
                            <td>" . $row['appointment_reason'] . "</td>
                            <td style='color:red;'>" . $row['appointment_time'] . "</td> 
                            <td>" . $row['dr_name'] . "</td>
                        </tr>";
                        }

                        echo "</table><br><br>";
                } else {
                    echo "<p class='message'>No upcoming Appoinments found.<p>";
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
                            c.consultation_time,
                            p.pet_id,
                            p.pet_name,
                            d.dr_name
                        FROM consultation c
                        INNER JOIN pet p ON c.pet_id = p.pet_id
                        INNER JOIN doctor d ON c.dr_id = d.dr_id
                        WHERE c.user_id = '$user_id' AND c.consultation_time>='$currentDateTime' AND c.status='Accepted'
                        ORDER BY c.consultation_time ASC
                        LIMIT 3";

                $result_consultations = mysqli_query($conn, $sql_consultations);

                if (mysqli_num_rows($result_consultations) > 0) {
                    echo "<table border=1>
                    <tr>
                        <th>Consultation id</th>
                        <th>Pet id</th>
                        <th>Pet name</th>
                        <th>Consultation reason</th>
                        <th>Consultation time</th>
                        <th>Doctor name</th>
                    </tr>";

                    while ($row = mysqli_fetch_assoc($result_consultations)) {

                        echo "<tr>
                            <td>" . $row['consultation_id'] . "</td>
                            <td>" . $row['pet_id'] . "</td>
                            <td>" . $row['pet_name'] . "</td>
                            <td>" . $row['consultation_reason'] . "</td>
                            <td style='color:red;'>" . $row['consultation_time'] . "</td>
                            <td>" . $row['dr_name'] . "</td>
                        </tr>";
                    }

                    echo "</table><br><br>";
                }else{
                    echo "<p class='message'>No upcoming consultations found.<p>";
                }
                ?>
            </div>

    </div>

    
</body>
</html>

<!--footer-->
<?php include_once "../footer.php"?>

<?php $conn->close(); ?>