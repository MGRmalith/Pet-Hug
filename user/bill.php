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
    WHERE user_id = $user_id AND appointment_time <= '$current_time' AND bill_id IS NULL AND status='Accepted' AND appointment_fee IS NOT NULL)

    UNION ALL

    (SELECT 
        'consultation' AS activity_type, consultation_id AS activity_id, dr_id AS doctor_id, consultation_time AS activity_time
    FROM consultation 
    WHERE user_id = $user_id AND consultation_time <= '$current_time' AND bill_id IS NULL AND status='Accepted' AND consultation_fee IS NOT NULL)

    UNION ALL

    (SELECT 
        'hostel' AS activity_type, hostel_id AS activity_id, dr_id AS doctor_id, end_date AS activity_time
    FROM hostel 
    WHERE user_id = $user_id AND end_date <= '$current_time' AND bill_id IS NULL AND status='Accepted' AND hostel_fee IS NOT NULL)


ORDER BY activity_time DESC
";

$unpaid_activities_result = $conn->query($unpaid_activities_query);
$unpaid_activities = [];

// Initialize total unpaid bill
$total_unpaid_bill = 0;

// Calculate fees for each unpaid activity
if ($unpaid_activities_result->num_rows > 0) {
    while ($activity = $unpaid_activities_result->fetch_assoc()) {
        $activity_type = $activity['activity_type'];
        $activity_id = $activity['activity_id'];
        $doctor_id = $activity['doctor_id'];

        // Initialize fees
        $activity_fee = 0;
        $doctor_fee = 0;

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
                    $activity_fee *= $days; // Total hostel fee for the duration
                }
            }
            $total_bill = $activity_fee;

        } elseif ($activity_type === 'consultation') {
            // Fetch consultation fee
            $consultation_fee_query = "SELECT consultation_fee FROM consultation WHERE consultation_id = $activity_id";
            $consultation_fee_result = $conn->query($consultation_fee_query);
            $consultation_data = $consultation_fee_result->fetch_assoc();
            
            if ($consultation_data) {
                $activity_fee = $consultation_data['consultation_fee'];
            }

            // Fetch doctor fee if a doctor was involved
            if ($doctor_id) {
                $doctor_result = $conn->query("SELECT dr_fee FROM doctor WHERE dr_id = $doctor_id");
                $doctor_fee = $doctor_result->fetch_assoc()['dr_fee'] ?? 0;
            }
            $total_bill = $activity_fee + $doctor_fee + $hospital_fee;

        } elseif ($activity_type === 'appointment') {
            // Fetch appointment fee
            $appointment_fee_query = "SELECT appointment_fee FROM appointment WHERE appointment_id = $activity_id";
            $appointment_fee_result = $conn->query($appointment_fee_query);
            $appointment_data = $appointment_fee_result->fetch_assoc();
            
            if ($appointment_data) {
                $activity_fee = $appointment_data['appointment_fee'];
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
            'activity_fee' => $activity_fee,
            'doctor_fee' => $doctor_fee,
            'hospital_fee' => $hospital_fee,
            'total_bill' => $total_bill,
        ];

        // Add to total unpaid bill
        $total_unpaid_bill += $total_bill;
    }
} 

$_SESSION['total_amount'] = $total_unpaid_bill;


// Collect activity IDs
$appointment_ids = [];
$consultation_ids = [];
$hostel_ids = [];

$sql = "SELECT appointment_id
    FROM appointment 
    WHERE user_id = $user_id AND appointment_time <= '$current_time' AND bill_id IS NULL AND status='Accepted' AND appointment_fee IS NOT NULL";


$result = mysqli_query($conn,$sql);

if ($result->num_rows > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $appointment_ids[] = $row['appointment_id'];
    }
}

$sql = "SELECT consultation_id
    FROM consultation 
    WHERE user_id = $user_id AND consultation_time <= '$current_time' AND bill_id IS NULL AND status='Accepted' AND consultation_fee IS NOT NULL";

$result = mysqli_query($conn,$sql);

if ($result->num_rows > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $consultation_ids[] = $row['consultation_id'];
    }
}

$sql = "SELECT hostel_id
    FROM hostel 
    WHERE user_id = $user_id AND end_date <= '$current_time' AND bill_id IS NULL AND status='Accepted' AND hostel_fee IS NOT NULL";

$result = mysqli_query($conn,$sql);

