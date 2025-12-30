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

// Fetch hospital fee
$hospital_fee_result = $conn->query("SELECT hospital_fee FROM hospital LIMIT 1");
$hospital_fee = $hospital_fee_result->fetch_assoc()['hospital_fee'] ?? 0;

// Get the current time
$current_time = date("Y-m-d H:i:s");

// Fetch all user IDs
$users_result = $conn->query("SELECT user_id FROM user");
$all_unpaid_bills = [];

// Loop through each user to calculate their unpaid total dues
while ($user = $users_result->fetch_assoc()) {
    $user_id = $user['user_id'];
    $user_name = $conn->query("SELECT CONCAT(user_first_name, ' ', user_last_name) AS user_name FROM user WHERE user_id = $user_id")->fetch_assoc()['user_name'];
    $user_email = $conn->query("SELECT user_email FROM user WHERE user_id = $user_id")->fetch_assoc()['user_email'];

    // Query to get unpaid activities and the earliest unpaid date
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

    ORDER BY activity_time ASC
    ";

    $unpaid_activities_result = $conn->query($unpaid_activities_query);
    $total_unpaid_bill = 0;
    $earliest_unpaid_date = null; // To store the earliest unpaid date

    // Calculate fees for each unpaid activity
    if ($unpaid_activities_result->num_rows > 0) {
        while ($activity = $unpaid_activities_result->fetch_assoc()) {
            // Update the earliest unpaid date
            if (!$earliest_unpaid_date || $activity['activity_time'] < $earliest_unpaid_date) {
                $earliest_unpaid_date = $activity['activity_time'];
            }
        }

        // Format the date to only include the date part (YYYY-MM-DD)
        if ($earliest_unpaid_date) {
            $earliest_unpaid_date = date("Y-m-d", strtotime($earliest_unpaid_date));
        }

        // Calculate fees based on activity type
        foreach ($unpaid_activities_result as $activity) {
            $activity_type = $activity['activity_type'];
            $activity_id = $activity['activity_id'];
            $doctor_id = $activity['doctor_id'];

            // Initialize fees
            $activity_fee = 0;
            $doctor_fee = 0;

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

            // Add up total bill for this activity
            $total_unpaid_bill += $total_bill;
        }
    }

    // Only store the results for users with unpaid bills
    if ($total_unpaid_bill > 0) {
        $all_unpaid_bills[] = [
            'user_id' => $user_id,
            'user_name' => $user_name,
            'user_email' => $user_email,
            'total_bill' => $total_unpaid_bill,
            'earliest_unpaid_date' => $earliest_unpaid_date, // Add earliest unpaid date to the result
        ];
    }
}

// Sort the unpaid bills by earliest unpaid date
usort($all_unpaid_bills, function($a, $b) {
    return strcmp($a['earliest_unpaid_date'], $b['earliest_unpaid_date']);
});



// Fetch paid users and their past activities, ordered by most recent payment date
$paid_query = "
SELECT 
    u.user_id,
    CONCAT(u.user_first_name, ' ', u.user_last_name) AS user_name,
    'appointment' AS activity_type, a.appointment_id AS activity_id, b.amount AS paid_amount, b.date AS paid_date, b.method AS payment_method
FROM appointment a
JOIN user u ON a.user_id = u.user_id
JOIN bill b ON a.bill_id = b.bill_id
WHERE a.appointment_time <= NOW()
UNION ALL
SELECT 
    u.user_id,
    CONCAT(u.user_first_name, ' ', u.user_last_name) AS user_name,
    'consultation' AS activity_type, c.consultation_id AS activity_id, b.amount AS paid_amount, b.date AS paid_date, b.method AS payment_method
FROM consultation c
JOIN user u ON c.user_id = u.user_id
JOIN bill b ON c.bill_id = b.bill_id
WHERE c.consultation_time <= NOW()
UNION ALL
SELECT 
    u.user_id,
    CONCAT(u.user_first_name, ' ', u.user_last_name) AS user_name,
    'hostel' AS activity_type, h.hostel_id AS activity_id, b.amount AS paid_amount, b.date AS paid_date, b.method AS payment_method
FROM hostel h
JOIN user u ON h.user_id = u.user_id
JOIN bill b ON h.bill_id = b.bill_id
WHERE h.start_date <= NOW()
ORDER BY paid_date DESC
";
$paid_result = $conn->query($paid_query);


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Payment Status</title>
    <link rel="stylesheet" href="../afterLoginAdmin_style/admin_user_payment_status.css">
</head>
<body>

    <div class="unpaid">
        <h2>Unpaid Bills Overview</h2>
        <?php if (!empty($all_unpaid_bills)): ?>
            <table border="1">
                <tr>
                    <th>User ID</th>
                    <th>Username</th>
                    <th>User Email</th>
                    <th>Earliest Unpaid Date</th>
                    <th>Total Due Amount</th>
                    <th>Details</th> 
                </tr>
                
                <?php foreach ($all_unpaid_bills as $bill): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($bill['user_id']); ?></td>
                        <td><?php echo htmlspecialchars($bill['user_name']); ?></td>
                        <td><?php echo htmlspecialchars($bill['user_email']); ?></td>
                        <td><?php echo htmlspecialchars($bill['earliest_unpaid_date']); ?></td> 
                        <td><strong><?php echo number_format($bill['total_bill'], 2); ?></strong></td>
                        <td>
                            <a href="admin_view_activity.php?user_id=<?php echo $bill['user_id']; ?>">View Activities</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>No unpaid bills found.</p>
        <?php endif; ?>
    </div>


    <!-- Paid users -->
    <div class="paid">
        <h2>Paid Bills Overview</h2>
            <?php if ($paid_result->num_rows > 0): ?>
                <table border="1">
                    <tr>
                        <th>User ID</th>
                        <th>Username</th>
                        <th>Activity Type</th>
                        <th>Activity ID</th>
                        <th>Paid Amount</th>
                        <th>Payment Method</th>
                        <th>Date of Payment</th>
                    </tr>
                    <?php while ($row = $paid_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['user_id']; ?></td>
                            <td><?php echo $row['user_name']; ?></td>
                            <td><?php echo ucfirst($row['activity_type']); ?></td>
                            <td><?php echo $row['activity_id']; ?></td>
                            <td><?php echo number_format($row['paid_amount'], 2); ?></td>
                            <td><?php echo $row['payment_method']; ?></td>
                            <td><?php echo date('Y-m-d', strtotime($row['paid_date'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            <?php else: ?>
                <p>No paid users found.</p>
            <?php endif; ?>
        </div>

        
</body>
</html>

<!--footer-->
<?php include_once '../footer.php';?>

<?php $conn->close(); ?>