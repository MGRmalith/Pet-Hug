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
    
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View pets</title>
    <link rel="stylesheet" href="../afterLoginUser_style/viewPets.css">
</head>
<body>

<?php 
    if(isset($_SESSION['success_message2'])) {
        echo '<p style="color:green;">'.$_SESSION['success_message2'].'</p>';
        unset($_SESSION['success_message2']);
    }
?>


<div class="container_H">
  <div class="about">
    <h2>My Pets</h2>
    <img src="../images/1920-ai-generated-dogs-run-in-grass-and-cats-run-on-the-front-yard (1).jpg"  class="about_img" alt="Pet" >
    <p class="about-text">Welcome to the PetHug Pet Management Page, your one-stop solution for caring for your beloved companions. Here, you can effortlessly add new pets to your profile, update their details, or manage existing records with ease. Whether you're keeping track of vaccinations, medical history, or simply organizing your pet's information, our user-friendly system ensures everything is in one place. Celebrate the joy of pet ownership with the confidence that their care is well-organized and at your fingertips. Let PetHug help you give your furry friends the love and attention they deserve!</p>    
    <div class="create-btn-container">
        <a href="add_pets.php" class="new-pet-btn">Add New Pet</a>
    </div>
  </div>

    

    <!-- Elements to display error and success messages -->
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
   
    <div class="container">
    <!-- Display Pets in Cards -->
    <?php 
        $sql = "SELECT * FROM pet WHERE user_id = $user_id ORDER BY pet_id ASC";
        $result = mysqli_query($conn,$sql);
    
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)){
                echo "<div class='pet-card'>
                        <img src='".$row['pet_image']."'alt='pet-photo'>
                        <h3>".$row['pet_name']."</h3>
                        <p>Species: ".$row['species']."</p>
                        <p>Breed: ".$row['breed']."</p>
                        <p>Age: ".$row['age']." years</p>
                        <p>Gender: ".$row['gender']."</p>
                        <a href='pet.php?pet_id=".$row['pet_id']."' id='pet".$row['pet_id']."'>View Details</a>
                    </div>";
            }
        }else {
            echo "<p>No pets found.</p>";
        }

    ?>  
    </div>  
</div>          

    <!--footer-->
    <?php include_once "../footer.php"?>
</body>
</html>

<?php $conn->close(); ?>