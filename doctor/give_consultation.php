<?php
session_start();
if (!isset($_SESSION['dr_id'])) {
    header("Location: doctorLogin.php");
    exit();
}

require_once '../connection.php';
include_once 'header_dr.php';

$doctor_id = $_SESSION['dr_id'];
$today = date('Y-m-d');

// Fetch today's consultations
$acceptedQuery = "SELECT consultation.*, pet.pet_name,user.user_phone AS owner_phone, user.user_first_name AS pet_owner_name FROM consultation 
                  JOIN pet ON consultation.pet_id = pet.pet_id 
                  JOIN user ON consultation.user_id = user.user_id 
                  WHERE consultation.dr_id = ?  AND consultation.status = 'accepted'";
$acceptedStmt = $conn->prepare($acceptedQuery);
$acceptedStmt->bind_param("i", $doctor_id,);
$acceptedStmt->execute();
$acceptedConsultations = $acceptedStmt->get_result();

// Fetch pending consultations
$pendingQuery = "SELECT consultation.*, pet.pet_name, user.user_first_name AS pet_owner_name FROM consultation 
                 JOIN pet ON consultation.pet_id = pet.pet_id 
                 JOIN user ON consultation.user_id = user.user_id 
                 WHERE consultation.dr_id = ? AND consultation.status = 'Pending'";
$pendingStmt = $conn->prepare($pendingQuery);
$pendingStmt->bind_param("i", $doctor_id);
$pendingStmt->execute();
$pendingConsultations = $pendingStmt->get_result();

// Fetch canceled consultations
$canceledQuery = "SELECT consultation.*, pet.pet_name, user.user_first_name AS pet_owner_name FROM consultation 
                  JOIN pet ON consultation.pet_id = pet.pet_id 
                  JOIN user ON consultation.user_id = user.user_id 
                  WHERE consultation.dr_id = ? AND consultation.status = 'canceled'";
$canceledStmt = $conn->prepare($canceledQuery);
$canceledStmt->bind_param("i", $doctor_id);
$canceledStmt->execute();
$canceledConsultations = $canceledStmt->get_result();

