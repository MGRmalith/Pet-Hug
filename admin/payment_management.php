<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: adminLogin.php");
    exit();
}

require '../connection.php';
//header
include_once 'header_admin.php';

$admin_id = $_SESSION['admin_id'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Payment Management</title>
    <style>
        body{
            background-color: #e0f7ff;
        }
        .container {
            width: fit-content;
            margin: auto;
            margin-top: 50px;
            padding: 30px;
            border-radius: 10px;
            background-color: #ffffff;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            text-align: center;
            
        }

        h2 {
            font-size: 2em;
            color: #333;
            margin-bottom: 40px;
        }

        .link-list {
            list-style: none;
            padding: 0;
        }

        .link-list li {
            margin-bottom: 20px; /* Increased space between links */
        }

        .link-button {
            display: block; /* Make the links fill the list item */
            padding: 15px 20px;
            border-radius: 5px;
            background-color: #007BFF;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
            text-decoration: none;
            transition: background-color 0.3s, transform 0.3s;
        }

        .link-button:hover {
            background-color: #0056b3;
            transform: translateY(-2px); /* Slight lift effect on hover */
        }

        .link-button:active {
            transform: translateY(1px); /* Push down effect on click */
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>Payment Management</h2>
        <ul class="link-list">
        <li><a class="link-button" href="admin_user_payment_status.php">Users' Payment Status</a></li>
        <li><a class="link-button" href="admin_set_fees.php">Set Fees</a></li>
        <li><a class="link-button" href="admin_payment_confirmation_bank_transfer.php">Confirm Bank Transfer Payments</a></li>
        <li><a class="link-button" href="admin_confirmation_cash.php">Confirm Cash Payments</a></li>
        <li><a class="link-button" href="view_doctor_earnings.php">View earnings</a></li>
    </ul>
    </div>

    <!--footer-->
    <?php include_once '../footer.php';?>
</body>
</html>
