<?php
session_start();
require_once 'config/config.php';
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$userData = getIncomeExpenseData($userId);
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="css/stylesO.css">
</head>
<body>
    <div class="overview-container">
        <h2>Income & Expense Overview</h2>
        
        <div class="summary-section">
            <div class="summary-item">
                <div class="summary-label">Total Income</div>
                <div class="summary-value income-value" id="incomeValue">
                    $<?php echo number_format($userData['income'], 2); ?>
                </div>
            </div>
            
            <div class="summary-item">
                <div class="summary-label">Total Expenses</div>
                <div class="summary-value expense-value" id="expenseValue">
                    $<?php echo number_format($userData['expenses']['total'], 2); ?>
                </div>
            </div>
            
            <div class="summary-item">
                <div class="summary-label">Net Balance</div>
                <div class="summary-value <?php echo ($userData['income'] - $userData['expenses']['total'] >= 0) ? 'income-value' : 'expense-value'; ?>" id="balanceValue">
                    $<?php echo number_format($userData['income'] - $userData['expenses']['total'], 2); ?>
                </div>
            </div>
        </div>

        <div class="expense-breakdown">
            <h3>Expense Breakdown</h3>
            <?php foreach ($userData['expenses']['categories'] as $category => $amount): ?>
                <div class="category-item">
                    <span class="category-name"><?php echo htmlspecialchars($category); ?></span>
                    <span class="category-amount">$<?php echo number_format($amount, 2); ?></span>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="controls">
            <select id="typeFilter" onchange="filterTransactions()">
                <option value="All">All</option>
                <?php
                $categories = getCategories();
                foreach ($categories as $category) {
                    echo "<option value='" . htmlspecialchars($category['category_name']) . "'>" . 
                         htmlspecialchars($category['category_name']) . "</option>";
                }
                ?>
            </select>
            <button onclick="sortTransactions()">Sort by Date</button>
        </div>
        
        <h3>Transaction Details</h3>
        <table id="transactionTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Source/Purpose</th>
                    <th>Category</th>
                    <th>Amount</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
        
        <div class="add-container">
            <a href="addnew.php" class="add-button">Add Transaction</a>
        </div>
    </div>

    <script>
        // Pass PHP data to JavaScript
        const data = <?php echo json_encode($userData, JSON_PRETTY_PRINT); ?>;
    </script>
    <script src="js/script.js"></script>
</body>
</html>