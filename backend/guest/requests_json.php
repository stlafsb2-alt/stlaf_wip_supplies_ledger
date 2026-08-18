<?php
require_once __DIR__ . '/../../sql/config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'forbidden']);
    exit;
}

$db = new Database();
$conn = $db->getConnection();

$search = isset($_GET['search']) ? trim($_GET['search']) : "";
$limit  = isset($_GET['limit']) && $_GET['limit'] > 0 ? (int) $_GET['limit'] : 10;
$page   = isset($_GET['page']) && $_GET['page'] > 0 ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

if ($search !== "") {
    $countStmt = $conn->prepare("SELECT COUNT(*) AS total FROM req_form WHERE name ILIKE ? OR item ILIKE ?");
    $countStmt->execute(["%$search%", "%$search%"]);
} else {
    $countStmt = $conn->prepare("SELECT COUNT(*) AS total FROM req_form");
    $countStmt->execute();
}
$totalRequests = (int) $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
$totalPages = $totalRequests > 0 ? (int) ceil($totalRequests / $limit) : 1;

$sql = "SELECT req_id, name, department, item, size, product_id, quantity, unit,
    to_char(date_req AT TIME ZONE 'UTC', 'YYYY-MM-DD HH24:MI:SS') || '+00' AS date_req,
    status, cancel_reason
    FROM req_form";
if ($search !== "") {
    $sql .= " WHERE name ILIKE ? OR item ILIKE ?";
}
$sql .= " ORDER BY date_req DESC LIMIT $limit OFFSET $offset";

$stmt = $conn->prepare($sql);
if ($search !== "") {
    $stmt->execute(["%$search%", "%$search%"]);
} else {
    $stmt->execute();
}
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['requests' => $rows, 'page' => $page, 'totalPages' => $totalPages]);