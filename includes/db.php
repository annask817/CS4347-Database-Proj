<?php
// includes/db.php
require_once __DIR__ . '/../config/config.php';

function getIncomeExpenseData($userId) {
    $conn = connectDB();
    
    // Get total income
    $incomeQuery = "
        SELECT COALESCE(SUM(t.amount), 0) as total_income
        FROM Transactions t
        JOIN Transaction_Income ti ON t.transaction_id = ti.trid
        WHERE t.usid = ? AND YEAR(t.dateOf) = YEAR(CURRENT_DATE())
    ";
    
    // Get expenses with categories
    $expenseQuery = "
        SELECT c.category_name, COALESCE(SUM(t.amount), 0) as amount
        FROM Transactions t
        JOIN Transaction_Expense te ON t.transaction_id = te.trid
        JOIN Expense_Category ec ON te.exid = ec.exid
        JOIN Category c ON ec.catid = c.category_id
        WHERE t.usid = ? AND YEAR(t.dateOf) = YEAR(CURRENT_DATE())
        GROUP BY c.category_name
    ";
    
    // Get all transactions with details
    $transactionQuery = "
        SELECT t.transaction_id, t.dateOf, t.amount,
               COALESCE(i.source, e.purpose) as description,
               c.category_name,
               CASE WHEN i.income_id IS NOT NULL THEN 'Income' ELSE 'Expense' END as type
        FROM Transactions t
        LEFT JOIN Transaction_Income ti ON t.transaction_id = ti.trid
        LEFT JOIN Income i ON ti.inid = i.income_id
        LEFT JOIN Transaction_Expense te ON t.transaction_id = te.trid
        LEFT JOIN Expense e ON te.exid = e.expense_id
        LEFT JOIN (
            SELECT ec.exid as id, c.category_name FROM Expense_Category ec
            JOIN Category c ON ec.catid = c.category_id
            UNION
            SELECT ic.inid as id, c.category_name FROM Income_Category ic
            JOIN Category c ON ic.catid = c.category_id
        ) c ON (i.income_id = c.id OR e.expense_id = c.id)
        WHERE t.usid = ?
        ORDER BY t.dateOf DESC";
    
    // Execute income query
    $stmt = $conn->prepare($incomeQuery);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $income = $stmt->get_result()->fetch_assoc()['total_income'];
    
    // Execute expense query
    $stmt = $conn->prepare($expenseQuery);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $expenseResult = $stmt->get_result();
    
    $expenses = ['total' => 0, 'categories' => []];
    while ($row = $expenseResult->fetch_assoc()) {
        $expenses['total'] += $row['amount'];
        $expenses['categories'][$row['category_name']] = $row['amount'];
    }
    
    // Execute transaction query
    $stmt = $conn->prepare($transactionQuery);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $transactionResult = $stmt->get_result();
    
    $transactions = [];
    while ($row = $transactionResult->fetch_assoc()) {
        $transactions[] = $row;
    }
    
    $conn->close();
    
    return [
        'income' => floatval($income),
        'expenses' => $expenses,
        'transactions' => $transactions
    ];
}

function getCategories() {
    $conn = connectDB();
    $categories = [];
    
    $result = $conn->query("SELECT * FROM Category ORDER BY category_name");
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
    
    $conn->close();
    return $categories;
}

function addTransaction($userId, $type, $amount, $category, $date, $notes) {
    $conn = connectDB();
    $conn->begin_transaction();
    
    try {
        // Insert into Transactions
        $stmt = $conn->prepare("INSERT INTO Transactions (usid, dateOf, amount) VALUES (?, ?, ?)");
        $stmt->bind_param("isd", $userId, $date, $amount);
        $stmt->execute();
        $transactionId = $conn->insert_id;
        
        // Insert into Performs
        $stmt = $conn->prepare("INSERT INTO Performs (usid, trid) VALUES (?, ?)");
        $stmt->bind_param("ii", $userId, $transactionId);
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
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    } finally {
        $conn->close();
    }
}
?>