<?php
require_once __DIR__ . '/../../../config/config.php';
require_role('user');

$user_id = (int) $_SESSION['user_id'];
$targets = [];
$approved_reports = [];

$conn = db_connect();
$stmt = $conn->prepare(
    'SELECT t.id, t.target_url, t.created_at, ' .
    'hr.id AS hacker_report_id, ' .
    'pr.id AS pentester_report_id, pr.pdf_path AS pentester_pdf, ' .
    'qr.id AS qa_review_id, qr.status AS qa_status, qr.severity, qr.cve_id, qr.bounty ' .
    'FROM targets t ' .
    'LEFT JOIN hacker_reports hr ON hr.target_id = t.id ' .
    'LEFT JOIN pentester_reports pr ON pr.hacker_report_id = hr.id ' .
    'LEFT JOIN qa_reviews qr ON qr.pentester_report_id = pr.id ' .
    'WHERE t.user_id = ? ' .
    'ORDER BY t.created_at DESC'
);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $targets[] = $row;
    if (($row['qa_status'] ?? '') === 'approved') {
        $approved_reports[] = $row;
    }
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard | <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/style.css">
</head>
<body class="role-user">
    <div class="page-shell">
        <header class="header-bar">
            <div>
                <h1>Customer Control Center</h1>
                <p class="muted">Track your targets and view approved reports.</p>
            </div>
            <nav class="nav-links">
                <a class="btn btn-ghost" href="<?php echo BASE_URL; ?>app/views/reports/submit.php">Submit Target</a>
                <a class="btn btn-ghost" href="<?php echo BASE_URL; ?>app/views/reports/view.php">Approved Reports</a>
                <a class="btn btn-ghost" href="<?php echo BASE_URL; ?>app/views/chat/user_chat.php">Chat Admin</a>
                <a class="btn btn-danger" href="<?php echo BASE_URL; ?>index.php?logout=1">Logout</a>
            </nav>
        </header>

        <section class="grid-3">
            <article class="card">
                <h3>Submission Rules</h3>
                <p>Only submit target URLs. No uploads or attachments at this stage.</p>
                <ul class="list">
                    <li>URL required</li>
                    <li>Auto timestamped</li>
                    <li>Private to your account</li>
                </ul>
            </article>
            <article class="card">
                <h3>Status Pipeline</h3>
                <p>Reports flow from hacker to pentester to QA for approval.</p>
                <ul class="list">
                    <li>Hacker report</li>
                    <li>Pentester verification</li>
                    <li>QA approval</li>
                </ul>
            </article>
            <article class="card">
                <h3>Support Access</h3>
                <p>Use secure chat with admin for help or escalations.</p>
                <ul class="list">
                    <li>Private messages</li>
                    <li>Logged timeline</li>
                    <li>Role-verified support</li>
                </ul>
            </article>
        </section>

        <section class="panel">
            <h2>My Targets</h2>
            <p class="muted">Every target you submitted and its current review status.</p>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Target URL</th>
                            <th>Submitted</th>
                            <th>QA Status</th>
                            <th>Severity</th>
                            <th>Bounty</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($targets)) : ?>
                            <tr>
                                <td colspan="5">No targets yet. Submit your first URL.</td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($targets as $target) : ?>
                                <tr>
                                    <td><?php echo e($target['target_url']); ?></td>
                                    <td><?php echo e($target['created_at']); ?></td>
                                    <td><?php echo e($target['qa_status'] ?? 'pending'); ?></td>
                                    <td><?php echo e($target['severity'] ?? '-'); ?></td>
                                    <td><?php echo $target['bounty'] ? '$' . e($target['bounty']) : '-'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="panel">
            <h2>Approved Reports</h2>
            <p class="muted">Only QA-approved reports are visible here.</p>
            <div class="grid-2">
                <?php if (empty($approved_reports)) : ?>
                    <div class="card">
                        <h3>No approvals yet</h3>
                        <p>Your approved reports will appear once QA marks them as approved.</p>
                    </div>
                <?php else : ?>
                    <?php foreach ($approved_reports as $report) : ?>
                        <article class="card">
                            <h3><?php echo e($report['target_url']); ?></h3>
                            <p>CVE: <?php echo e($report['cve_id'] ?? 'N/A'); ?></p>
                            <p>Severity: <?php echo e($report['severity'] ?? 'N/A'); ?></p>
                            <p>Bounty: $<?php echo e($report['bounty'] ?? 0); ?></p>
                            <?php if (!empty($report['pentester_pdf'])) : ?>
                                <a class="btn btn-primary" href="<?php echo BASE_URL . e($report['pentester_pdf']); ?>" target="_blank">Download Report</a>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </div>
    <script src="<?php echo BASE_URL; ?>public/js/app.js"></script>
</body>
</html>
