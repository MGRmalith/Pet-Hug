<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: userLogin.php");
    exit();
}

require '../connection.php'; // Assuming this file handles the database connection
include_once "header_user.php";

$user_id = $_SESSION['user_id'];
$success_message = "";
$error_message = "";

// Fetch pending requests
$pending_sql = "SELECT h.hostel_id, h.start_date, h.end_date, h.details, p.pet_name, p.pet_image, h.status
                FROM hostel h
                JOIN pet p ON h.pet_id = p.pet_id
                WHERE h.user_id = ? AND h.status = 'Pending'
                ORDER BY h.start_date ASC";
$pending_stmt = $conn->prepare($pending_sql);
if (!$pending_stmt) {
    die("Error preparing statement for pending requests: " . $conn->error);
}
$pending_stmt->bind_param("i", $user_id);
$pending_stmt->execute();
$pending_result = $pending_stmt->get_result();

// Fetch confirmed requests
$confirmed_sql = "SELECT h.hostel_id, h.start_date, h.end_date, h.details, p.pet_name, p.pet_image, h.status
                  FROM hostel h
                  JOIN pet p ON h.pet_id = p.pet_id
                  WHERE h.user_id = ? AND h.status = 'Accepted'
                  ORDER BY h.start_date ASC";
$confirmed_stmt = $conn->prepare($confirmed_sql);
if (!$confirmed_stmt) {
    die("Error preparing statement for confirmed requests: " . $conn->error);
}
$confirmed_stmt->bind_param("i", $user_id);
$confirmed_stmt->execute();
$confirmed_result = $confirmed_stmt->get_result();


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $hostel_id = $_POST['hostel_id'];
    if(isset($_POST['delete_hostel_request'])) {
        $deleteQuery = "DELETE FROM hostel WHERE hostel_id = ? AND user_id = ?";
        $deleteStmt = $conn->prepare($deleteQuery);
        $deleteStmt->bind_param("ii", $hostel_id, $user_id);
        if ($deleteStmt->execute()) {
            $success_message = "Hostel request successfully deleted!";
            header("location: my_hostel.php");
            exit();
        } else {
            $error_message = "Error deleting hostel request.";
        }
    }

}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Hostel Bookings</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #e0f7ff; /* Sky blue background */
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1200px;
            margin: 50px auto;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
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
        h2 { 
            position: absolute;
            color: #0056b3;
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
        h3{
            width: 100%;
            text-align: center; 
            margin-top: 40px; 
            font-size: 28px;
            color: #007bff;
        }
        .button {
            margin-top: 20px;
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 10px;
            font-size: 22px;
        }
        .button:hover {
            background-color: #0056b3;
        }
        .button-container {
            position: absolute;
            width: 100%;
            text-align: center; 
            top: 37vh; 
        }
        .table-container {
            overflow-x: auto;
            margin-top: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 15px;
            text-align: center;
            border: 1px solid #ccc;
        }
        th {
            background-color: #007bff;
            color: white;
            position: sticky;
            top: 0;
        }
        td img { 
            max-width: 50px; 
            height: auto; 
            border-radius: 50%; 
        }
        td {
            background-color: #f9f9f9;
        }
        tr:nth-child(even) {
            background-color: #f1f1f1;
        }
        .status-pending {
            color: orange;
            font-weight: bold;
        }
        .status-confirmed {
            color: green;
            font-weight: bold;
        }
        .action-button {
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .action-cancel {
            background-color: #dc3545;
            color: white;
        }
        .fee-display {
            background-color: #28a745;
            color: white;
            border-radius: 5px;
            padding: 5px;
        }
        .hero-image {
            width: 100%;
            height: 300px;
            background-image: url("../images/my_appointments.jpeg");
            background-size: cover;
            background-position: center;
            border-radius: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="container">
<div class="about">
    <h2>My Hostel Bookings</h2>
    <img class="about-image" src="../images/ai-generated-8985306_640.jpg" alt="Pet" >
    <p class="about-text">Here, you can arrange comfortable stays for your pet by creating new hostel requests and managing existing ones. Ensure your pet has a safe and cozy place to stay—all from one simple page.</p>    
     <div class="button-container">
     <a href="request_hostel.php" class="button">Book a New Hostel</a>
    </div>
</div>
    

    <!-- Table of bookings -->
    <div class="table-container">
        <h3>Pending Requests</h3>
        <table>
            <thead>
                <tr>
                    <th>Hostel ID</th>
                    <th>Pet</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Details</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- Fetch and display pending requests -->
                <?php while ($row = $pending_result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $row['hostel_id']; ?></td>
                    <td><img src="<?php echo $row['pet_image']; ?>" alt="<?php echo $row['pet_name']; ?>"><?php echo $row['pet_name']; ?></td>
                    <td><?php echo $row['start_date']; ?></td>
                    <td><?php echo $row['end_date']; ?></td>
                    <td><?php echo $row['details']; ?></td>
                    <td>
                    <form method="POST" style="display:inline;">
                            <input type="hidden" name="hostel_id" value="<?php echo $row['hostel_id']; ?>">
                            <button type="submit" name="delete_hostel_request" class="action-button action-cancel">Cancel</button>
                        </form>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <div class="table-container">
        <h3>Confirmed Requests</h3>
        <table>
            <thead>
                <tr>
                    <th>Hostel ID</th>
                    <th>Pet</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                <!-- Fetch and display confirmed requests -->
                <?php while ($row = $confirmed_result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $row['hostel_id']; ?></td>
                    <td><img src="<?php echo $row['pet_image']; ?>" alt="<?php echo $row['pet_name']; ?>"><?php echo $row['pet_name']; ?></td>
                    <td><?php echo $row['start_date']; ?></td>
                    <td><?php echo $row['end_date']; ?></td>
                    <td><?php echo $row['details']; ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>
<?php
include_once "../footer.php"; 
?>