<?php
require_once __DIR__ . '/../../sql/config.php';
require_once __DIR__ . '/logics/items.php';

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['department']) !== 'admin') {
    http_response_code(403);
    echo json_encode(['error'=>'forbidden']);
    exit;
}

$db = new Database();
$conn = $db->getConnection();
$item = new Item($conn);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error'=>'method']);
    exit;
}

$data = $_POST;
$saved = true;
for ($i = 0; $i < count($data['description'] ?? []); $i++) {
    $descr = $data['description'][$i] ?? '';
    $unit = $data['unit'][$i] ?? '';
    $unit_price = $data['unit_price'][$i] ?? 0;
    $supplier = $data['supplier'][$i] ?? '';
    $department = $data['department'][$i] ?? '';
    $threshold = $data['threshold'][$i] ?? 0;
    $date = $data['date_added'][$i] ?? date('Y-m-d');

    $ok = $item->addItem($descr, $unit, $unit_price, $supplier, $department, $threshold, $date);
    if (!$ok) $saved = false;
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['success' => $saved]);
