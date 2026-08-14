<?php
require_once __DIR__ . '/../../sql/config.php';
require_once __DIR__ . '/../../backend/guest/emailing.php';

class Request
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function insertRequest($name, $department, $itemName, $size, $product_id, $quantity, $unit, $sendEmail = true)
    {
        $sql = "INSERT INTO req_form 
        (name, department, item, size, product_id, quantity, unit, date_req, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, 'Pending')";
        $stmt = $this->conn->prepare($sql);
        $executed = $stmt->execute([$name, $department, $itemName, $size, $product_id, $quantity, $unit]);

        if ($executed && $sendEmail) {
            sendSupplyRequestEmail($name, $department, $itemName, $product_id, $quantity, $unit);
        }

        return $executed;
    }

    public function getAllRequests()
    {
        $sql = "SELECT * FROM req_form ORDER BY date_req DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus($req_id, $status, $cancel_reason = null)
    {
        if ($status === "Delivered") {
            $sql2 = "SELECT product_id, quantity, item FROM req_form WHERE req_id = ?";
            $stmt2 = $this->conn->prepare($sql2);
            $stmt2->execute([(int)$req_id]);
            $info = $stmt2->fetch(PDO::FETCH_ASSOC);

            $item_id = $info['product_id'];
            $qty_out = $info['quantity'];
            $item_name = $info['item'];

            $sqlCheck = "SELECT qty_on_hand FROM items WHERE id = ?";
            $stmtCheck = $this->conn->prepare($sqlCheck);
            $stmtCheck->execute([(int)$item_id]);
            $available = $stmtCheck->fetch(PDO::FETCH_ASSOC)['qty_on_hand'];

            if ($qty_out > $available) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Not enough stock. Available: ' . $available
                ]);
                exit;
            }

            // Deduct stock
            $sql3 = "UPDATE items SET qty_on_hand = qty_on_hand - ? WHERE id = ?";
            $stmt3 = $this->conn->prepare($sql3);
            $stmt3->execute([$qty_out, $item_id]);

            // Insert into stock_out
            $remarks = "Delivered from request ID #$req_id ($item_name)";
            $sql4 = "INSERT INTO stock_out (item_id, qty_out, date_out, remarks) VALUES (?, ?, CURRENT_TIMESTAMP, ?)";
            $stmt4 = $this->conn->prepare($sql4);
            $stmt4->execute([$item_id, $qty_out, $remarks]);
        }

        // Only update status if it's NOT Delivered, or stock check passed
        $sql = "UPDATE req_form SET status = ?, cancel_reason = ? WHERE req_id = ?";
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([$status, $cancel_reason, $req_id]);

        return $result;
    }
}

$db = new Database();
$conn = $db->getConnection();
$request = new Request($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['method'] ?? '') === 'updateStatus') {
    header('Content-Type: application/json');

    $req_id = $_POST['req_id'] ?? null;
    $status = $_POST['status'] ?? null;
    $reason = $_POST['reason'] ?? null;

    if (!$req_id || !$status) {
        echo json_encode(['status' => 'error', 'message' => 'Missing parameters']);
        exit;
    }

    $success = $request->updateStatus($req_id, $status, $reason);
    echo json_encode([
        'status' => $success ? 'success' : 'error',
        'message' => $success ? 'Status updated successfully' : 'Failed to update status'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST['method'])) {
    $name        = $_POST['name'] ?? '';
    $department  = $_POST['department'] ?? '';
    $items       = $_POST['item'] ?? [];
    $sizes       = $_POST['size'] ?? [];
    $product_ids = $_POST['product_id'] ?? [];
    $quantities  = $_POST['quantity'] ?? [];
    $units       = $_POST['unit'] ?? [];

    $count = count($items);

    if ($department === "all") {
        $departments = ['HR', 'ACCOUNTING', 'CORPORATE', 'OPS', 'LITIGATION', 'MARKETING', 'IT'];
        $ok = true;

        for ($i = 0; $i < $count; $i++) {
            $itemName = $items[$i];
            $size = $sizes[$i] ?? '';
            $product_id = $product_ids[$i];
            $quantity = $quantities[$i];
            $unit = $units[$i];

            foreach ($departments as $dept) {
                if (!$request->insertRequest($name, $dept, $itemName, $size, $product_id, $quantity, $unit, false)) {
                    $ok = false;
                }
            }
        }

        sendSupplyRequestEmail(
            $name,
            "ALL DEPARTMENTS",
            implode(", ", $items),
            implode(", ", $product_ids),
            implode(", ", $quantities),
            implode(", ", $units)
        );
    } else {
        $ok = true;
        for ($i = 0; $i < $count; $i++) {
            $itemName = $items[$i];
            $size = $sizes[$i] ?? '';
            $product_id = $product_ids[$i];
            $quantity = $quantities[$i];
            $unit = $units[$i];

            if (!$request->insertRequest($name, $department, $itemName, $size, $product_id, $quantity, $unit)) {
                $ok = false;
            }
        }
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
