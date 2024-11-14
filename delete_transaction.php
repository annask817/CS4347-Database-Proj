<?php
// delete_transaction.php
session_start();
require_once 'config/config.php';
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['transaction_id'])) {
    http_response_code(401);
    exit('Unauthorized');
}

function deleteTransaction($transactionId, $userId) {
    $conn = connectDB();
    $conn->begin_transaction();
    
    try {
        // Verify the transaction belongs to the user
        $stmt = $conn->prepare("SELECT * FROM Transactions WHERE transaction_id = ? AND usid = ?");
        $stmt->bind_param("ii", $transactionId, $userId);
        $stmt->execute();
        if ($stmt->get_result()->num_rows === 0) {
            throw new Exception("Transaction not found or unauthorized");
        }
        
        // Get transaction type (Income or Expense)
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
            // Delete from Income_Category
            $stmt = $conn->prepare("DELETE FROM Income_Category WHERE inid = ?");
            $stmt->bind_param("i", $result['type_id']);
            $stmt->execute();
            
            // Delete from Transaction_Income
            $stmt = $conn->prepare("DELETE FROM Transaction_Income WHERE trid = ?");
            $stmt->bind_param("i", $transactionId);
            $stmt->execute();
            
            // Delete from Income
            $stmt = $conn->prepare("DELETE FROM Income WHERE income_id = ?");
            $stmt->bind_param("i", $result['type_id']);
            $stmt->execute();
        } else {
            // Delete from Expense_Category
            $stmt = $conn->prepare("DELETE FROM Expense_Category WHERE exid = ?");
            $stmt->bind_param("i", $result['type_id']);
            $stmt->execute();
            
            // Delete from Transaction_Expense
            $stmt = $conn->prepare("DELETE FROM Transaction_Expense WHERE trid = ?");
            $stmt->bind_param("i", $transactionId);
            $stmt->execute();
            
            // Delete from Expense
            $stmt = $conn->prepare("DELETE FROM Expense WHERE expense_id = ?");
            $stmt->bind_param("i", $result['type_id']);
            $stmt->execute();
        }
        
        // Delete from Performs
        $stmt = $conn->prepare("DELETE FROM Performs WHERE trid = ?");
        $stmt->bind_param("i", $transactionId);
        $stmt->execute();
        
        // Delete from Transactions
        $stmt = $conn->prepare("DELETE FROM Transactions WHERE transaction_id = ?");
        $stmt->bind_param("i", $transactionId);
        $stmt->execute();
        
        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    } finally {
        $conn->close();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (deleteTransaction($_POST['transaction_id'], $_SESSION['user_id'])) {
            echo json_encode(['success' => true]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>