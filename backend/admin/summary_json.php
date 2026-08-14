<?php
require_once __DIR__ . '/../../sql/config.php';
require_once __DIR__ . '/logics/stock_out.php';

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['department']) !== 'admin') {
    http_response_code(403);
    echo json_encode(['error'=>'forbidden']);
    exit;
}

$db = new Database();
$conn = $db->getConnection();
$stock = new StockOut($conn);

$month = isset($_GET['month']) ? $_GET['month'] : null;
$year = isset($_GET['year']) ? $_GET['year'] : null;

$statsResult = $stock->getStockOutStatistics($month, $year);
$stats = $statsResult ? $statsResult->fetchAll(PDO::FETCH_ASSOC) : [];

usort($stats, function($a,$b){ return $b['total_qty_out'] - $a['total_qty_out']; });

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['stats'=>$stats]);
