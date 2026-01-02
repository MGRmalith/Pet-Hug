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

    $appointment_ids = $_SESSION['appointment_ids']; 
    $consultation_ids = $_SESSION['consultation_ids'];
    $hostel_ids = $_SESSION['hostel_ids'];
    //print_r($_SESSION);

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $card_number = $_POST['card_number'];
        $cardholder_name = $_POST['cardholder_name'];
        $expiry_date = $_POST['expiry_date'];
        $cvv = $_POST['cvv'];
        $method = 'Debit Card';
        $date = date('Y-m-d');
        $status = 'Confirmed';

        // Validation
        $card_number = str_replace(' ', '', $card_number);
        
        $errors = [];
        if (empty($card_number) || empty($cardholder_name) || empty($expiry_date) || empty($cvv)) {
            $errors[] = "All fields are required.";
        }else if (!preg_match("/^\d{16}$/", $card_number)) {
            $errors[] = "Card number must be 16 digits.";
        }else if (!preg_match("/^(0[1-9]|1[0-2])\/?([0-9]{2})$/", $expiry_date)) {
            $errors[] = "Expiry date must be in the format MM/YY.";
        }else if (!preg_match("/^\d{3}$/", $cvv)) {
            $errors[] = "CVV must be 3 digits.";
        }

        // Insert into database if no errors
        if (empty($errors)) {
            $stmt = $conn->prepare("INSERT INTO bill (amount, date, method, user_id, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("dssis", $amount, $date, $method, $user_id, $status);

            if ($stmt->execute()) {
                $bill_id = $stmt->insert_id;
                foreach ($appointment_ids as $appointment_id) {
                    $stmt = $conn->prepare("UPDATE appointment SET bill_id = ?, status = 'Completed' WHERE appointment_id = ? AND user_id = ?");
                    $stmt->bind_param("iii", $bill_id, $appointment_id, $user_id);
                    $stmt->execute();
                }
            
                foreach ($consultation_ids as $consultation_id) {
                    $stmt = $conn->prepare("UPDATE consultation SET bill_id = ?, status = 'Completed' WHERE consultation_id = ? AND user_id = ?");
                    $stmt->bind_param("iii", $bill_id, $consultation_id, $user_id);
                    $stmt->execute();
                }
                
                foreach ($hostel_ids as $hostel_id) {
                    $stmt = $conn->prepare("UPDATE hostel SET bill_id = ?, status = 'Completed' WHERE hostel_id = ? AND user_id = ?");
                    $stmt->bind_param("iii", $bill_id, $hostel_id, $user_id);
                    $stmt->execute();
                }
                echo "<script>
                        alert('Payment Successful! Thank you for your payment of Rs. {$amount}.');
                        setTimeout(function() {
                            window.location.href = 'bill.php';
                        }, 1000);
                    </script>";
                exit();
            } else {
                echo "<p class='error'>Error: " . $stmt->error . "</p>";
            }
            $stmt->close();
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
    <title>Debit Card Payment</title>
    <link rel="stylesheet" href="../afterLoginUser_style/card_payment.css">
    <?php echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">';?>
</head>
<body>
<div class="credit_card_container">
        <form action="" method="post">
            <div class="title">
                <h1>Debit Card Payment</h1>
                <p>Total Amount: <strong>Rs. <?php echo $amount; ?></strong></p><br><br>
            </div>

            <label>Name On Card:</label>
            <input type="text" name="cardholder_name" placeholder="Name On Card" required>
            
            <label>Card Number:</label>
            <div class="input-icon">
                <input type="text" name="card_number" placeholder="0000 0000 0000 0000" required>
                <i class="fa fa-credit-card"></i>
            </div>
            
            <div class="row">
                <div>
                    <label>Expiration Date</label>
                    <input type="text" name="expiry_date" placeholder="mm/yy" required>
                </div>
                <div>
                    <label>CVV</label>
                    <input type="text" name="cvv" placeholder="XXX" required>
                </div>
            </div>
            
            <button type="submit">Pay Now</button>
        </form>
    <div>


</body>
</html>
