<?php
    session_start();
    include_once "../connection.php";

    if (!isset($_SESSION['user_id'])) {
        header("Location: userLogin.php");
        exit();
    }

    // Check if pet_id included
    if (isset($_GET['pet_id'])) {
        $pet_id = $_GET['pet_id'];
        $user_id = $_SESSION['user_id'];

        //select pet details
        $sql = "SELECT * FROM pet WHERE pet_id = $pet_id AND user_id = $user_id";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_assoc($result);
        } else {
            echo "<p>Pet not found or you do not have permission to view this pet.</p>";
            exit();
        }

        // Get the current time
        $current_time = date("Y-m-d H:i:s"); 

        //appointment-report details
        $sql_appointment = "SELECT 
                                a.appointment_id,
                                a.appointment_reason,
                                a.appointment_time,
                                d.dr_name,
                                a.details
                            FROM appointment a
                            INNER JOIN doctor d ON d.dr_id = a.doctor_id
                            WHERE pet_id = $pet_id AND user_id = $user_id AND a.appointment_time <= NOW() 
                            ORDER BY a.appointment_time DESC
                            LIMIT 1";
        $result_appointment = mysqli_query($conn, $sql_appointment);

        //consultation-report details
        $sql_consultation = "SELECT 
                                c.consultation_id,
                                c.consultation_reason,
                                c.consultation_time,
                                d.dr_name,
                                c.details
                            FROM consultation c
                            INNER JOIN doctor d ON d.dr_id = c.dr_id
                            WHERE pet_id = $pet_id AND user_id = $user_id AND c.consultation_time <= NOW() 
                            ORDER BY c.consultation_time DESC
                            LIMIT 1";
        $result_consultation = mysqli_query($conn, $sql_consultation);

        //boarding-report details
        $sql_hostel = "SELECT *
                        FROM hostel
                        WHERE pet_id = $pet_id AND user_id = $user_id AND end_date <= NOW() 
                        ORDER BY end_date DESC
                        LIMIT 1";
        $result_hostel = mysqli_query($conn, $sql_hostel);

    } else {
        echo "<p>No pet selected.</p>";
        header("Location: viewPets.php");
        exit();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pet Details</title>
    <link rel="stylesheet" href="../afterLoginUser_style/pet.css">
</head>
<body>
    <!--header-->
    <?php include_once "header_user.php"?>

    <!-- Elements to display error and success messages -->
    <?php
            if(isset($_SESSION['success_message4'])) {
                echo '<p style="color:green;">'.$_SESSION['success_message4'].'</p>';
                unset($_SESSION['success_message4']);
            }
    ?>

    <!--main topic-->
    <h1 class="title">Pet Details for "<?php echo htmlspecialchars($row['pet_name']); ?>"</h1>

    <div class="pet-detail-container">
        <!--pet details-->
        <div class="pet-details">
            <img src="<?php echo $row['pet_image']; ?>" alt="pet photo">
            <p><strong>Pet ID:</strong> <?php echo htmlspecialchars($row['pet_id']); ?></p>
            <p><strong>Species:</strong> <?php echo htmlspecialchars($row['species']); ?></p>
            <p><strong>Breed:</strong> <?php echo htmlspecialchars($row['breed']); ?></p>
            <p><strong>Age:</strong> <?php echo htmlspecialchars($row['age']); ?> years</p>
            <p><strong>Weight:</strong> <?php echo htmlspecialchars($row['weight']); ?> kg</p>
            <p><strong>Gender:</strong> <?php echo htmlspecialchars($row['gender']); ?></p>

            <!-- buttons for editing and deleting the pet -->
            <a href="edit_pet.php?pet_id=<?php echo $row['pet_id']; ?>">Edit Pet</a>
            <a href="delete_pet.php?pet_id=<?php echo $row['pet_id']; ?>" onclick="return confirm('Are you sure you want to delete this pet?');">Delete Pet</a>
        </div>

        <!-- reports for pet -->
        <div class="reports-section">
            <h2 class="reports">Latest Reports</h2><br>

            <!-- Appointment Reports -->
            <h3>Appointment Reports</h3>
            <?php
                if (mysqli_num_rows($result_appointment) > 0){
                    while ($row_appointment = mysqli_fetch_assoc($result_appointment)){ 
                        $appointment_time = $row_appointment['appointment_time'];
                        $details = $row_appointment['details'] ?? 'Report pending';

                        if (is_null($row_appointment['details']) && $current_time > $appointment_time) {
                            $details = 'Report pending';
                        }

                        echo "
                        <div class='report-item'>
                            <p><strong>Appointment id: </strong>". htmlspecialchars($row_appointment['appointment_id']) . "</p>
                            <p><strong>Reason: </strong>". htmlspecialchars($row_appointment['appointment_reason']) . "</p>
                            <p><strong>Date: </strong>". htmlspecialchars($row_appointment['appointment_time']) . "</p>
                            <p><strong>Doctor name: </strong>". htmlspecialchars($row_appointment['dr_name']) . "</p>
                            <p><strong>Details: </strong>". htmlspecialchars($details) . "</p><br>";
                        echo "</div>";
                    }
                }else{
                    echo "<p>No appointment reports available.</p><br>";
                }
            ?>
            <br>

            <!-- Consultation Reports -->
            <h3>Consultation Reports</h3>
            <?php
                if (mysqli_num_rows($result_consultation) > 0){
                    while ($row_consult = mysqli_fetch_assoc($result_consultation)){
                        $consultation_time = $row_consult['consultation_time'];
                        $details = $row_consult['details'] ?? 'Report pending';

                        if (is_null($row_consult['details']) && $current_time > $consultation_time) {
                            $details = 'Report pending';
                        }

                        echo "
                        <div class='report-item'>
                            <p><strong>Consultation id: </strong>". htmlspecialchars($row_consult['consultation_id']) . "</p>
                            <p><strong>Reason: </strong>". htmlspecialchars($row_consult['consultation_reason']) . "</p>
                            <p><strong>Date: </strong>". htmlspecialchars($row_consult['consultation_time']) . "</p>
                            <p><strong>Doctor name: </strong>". htmlspecialchars($row_consult['dr_name']) . "</p>
                            <p><strong>Details: </strong>". htmlspecialchars($details) . "</p><br>"; 
                        echo "</div>";
                    }
                }else{
                    echo "<p>No consultation reports available.</p><br>";
                }
            ?>
            <br>

            <!-- Hostel Reports -->
            <h3>Hostel Reports</h3>
            <?php
                if (mysqli_num_rows($result_hostel) > 0){
                    while ($row_hostel = mysqli_fetch_assoc($result_hostel)){
                        $end_date = $row_hostel['end_date'];
                        $details = $row_hostel['details'] ?? 'Report pending';

                        if (is_null($row_hostel['details']) && $current_time > $end_date) {
                            $details = 'Report pending';
                        }

                        echo "
                        <div class='report-item'>
                            <p><strong>Start Date: </strong>". htmlspecialchars($row_hostel['start_date']) . "</p>
                            <p><strong>End Date: </strong>". htmlspecialchars($row_hostel['end_date']) . "</p>
                            <p><strong>Details: </strong>". htmlspecialchars($details) . "</p><br>";
    
                        echo "</div>";
                    }
                }else{
                    echo "<p>No boarding reports available.</p><br>";
                }
            ?>
        </div>
    </div>

    <!--footer-->
    <?php include_once "../footer.php"?>

</body>
</html>

<?php $conn->close(); ?>