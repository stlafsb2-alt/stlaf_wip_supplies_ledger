<?php
session_start();
require_once '../../sql/config.php';
require_once "../../auth/oop/request_form.php";

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['department']) !== 'admin') {
    http_response_code(403);
    exit('Forbidden');
}

$db = new Database();
$conn = $db->getConnection();
$request = new Request($conn);

$requests = $request->getAllRequests();
$hasPending = false;

if (!empty($requests)):
    foreach ($requests as $r):
        if (in_array(strtolower($r['status']), ['cancelled', 'delivered'])) continue;
        $hasPending = true;
?>
        <tr class="text-center">
            <td><span class="fw-bold text-primary"><?= htmlspecialchars($r['req_id']) ?></span></td>
            <td><?= htmlspecialchars($r['name']) ?></td>
            <td><?= ucfirst($r['department']) ?></td>
            <td><?= htmlspecialchars($r['item']) ?></td>
            <td><?= htmlspecialchars($r['quantity']) ?></td>
            <td><?= htmlspecialchars($r['unit']) ?></td>

            <td>
                <?php if (strtolower($r['status']) === 'pending'): ?>
                    <span class="badge bg-secondary px-3 py-2 shadow-sm">Pending</span>
                <?php elseif (strtolower($r['status']) === 'approved'): ?>
                    <span class="badge bg-success px-3 py-2 shadow-sm">Approved</span>
                <?php endif; ?>
            </td>

            <td>
                <?php if (strtolower($r['status']) === 'pending'): ?>
                    <button onclick="updateRequestStatus(<?= $r['req_id'] ?>, 'Approved')" class="btn btn-success btn-sm px-2 shadow-sm">
                        Approve
                    </button>

                    <button onclick="updateRequestStatus(<?= $r['req_id'] ?>, 'Cancelled')" class="btn btn-danger btn-sm px-2 shadow-sm">
                        Decline
                    </button>

                <?php elseif (strtolower($r['status']) === 'approved'): ?>
                    <button onclick="updateRequestStatus(<?= $r['req_id'] ?>, 'Delivered')" class="btn btn-primary btn-sm px-2 shadow-sm">
                        Delivered
                    </button>
                <?php endif; ?>

                <button onclick="deleteRequest(<?= $r['req_id'] ?>)" class="btn btn-outline-danger btn-sm px-2 shadow-sm">
                    <i class="bi bi-trash"></i>
                </button>
            </td>

        </tr>
    <?php
    endforeach;
endif;

if (!$hasPending): ?>
    <tr>
        <td colspan="9" class="text-center text-muted py-3">No requests available.</td>
    </tr>
<?php endif; ?>