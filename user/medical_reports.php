<?php
session_start();
include_once "../connection.php";
include_once "header_user.php";

$user_id = $_SESSION['user_id'];

// Fetch user's pets from the database
$pet_query = "SELECT pet_id, pet_name FROM pet WHERE user_id = $user_id";
$pet_result = $conn->query($pet_query);

if (!$pet_result) {
    die("Error fetching pets: " . $conn->error);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pet Medical Report</title>
    <style>
        /* CSS Styling */
        body {
            font-family: Arial, sans-serif;
            background-color: #e0f7ff;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .medical_records h1, .medical_records h2 {
            margin: 20px 0;
            text-align: center;
            color: #007bff;
        }
        .medical_records form {
            max-width: 500px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        label, select, input {
            display: block;
            width: 100%;
            margin-bottom: 10px;
        }
        .medical_records button {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .medical_record button:hover {
            background-color: #0056b3;
        }
        .container {
            max-width: 1200px;
            margin: auto;
            margin-top: 30px;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .table-container {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #ccc;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            text-align: center;
            border-bottom: 1px solid #ccc;
        }
        th {
            background-color: #007bff;
            color: white;
            position: sticky;
            top: 0;
            z-index: 1;
        }
       
    </style>
</head>
<body>
    <div class="medical_records">
    <h1>Pet Medical Report</h1>

    <!-- HTML Form -->
    <form action="medical_records.php" method="POST">
        <label for="pet_id">Select Pet:</label>
        <select name="pet_id" id="pet_id" required>
            <?php
            if ($pet_result->num_rows === 0) {
                echo "<option disabled>No pets found.</option>";
            } else {
                echo "<option value='all'>All Pets</option>";
                while ($row = $pet_result->fetch_assoc()) {
                    echo "<option value='{$row['pet_id']}'>{$row['pet_name']}</option>";
                }
            }
            ?>
        </select>

        <label for="report_type">Report Type:</label>
        <select name="report_type" id="report_type" required>
            <option value="appointments">Appointments</option>
            <option value="consultations">Consultations</option>
            <option value="both">Both</option>
        </select>

        <label for="start_date">Start Date:</label>
        <input type="date" name="start_date" id="start_date">

        <label for="end_date">End Date:</label>
        <input type="date" name="end_date" id="end_date">

        <button type="submit">Generate Report</button>
    </form>

    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $pet_id = $_POST['pet_id'];
        $report_type = $_POST['report_type'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];

        // Base SQL conditions
        $Appointments_conditions = " a.user_id = $user_id";
        
        if ($pet_id !== 'all') {
            $Appointments_conditions .= " AND a.pet_id = $pet_id";
        }
        if ($start_date && $end_date) {
            $Appointments_conditions .= " AND a.appointment_time BETWEEN '$start_date' AND '$end_date'";
        } elseif ($start_date) {
            $Appointments_conditions .= " AND a.appointment_time >= '$start_date'";
        } elseif ($end_date) {
            $Appointments_conditions .= " AND a.appointment_time <= '$end_date'";
        }

         // Base SQL conditions
         $Consultations_conditions = " c.user_id = $user_id";
        
         if ($pet_id !== 'all') {
             $Consultations_conditions .= " AND c.pet_id = $pet_id";
         }
         if ($start_date && $end_date) {
             $Consultations_conditions .= " AND c.created_at BETWEEN '$start_date' AND '$end_date'";
         } elseif ($start_date) {
             $Consultations_conditions .= " AND c.created_at >= '$start_date'";
         } elseif ($end_date) {
             $Consultations_conditions .= " AND c.created_at <= '$end_date'";
         }

        // Appointments Query
        if ($report_type === "appointments" || $report_type === "both") {
            $appointment_sql = "
                SELECT a.appointment_id, DATE(a.appointment_time) AS date, d.dr_name, a.details 
                FROM appointment a
                JOIN doctor d ON a.doctor_id = d.dr_id
                WHERE a.status = 'Completed' AND $Appointments_conditions
                ORDER BY a.appointment_time
            ";

            $appointment_result = $conn->query($appointment_sql);
           echo "<div class='container'>";
            if (!$appointment_result) {
                echo "<p>Error fetching appointments: " . $conn->error . "</p>";
            } else {
                echo "<h2>Appointments</h2>";
                if ($appointment_result->num_rows === 0) {
                    echo "<p>No appointments found.</p>";
                } else {
                    echo "
                    <div class='table-container'>
                    <table>
                    <thead>
                    <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Doctor</th>
                    <th>Notes</th>
                    </tr>
                    </thead>";
                    while ($row = $appointment_result->fetch_assoc()) {
                        echo "
                        <tbody>
                        <tr>
                        <td>{$row['appointment_id']}</td>
                        <td>{$row['date']}</td>
                        <td>{$row['dr_name']}</td>
                        <td>{$row['details']}</td>
                        </tr>
                        </tbody>";
                    }
                    echo "</table>
                    </div>";
                }
            }
                echo "</div>";
        }

        // Consultations Query
        if ($report_type === "consultations" || $report_type === "both") {
            $consultation_sql = "
                SELECT c.consultation_id, DATE(c.created_at) AS date, d.dr_name, c.details 
                FROM consultation c
                JOIN doctor d ON c.dr_id = d.dr_id
                WHERE c.status = 'Completed' AND $Consultations_conditions
                ORDER BY c.consultation_time";

            $consultation_result = $conn->query($consultation_sql);
            echo "<div class='container'>";
            if (!$consultation_result) {
                echo "<p>Error fetching consultations: " . $conn->error . "</p>";
            } else {
                echo "<h2>Consultations</h2>";
                if ($consultation_result->num_rows === 0) {
                    echo "<p>No consultations found.</p>";
                } else {
                    echo "
                    <div class='table-container'>
                    <table>
                    <thead>
                    <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Doctor</th>
                    <th>Notes</th>
                    </tr>
                    </thead>";
                    while ($row = $consultation_result->fetch_assoc()) {
                        echo "
                        <tbody>
                        <tr>
                        <td>{$row['consultation_id']}</td>
                        <td>{$row['date']}</td>
                        <td>{$row['dr_name']}</td>
                        <td>{$row['details']}</td>
                        </tr>
                        </tbody>";
                    }
                    echo "</table>
                    </div>";
                }
            }
            echo "</div>";
        }

        $conn->close();
    }
    ?>
</div>
</body>
</html>
<?php
include_once '../footer.php';
?>
