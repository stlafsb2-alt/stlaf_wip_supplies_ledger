<?php
session_start();
require_once '../../sql/config.php';

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['department']) !== 'admin') {
    echo "unauthorized";
    exit();
}

if (!isset($_POST['request_id'])) {
    echo "missing_id";
    exit();
}

$request_id = intval($_POST['request_id']);

$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->prepare("DELETE FROM req_form WHERE req_id = ?");

if (!$stmt) {
    echo "prepare_failed";
    exit();
}

if ($stmt->execute([$request_id])) {
    if ($stmt->rowCount() > 0) {
        echo "success";
    } else {
        echo "not_found";
    }
} else {
    echo "execute_failed";
}
