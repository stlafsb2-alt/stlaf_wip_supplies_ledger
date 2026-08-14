<?php
require_once __DIR__ . '/../../sql/config.php';
require_once __DIR__ . '/logics/stock_out.php';

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['department']) !== 'admin') {
    http_response_code(403);
    echo json_encode(['error'=>'forbidden']);
    exit;
}

$db = new Database();
$conn = $db->getConnection();
$stock = new StockOut($conn);
$ledger = $stock->getLedger();

$rows = [];
if ($ledger) {
    $rows = $ledger->fetchAll(PDO::FETCH_ASSOC);
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['ledger'=>$rows]);
