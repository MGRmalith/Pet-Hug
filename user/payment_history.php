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

 // SQL query to get payment history
 $sql = "SELECT bill_id, amount, date, method, status, transaction_reference 
         FROM bill 
         ORDER BY date DESC";

 $result = $conn->query($sql);
?>

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
                                <td style='color: " . 
                                    ($status === 'Rejected' ? 'red' : 
                                    ($status === 'Confirmed' ? 'green' : 'black')) . ";'>
                                    {$row['status']}</td>
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

<?php
$conn->close();
?>
