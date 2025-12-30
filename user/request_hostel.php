<?php include_once "header_user.php"?>

<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: userLogin.php");
    exit();
}

include_once "../connection.php";

// Fetch pets of the logged-in user
$user_id = $_SESSION['user_id'];
$query = "SELECT pet_id, pet_name FROM pet WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$pets_result = $stmt->get_result();

$success_message = "";
$error_message = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pet_id = $_POST['pet_id'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $comments = $_POST['comments'];

    // Validations
    if (empty($pet_id) || empty($start_date) || empty($end_date)) {
        $error_message = "All fields are required.";
    } elseif (strtotime($start_date) > strtotime($end_date)) {
        $error_message = "Start date cannot be after end date.";
    } else {
        // Insert request into the database
        $sql = "INSERT INTO hostel (user_id, pet_id, start_date, end_date, details, status) 
                VALUES (?, ?, ?, ?, ?, 'Pending')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iisss", $user_id, $pet_id, $start_date, $end_date, $comments);
        if ($stmt->execute()) {
            $success_message = "Hostel request submitted successfully.";
            header("Location:my_hostel.php");
        } else {
            $error_message = "There was an error submitting your request.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hostel Request - PetHug</title>
    <style>
        body {
            background-color: #e0f7ff;
            font-family: Arial, sans-serif;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            max-width: 600px;
            margin: 50px auto;
        }
        .container h2 {
            text-align: center;
            color: #007bff;
        }
        .container form {
            display: flex;
            flex-direction: column;
        }
        .container label {
            margin: 10px 0 5px;
        }
        .container input, .container select, .container textarea {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: 1px solid #ccc;
            width: 100%;
        }
        .container button {
            background-color: #007bff;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .message {
            text-align: center;
            color: green;
            margin-top: 10px;
        }
        .error-message {
            text-align: center;
            color: red;
            margin-top: 10px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Request Hostel Facility</h2>
    <?php if ($success_message) { ?>
        <p class="message"><?php echo $success_message; ?></p>
    <?php } ?>
    <?php if ($error_message) { ?>
        <p class="error-message"><?php echo $error_message; ?></p>
    <?php } ?>

    <form method="POST" action="">
        <label for="pet_id">Select Pet</label>
        <select name="pet_id" required>
            <option value="">--Select Your Pet--</option>
            <?php while ($pet = $pets_result->fetch_assoc()) { ?>
                <option value="<?php echo $pet['pet_id']; ?>"><?php echo $pet['pet_name']; ?></option>
            <?php } ?>
        </select>

        <label for="start_date">Start Date</label>
        <input type="date" name="start_date" required>

        <label for="end_date">End Date</label>
        <input type="date" name="end_date" required>

        <label for="comments">Additional Comments</label>
        <textarea name="comments" rows="4"></textarea>

        <button type="submit">Submit Request</button>
    </form>
</div>

</body>
</html>

<?php
include_once "../footer.php";
?>

<?php $conn->close(); ?>