<?php
require_once __DIR__ . '/../../sql/config.php';

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['department']) !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'forbidden']);
    exit;
}

$month = isset($_GET['month']) ? $_GET['month'] : null;
$year = isset($_GET['year']) ? $_GET['year'] : null;

$db = new Database();
$conn = $db->getConnection();

$sql = "SELECT name, department, item, unit, quantity, date_req, status FROM req_form WHERE status='Delivered'";
$params = [];
if ($month) {
    $sql .= " AND EXTRACT(MONTH FROM date_req) = ?";
    $params[] = (int)$month;
}
if ($year) {
    $sql .= " AND EXTRACT(YEAR FROM date_req) = ?";
    $params[] = (int)$year;
}
$sql .= " ORDER BY date_req DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['records' => $rows]);
