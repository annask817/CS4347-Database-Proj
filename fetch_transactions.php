<?php
// fetch_transactions.php
session_start();
require_once 'config/config.php';
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Unauthorized');
}

$userId = $_SESSION['user_id'];
$category = $_GET['category'] ?? 'All';

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
        c.category_name
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
";

if ($category !== 'All') {
    $query .= " AND c.category_name = ?";
}

$query .= " ORDER BY t.dateOf DESC";

$stmt = $conn->prepare($query);

if ($category !== 'All') {
    $stmt->bind_param("is", $userId, $category);
} else {
    $stmt->bind_param("i", $userId);
}

$stmt->execute();
$result = $stmt->get_result();

$transactions = [];
while ($row = $result->fetch_assoc()) {
    $transactions[] = [
        'transaction_id' => $row['transaction_id'],
        'dateOf' => $row['dateOf'],
        'amount' => $row['amount'],
        'type' => $row['type'],
        'description' => $row['description'],
        'category' => $row['category_name']
    ];
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($transactions);
?>