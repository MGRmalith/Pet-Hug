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

    // Check if bill already exists and retrieve its status
    $bill_id = $_SESSION['bill_id'] ?? null;
    $status = null;
    
    if ($bill_id) {
        $stmt = $conn->prepare("SELECT status FROM bill WHERE bill_id = ?");
        $stmt->bind_param("i", $bill_id);
        $stmt->execute();
        $stmt->bind_result($status);
        $stmt->fetch();
        $stmt->close(); 
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && !$bill_id) {
        $method = 'Cash';
        $date = date('Y-m-d');
        $status = 'Pending';
    
        $stmt = $conn->prepare("INSERT INTO bill (amount, date, method, user_id, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("dssis", $amount, $date, $method, $user_id, $status);
        $stmt->execute();

        $bill_id = $stmt->insert_id; 
        $_SESSION['bill_id'] = $bill_id; 
        $stmt->close();

        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    $conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cash Payment</title>
    <link rel="stylesheet" href="../afterLoginUser_style/cash_payment.css">
</head>
<body>
    <div class="cash_container">
        <h1>Cash Payment</h1>
        <p>Total Amount: <strong>Rs. <?php echo $amount; ?></strong></p><br><br>

        <div class="instructions">
            <h3>How to Make Your Payment</h3>
            <p>Please follow these steps to complete your cash payment:</p>
            <ol>
                <li>Visit our <strong>billing counter</strong>.</li>
                <li>Inform the staff of your User ID: <strong><?php echo $user_id; ?></strong> and the amount due.</li>
                <li>Complete your payment at the counter.</li>
            </ol>
        </div>

        <?php if ($status === 'Confirmed'): ?>
            <div class="success">
                <p>Payment Successful! Thank you for your payment.</p>
            </div>
        <?php elseif ($status === 'Pending'): ?>
            <div class="error">
                <p>Your payment is pending. Please check back later to confirm.</p>
            </div>
        <?php else: ?>
            <form action="<?php $_SERVER['PHP_SELF']?>" method="post">
                <button type="submit">Complete Payment</button>
            </form>
        <?php endif; ?>

        <a href="dashboard.php">Go to dashboard</a>

        <?php unset($_SESSION['bill_id']); ?>
    </div>

</body>
</html>
