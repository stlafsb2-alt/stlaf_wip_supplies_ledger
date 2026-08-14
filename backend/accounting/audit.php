<?php
session_start();
require_once '../../sql/config.php';

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['department']) !== 'accounting') {
    header('Location: ' . BASE_URL . 'index.php');
    exit();
}

$db = new Database();
$conn = $db->getConnection();

$firstname = ucfirst($_SESSION['username'] ?? 'accounting');

// ✅ Selected week (default = this week's Monday)
$selectedMonday = $_GET['week'] ?? date('Y-m-d', strtotime('monday this week'));
$startOfWeek = date('Y-m-d', strtotime($selectedMonday));
$endOfWeek = date('Y-m-d', strtotime($selectedMonday . ' +4 days')); // Monday to Friday

// Format display like "Dec 15-20, 2025"
$weekDisplay = date('M d', strtotime($startOfWeek)) . '-' . date('d, Y', strtotime($endOfWeek));

$checkAudit = $conn->prepare("
    SELECT 1 FROM item_audits
    WHERE audit_week = ?
    LIMIT 1
");
$checkAudit->bind_param("s", $startOfWeek);
$checkAudit->execute();
$auditDone = $checkAudit->get_result()->num_rows > 0;

$items = $conn->query("
    SELECT id, description, qty_on_hand
    FROM items
    WHERE is_archived = 0
");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STLAF | Weekly Audit</title>

    <link rel="icon" type="image/png" href="../../assets/images/sub_logo_light.png">
    <link rel="stylesheet" href="../../assets/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/super.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        .card {
            overflow: hidden;
        }

        .audit-table-wrapper {
            overflow-y: auto;
            height: calc(83vh - 200px);
            padding-right: 6px;
        }

        .audit-table thead th {
            position: sticky;
            top: 0;
            z-index: 10;
            background: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
        }

        .audit-table td,
        .audit-table th {
            font-size: 15px;
            vertical-align: middle;
        }

        .audit-table input[type="number"] {
            max-width: 120px;
            text-align: center;
            margin: auto;
        }
    </style>
</head>

<body>
    <div class="d-flex">
        <!-- SIDEBAR -->
        <aside id="sidebar" class="sidebar-toggle">
            <div class="sidebar-logo mt-3">
                <img src="../../assets/images/official_logo.png" width="80" height="80">
            </div>
            <div class="menu-title">Navigation</div>
            <li class="sidebar-item"><a href="auditing.php" class="sidebar-link"><i class="bi bi-cast"></i><span style="font-size:18px;">Dashboard</span></a></li>
            <li class="sidebar-item"><a href="audit.php" class="sidebar-link active"><i class="bi bi-card-checklist"></i><span style="font-size:18px;">Weekly Audit</span></a></li>
            <li class="sidebar-item"><a href="../../logout.php" class="sidebar-link"><i class="bi bi-box-arrow-right"></i><span style="font-size:18px;">Logout</span></a></li>
        </aside>

        <!-- MAIN -->
        <div class="main">
            <!-- TOPBAR -->
            <div class="topbar">
                <div class="toggle">
                    <button class="toggler-btn" type="button">
                        <i class="bi bi-list-ul" style="font-size:28px;"></i>
                    </button>
                </div>
                <div class="logo d-flex align-items-center">
                    <span class="username me-2 fw-bold text-primary"><?= htmlspecialchars($firstname) ?> (Accounting)</span>
                </div>
            </div>

            <!-- CONTENT -->
            <div class="container-fluid mt-3">
                <div class="card shadow-lg border-0 rounded-4" style="height:83vh;">
                    <div class="card-body d-flex flex-column">

                        <!-- HEADER -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h3 class="fw-bold mb-0">Weekly Inventory Audit</h3>
                            <div class="d-flex flex-column align-items-end">
                                <form method="GET">
                                    <input type="week" name="week" value="<?= date('Y-\WW', strtotime($startOfWeek)) ?>" class="form-control form-control-sm" id="weekPicker">
                                </form>
                                <span class="badge bg-primary fs-6 mt-1">Week of <?= $weekDisplay ?></span>
                            </div>
                        </div>

                        <?php if ($auditDone): ?>
                            <div class="alert alert-success d-flex align-items-center mb-3">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                <strong>Weekly audit already completed.</strong>
                                <span class="ms-2 text-muted">No further edits allowed for this week.</span>
                            </div>
                        <?php endif; ?>

                        <!-- FORM -->
                        <form method="POST" action="logics/save_audit.php" class="d-flex flex-column flex-grow-1">
                            <input type="hidden" name="audit_week" value="<?= $startOfWeek ?>">

                            <!-- TABLE -->
                            <div class="audit-table-wrapper">
                                <table class="table table-bordered align-middle audit-table mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width:60%;">Item Description</th>
                                            <th style="width:20%;" class="text-center">Current Qty.</th>
                                            <th style="width:20%;" class="text-center">Audited Qty.</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = $items->fetch_assoc()): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($row['description']) ?></td>
                                                <td class="text-center fw-semibold"><?= $row['qty_on_hand'] ?></td>
                                                <td class="text-center">
                                                    <input type="number"
                                                        name="audit[<?= $row['id'] ?>]"
                                                        class="form-control mx-auto"
                                                        min="0"
                                                        <?= $auditDone ? 'disabled' : 'required' ?>>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- FOOTER -->
                            <div class="pt-3 border-top bg-white d-flex justify-content-end">
                                <?php if (!$auditDone): ?>
                                    <button class="btn btn-primary btn-sm fw-semibold px-4">
                                        <i class="bi bi-check2-circle me-1"></i> Submit Audit
                                    </button>
                                <?php endif; ?>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const toggler = document.querySelector(".toggler-btn");
        toggler.addEventListener("click", function() {
            document.querySelector("#sidebar").classList.toggle("collapsed");
        });

        // Auto-submit week picker
        document.getElementById('weekPicker').addEventListener('change', function() {
            this.form.submit();
        });
    </script>
    <script src="../../assets/bootstrap/bootstrap.bundle.min.js"></script>
</body>

</html>