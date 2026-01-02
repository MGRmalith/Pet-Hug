<?php
session_start();
if (!isset($_SESSION['dr_id'])) {
    header("Location: doctorLogin.php");
    exit();
}

// Include database connection
include_once '../connection.php';
include_once 'header_dr.php';
   



// Get doctor ID from session
$doctor_id = $_SESSION['dr_id'];

// Fetch only completed appointments
$appointments_query = "SELECT * FROM appointment WHERE doctor_id = $doctor_id AND status = 'Completed'";
$consultations_query = "SELECT * FROM consultation WHERE dr_id = $doctor_id AND status = 'Completed'";

$appointments_result = mysqli_query($conn, $appointments_query);
$consultations_result = mysqli_query($conn, $consultations_query);

// Process completed appointments
$appointments = [];
while ($row = mysqli_fetch_assoc($appointments_result)) {
    $appointments[] = $row;
}

// Process completed consultations
$consultations = [];
while ($row = mysqli_fetch_assoc($consultations_result)) {
    $consultations[] = $row;
}

// Aggregate data
$total_appointments = count($appointments);
$total_consultations = count($consultations);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #e0f7ff;
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
        h1{
          color: #0056b3;
          width: 100%;
          text-align: center; 
          font-size: 36px;
        }
        h2{
            width: 100%;
            text-align: center; 
            font-size: 28px;
            color: #007bff;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        .summary {
            margin: 20px 0;
            display: flex;
            justify-content: space-between;
        }
        .summary div {
            padding: 10px;
            background: #007bff;
            color: white;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Doctor Report</h1>
        <div class="summary">
            <div>Total Completed Appointments: <?php echo $total_appointments; ?></div>
            <div>Total Completed Consultations: <?php echo $total_consultations; ?></div>
        </div>
        <h2>Completed Appointments</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($appointments as $appointment): ?>
                    <tr>
                        <td><?php echo $appointment['appointment_id']; ?></td>
                        <td><?php echo $appointment['appointment_time']; ?></td>
                       
                        <td><?php echo $appointment['details']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <h2>Completed Consultations</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($consultations as $consultation): ?>
                    <tr>
                        <td><?php echo $consultation['consultation_id']; ?></td>
                        <td><?php echo $consultation['created_at']; ?></td>
                        <td><?php echo $consultation['details']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<?php
include_once "../footer.php";