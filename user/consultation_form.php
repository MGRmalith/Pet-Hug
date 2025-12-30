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

    if (isset($_POST["submit"])) {    
        $pet_id = $_POST['pet_id'];
        $dr_id = $_POST['dr_id'];              
        $message = $_POST['consultation_reason'];
       

       

        // Insert data into the consultation table
        $stmt = $conn->prepare("INSERT INTO consultation (user_id, pet_id, dr_id, consultation_reason) 
                        VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $user_id, $pet_id, $dr_id,  $message);

        if ($stmt->execute()) {
            $_SESSION['success_message5'] = "Consultation request submitted successfully!";
            header("location: my_consultations.php");
            exit();
        } else {
            $_SESSION['error_message5'] = "Error: " . $stmt->error;
        }
            $stmt->close();
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consult a Veterinarian</title>
    <link rel=stylesheet href="../afterLoginUser_style/consultation_form.css">

    <script>
        function validateForm() {
            const pet = document.querySelector("[name='pet_id']").value;
            const petBreed = document.querySelector("[name='pet_breed']").value;
            const veterinarian = document.querySelector("[name='dr_id']").value;
            const consultationType = document.querySelector("[name='consultation_type']").value;
            const message = document.getElementById('message').value;

            
            // Check if pet is selected
            if (!pet) {
                alert("Please select your pet.");
                return false;
            }
            
            // Check if pet breed is selected
            if (!petBreed) {
                alert("Please select the pet breed.");
                return false;
            }

            // Check if veterinarian is selected
            if (!veterinarian) {
                alert("Please select a veterinarian.");
                return false;
            }

            // Check if consultation type is selected
            if (!consultationType) {
                alert("Please select the consultation type.");
                return false;
            }
            // Check if message is at least 10 characters
            if (message.length < 10) {
                alert("Please describe your pet's issue with at least 10 characters.");
                return false;
            }
            
            return true;
        }
    </script>

</head>
<body>

    <div class="title">
        <h1>Consult a Veterinarian Anytime, Anywhere</h1>
    </div>

    <div class="consult-container">

            <!-- details -->
            <div class="section-text">
                <h2>Find a Veterinarian</h2>
                <p>
                    PetHug is a telemedicine platform dedicated to Animal Health Care.
                    It enables pet owners to consult online with Veterinary Doctors anytime and anywhere.
                    Ask questions about your pet's health, nutrition, behavior, or any other topic.
                </p>
                <ul class="list">
                    <li>✔ Talk to highly qualified vets online</li>
                    <li>✔ Save time & money</li>
                    <li>✔ Get a second opinion from experts</li>
                </ul>
            </div>

            <!-- form -->
            <div class="consultation-form">

                <?php
                if(isset($_SESSION['error_message5'])) {
                    echo '<p style="color:red;">'.$_SESSION['error_message5'].'</p>';
                    unset($_SESSION['error_message5']);
                }
                ?>

                <h3>Request a Consultation</h3>

                <form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="POST" onsubmit="return validateForm()">
                    <label for="pet">Select Pet:</label>
                    <select name="pet_id" required>
                    <option value="">Select pet</option>
                        <?php
                            $sql1 = "SELECT * FROM pet WHERE user_id = $user_id";
                            $result1 = mysqli_query($conn, $sql1);
                            while ($row = mysqli_fetch_assoc($result1)) {
                                echo "<option value='" . $row['pet_id'] . "'>" . $row['pet_name'] . "</option>";
                            }
                        ?>
                    </select>

                    
                    <label for="vet">Veterinarian:</label>
                    <select name="dr_id" required>
                    <option value="">Select veterinarian</option>
                        <?php
                            $sql3 = "SELECT DISTINCT * FROM doctor";
                            $result3 = mysqli_query($conn, $sql3);
                            while ($row = mysqli_fetch_assoc($result3)) {
                                echo "<option value='" . $row['dr_id'] . "'>" . $row['dr_name'] . "</option>";
                            }
                        ?>
                    </select>

                    <label for="message">Describe your pet's issue:</label>
                    <textarea id="message" name="consultation_reason" rows="4" required></textarea>

                    <button type="submit" class="btn" name="submit">Submit Request</button>
                </form>
            </div>


    </div>

</body>
</html>

<!--footer-->
<?php include_once "../footer.php" ?>

<?php $conn->close(); ?>
