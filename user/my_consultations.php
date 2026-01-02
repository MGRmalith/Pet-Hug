<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: userLogin.php");
    exit();
}

include_once '../connection.php';
include_once 'header_user.php';

$user_id = $_SESSION['user_id'];

// Fetch consultations based on status
$query = "SELECT c.consultation_id, c.consultation_reason, c.status, p.pet_name, d.dr_name, p.pet_image
          FROM consultation c
          JOIN pet p ON c.pet_id = p.pet_id
          JOIN doctor d ON c.dr_id = d.dr_id
          WHERE c.user_id = ? 
          ORDER BY c.created_at ASC";

$stmt = $conn->prepare($query);
if ($stmt === false) {
    die("Error preparing the SQL statement: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Handle consultation actions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $consultation_id = $_POST['consultation_id'];

    if (isset($_POST['cancel_consultation'])) {
        $cancelQuery = "UPDATE consultation SET status='Canceled' WHERE consultation_id = ? AND user_id = ? AND status='Pending'";
        $cancelStmt = $conn->prepare($cancelQuery);
        $cancelStmt->bind_param("ii", $consultation_id, $user_id);

        if ($cancelStmt->execute()) {
            $cancel_message = "Consultation successfully canceled!";
            header("location: my_consultations.php");
            exit();
        } else {
            $error_message = "Error cancelling consultation.";
        }
    }

   

    if (isset($_POST['reschedule_consultation'])) {
        $new_reason = $_POST['consultation_reason'];
        $consultation_id = $_POST['consultation_id'];
        $user_id = $_SESSION['user_id'];
       

        $rescheduleQuery = "UPDATE consultation SET consultation_reason = ?, status = 'Pending' 
                            WHERE consultation_id = ? AND user_id = ? AND status='Canceled'";
        $rescheduleStmt = $conn->prepare($rescheduleQuery);
        $rescheduleStmt->bind_param("sii", $new_reason, $consultation_id, $user_id);

        if ($rescheduleStmt->execute()) {
            $success_message = "Consultation successfully rescheduled!";
            header("location: my_consultations.php");
            exit();
        } else {
            $error_message = "Error rescheduling consultation.";
        }
    }
    if(isset($_POST['delete_consultation'])) {

        $deleteQuery = "DELETE FROM consultation WHERE consultation_id = ? AND user_id = ?";
        $deleteStmt = $conn->prepare($deleteQuery);
        $deleteStmt->bind_param("ii", $consultation_id, $user_id);
        if ($deleteStmt->execute()) {
            $success_message = "Consultation successfully deleted!";
            header("location: my_consultations.php");
            exit();
        } else {
            $error_message = "Error deleting consultation.";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Consultations</title>
    <style>
        body {
            background-color: #e0f7ff;
            font-family: 'Arial', sans-serif; 
            color: #333; 
            margin: 0; 
            padding: 0; 
        }

        .container { 
            max-width: 1200px; 
            margin: 50px auto; 
            padding: 20px; 
            background-color: #fff; 
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); 
            border-radius: 10px; 
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
            opacity: 0.9;
            border-radius: 10px;   
            filter: brightness(70%);
            
        }
        .container h2 { 
            position: absolute;
            color:  #333;
            top: 6vh;
            width: 100%;
            text-align: center; 
            font-size: 44px;
            z-index: 1;
        }
        .about-text{
            position: absolute;
            top: 16vh; 
            color: black;
            font-size: 20px;
            left: 2vw;
        }
        .container h3{
            width: 100%;
            text-align: center; 
            margin-top: 40px; 
            font-size: 28px;
            color: #007bff;
        }
        .table-container {
            max-height: 400px;
            overflow-y: auto;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px; 
            margin-bottom: 20px;
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
            position: sticky;
            top: 0;
            z-index: 1;
        }
        td img { 
            max-width: 50px; 
            height: auto; 
            border-radius: 50%; 
        }
        tbody{
            max-width: 400px;
            overflow:y;
        }
        .container button { 
            padding: 10px 15px; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer; 
        }

        .cancel-btn { 
            background-color: #ff4500; 
            color: white; 
        }

        .pay-btn {
            padding: 10px 15px; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer; 
            background-color: #28a745; 
            color: white; 
        }

        .reschedule-btn { 
            background-color: #ff6347; 
            color: white; 
        }
        .message { 
            text-align: center; 
            margin-bottom: 20px; 
            color: #555; 
            font-size: 1.2em; 
        }
        .new-appointment-btn {
            margin-top: 20px;
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 10px;
            font-size: 22px;
        }

        .new-appointment-btn:hover {
            background-color: #0056b3;
        }

        .create-btn-container { 
            position: absolute;
            width: 100%;
            text-align: center; 
            top: 40vh; 
        }
        .delete-btn {
            background-color: #dc3545;
            color: white;
            margin-top: 3px;
        }

        /* Modal styles */
        .modal {
            display: none; 
            position: fixed; 
            z-index: 1; 
            left: 0; 
            top: 0; 
            width: 100%; 
            height: 100%; 
            overflow: auto; 
            background-color: rgb(0,0,0); 
            background-color: rgba(0,0,0,0.4); 
            padding-top: 60px; 
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%; 
            max-width: 500px; 
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
    <script>
        function confirmCancel() {
            return confirm("Are you sure you want to cancel this consultation?");
        }

        function openModal(consultationId, currentDate, currentTime, currentReason) {
            document.getElementById("consultation_id").value = consultationId;
            document.getElementById("consultation_reason").value = currentReason;
            document.getElementById("myModal").style.display = "block";
        }

        function closeModal() {
            document.getElementById("myModal").style.display = "none";
        }

        window.onclick = function(event) {
            var modal = document.getElementById("myModal");
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</head>
<body>

<div class="container">
<div class="about">
    <h2>Your Consultations</h2>
    <img class="about-image" src="../images/consultaion.jpg" alt="Pet" >
    <p class="about-text">This page makes it easy to request new consultations with our veterinary team and manage existing ones. Stay on top of your pet’s health by scheduling and tracking consultations whenever needed—all in one convenient place.</p>    
     <div class="create-btn-container">
        <a href="consultation_form.php" class="new-appointment-btn">Book New Consultation</a>
    </div>
</div>
    
    <?php if (isset($cancel_message)) { echo "<div class='success'>$cancel_message</div>"; } ?>
    <?php if (isset($error_message)) { echo "<div class='error'>$error_message</div>"; } ?>

    <?php
    // Fetch separate consultations by status
    $statuses = ['Pending', 'Accepted', 'Canceled'];
    
    foreach ($statuses as $status) {
        $queryByStatus = "SELECT c.consultation_id, c.created_at, c.consultation_reason, c.status, p.pet_name, d.dr_name, p.pet_image 
                          FROM consultation c
                          JOIN pet p ON c.pet_id = p.pet_id
                          JOIN doctor d ON c.dr_id = d.dr_id
                          WHERE c.user_id = ? AND c.status = ?
                          ORDER BY c.created_at ASC";
    
        $stmtByStatus = $conn->prepare($queryByStatus);
        $stmtByStatus->bind_param("is", $user_id, $status);
        $stmtByStatus->execute();
        $resultByStatus = $stmtByStatus->get_result();
        
        if ($resultByStatus->num_rows > 0) { ?>
            <h3><?php echo $status; ?> Consultations</h3>
            <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Consultation ID</th>
                        <th>Pet</th>
                        <th>Doctor</th>
                        <th>Consultation Date and Time</th>
                        <th>Reason</th>
                        <?php if ($status !== 'Accepted') { ?>
                        <th>Actions</th>
                        <?php } ?>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $resultByStatus->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $row['consultation_id']; ?></td>
                            <td>
                            <img src="<?php echo $row['pet_image']; ?>" alt="<?php echo $row['pet_name']; ?>"><?php echo $row['pet_name']; ?>
                            </td>
                            <td><?php echo $row['dr_name']; ?></td>
                            <td><?php echo $row['created_at']; ?></td>
                            <td><?php echo $row['consultation_reason']; ?></td>
                            <?php if ($status !== 'Accepted') { ?>
                            <td>
                                <?php if ($status == 'Pending') { ?>
                                    <form method="POST" onsubmit="return confirmCancel();">
                                        <input type="hidden" name="consultation_id" value="<?php echo $row['consultation_id']; ?>">
                                        <button type="submit" name="cancel_consultation" class="cancel-btn">Cancel</button>
                                    </form>
                                <?php } elseif ($status == 'Canceled') { ?>
                                    <button onclick="openModal(<?php echo $row['consultation_id']; ?>, '<?php echo $row['created_at']; ?>', '<?php echo $row['consultation_reason']; ?>')" class="reschedule-btn">Reschedule</button>
                                    <form method="POST" style="display:inline;">
                                      <input type="hidden" name="consultation_id" value="<?php echo $row['consultation_id']; ?>">
                                      <button type="submit" name="delete_consultation" class="delete-btn">Delete</button>
                                    </form>
                                <?php } ?>
                            </td>
                            <?php }  ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            </div>
        <?php } else { ?>
            <p class="message">You have no <?php echo strtolower($status); ?> consultations at the moment.</p>
        <?php }
    }
    ?>
    
</div>

  <!-- Modal for Rescheduling Consultation -->
  <div id="myModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Reschedule Consultation</h2><br>
            <form method="POST" >
                <input type="hidden" id="consultation_id" name="consultation_id" value="">
                <label for="consultation_reason">New Reason:</label>
                <textarea id="consultation_reason" name="consultation_reason" required></textarea><br><br>
                <button type="submit" class="pay-btn" name="reschedule_consultation" id="reschedule_consultation">Reschedule</button>
            </form>
        </div>
    </div>


</body>
</html>


<?php
include_once "../footer.php";
?>