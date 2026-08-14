<?php
session_start();
require_once '../../sql/config.php';
require_once '../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['department']) !== 'admin') {
    header("Location: ../../index.php");
    exit();
}

$month = $_GET['month'] ?? null;
$year = $_GET['year'] ?? null;

$db = new Database();
$conn = $db->getConnection();

$sql = "SELECT name, department, item, unit, quantity, date_req, status FROM req_form WHERE status='Delivered'";
$params = [];
if ($month) {
    $sql .= " AND EXTRACT(MONTH FROM date_req) = ?";
    $params[] = (int)$month;
}
if ($year) {
    $sql .= " AND EXTRACT(YEAR FROM date_req) = ?";
    $params[] = (int)$year;
}
$sql .= " ORDER BY date_req DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$monthName = $month ? date("F", mktime(0, 0, 0, $month, 1)) : "All Months";

$html = '
<style>
    body { font-family: sans-serif; font-size: 11px; color: #333; }
    h2 { text-align: center; margin-bottom: 5px; text-transform: uppercase; }
    .filter-info { text-align: center; margin-bottom: 20px; font-style: italic; }
    table { width: 100%; border-collapse: collapse; }
    th { background-color: #444; color: white; padding: 10px; text-align: left; }
    td { border: 1px solid #ccc; padding: 8px; vertical-align: top; }
    tr:nth-child(even) { background-color: #f9f9f9; }
</style>

<h2>Request Detailed Report</h2>
<div class="filter-info">Period: ' . $monthName . ' ' . ($year ?: "All Years") . '</div>

<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Department</th>
            <th>Item Description</th>
            <th>Qty</th>
            <th>Size</th>
            <th>Date Requested</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>';

if (count($rows) > 0) {
    foreach ($rows as $row) {
        $html .= "\n        <tr>\n            <td>" . htmlspecialchars($row['name']) . "</td>\n            <td>" . htmlspecialchars($row['department']) . "</td>\n            <td>" . htmlspecialchars($row['item']) . "</td>\n            <td>" . htmlspecialchars($row['quantity'] ?: '-') . "</td>\n            <td>" . htmlspecialchars($row['unit'] ?: '-') . "</td>\n            <td>" . date("Y-m-d H:i", strtotime($row['date_req'])) . "</td>\n            <td style=\"font-weight:bold;\">" . htmlspecialchars($row['status']) . "</td>\n        </tr>";
    }
} else {
    $html .= '<tr><td colspan="6" style="text-align:center;">No records found for the selected period.</td></tr>';
}

$html .= '</tbody></table>';

if (ob_get_length()) ob_end_clean();

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('defaultFont', 'Helvetica');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait'); // Better for 6+ columns
$dompdf->render();

$filename = "Detailed_Report_{$monthName}_{$year}.pdf";
$dompdf->stream($filename, ["Attachment" => true]);
exit;