// Handle button actions
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $consultation_id = $_POST['consultation_id'];
    $action = $_POST['action'];

    switch ($action) {
        case "accept":
            $updateQuery = "UPDATE consultation SET status = 'accepted', created_at = NOW() WHERE consultation_id = ?";
            break;
        case "cancel":
            $updateQuery = "UPDATE consultation SET status = 'canceled' WHERE consultation_id = ?";
            break;
        case "delete":
            $updateQuery = "DELETE FROM consultation WHERE consultation_id = ?";
            break;
        case "update_notes":
            $dr_notes = $_POST['details'];
            $updateQuery = "UPDATE consultation SET details = ?, status = 'completed' WHERE consultation_id = ?";
            break;
        default:
            $updateQuery = "";
            break;
    }

    if (!empty($updateQuery)) {
        $updateStmt = $conn->prepare($updateQuery);
        if ($action === "update_notes") {
            $updateStmt->bind_param("si", $dr_notes, $consultation_id);
        } else {
            $updateStmt->bind_param("i", $consultation_id);
        }

        if ($updateStmt->execute()) {
            header("Location: give_consultation.php");
            exit();
        } else {
            $error_message = "Error updating consultation. Please try again.";
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
    <title>Consultation Management - PetHug</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #e0f7ff;
            margin: 0;
        }
        .container {
            max-width: 1200px;
            margin: auto;
            margin-top: 50px;
            margin-bottom: 50px;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .about{
            position: relative;
            width: 100%;
            margin: 0 auto;
        }
        .about-image{
            display: block;
            margin-left: auto;
            margin-right: auto;
            width: 100%;
            position: relative;
            opacity: 0.7;
            border-radius: 10px;
            filter: brightness(70%);
        }
        h2 { 
            position: absolute;
            color: #333;
            top: 9%;
            width: 100%;
            text-align: center; 
            font-size: 44px;
            z-index: 1;
        }
        .about-text{
            position: absolute;
            top: 27%; 
            color: black;
            font-size: 20px;
            left: 2vw;
        }
        h3{
            width: 100%;
            text-align: center; 
            margin-top: 40px; 
            font-size: 28px;
            color: #007bff;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table, th, td {
            border: 1px solid #ccc;
        }
        th, td {
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #007bff;
            color: white;
        }
       
        .accept-btn {
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            background-color: #28a745;
            color: white;
        }
        .cancel-btn, .delete-btn {
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            background-color: #dc3545;
            color: white;
        }
        .update-notes-btn, .ShowNotes-btn {
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            background-color: #007bff;
            color: white;
        }
        .ShowNotes-btn {
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            background-color: #007bff;
            color: white; 
            text-decoration: none;
            display: inline-block;
            text-align: center; 
        }
        .notes-container {
            display: none;
            border: 1px solid #ccc;
            padding: 10px;
            margin-top: 5px;
            background-color: #f9f9f9;
        }
        .whatsapp-btn button {
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            background-color: #25D366;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 3px;
            margin-left: 3px;
        }

       .whatsapp-btn button:hover {
           background-color: #1da851;
        }

    </style>
</head>
<body>

<div class="container">
    <div class="about">
        <h2>Consultation Management</h2>
        <img class="about-image" src="../images/1920-female-hands-playing-with-an-orange-kitten.jpg"alt="Consultation">
        <p class="about-text">As a dedicated veterinary professional, streamline your consultation management effortlessly 
            with this organized system. Accept new consultations to keep your schedule full, or cancel and 
            delete as needed to maintain control over your day. By staying organized with pending, 
            accepted, and canceled consultations in one place, you can prioritize each pet and client 
            interaction, ensuring you have the time and focus to deliver the highest level of care. 
            This system is designed to support your workflow, making it easier to manage appointments and 
            keep every consultation running smoothly.</p>
    </div>
    
    <?php if (isset($error_message)) { echo "<p style='color:red;text-align:center;'>$error_message</p>"; } ?>

    <h3>Accepted Consultations</h3>
    <table>
        <tr>
            <th>Consultation ID</th>
            <th>Pet Owner</th>
            <th>Pet Name</th>
            <th>Status</th>
            <th>Doctor Notes</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $acceptedConsultations->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $row['consultation_id']; ?></td>
                <td><?php echo $row['pet_owner_name']; ?></td>
                <td><?php echo $row['pet_name']; ?></td>
                <td><?php echo ucfirst($row['status']); ?></td>
                <td>
                  <a href="../Doctor/doctor_view_report.php?consultation_id=<?php echo $row['consultation_id']; ?>&pet_id=<?php echo $row['pet_id']; ?>" class="ShowNotes-btn">
                  Show Notes
                  </a>
                </td>
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="consultation_id" value="<?php echo $row['consultation_id']; ?>">
                        <input type="hidden" name="action" value="update_notes">
                        <textarea name="details" id="details_<?php echo $row['consultation_id']; ?>" placeholder="Enter notes..." required></textarea>
                        <button type="submit" class="update-notes-btn">Save Notes</button>
                    </form>
                 <a 
                    href="https://wa.me/<?php echo $row['owner_phone']; ?>?text=<?php echo urlencode("Hello " . $row['pet_owner_name'] . ", regarding your pet " . $row['pet_name'] . ": "); ?>" 
                    target="_blank" 
                    class="whatsapp-btn">
                    <button type="button">WhatsApp</button>
                </a>
                </td>
            </tr>
        <?php } ?>
    </table>

    <h3>Pending Consultations</h3>
    <table>
        <tr>
            <th>Consultation ID</th>
            <th>Pet Owner</th>
            <th>Pet Name</th>
            <th>Date and Time</th>
            <th>Doctor Notes</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $pendingConsultations->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $row['consultation_id']; ?></td>
                <td><?php echo $row['pet_owner_name']; ?></td>
                <td><?php echo $row['pet_name']; ?></td>
                <td><?php echo $row['created_at']; ?></td>
                <td>
                  <a href="../Doctor/doctor_view_report.php?consultation_id=<?php echo $row['consultation_id']; ?>&pet_id=<?php echo $row['pet_id']; ?>" class="ShowNotes-btn">
                  Show Notes
                  </a>
                </td>
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="consultation_id" value="<?php echo $row['consultation_id']; ?>">
                        <input type="hidden" name="action" value="accept">
                        <button type="submit" class="accept-btn">Accept</button>
                    </form>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="consultation_id" value="<?php echo $row['consultation_id']; ?>">
                        <input type="hidden" name="action" value="cancel">
                        <button type="submit" class="cancel-btn">Cancel</button>
                    </form>
                </td>
            </tr>
        <?php } ?>
    </table>


 <!-- Canceled Appointments -->
 <h3>Canceled Appointments</h3>
<table>
    <tr>
        <th>Consultation ID</th>
        <th>Pet Owner</th>
        <th>Pet Name</th>
        <th>Date and Time</th>
        <th>Actions</th> <!-- Add Actions header -->
    </tr>
    <?php while ($row = $canceledConsultations->fetch_assoc()) { ?>
        <tr>
            <td><?php echo $row['consultation_id']; ?></td>
            <td><?php echo $row['pet_owner_name']; ?></td>
            <td><?php echo $row['pet_name']; ?></td>
            <td><?php echo $row['created_at']; ?></td>
            <td>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="consultation_id" value="<?php echo $row['consultation_id']; ?>">
                    <input type="hidden" name="action" value="delete">
                    <button type="submit" class="delete-btn">Delete</button>
                </form>
            </td>
        </tr>
    <?php } ?>
</table>

</div>

<script>
function showNotes(petId) {
    var notesContainer = document.getElementById("notes_" + petId);
    if (notesContainer.style.display === "block") {
        notesContainer.style.display = "none";
    } else {
        notesContainer.style.display = "block";
    }
}
</script>

</body>
</html>
<?php
include_once "../footer.php";
?>