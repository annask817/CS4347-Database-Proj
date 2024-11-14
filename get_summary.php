<?php
// get_summary.php
session_start();
require_once 'config/config.php';
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Unauthorized');
}

$userId = $_SESSION['user_id'];
$data = getIncomeExpenseData($userId);

header('Content-Type: application/json');
echo json_encode([
    'income' => floatval($data['income']),
    'expenses' => $data['expenses']
]);
?>