if ($result->num_rows > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $hostel_ids[] = $row['hostel_id'];
    }
}

// Store activity IDs in session
$_SESSION['appointment_ids'] = $appointment_ids;
$_SESSION['consultation_ids'] = $consultation_ids;
$_SESSION['hostel_ids'] = $hostel_ids;

// Fetch user details
$user_query = "SELECT * FROM user WHERE user_id = $user_id"; 
$user_result = $conn->query($user_query);
$user_data = $user_result->fetch_assoc();

if ($user_data) {
    $user_first_name = $user_data['user_first_name'];
    $user_last_name = $user_data['user_last_name']; 
    $user_email = $user_data['user_email']; 
    $user_phone = $user_data['user_phone']; 
    $user_address = $user_data['user_address'];
} else {
    echo "User not found.";
    exit; 
}

//payment history

// SQL query to get payment history
$sql = "SELECT bill_id, amount, date, method, status, transaction_reference 
        FROM bill 
        ORDER BY date DESC";

$result = $conn->query($sql);
?>

<!-- all unpaid bills-->
<!DOCTYPE html>
<html lang="en">
<head>
    <title>All Unpaid Activities Bill</title>
    <link rel="stylesheet" href="../afterLoginUser_style/bill.css">
</head>
<body>

    <div class="user-view-bill">
        <h1>All Unpaid Activity Bills</h1>

        <?php if (!empty($unpaid_activities)): ?>
            <table>
                <tr>
                    <th>Activity Type</th>
                    <th>Activity ID</th>
                    <th>Activity Fee</th>
                    <th>Doctor Fee</th>
                    <th>Hospital Fee</th>
                    <th>Total Bill</th>
                </tr>
                <?php foreach ($unpaid_activities as $activity): ?>
                    <tr>
                        <td><?php echo ucfirst($activity['activity_type']); ?></td>
                        <td><?php echo ucfirst($activity['activity_id']); ?></td>
                        <td><?php echo number_format($activity['activity_fee'], 2); ?></td>
                        <td>
                            <?php if ($activity['activity_type'] === 'appointment' || $activity['activity_type'] === 'consultation'): ?>
                                <?php echo number_format($activity['doctor_fee'], 2); ?>
                            <?php else: ?>
                                <?php echo 'N/A'; ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($activity['activity_type'] === 'appointment' || $activity['activity_type'] === 'consultation'): ?>
                                <?php echo number_format($activity['hospital_fee'], 2); ?>
                            <?php else: ?>
                                <?php echo 'N/A'; ?>
                            <?php endif; ?>
                        </td>
                        <td><strong><?php echo number_format($activity['total_bill'], 2); ?></strong></td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <!-- Display the total unpaid bill -->
            <h2 id="total">Total Amount Due: <strong><?php echo number_format($total_unpaid_bill, 2); ?></strong></h2>
            <button type="submit" id="payment"><a href="payment_way.php">Proceed to Payment</a></button>

        <?php else: ?>
            <p>No unpaid activities available.</p>
        <?php endif; ?>

    </div>

</body>
</html>




<!-- payment history-->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment History</title>
    <link rel="stylesheet" href="../afterLoginUser_style/payment_history.css">
</head>
<body>

<div class="container">
    <h2>Payment History</h2>
    <div class="table-container">       
                <?php
                if ($result->num_rows > 0) {
                    echo "
                        <table class='table table-bordered'>
                            <thead>
                                <tr>
                                    <th>Bill ID</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                    <th>Method</th>
                                    <th>Status</th>
                                    <th>Transaction Reference</th>
                                </tr>
                            </thead>
                            <tbody>
                    ";

                    while($row = $result->fetch_assoc()) {
                        
                        echo "<tr>
                                <td>{$row['bill_id']}</td>
                                <td>{$row['amount']}</td>
                                <td>{$row['date']}</td>
                                <td>{$row['method']}</td>
                                <td style='color: " . (($row['status'] === 'Rejected') ? 'red' : (($row['status'] === 'Confirmed') ? 'green' : 'black')) . ";'>{$row['status']}</td>
                                <td>{$row['transaction_reference']}</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' class='text-center'>No payment history found.</td></tr>";
                }

                echo "
                    </tbody>
                </table>
                ";
                ?>
    </div>
</div>

</body>
</html>

<!--footer-->
<?php include_once "../footer.php"?>

<?php $conn->close(); ?>