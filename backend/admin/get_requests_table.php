<?php
require_once __DIR__ . '/../../sql/config.php';
require_once __DIR__ . '/../../auth/oop/request_form.php';

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['department']) !== 'admin') {
    http_response_code(403);
    exit('Forbidden');
}

$status = $_GET['status'] ?? 'Approved';

$requests = $request->getAllRequests();

// Reuse the server-side renderer
require_once __DIR__ . '/logics/request_table.php';

// showRequests echoes HTML directly
showRequests($requests, $status);
