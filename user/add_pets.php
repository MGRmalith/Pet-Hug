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
        // input fields
        $pet_name = $_POST['pet_name'];
        $species = $_POST['species'];
        $age = $_POST['age'];
        $weight = $_POST['weight'];
        $breed = $_POST['breed'];
        $gender = $_POST['gender']; 

        // validations
        if (empty($pet_name)) {
            $_SESSION['error_message2'] = "Pet name is required.";
        }else if(empty($species)) {
            $_SESSION['error_message2'] = "Species is required.";
        }else if(empty($breed)) {
                $_SESSION['error_message2'] = "Breed is required.";
        }else if(empty($age) || !is_numeric($age) || $age < 0) {
            $_SESSION['error_message2'] = "Valid age is required.";
        }else if(empty($weight) || !is_numeric($weight) || $weight < 0) {
            $_SESSION['error_message2'] = "Valid weight is required.";
        }else if(empty($gender)) {
            $_SESSION['error_message2'] = "Gender is required.";
        }else{
            //************* image upload *********
            $target_dir = "../uploads/";
            if (!isset($_FILES["fileToUpload"]) || empty($_FILES["fileToUpload"]["name"])) {
                $_SESSION['error_message2'] = "Pet image is required.";
            }else{
            $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);

            $uploadOk = 1;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            // Check if image file is an image or not
            if(isset($_POST["submit"])){ 
                $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
                if($check !== false){
                    //echo "File is an image - " . $check["mime"] . ".";
                    $uploadOk = 1;
                } else {
                    $_SESSION['error_message2'] = "File is not an image.";
                    $uploadOk = 0;
                }
            }

            // Check if file already exists
            if (file_exists($target_file)){
                $_SESSION['error_message2'] = "Sorry, file already exists.";
                $uploadOk = 0;
                header("Location: add_pets.php");
                exit();
            }
            

            // Check file size
            if ($_FILES["fileToUpload"]["size"] > 500000){
                $_SESSION['error_message2'] = "Sorry, your file is too large.";
                $uploadOk = 0;
                header("Location: add_pets.php");
                exit();
            }

            // Allow certain file formats
            if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif"){
                $_SESSION['error_message2'] =  "Sorry, only JPG, JPEG, PNG & GIF are allowed.";
                $uploadOk = 0;
                header("Location: add_pets.php");
                exit();
            }

            // Check if $uploadOk is set to 0 by an error
            if ($uploadOk == 0){
                $_SESSION['error_message2'] =  "Sorry, your file was not uploaded.";
                header("Location: add_pets.php");
                exit();
            }


            // if everything is ok, no errors try to upload file
            if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)){
                echo "The file " . htmlspecialchars(basename($_FILES["fileToUpload"]["name"])) . " has been uploaded.";

                // Prepare SQL to insert the file name and path into the database
                $image_name = basename($_FILES["fileToUpload"]["name"]);
                $image_path = $target_file;

                $sql = "INSERT INTO pet (user_id, pet_name, species, pet_id, age, weight, breed, gender, pet_image) 
                        VALUES ('$user_id', '$pet_name', '$species', '$pet_id', '$age', '$weight', '$breed', '$gender', '$image_path')";

                $result = mysqli_query($conn,$sql);

                if($result){
                    $_SESSION['success_message2'] = "Successfully added new pet: " . htmlspecialchars($pet_name);
                    // Redirect to avoid form resubmission on page refresh (optional)
                    header("location: pet.php");
                    exit();
                }else{
                    $_SESSION['error_message2'] = "Update error". mysqli_error($conn);
                }

            } else {
                $_SESSION['error_message2'] = "Sorry, there was an error uploading your file.";
                header("Location: add_pet.php");
                exit();
            }
        }
    }
}
$conn->close(); 
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
            if(isset($_SESSION['error_message2'])) {
                echo '<p style="color:red;">'.$_SESSION['error_message2'].'</p>';
                unset($_SESSION['error_message2']);
            }
            
    ?>

    <div id="main">
        <h2>Enter Pet Details</h2>
        <form action="add_pets.php" method="post" enctype="multipart/form-data">

            <label for="name">Pet Name:</label>
            <input id="name" type="text" name="pet_name" size="35" placeholder="Pet Name"><br>

            <label for="species">Species:</label>
            <input id="species" type="text" name="species" size="35" placeholder="Species"><br>

            <label for="breed">Breed:</label>
            <input id="breed" type="text" name="breed" size="35" placeholder="Breed"><br>

            <label for="age">Age:</label>
            <input id="age" type="text" name="age" size="35" placeholder="Age"><br>

            <label for="weight">Weight:</label>
            <input id="weight" type="text" name="weight" size="35" placeholder="Weight"><br>

            <label>Gender: </label>
            <select name="gender" required>
                <option value="">Select Gender</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Unknown</option>
            </select><br>

            <lable for="fileToUpload">Add image: </lable>
            <input id="fileToUpload" type="file" name="fileToUpload" >
            
            <input id="submit" type="submit" name="submit" value="Submit">            
        </form>
    </div>
    
</body>
</html>

<!-- footer -->
<?php include_once "../footer.php"?>