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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');
    if (isset($_POST['updateField'])) {
        $id = (int) $_POST['id'];
        $field = $_POST['field'];
        $value = $_POST['value'];

        if (in_array($field, ['description', 'unit', 'qty_on_hand', 'threshold'])) {
            $stmt = $conn->prepare("UPDATE items SET $field = ? WHERE id = ?");
            $ok = $stmt->execute([$value, $id]);
            echo json_encode(['success' => (bool) $ok]);
        } else {
            echo json_encode(['success' => false]);
        }
        exit;
    }

    if (isset($_POST['action']) && isset($_POST['id'])) {
        $id = (int) $_POST['id'];
        $action = $_POST['action'];

        if ($action === 'archive') {
            $ok = $item->archiveItem($id);
            echo json_encode(['success' => (bool) $ok, 'type' => 'archive']);
            exit;
        }

        if ($action === 'delete') {
            $ok = $item->deleteItem($id);
            echo json_encode(['success' => (bool) $ok, 'type' => 'delete']);
            exit;
        }

        echo json_encode(['success' => false]);
        exit;
    }
}

$search = isset($_GET['search']) ? $_GET['search'] : "";
$limit = isset($_GET['limit']) && $_GET['limit'] > 0 ? (int) $_GET['limit'] : 10;
$page  = isset($_GET['page']) && $_GET['page'] > 0 ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

if ($search !== "") {
    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM items WHERE is_archived = false AND description LIKE ?");
    $stmt->execute(["%$search%"]);
} else {
    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM items WHERE is_archived = false");
    $stmt->execute();
}

$totalItems = (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$totalPages = $totalItems > 0 ? ceil($totalItems / $limit) : 1;

$sql = "SELECT id, description, unit, qty_on_hand, threshold FROM items WHERE is_archived = false";
if ($search !== "") {
    $sql .= " AND description LIKE ?";
}
$sql .= " ORDER BY description ASC LIMIT $limit OFFSET $offset";

$stmt = $conn->prepare($sql);
if ($search !== "") {
    $stmt->execute(["%$search%"]);
} else {
    $stmt->execute();
}

$itemsArr = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['items'=>$itemsArr, 'page'=>$page, 'totalPages'=>$totalPages]);