<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: adminLogin.php");
    exit();
}

include_once 'header_admin.php';
require '../connection.php'; // Database connection

// Fetch all users initially (limit 10 for infinite scrolling)
$limit = 10;
$query = "SELECT * FROM user LIMIT ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $limit);
$stmt->execute();
$users = $stmt->get_result();

// Handle updating user details
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_user'])) {
    $user_id = $_POST['user_id'];
    $first_name = $_POST['user_first_name'];
    $last_name = $_POST['user_last_name'];
    $email = $_POST['user_email'];
    $address = $_POST['user_address'];
    $phone = $_POST['user_phone'];

    // Update the user in the database
    $updateQuery = "UPDATE user SET user_first_name=?, user_last_name=?, user_email=?, user_address=?, user_phone=? WHERE user_id=?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("sssssi", $first_name, $last_name, $email, $address, $phone, $user_id);
    if ($updateStmt->execute()) {
        header("Location: admin_user_management.php");
        exit();
    } else {
        $error_message = "Error updating user. Please try again.";
    }
}

// Handle deleting a user and related records
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];

    // Start a transaction to handle cascading deletions
    $conn->begin_transaction();
    try {
        // Delete related records from other tables
        $deleteAppointments = "DELETE FROM appointment WHERE user_id=?";
        $deleteHostels = "DELETE FROM hostel WHERE user_id=?";
        $deletePets = "DELETE FROM pet WHERE user_id=?";
        
        // Prepare and execute deletion for appointments
        $stmtAppointment = $conn->prepare($deleteAppointments);
        $stmtAppointment->bind_param("i", $user_id);
        $stmtAppointment->execute();

        // Prepare and execute deletion for hostels
        $stmtHostel = $conn->prepare($deleteHostels);
        $stmtHostel->bind_param("i", $user_id);
        $stmtHostel->execute();

        // Prepare and execute deletion for pets
        $stmtPets = $conn->prepare($deletePets);
        $stmtPets->bind_param("i", $user_id);
        $stmtPets->execute();

        // Delete the user from the user table
        $deleteQuery = "DELETE FROM user WHERE user_id=?";
        $deleteStmt = $conn->prepare($deleteQuery);
        $deleteStmt->bind_param("i", $user_id);
        $deleteStmt->execute();

        // Commit transaction
        $conn->commit();
        echo "<script>alert('User and related records deleted successfully!'); window.location.href='admin_user_management.php';</script>";
        exit();
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        $error_message = "Error deleting user. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin User Management - PetHug</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #e0f7ff;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1200px;
            margin: 10px auto;
            padding: 20px;
            margin-top: 50px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            color: #333;
        }
        .table-container {
            max-height: 400px; /* Set a fixed height for scrolling */
            overflow-y: auto; 
            margin-top: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
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
        .update-btn{
            background-color: #007bff;
            color: white;
        }
        </style>
        <script>
        function openModal(user_id, first_name, last_name, email, address, phone) {
            document.getElementById("modal").style.display = "flex";
            document.getElementById("user_id").value = user_id;
            document.getElementById("user_first_name").value = first_name;
            document.getElementById("user_last_name").value = last_name;
            document.getElementById("user_email").value = email;
            document.getElementById("user_address").value = address;
            document.getElementById("user_phone").value = phone;
        }

        function closeModal() {
            document.getElementById("modal").style.display = "none";
        }

        function confirmDelete() {
            return confirm("Are you sure you want to delete this user and all related data?");
        }
    </script>
</head>
<body>
    <div class="container">
        <h2>Admin User Management</h2>
        <?php if (isset($error_message)) echo "<p style='color:red;'>$error_message</p>"; ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                        <th>Address</th>
                        <th>Phone</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $users->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $row['user_id']; ?></td>
                            <td><?php echo htmlspecialchars($row['user_first_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['user_last_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['user_email']); ?></td>
                            <td><?php echo htmlspecialchars($row['user_address']); ?></td>
                            <td><?php echo htmlspecialchars($row['user_phone']); ?></td>
                            <td>
                                <button class="view-btn" onclick="openModal('<?php echo $row['user_id']; ?>', '<?php echo addslashes($row['user_first_name']); ?>', '<?php echo addslashes($row['user_last_name']); ?>', '<?php echo addslashes($row['user_email']); ?>', '<?php echo addslashes($row['user_address']); ?>', '<?php echo addslashes($row['user_phone']); ?>')">View / Edit</button>
                                <form method="POST" style="display:inline;" onsubmit="return confirmDelete();">
                                    <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
                                    <button type="submit" name="delete_user" class="delete-btn">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal for editing user details -->
    <div id="modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit User</h2>
                <span class="close-btn" onclick="closeModal()">×</span>
            </div>
            <form method="POST">
                <input type="hidden" id="user_id" name="user_id"><br>
                <label>First Name:</label>
                <input type="text" id="user_first_name" name="user_first_name" required><br><br>
                <label>Last Name:</label>
                <input type="text" id="user_last_name" name="user_last_name" required><br><br>
                <label>Email:</label>
                <input type="email" id="user_email" name="user_email" required><br><br>
                <label>Address:</label>
                <input type="text" id="user_address" name="user_address" required><br><br>
                <label>Phone:</label>
                <input type="text" id="user_phone" name="user_phone" required><br>
                <button type="submit" class="update-btn"  name="update_user">Update User</button>
            </form>
        </div>
    </div>
</body>
</html>

<?php
include_once "../footer.php";
?>