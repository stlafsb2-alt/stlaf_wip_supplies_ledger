<?php
session_start();
require_once '../../sql/config.php';
require_once 'logics/stock_out.php';
require_once '../../vendor/autoload.php';

use Dompdf\Dompdf;

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['department']) !== 'admin') {
    header("Location: ../../index.php");
    exit();
}

$month = $_GET['month'] ?? null;
$year = $_GET['year'] ?? null;

$db = new Database();
$conn = $db->getConnection();
$stock = new StockOut($conn);

$statsResult = $stock->getStockOutStatistics($month, $year);
$stats = $statsResult->fetchAll(PDO::FETCH_ASSOC);

$monthName = $month ? date("F", mktime(0, 0, 0, $month, 1)) : "All";

$html = '
<h2 style="text-align:center; margin-bottom:20px;">Stock Out Summary</h2>
<p><strong>Month:</strong> ' . htmlspecialchars($monthName) . '</p>
<p><strong>Year:</strong> ' . ($year ? htmlspecialchars($year) : "All") . '</p>
<table width="100%" border="1" cellspacing="0" cellpadding="8" style="border-collapse:collapse; margin-top:10px;">
    <thead>
        <tr style="background:#f0f0f0;">
            <th style="width:70%;">Item</th>
            <th style="width:30%;">Quantity Out</th>
        </tr>
    </thead>
    <tbody>';

foreach ($stats as $s) {
    $itemName = $s['description'] ?? 'No Name';
    $qty = $s['total_qty_out'] ?? 0;

    $html .= '
        <tr>
            <td>' . htmlspecialchars($itemName) . '</td>
            <td>' . htmlspecialchars($qty) . '</td>
        </tr>';
}

$html .= '
    </tbody>
</table>
';

if (ob_get_length()) ob_end_clean();

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$filename = ($monthName ?: "All") . '_' . ($year ?: "All") . '.pdf';
$filename = str_replace(' ', '_', $filename);

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');

$dompdf->stream($filename, ["Attachment" => true]);
exit;