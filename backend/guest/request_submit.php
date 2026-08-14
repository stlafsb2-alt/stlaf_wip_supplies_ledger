<?php
require_once __DIR__ . '/../../sql/config.php';
require_once __DIR__ . '/emailing.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['status'=>'error','message'=>'forbidden']);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

// TEMPORARY diagnostic wrapper: the server is returning an empty body on
// error instead of a normal PHP error page, so this catches anything --
// including a genuine Error (missing class, etc.), not just Exception --
// and reports it back in the JSON response so we can actually see it.
// Remove this try/catch once the real bug is found and fixed.
try {

$name        = $_POST['name'] ?? '';
$department  = $_POST['department'] ?? '';
$items       = $_POST['item'] ?? [];
$sizes       = $_POST['size'] ?? [];
$product_ids = $_POST['product_id'] ?? [];
$quantities  = $_POST['quantity'] ?? [];
$units       = $_POST['unit'] ?? [];

if (empty($name) || empty($department) || empty($items)) {
    echo json_encode(['status'=>'error','message'=>'Missing required fields']);
    exit;
}

$db = new Database();
$conn = $db->getConnection();

$count = count($items);
$ok = true;

if ($department === 'all') {
    $departments = ['HR','ACCOUNTING','CORPORATE','OPS','LITIGATION','MARKETING','IT'];
    for ($i=0;$i<$count;$i++){
        $itemName = $items[$i];
        $size = $sizes[$i] ?? '';
        $product_id = $product_ids[$i] ?? null;
        $quantity = $quantities[$i] ?? 0;
        $unit = $units[$i] ?? '';
        foreach($departments as $dept){
            $stmt = $conn->prepare("INSERT INTO req_form (name, department, item, size, product_id, quantity, unit, date_req, status) VALUES (?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, 'Pending')");
            if (!$stmt->execute([$name, $dept, $itemName, $size, $product_id, $quantity, $unit])) $ok = false;
        }
    }
    // send consolidated email
    sendSupplyRequestEmail($name, 'ALL DEPARTMENTS', implode(', ', $items), implode(', ', $product_ids), implode(', ', $quantities), implode(', ', $units));
} else {
    for ($i=0;$i<$count;$i++){
        $itemName = $items[$i];
        $size = $sizes[$i] ?? '';
        $product_id = $product_ids[$i] ?? null;
        $quantity = $quantities[$i] ?? 0;
        $unit = $units[$i] ?? '';
        $stmt = $conn->prepare("INSERT INTO req_form (name, department, item, size, product_id, quantity, unit, date_req, status) VALUES (?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, 'Pending')");
        if (!$stmt->execute([$name, $department, $itemName, $size, $product_id, $quantity, $unit])) $ok = false;
    }
    sendSupplyRequestEmail($name, $department, implode(', ', $items), implode(', ', $product_ids), implode(', ', $quantities), implode(', ', $units));
}

if ($ok) echo json_encode(['status'=>'success','message'=>'Requests submitted']);
else echo json_encode(['status'=>'error','message'=>'One or more inserts failed']);

} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => $e->getMessage(),
        'type'    => get_class($e),
        'file'    => $e->getFile(),
        'line'    => $e->getLine(),
    ]);
}