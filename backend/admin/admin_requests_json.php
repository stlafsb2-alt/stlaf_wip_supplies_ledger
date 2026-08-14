<?php
require_once __DIR__ . '/../../sql/config.php';
require_once __DIR__ . '/../../auth/oop/request_form.php';

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['department']) !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'forbidden']);
    exit;
}

$requests = $request->getAllRequests();
header('Content-Type: application/json; charset=utf-8');
echo json_encode($requests);
