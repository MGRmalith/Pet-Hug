<?php

    session_start();

    include_once "../connection.php"; 
    
    if (!isset($_SESSION['user_id'])) {
        header("Location: userLogin.php");
        exit();
    }
    
    $user_id = $_SESSION['user_id'];     
   
    if (!isset($_SESSION['total_amount'])) {
        header("Location: bill.php");
        exit();
    }
    
    $amount = $_SESSION['total_amount'];

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Get the selected payment method
        $payment_method = $_POST['payment_method'];

        // Redirect to the corresponding payment processing file
        switch ($payment_method) {
            case 'credit_card':
                header("Location: credit_card.php");
                exit;
            case 'debit_card':
                header("Location: debit_card.php");
                exit;
            case 'cash':
                header("Location: cash.php");
                exit;
            case 'online_bank_transfer':
                header("Location: online_bank_transfer.php");
                exit;
            default:
                echo "Invalid payment method selected.";
        }
    }
    $conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Payment Method</title>
    <link rel="stylesheet" href="../afterLoginUser_style/payment_way.css">
    <style>
        body { font-family: Arial, sans-serif; }
        .error { color: red; }
    </style>
</head>
<body>
    <div class="payment_way_container">
        <h1>Select Payment Method</h1>
    
        <form id="payment-form" action="" method="post">
            <label>
                <input type="radio" name="payment_method" value="credit_card" required>
                Credit Card
            </label><br>
            <label>
                <input type="radio" name="payment_method" value="debit_card" required>
                Debit Card
            </label><br>
            <label>
                <input type="radio" name="payment_method" value="cash" required>
                Cash
            </label><br>
            <label>
                <input type="radio" name="payment_method" value="online_bank_transfer" required>
                Online Bank Transfer
            </label><br><br>
            <button type="submit">Proceed to Payment</button>
        </form>

        <div id="error-messages" class="error"></div>
    </div>

    
</body>
</html>
