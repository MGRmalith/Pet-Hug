<?php
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header("Location: userLogin.php");
        exit();
    }
    
    $user_id = $_SESSION['user_id'];
 
    include_once "../connection.php";
    //header
    include_once "header_user.php";

if (!isset($_SESSION['total_amount'])) {
    header("Location: bill.php"); 
    exit();
}

$amount = $_SESSION['total_amount'];
$status = null;
$errors = [];

// Check for bill ID and its status before handling form submission
$bill_id = $_SESSION['bill_id'] ?? null;
if ($bill_id) {
    $stmt = $conn->prepare("SELECT status FROM bill WHERE bill_id = ?");
    $stmt->bind_param("i", $bill_id);
    $stmt->execute();
    $stmt->bind_result($status);
    $stmt->fetch();
    $stmt->close();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $transaction_reference = $_POST['transaction_reference'] ?? null;
    $method = 'Online Bank Transfer';
    $date = date('Y-m-d');
    $status = 'Pending';

    // Validation
    if (empty($transaction_reference)) {
        $errors[] = "Reference number is required.";
    }

    // Insert into database if no errors
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO bill (amount, date, method, user_id, status, transaction_reference) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("dssiss", $amount, $date, $method, $user_id, $status, $transaction_reference);
        $stmt->execute();

        $bill_id = $stmt->insert_id;
        $_SESSION['bill_id'] = $bill_id;
        $stmt->close();

        // Refresh the page to check the new status
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        foreach ($errors as $error) {
            echo "<p class='error'>$error</p>";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Bank Transfer</title>
    <link rel="stylesheet" href="../afterLoginUser_style/online_bank_transfer.css">
</head>
<body>
    <div class="bank_container">
    <h1>Online Bank Transfer</h1>
    <p class="total">Total Amount: <strong>Rs. <?php echo $amount; ?></strong></p><br>

    <?php if ($status === 'Confirmed'): ?>
        <div class="success">
            <p>Payment Successful! Thank you for your payment.</p>
        </div>
    <?php elseif ($status === 'Pending'): ?>
        <div class="error">
            <p>Your payment is pending. Please check back later to confirm.</p>
        </div>  
    <?php else: ?>

        <div class="inline-container">
            <div class="details">
                <h2>Bank Transfer Details</h2>
                <p>Please use the following details to make your bank transfer:</p>
                <ul>
                    <li><strong>Bank Name:</strong> People's Bank</li>
                    <li><strong>Account Name:</strong> Pethug Veterinary Hospital</li>
                    <li><strong>Account Number:</strong> 2001123456789090</li>
                    <li><strong>Branch Code:</strong> 00123</li>
                </ul>
                <p>After making the payment, you will receive a transaction reference number from your bank. Please enter this number in the form below to complete your payment.</p>
                <p><strong>Note:</strong> Ensure that you mention your full name and user ID in the bank transfer notes for quicker verification.</p>
            </div>

            <div class="form">
                <p>Please enter the transaction reference number you received upon making the online bank transfer.</p>
                <form action="" method="post">
                    <label>Enter Reference Number: <input type="text" name="transaction_reference" required></label><br><br>
                    <button type="submit">Complete Payment</button>
                </form>
            </div>
        </div>
    <?php endif; ?>
    <a href="dashboard.php">Go to dashboard</a>
    <?php unset($_SESSION['bill_id']); ?>
    <div>
</body>
</html>
