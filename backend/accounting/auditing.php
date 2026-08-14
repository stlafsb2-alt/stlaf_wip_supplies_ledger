<?php
session_start();
require_once '../../sql/config.php';
require_once "../../auth/oop/request_form.php";

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['department']) !== 'accounting') {
    header('Location: ' . BASE_URL . 'index.php');
    exit();
}

$firstname = ucfirst($_SESSION['username'] ?? 'accounting');

// ✅ Selected week (default = this week's Monday)
$selectedMonday = $_GET['week'] ?? date('Y-m-d', strtotime('monday this week'));
$startOfWeek = date('Y-m-d', strtotime($selectedMonday));
$endOfWeek = date('Y-m-d', strtotime($selectedMonday . ' +4 days')); // Monday to Friday

// Format display like "Dec 15-19, 2025"
$weekDisplay = date('M d', strtotime($startOfWeek)) . '-' . date('d, Y', strtotime($endOfWeek));

$db = new Database();
$conn = $db->getConnection();

$summaryStmt = $conn->prepare("
    SELECT
        COUNT(*) AS total_items,
        SUM(CASE WHEN variance != 0 THEN 1 ELSE 0 END) AS with_variance
    FROM item_audits
    WHERE audit_week = ?
");
$summaryStmt->bind_param("s", $startOfWeek);
$summaryStmt->execute();
$summary = $summaryStmt->get_result()->fetch_assoc();

// Audit Details
$auditStmt = $conn->prepare("
    SELECT 
        i.description,
        a.system_qty,
        a.physical_qty,
        a.variance
    FROM item_audits a
    JOIN items i ON a.item_id = i.id
    WHERE a.audit_week = ?
    ORDER BY ABS(a.variance) DESC
");
$auditStmt->bind_param("s", $startOfWeek);
$auditStmt->execute();
$auditResults = $auditStmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STLAF | Dashboard</title>
    <link rel="icon" type="image/png" href="../../assets/images/sub_logo_light.png">
    <link rel="stylesheet" href="../../assets/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/super.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        /* Summary cards */
        .summary-card {
            border-radius: 14px;
            transition: 0.15s;
        }

        .summary-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
        }

        .summary-card h6 {
            font-size: 13px;
            letter-spacing: .5px;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .summary-card h3 {
            font-size: 30px;
            margin-bottom: 0;
        }

        /* Audit table */
        .audit-table th {
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: .4px;
            background: #f8f9fa;
            position: sticky;
            top: 0;
            z-index: 1;
        }

        .audit-table td {
            font-size: 14px;
            vertical-align: middle;
        }

        .audit-table tbody tr:hover {
            background-color: #f9fbff;
        }

        .audit-scroll {
            max-height: 600px;
            overflow-y: auto;
        }

        .section-title {
            font-weight: 700;
            letter-spacing: .3px;
        }

        .badge {
            font-size: 12px;
            padding: 6px 10px;
            border-radius: 12px;
        }
    </style>
</head>

<body>
    <div class="d-flex">
        <aside id="sidebar" class="sidebar-toggle">
            <div class="sidebar-logo mt-3">
                <img src="../../assets/images/official_logo.png" width="80px" height="80px">
            </div>
            <div class="menu-title">Navigation</div>
            <li class="sidebar-item"><a href="auditing.php" class="sidebar-link"><i class="bi bi-cast"></i><span style="font-size:18px;">Dashboard</span></a></li>
            <li class="sidebar-item"><a href="audit.php" class="sidebar-link"><i class="bi bi-card-checklist"></i><span style="font-size:18px;">Weekly Audit</span></a></li>
            <li class="sidebar-item"><a href="../../logout.php" class="sidebar-link"><i class="bi bi-box-arrow-right"></i><span style="font-size:18px;">Logout</span></a></li>
        </aside>

        <div class="main">
            <div class="topbar">
                <div class="toggle">
                    <button class="toggler-btn" type="button">
                        <i class="bi bi-list-ul" style="font-size:28px;"></i>
                    </button>
                </div>
                <div class="logo d-flex align-items-center">
                    <span class="username me-2 fw-bold text-primary"><?= htmlspecialchars($firstname) ?> (Admin)</span>
                </div>
            </div>

            <div style="width:95%; margin:20px auto;">
                <!-- WEEKLY AUDIT SUMMARY -->
                <div class="row mb-4 g-3">
                    <div class="col-md-6 col-lg-3">
                        <div class="card shadow-sm border-0 summary-card">
                            <div class="card-body">
                                <h6 class="text-muted">Audited Items</h6>
                                <h3 class="fw-bold text-primary"><?= $summary['total_items'] ?? 0 ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="card shadow-sm border-0 summary-card">
                            <div class="card-body">
                                <h6 class="text-muted">With Variance</h6>
                                <h3 class="fw-bold text-danger"><?= $summary['with_variance'] ?? 0 ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="card shadow-sm border-0 summary-card">
                            <div class="card-body d-flex flex-column">
                                <h6 class="text-muted">Audit Week</h6>
                                <form method="GET" class="d-flex gap-2 align-items-center">
                                    <input type="week" name="week" value="<?= date('Y-\WW', strtotime($selectedMonday)) ?>" class="form-control form-control-sm" id="weekPicker">
                                </form>
                                <h6 class="fw-semibold mt-2"><?= $weekDisplay ?></h6>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- WEEKLY AUDIT TABLE -->
                <div class="card shadow-sm border-0 mt-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="section-title mb-0">Weekly Audit Results</h5>
                            <span class="badge bg-primary">Week of <?= $weekDisplay ?></span>
                        </div>

                        <?php if ($auditResults->num_rows === 0): ?>
                            <div class="alert alert-warning mb-0">No audit submitted for this week yet.</div>
                        <?php else: ?>
                            <div class="table-responsive audit-scroll">
                                <table class="table table-hover align-middle audit-table mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Item</th>
                                            <th class="text-center">Current Qty.</th>
                                            <th class="text-center">Audited Qty.</th>
                                            <th class="text-center">Variance</th>
                                            <th class="text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = $auditResults->fetch_assoc()): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($row['description']) ?></td>
                                                <td class="text-center"><?= $row['system_qty'] ?></td>
                                                <td class="text-center"><?= $row['physical_qty'] ?></td>
                                                <td class="text-center fw-bold">
                                                    <?php if ($row['variance'] < 0): ?>
                                                        <span class="text-danger"><?= $row['variance'] ?></span>
                                                    <?php elseif ($row['variance'] > 0): ?>
                                                        <span class="text-success">+<?= $row['variance'] ?></span>
                                                    <?php else: ?>
                                                        <span class="text-muted">0</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <?php if ($row['variance'] == 0): ?>
                                                        <span class="badge bg-success">OK</span>
                                                    <?php elseif ($row['variance'] < 0): ?>
                                                        <span class="badge bg-danger">Short</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning text-dark">Excess</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="../../assets/bootstrap/bootstrap.bundle.min.js"></script>
    <script>
        // Sidebar toggle
        const toggler = document.querySelector(".toggler-btn");
        toggler.addEventListener("click", function() {
            document.querySelector("#sidebar").classList.toggle("collapsed");
        });

        // Auto-submit week picker
        document.getElementById('weekPicker').addEventListener('change', function() {
            this.form.submit();
        });
    </script>

</body>

</html>