<?php
// addnew.php
session_start();
require_once 'config/config.php';
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = connectDB();
    
    $type = $_POST['type'];
    $amount = $_POST['amount'];
    $category = $_POST['category'];
    $date = $_POST['date'];
    $notes = $_POST['notes'];
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Insert into Transactions
        $stmt = $conn->prepare("INSERT INTO Transactions (usid, dateOf, amount) VALUES (?, ?, ?)");
        $stmt->bind_param("isd", $_SESSION['user_id'], $date, $amount);
        $stmt->execute();
        $transactionId = $conn->insert_id;
        
        // Insert into Performs
        $stmt = $conn->prepare("INSERT INTO Performs (usid, trid) VALUES (?, ?)");
        $stmt->bind_param("ii", $_SESSION['user_id'], $transactionId);
        $stmt->execute();
        
        if ($type === 'Income') {
            // Insert into Income
            $stmt = $conn->prepare("INSERT INTO Income (source) VALUES (?)");
            $stmt->bind_param("s", $notes);
            $stmt->execute();
            $incomeId = $conn->insert_id;
            
            // Insert into Transaction_Income
            $stmt = $conn->prepare("INSERT INTO Transaction_Income (trid, inid) VALUES (?, ?)");
            $stmt->bind_param("ii", $transactionId, $incomeId);
            $stmt->execute();
            
            // Insert into Income_Category
            $stmt = $conn->prepare("INSERT INTO Income_Category (catid, inid) VALUES (?, ?)");
            $stmt->bind_param("ii", $category, $incomeId);
            $stmt->execute();
        } else {
            // Insert into Expense
            $stmt = $conn->prepare("INSERT INTO Expense (purpose) VALUES (?)");
            $stmt->bind_param("s", $notes);
            $stmt->execute();
            $expenseId = $conn->insert_id;
            
            // Insert into Transaction_Expense
            $stmt = $conn->prepare("INSERT INTO Transaction_Expense (trid, exid) VALUES (?, ?)");
            $stmt->bind_param("ii", $transactionId, $expenseId);
            $stmt->execute();
            
            // Insert into Expense_Category
            $stmt = $conn->prepare("INSERT INTO Expense_Category (catid, exid) VALUES (?, ?)");
            $stmt->bind_param("ii", $category, $expenseId);
            $stmt->execute();
        }
        
        $conn->commit();
        header('Location: overview.php');
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Error adding transaction: " . $e->getMessage();
    }
    
    $conn->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="css/styleIE.css">
</head>
<body>
    <h1>Add New Income/Expense</h1>
    <form method="POST">
        <div>
            <label>Type:</label>
            <select name="type" required>
                <option value="Income">Income</option>
                <option value="Expense">Expense</option>
            </select>
        </div>
        
        <div>
            <label>Amount:</label>
            <input type="number" name="amount" step="0.01" required>
        </div>
        
        <div>
            <label>Category:</label>
            <select name="category" required>
                <?php
                $conn = connectDB();
                $result = $conn->query("SELECT * FROM Category");
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='" . $row['category_id'] . "'>" . 
                         htmlspecialchars($row['category_name']) . "</option>";
                }
                $conn->close();
                ?>
            </select>
        </div>
        
        <div>
            <label>Date:</label>
            <input type="date" name="date" required>
        </div>
        
        <div>
            <label>Notes:</label>
            <textarea name="notes" required></textarea>
        </div>
        
        <input type="submit" value="Add Transaction">
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
    </form>
</body>
</html>