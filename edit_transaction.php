<?php
// edit_transaction.php
session_start();
require_once 'config/config.php';
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

function getTransactionDetails($transactionId, $userId) {
    $conn = connectDB();
    
    $query = "
        SELECT 
            t.transaction_id,
            t.dateOf,
            t.amount,
            CASE 
                WHEN i.income_id IS NOT NULL THEN 'Income'
                ELSE 'Expense'
            END as type,
            COALESCE(i.source, e.purpose) as description,
            c.category_id,
            c.category_name
        FROM Transactions t
        LEFT JOIN Transaction_Income ti ON t.transaction_id = ti.trid
        LEFT JOIN Income i ON ti.inid = i.income_id
        LEFT JOIN Transaction_Expense te ON t.transaction_id = te.trid
        LEFT JOIN Expense e ON te.exid = e.expense_id
        LEFT JOIN (
            SELECT ec.exid as id, c.category_id, c.category_name 
            FROM Expense_Category ec
            JOIN Category c ON ec.catid = c.category_id
            UNION
            SELECT ic.inid as id, c.category_id, c.category_name 
            FROM Income_Category ic
            JOIN Category c ON ic.catid = c.category_id
        ) c ON (i.income_id = c.id OR e.expense_id = c.id)
        WHERE t.transaction_id = ? AND t.usid = ?
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $transactionId, $userId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    $conn->close();
    return $result;
}

function updateTransaction($transactionId, $userId, $data) {
    $conn = connectDB();
    $conn->begin_transaction();
    
    try {
        // Update Transactions table
        $stmt = $conn->prepare("UPDATE Transactions SET dateOf = ?, amount = ? WHERE transaction_id = ? AND usid = ?");
        $stmt->bind_param("sdii", $data['date'], $data['amount'], $transactionId, $userId);
        $stmt->execute();
        
        // Get transaction type
        $stmt = $conn->prepare("
            SELECT 
                CASE 
                    WHEN ti.trid IS NOT NULL THEN 'Income'
                    ELSE 'Expense'
                END as type,
                COALESCE(ti.inid, te.exid) as type_id
            FROM Transactions t
            LEFT JOIN Transaction_Income ti ON t.transaction_id = ti.trid
            LEFT JOIN Transaction_Expense te ON t.transaction_id = te.trid
            WHERE t.transaction_id = ?
        ");
        $stmt->bind_param("i", $transactionId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if ($result['type'] === 'Income') {
            // Update Income
            $stmt = $conn->prepare("UPDATE Income SET source = ? WHERE income_id = ?");
            $stmt->bind_param("si", $data['notes'], $result['type_id']);
            $stmt->execute();
            
            // Update Income_Category
            $stmt = $conn->prepare("UPDATE Income_Category SET catid = ? WHERE inid = ?");
            $stmt->bind_param("ii", $data['category'], $result['type_id']);
            $stmt->execute();
        } else {
            // Update Expense
            $stmt = $conn->prepare("UPDATE Expense SET purpose = ? WHERE expense_id = ?");
            $stmt->bind_param("si", $data['notes'], $result['type_id']);
            $stmt->execute();
            
            // Update Expense_Category
            $stmt = $conn->prepare("UPDATE Expense_Category SET catid = ? WHERE exid = ?");
            $stmt->bind_param("ii", $data['category'], $result['type_id']);
            $stmt->execute();
        }
        
        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    } finally {
        $conn->close();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update') {
        try {
            $data = [
                'date' => $_POST['date'],
                'amount' => $_POST['amount'],
                'category' => $_POST['category'],
                'notes' => $_POST['notes']
            ];
            
            if (updateTransaction($_POST['transaction_id'], $_SESSION['user_id'], $data)) {
                header('Location: overview.php');
                exit;
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

$transaction = null;
if (isset($_GET['id'])) {
    $transaction = getTransactionDetails($_GET['id'], $_SESSION['user_id']);
}
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="css/styleIE.css">
</head>
<body>
    <h1>Edit Transaction</h1>
    <?php if ($transaction): ?>
    <form method="POST">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="transaction_id" value="<?php echo $transaction['transaction_id']; ?>">
        
        <div>
            <label>Type:</label>
            <input type="text" value="<?php echo htmlspecialchars($transaction['type']); ?>" readonly>
        </div>
        
        <div>
            <label>Amount:</label>
            <input type="number" name="amount" step="0.01" value="<?php echo $transaction['amount']; ?>" required>
        </div>
        
        <div>
            <label>Category:</label>
            <select name="category" required>
                <?php
                $conn = connectDB();
                $result = $conn->query("SELECT * FROM Category");
                while ($row = $result->fetch_assoc()) {
                    $selected = ($row['category_id'] == $transaction['category_id']) ? 'selected' : '';
                    echo "<option value='" . $row['category_id'] . "' $selected>" . 
                         htmlspecialchars($row['category_name']) . "</option>";
                }
                $conn->close();
                ?>
            </select>
        </div>
        
        <div>
            <label>Date:</label>
            <input type="date" name="date" value="<?php echo $transaction['dateOf']; ?>" required>
        </div>
        
        <div>
            <label>Notes:</label>
            <textarea name="notes" required><?php echo htmlspecialchars($transaction['description']); ?></textarea>
        </div>
        
        <input type="submit" value="Update Transaction">
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
    </form>
    <?php else: ?>
        <p>Transaction not found or unauthorized.</p>
    <?php endif; ?>
</body>
</html>