<?php
require_once __DIR__ . '/../../../config/config.php';
require_role('admin');

$feedback = '';
$feedback_type = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_payout'])) {
    $qa_review_id = (int) ($_POST['qa_review_id'] ?? 0);
    $payout_status = strtolower(trim($_POST['payout_status'] ?? ''));
    $valid_status = ['paid', 'unpaid'];

    if ($qa_review_id <= 0) {
        $feedback = 'Invalid review selected.';
        $feedback_type = 'error';
    } elseif (!in_array($payout_status, $valid_status, true)) {
        $feedback = 'Invalid payout status.';
        $feedback_type = 'error';
    } else {
        $conn = db_connect();
        $admin_id = (int) $_SESSION['user_id'];

        $check = $conn->prepare('SELECT id FROM payouts WHERE qa_review_id = ? LIMIT 1');
        $check->bind_param('i', $qa_review_id);
        $check->execute();
        $existing = $check->get_result();

        if ($existing && $existing->num_rows > 0) {
            $stmt = $conn->prepare('UPDATE payouts SET admin_id = ?, payout_status = ?, updated_at = NOW() WHERE qa_review_id = ?');
            $stmt->bind_param('isi', $admin_id, $payout_status, $qa_review_id);
        } else {
            $stmt = $conn->prepare('INSERT INTO payouts (qa_review_id, admin_id, payout_status, updated_at) VALUES (?, ?, ?, NOW())');
            $stmt->bind_param('iis', $qa_review_id, $admin_id, $payout_status);
        }

        if ($stmt->execute()) {
            set_flash('Payout status updated.', 'success');
            $stmt->close();
            $check->close();
            $conn->close();
            redirect('app/views/dashboard/admin.php');
        }

        $feedback = 'Failed to update payout status.';
        $feedback_type = 'error';
        $stmt->close();
        $check->close();
        $conn->close();
    }
}

$flash = get_flash();
$rows = [];

$conn = db_connect();
$stmt = $conn->prepare(
    'SELECT t.id AS target_id, t.target_url, t.created_at, u.name AS customer_name, u.email AS customer_email, ' .
    'hr.id AS hacker_report_id, pr.id AS pentester_report_id, ' .
    'qr.id AS qa_review_id, qr.status AS qa_status, qr.severity, qr.bounty, qr.cve_id, ' .
    'p.payout_status, p.updated_at AS payout_updated ' .
    'FROM targets t ' .
    'JOIN users u ON u.id = t.user_id ' .
    'LEFT JOIN hacker_reports hr ON hr.target_id = t.id ' .
    'LEFT JOIN pentester_reports pr ON pr.hacker_report_id = hr.id ' .
    'LEFT JOIN qa_reviews qr ON qr.pentester_report_id = pr.id ' .
    'LEFT JOIN payouts p ON p.qa_review_id = qr.id ' .
    'ORDER BY t.created_at DESC'
);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/style.css">
</head>
<body class="role-admin">
    <div class="page-shell">
        <header class="header-bar">
            <div>
                <h1>Admin Oversight Hub</h1>
                <p class="muted">Monitor pipeline status and manage payouts.</p>
            </div>
            <nav class="nav-links">
                <a class="btn btn-ghost" href="<?php echo BASE_URL; ?>app/views/chat/admin_chat.php">Chat Customers</a>
                <a class="btn btn-ghost" href="<?php echo BASE_URL; ?>app/views/tools/upload.php">Upload Tools</a>
                <a class="btn btn-danger" href="<?php echo BASE_URL; ?>index.php?logout=1">Logout</a>
            </nav>
        </header>

        <section class="grid-3">
            <article class="card">
                <h3>Pipeline Visibility</h3>
                <p>See the full status for every submitted target.</p>
                <ul class="list">
                    <li>Target details</li>
                    <li>QA status</li>
                    <li>Payout tracking</li>
                </ul>
            </article>
            <article class="card">
                <h3>Payout Control</h3>
                <p>Admins can mark payouts as paid or unpaid only.</p>
                <ul class="list">
                    <li>No report edits</li>
                    <li>Simple status toggle</li>
                    <li>Timestamped updates</li>
                </ul>
            </article>
            <article class="card">
                <h3>Secure Messaging</h3>
                <p>Customer chat stays in one verified thread per user.</p>
                <ul class="list">
                    <li>Auditable logs</li>
                    <li>Role tags</li>
                    <li>Quick response</li>
                </ul>
            </article>
        </section>

        <?php if ($flash) : ?>
            <div class="alert alert-<?php echo e($flash['type']); ?>">
                <?php echo e($flash['message']); ?>
            </div>
        <?php endif; ?>
        <?php if ($feedback !== '') : ?>
            <div class="alert alert-<?php echo e($feedback_type); ?>">
                <?php echo e($feedback); ?>
            </div>
        <?php endif; ?>

        <section class="panel">
            <h2>All Targets & Status</h2>
            <p class="muted">Admin view across the entire review chain.</p>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Target</th>
                            <th>Customer</th>
                            <th>QA Status</th>
                            <th>Severity</th>
                            <th>Bounty</th>
                            <th>Payout</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($rows)) : ?>
                            <tr>
                                <td colspan="6">No targets submitted yet.</td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($rows as $row) : ?>
                                <tr>
                                    <td>
                                        <strong><?php echo e($row['target_url']); ?></strong>
                                        <div class="muted">Submitted: <?php echo e($row['created_at']); ?></div>
                                    </td>
                                    <td>
                                        <?php echo e($row['customer_name']); ?><br>
                                        <span class="muted"><?php echo e($row['customer_email']); ?></span>
                                    </td>
                                    <td><?php echo e($row['qa_status'] ?? 'pending'); ?></td>
                                    <td><?php echo e($row['severity'] ?? '-'); ?></td>
                                    <td><?php echo $row['bounty'] ? '$' . e($row['bounty']) : '-'; ?></td>
                                    <td>
                                        <?php if (!empty($row['qa_review_id'])) : ?>
                                            <form method="post" class="inline-form" data-confirm="Update payout status?">
                                                <input type="hidden" name="qa_review_id" value="<?php echo (int) $row['qa_review_id']; ?>">
                                                <select name="payout_status">
                                                    <option value="unpaid" <?php echo ($row['payout_status'] === 'unpaid') ? 'selected' : ''; ?>>Unpaid</option>
                                                    <option value="paid" <?php echo ($row['payout_status'] === 'paid') ? 'selected' : ''; ?>>Paid</option>
                                                </select>
                                                <button type="submit" name="update_payout" class="btn btn-ghost">Update</button>
                                            </form>
                                            <div class="muted">Updated: <?php echo e($row['payout_updated'] ?? 'n/a'); ?></div>
                                        <?php else : ?>
                                            <span class="muted">Pending QA review</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
    <script src="<?php echo BASE_URL; ?>public/js/app.js"></script>
</body>
</html>
