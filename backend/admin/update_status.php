<?php
// Thin wrapper that forwards the updateStatus POST to the existing handler in auth/oop/request_form.php
require_once __DIR__ . '/../../sql/config.php';
require_once __DIR__ . '/../../auth/oop/request_form.php';

// request_form.php already handles POST with method=updateStatus and will emit JSON and exit.
// If we get here (no response from include), return a generic error.
http_response_code(400);
echo json_encode(['status' => 'error', 'message' => 'Bad request']);
