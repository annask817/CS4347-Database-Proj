<?php
session_start();
require_once 'config/config.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Search Transactions (Vulnerable)</title>
</head>
<body>
    <h2>Search Transactions</h2>
    <form method="GET">
        <div>
            <label>User Name:</label>
            <input type="text" name="username" placeholder="Try: ' OR '1'='1">
        </div>
        <button type="submit">Search Transactions</button>
    </form>
    
    <?php
    if(isset($_GET['username'])) {
        $conn = connectDB();
        
        // Vulnerable query
        $query = "SELECT t.transaction_id, t.dateOf, t.amount, u.user_name,
                        CASE 
                            WHEN i.income_id IS NOT NULL THEN 'Income'
                            ELSE 'Expense'
                        END as type,
                        COALESCE(i.source, e.purpose) as description
                 FROM Transactions t
                 JOIN User u ON t.usid = u.user_id
                 LEFT JOIN Transaction_Income ti ON t.transaction_id = ti.trid
                 LEFT JOIN Income i ON ti.inid = i.income_id
                 LEFT JOIN Transaction_Expense te ON t.transaction_id = te.trid
                 LEFT JOIN Expense e ON te.exid = e.expense_id
                 WHERE u.user_name = '" . $_GET['username'] . "'";
                 
        echo "<div>Query: " . htmlspecialchars($query) . "</div>";
        
        $result = $conn->query($query);
        
        if($result) {
            while($row = $result->fetch_assoc()) {
                echo "<div>";
                echo "ID: " . htmlspecialchars($row['transaction_id']) . "<br>";
                echo "User: " . htmlspecialchars($row['user_name']) . "<br>";
                echo "Date: " . htmlspecialchars($row['dateOf']) . "<br>";
                echo "Amount: $" . htmlspecialchars($row['amount']) . "<br>";
                echo "Type: " . htmlspecialchars($row['type']) . "<br>";
                echo "Description: " . htmlspecialchars($row['description']) . "<br>";
                echo "</div><hr>";
            }
        }
        $conn->close();
    }
    ?>
</body>
</html>