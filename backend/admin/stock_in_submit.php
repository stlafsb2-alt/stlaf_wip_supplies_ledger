<?php
require_once __DIR__ . '/../../sql/config.php';
require_once __DIR__ . '/logics/stock_in.php';

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['department']) !== 'admin') {
    http_response_code(403);
    echo json_encode(['error'=>'forbidden']);
    exit;
}

$db = new Database();
$conn = $db->getConnection();
$stock = new StockIn($conn);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error'=>'method']);
    exit;
}

$data = $_POST;
$saved = true;
for ($i = 0; $i < count($data['item_id'] ?? []); $i++) {
    $item_id = $data['item_id'][$i] ?? null;
    $qty_in = (int)($data['qty_in'][$i] ?? 0);
    $remarks = $data['remarks'][$i] ?? '';
    $supplier = $data['supplier'][$i] ?? '';
    $stock_date = $data['stock_date'][$i] ?? date('Y-m-d');

    if (!$item_id || $qty_in <= 0) continue;
    $result = $stock->addStockIn($item_id, $qty_in, $remarks, $supplier, $stock_date);
    if (!$result) $saved = false;
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['success'=>$saved]);
