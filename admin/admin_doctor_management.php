<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: adminLogin.php");
    exit();
}

require '../connection.php'; // Database connection
include_once 'header_admin.php';
// Fetch all doctors initially (limit 10 for infinite scrolling)
$limit = 10;
$query = "SELECT * FROM doctor LIMIT ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $limit);
$stmt->execute();
$doctors = $stmt->get_result();

// Handle updating doctor details
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_doctor'])) {
    $dr_id = $_POST['dr_id'];
    $dr_name = $_POST['dr_name'];
    $dr_email = $_POST['dr_email'];
    $specialization = $_POST['specialization'];
    $dr_phone = $_POST['dr_phone'];

    // Update the doctor in the database
    $updateQuery = "UPDATE doctor SET dr_name=?, dr_email=?, specialization=?, dr_phone=? WHERE dr_id=?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("ssssi", $dr_name, $dr_email, $specialization, $dr_phone, $dr_id);
    if ($updateStmt->execute()) {
        header("Location: admin_doctor_management.php");
        exit();
    } else {
        $error_message = "Error updating doctor. Please try again.";
    }
}

// Handle deleting a doctor
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_doctor'])) {
    $dr_id = $_POST['dr_id'];

    // First, delete related records in other tables
    $deleteAppointmentsQuery = "DELETE FROM appointment WHERE dr_id=?";
    $deleteAppointmentsStmt = $conn->prepare($deleteAppointmentsQuery);
    $deleteAppointmentsStmt->bind_param("i", $dr_id);
    $deleteAppointmentsStmt->execute();

    // Now delete doctor record
    $deleteQuery = "DELETE FROM doctor WHERE dr_id=?";
    $deleteStmt = $conn->prepare($deleteQuery);
    $deleteStmt->bind_param("i", $dr_id);
    if ($deleteStmt->execute()) {
        header("Location: admin_doctor_management.php");
        exit();
    } else {
        $error_message = "Error deleting doctor. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Doctor Management - PetHug</title>
    <style>
        /* General Styling */
        body {
            font-family: Arial, sans-serif;
            background-color: #e0f7ff;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1200px;
            margin: 10px auto;
            margin-top: 50px;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            color: #333;
        }

        /* Table Styling */
        .table-container {
            max-height: 400px;
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
        
        .edit-btn {
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 3px;
            background-color: #007bff;
            color: white;
        }
        .delete-btn {
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 3px;
            background-color: #ff4500;
            color: white;
        }
        .view-btn {
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 3px;
            background-color: #28a745;
            color: white;
        }

        /* Modal Styling */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 100 ;
        }
        .modal-content {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            width: 400px;
            position: relative;
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .close-btn {
            cursor: pointer;
            font-size: 18px;
            color: #333;
        }
    </style>
    <script>
        function openModal(dr_id, dr_name, dr_email, specialization, dr_phone) {
            document.getElementById("modal").style.display = "flex";
            document.getElementById("dr_id").value = dr_id;
            document.getElementById("dr_name").value = dr_name;
            document.getElementById("dr_email").value = dr_email;
            document.getElementById("specialization").value = specialization;
            document.getElementById("dr_phone").value = dr_phone;
        }

        function closeModal() {
            document.getElementById("modal").style.display = "none";
        }

        function confirmDelete(dr_id) {
            const confirmation = confirm("Are you sure you want to delete this doctor?");
            if (confirmation) {
                document.getElementById("delete_form_" + dr_id).submit();
            }
        }
    </script>
</head>
<body>

<div class="container">
    <h2>Doctor Management</h2><br>
    
    <!-- Scrollable Table Container -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Doctor ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Specialization</th>
                    <th>Phone</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $doctors->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $row['dr_id']; ?></td>
                        <td><?php echo $row['dr_name']; ?></td>
                        <td><?php echo $row['dr_email']; ?></td>
                        <td><?php echo $row['specialization']; ?></td>
                        <td><?php echo $row['dr_phone']; ?></td>
                        <td>
                            <button class="view-btn" onclick="openModal('<?php echo $row['dr_id']; ?>', '<?php echo $row['dr_name']; ?>', '<?php echo $row['dr_email']; ?>', '<?php echo $row['specialization']; ?>', '<?php echo $row['dr_phone']; ?>')">View / Edit</button>
                            <form id="delete_form_<?php echo $row['dr_id']; ?>" method="POST" style="display:inline;">
                                <input type="hidden" name="dr_id" value="<?php echo $row['dr_id']; ?>">
                                <button type="button" name="delete_doctor" class="delete-btn" onclick="confirmDelete('<?php echo $row['dr_id']; ?>')">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal for Edit Doctor -->
<div id="modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit Doctor Details</h3>
            <span class="close-btn" onclick="closeModal()">X</span>
        </div>
        <form method="POST">
            <input type="hidden" name="dr_id" id="dr_id"><br>
            <label for="dr_name">Name:</label>
            <input type="text" id="dr_name" name="dr_name" required><br><br>
            <label for="dr_email">Email:</label>
            <input type="email" id="dr_email" name="dr_email" required><br><br>
            <label for="specialization">Specialization:</label>
            <input type="text" id="specialization" name="specialization" required><br><br>
            <label for="dr_phone">Phone:</label>
            <input type="text" id="dr_phone" name="dr_phone" required><br><br>
            <button type="submit" name="update_doctor" class="edit-btn">Update Doctor</button>
        </form>
    </div>
</div>

</body>
</html>
<?php
include_once "../footer.php";
?>
