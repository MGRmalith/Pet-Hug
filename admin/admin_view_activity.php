<?php
session_start();
include_once "../connection.php";

//header
include_once 'header_admin.php';

// Check if the admin is logged in (you can adjust this as needed)
if (!isset($_SESSION['admin_id'])) {
    header("Location: adminLogin.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];

// Check if user_id is provided in the URL
if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
}else{
    echo "User ID not specified.";
    exit();
}

// Fetch hospital fee
$hospital_fee_result = $conn->query("SELECT hospital_fee FROM hospital LIMIT 1");
$hospital_fee = $hospital_fee_result->fetch_assoc()['hospital_fee'] ?? 0;

// Get the current time
$current_time = date("Y-m-d H:i:s");

// Get all unpaid activities based on appointment time, consultation time, and hostel end date compared to the current time
$unpaid_activities_query = "
    (SELECT 
        'appointment' AS activity_type, appointment_id AS activity_id, doctor_id, appointment_time AS activity_time
    FROM appointment 
    WHERE user_id = $user_id AND appointment_time <= '$current_time' AND bill_id IS NULL)

    UNION ALL

    (SELECT 
        'consultation' AS activity_type, consultation_id AS activity_id, dr_id AS doctor_id, consultation_time AS activity_time
    FROM consultation 
    WHERE user_id = $user_id AND consultation_time <= '$current_time' AND bill_id IS NULL)

    UNION ALL

    (SELECT 
        'hostel' AS activity_type, hostel_id AS activity_id, NULL AS doctor_id, end_date AS activity_time
    FROM hostel 
    WHERE user_id = $user_id AND end_date <= '$current_time' AND bill_id IS NULL)

ORDER BY activity_time DESC
";

$unpaid_activities_result = $conn->query($unpaid_activities_query);
$unpaid_activities = [];


// Calculate fees for each unpaid activity
if ($unpaid_activities_result->num_rows > 0) {
    while ($activity = $unpaid_activities_result->fetch_assoc()) {
        $activity_type = $activity['activity_type'];
        $activity_id = $activity['activity_id'];
        $doctor_id = $activity['doctor_id'];
        $activity_time = $activity['activity_time'] ?? null;

        // Initialize fees
        $activity_fee = 0;
        $doctor_fee = 0;
        $total_bill = 0;

        // Calculate fees based on activity type
        if ($activity_type === 'hostel') {
            // Fetch hostel fee directly
            $hostel_fee_query = "SELECT hostel_fee, start_date, end_date FROM hostel WHERE hostel_id = $activity_id";
            $hostel_fee_result = $conn->query($hostel_fee_query);
            $hostel_data = $hostel_fee_result->fetch_assoc();
            
            if ($hostel_data) {
                $activity_fee = $hostel_data['hostel_fee'];
                $start_date = $hostel_data['start_date'];
                $end_date = $hostel_data['end_date'];

                if ($start_date && $end_date) {
                    $start = new DateTime($start_date);
                    $end = new DateTime($end_date);
                    $interval = $start->diff($end);
                    $days = $interval->days + 1; // Including both start and end day
                    $activity_fee *= $days; 
                }
            }
            $total_bill = $activity_fee;

        } elseif ($activity_type === 'consultation') {
            // Fetch consultation fee
            $consultation_fee_query = "SELECT consultation_fee, consultation_time FROM consultation WHERE consultation_id = $activity_id";
            $consultation_fee_result = $conn->query($consultation_fee_query);
            $consultation_data = $consultation_fee_result->fetch_assoc();
            
            if ($consultation_data) {
                $activity_fee = $consultation_data['consultation_fee'];
                $activity_time = $consultation_data['consultation_time'];
            }

            // Fetch doctor fee if a doctor was involved
            if ($doctor_id) {
                $doctor_result = $conn->query("SELECT dr_fee FROM doctor WHERE dr_id = $doctor_id");
                $doctor_fee = $doctor_result->fetch_assoc()['dr_fee'] ?? 0;
            }
            $total_bill = $activity_fee + $doctor_fee + $hospital_fee;

        } elseif ($activity_type === 'appointment') {
            // Fetch appointment fee
            $appointment_fee_query = "SELECT appointment_fee, appointment_time FROM appointment WHERE appointment_id = $activity_id";
            $appointment_fee_result = $conn->query($appointment_fee_query);
            $appointment_data = $appointment_fee_result->fetch_assoc();
            
            if ($appointment_data) {
                $activity_fee = $appointment_data['appointment_fee'];
                $activity_time = $appointment_data['appointment_time'];
            }

            // Fetch doctor fee if a doctor was involved
            if ($doctor_id) {
                $doctor_result = $conn->query("SELECT dr_fee FROM doctor WHERE dr_id = $doctor_id");
                $doctor_fee = $doctor_result->fetch_assoc()['dr_fee'] ?? 0;
            }
            $total_bill = $activity_fee + $doctor_fee + $hospital_fee;
        }

        // Store the results
        $unpaid_activities[] = [
            'activity_type' => $activity_type,
            'activity_id' => $activity_id,
            'activity_time' => $activity_time,
            'total_bill' => $total_bill,
        ];
    }
} else {
    echo "<p>No unpaid activities found.</p>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Unpaid Activities for User</title>
    <link rel="stylesheet" href="../afterLoginAdmin_style/admin_view_activity.css">
</head>
<body>

    <div class="container-activities">
        <h1>Unpaid Activities for User ID: <?php echo $user_id; ?></h1>
        
        <?php if (!empty($unpaid_activities)): ?>
            <table border="1">
                <tr>
                    <th>Activity Type</th>
                    <th>Activity ID</th>
                    <th>Activity Date</th>
                    <th>Due Amount</th>
                </tr>

                <?php foreach ($unpaid_activities as $activity): ?>
                    <tr>
                        <td><?php echo ucfirst($activity['activity_type']); ?></td>
                        <td><?php echo ucfirst($activity['activity_id']); ?></td>
                        <td><?php echo date('Y-m-d', strtotime($activity['activity_time'])); ?></td>
                        <td><?php echo number_format($activity['total_bill'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>No unpaid activities found for this user.</p>
        <?php endif; ?>
        
        <p><a href="admin_user_payment_status.php">Back to Unpaid Users</a></p>
    </div>

    
</body>
</html>

<!--footer-->
<?php include_once '../footer.php';?>

<?php $conn->close(); ?>