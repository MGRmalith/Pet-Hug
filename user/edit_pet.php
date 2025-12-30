<?php
    session_start();

    include_once "../connection.php";
    //header
    include_once "header_user.php";

    if (!isset($_SESSION['user_id'])) {
        header("Location: userLogin.php");
        exit();
    }
    
    $user_id = $_SESSION['user_id'];

    // check pet_id passerd from url
    if (isset($_GET['pet_id'])) {
        $pet_id = $_GET['pet_id'];
        $user_id = $_SESSION['user_id'];
        
        // Fetch pet details from the database
        $sql = "SELECT * FROM pet WHERE pet_id = $pet_id AND user_id = $user_id";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_assoc($result);
        } else {
            $_SESSION['error_message4'] = "<p>Pet not found or you do not have permission to edit this pet.</p>";
            exit();
        }
    } else {
        $_SESSION['error_message4'] = "<p>No pet selected.</p>";
        header("Location: pet.php");
        exit();
    }

    // Handle form submission to update pet details
    if (isset($_POST["update"])) {
        $pet_name = $_POST['pet_name'];
        $species = $_POST['species'];
        $age = $_POST['age'];
        $weight = $_POST['weight'];
        $breed = $_POST['breed'];
        $gender = $_POST['gender'];

        // Validate the input
        if (empty($pet_name)) {
            $_SESSION['error_message4'] = "Pet name is required.";
        } else if (empty($species)) {
            $_SESSION['error_message4'] = "Species is required.";
        } else if (empty($age) || !is_numeric($age) || $age < 0) {
            $_SESSION['error_message4'] = "Valid age is required.";
        } else if (empty($weight) || !is_numeric($weight) || $weight < 0) {
            $_SESSION['error_message4'] = "Valid weight is required.";
        } else if (empty($gender)) {
            $_SESSION['error_message4'] = "Gender is required.";
        } else {
            // Check if an image is being updated
            $target_file = $row['pet_image']; // Keep the old image by default
            $uploadOk = 1;
            if (!empty($_FILES["fileToUpload"]["name"])) {
                $target_dir = "../uploads/";
                $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);

                
                $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

                // Validate the new image
                $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
                if ($check === false) {
                    $uploadOk = 0;
                    $_SESSION['error_message4'] = "File is not an image.";
                } else if ($_FILES["fileToUpload"]["size"] > 500000) {
                    $uploadOk = 0;
                    $_SESSION['error_message4'] = "Sorry, your file is too large.";
                } else if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
                    $uploadOk = 0;
                    $_SESSION['error_message4'] = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
                } else {
                    move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file);
                }
            }

            // Update the database with the new pet details
            if ($uploadOk == 1) {
                $sql = "UPDATE pet SET pet_name='$pet_name', species='$species', age='$age', weight='$weight', breed='$breed', gender='$gender', pet_image='$target_file' WHERE pet_id='$pet_id' AND user_id='$user_id'";

                if (mysqli_query($conn, $sql)) {
                    $_SESSION['success_message4'] = "Pet details updated successfully.";
                    header("Location: pet.php?pet_id=$pet_id");
                    exit();
                } else {
                    $_SESSION['error_message4'] = "Error updating pet: " . mysqli_error($conn);
                }
            }
        }
    }elseif (isset($_POST["cancel"])) {
        // Redirect to pet.php if cancel is clicked
        header("Location: pet.php");
        exit();
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add pets form</title>
    <link rel="stylesheet" href="../afterLoginUser_style/add_pet.css">
</head>
<body>

    <!-- Elements to display error and success messages -->
    <?php
            if(isset($_SESSION['error_message4'])) {
                echo '<p style="color:red;">'.$_SESSION['error_message4'].'</p>';
                unset($_SESSION['error_message4']);
            }
    ?>

    <div id="main">
        <h2>Edit Pet Details</h2>
        <form action="edit_pet.php?pet_id=<?php echo $pet_id; ?>" method="post" enctype="multipart/form-data">
            
            <label for="name">Pet Name:</label>
            <input id="name" type="text" name="pet_name" size="35" value="<?php echo htmlspecialchars($row['pet_name']); ?>"><br>

            <label for="species">Species:</label>
            <input id="species" type="text" name="species" size="35" value="<?php echo htmlspecialchars($row['species']); ?>"><br>

            <label for="breed">Breed:</label>
            <input id="breed" type="text" name="breed" size="35" value="<?php echo htmlspecialchars($row['breed']); ?>"><br>

            <label for="age">Age:</label>
            <input id="age" type="text" name="age" size="35" value="<?php echo htmlspecialchars($row['age']); ?>"><br>

            <label for="weight">Weight:</label>
            <input id="weight" type="text" name="weight" size="35" value="<?php echo htmlspecialchars($row['weight']); ?>"><br>

            <label>Gender: </label>
            <select name="gender">
                <option value="Male" <?php echo ($row['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                <option value="Female" <?php echo ($row['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                <option value="Other" <?php echo ($row['gender'] == 'Other') ? 'selected' : ''; ?>>Unknown</option>
            </select><br>

            <label for="fileToUpload">Update Image:</label>
            <input id="fileToUpload" type="file" name="fileToUpload"><br>
            <img src="<?php echo htmlspecialchars($row['pet_image']); ?>" alt="Current Pet Image" style="width:100px;"><br>

            <!--submit button-->
            <input id="submit" type="submit" name="update" value="Update Pet"><br>
            <!-- Cancel button as a submit button -->
            <input id="cancel" type="submit" name="cancel" value="Cancel">
        </form>
    </div>


    
</body>
</html>

<!-- footer -->
<?php include_once "../footer.php"?>

<?php $conn->close(); ?>