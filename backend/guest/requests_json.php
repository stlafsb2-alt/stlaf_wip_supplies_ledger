<?php
require_once __DIR__ . '/../../sql/config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'forbidden']);
    exit;
}

$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->prepare("SELECT req_id, name, department, item, size, product_id, quantity, unit,
    to_char(date_req AT TIME ZONE 'UTC', 'YYYY-MM-DD HH24:MI:SS') || '+00' AS date_req,
    status, cancel_reason
    FROM req_form ORDER BY date_req DESC");
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['requests' => $rows]);