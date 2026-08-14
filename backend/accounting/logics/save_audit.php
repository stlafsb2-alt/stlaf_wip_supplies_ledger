<?php
session_start();

// ✅ CORRECT PATH (3 levels up)
require_once '../../../sql/config.php';

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['department']) !== 'accounting') {
    header('Location: ' . BASE_URL . 'index.php');
    exit();
}

// ✅ CREATE CONNECTION (REQUIRED)
$db = new Database();
$conn = $db->getConnection();

if (!$conn) {
    die('Database connection failed');
}

$userId   = $_SESSION['user_id'];
$auditWeek = $_POST['audit_week'] ?? null;

// Safety check
if (!$auditWeek || empty($_POST['audit'])) {
    header("Location: ../audit.php?error=invalid");
    exit();
}

// ✅ Prevent double audit (server-side protection)
$checkAudit = $conn->prepare("
    SELECT 1 FROM item_audits
    WHERE audit_week = ?
    LIMIT 1
");
$checkAudit->bind_param("s", $auditWeek);
$checkAudit->execute();

if ($checkAudit->get_result()->num_rows > 0) {
    header("Location: ../audit.php?error=already_done");
    exit();
}

// ✅ SAVE AUDIT
foreach ($_POST['audit'] as $itemId => $physicalQty) {

    // Get system quantity
    $stmt = $conn->prepare("
        SELECT qty_on_hand 
        FROM items 
        WHERE id = ?
    ");
    $stmt->bind_param("i", $itemId);
    $stmt->execute();
    $systemQty = $stmt->get_result()->fetch_assoc()['qty_on_hand'];

    $variance = $physicalQty - $systemQty;

    $insert = $conn->prepare("
        INSERT INTO item_audits
        (item_id, system_qty, physical_qty, variance, audit_week, audited_by)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $insert->bind_param(
        "iiiisi",
        $itemId,
        $systemQty,
        $physicalQty,
        $variance,
        $auditWeek,
        $userId
    );
    $insert->execute();
}

header("Location: ../audit.php?success=1");
exit;
