<?php

    session_start();

    include_once "../connection.php";

    if (!isset($_SESSION['user_id'])) {
        header("Location: userLogin.php");
        exit();
    }

    $pet_id = $_GET['pet_id'];
    $user_id = $_SESSION['user_id'];

    // Retrieve the pet's name before deletion
    $sql_getpetname = "SELECT pet_name FROM pet WHERE pet_id = $pet_id AND user_id = $user_id";
    $result_getpetname = mysqli_query($conn, $sql_getpetname);

    if ($result_getpetname) {
        $row = mysqli_fetch_assoc($result_getpetname);
        $pet_name = $row['name'];

        //delete pet
        $sql_delete = "DELETE FROM pet WHERE pet_id = $pet_id AND user_id = $user_id";
        $result_delete = mysqli_query($conn,$sql_delete);

        if ($result_delete) {
            $_SESSION['success_message3'] = "Pet '$pet_name' deleted successfully.";
        } else {
            $_SESSION['error_message3'] = "Failed to delete pet. '$pet_name'";
        }
    
    header("Location: viewPets.php");
    exit();
    
    }
    $conn->close(); 
?>