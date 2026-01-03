<?php
require_once __DIR__ . '/../../../config/config.php';
require_role('qa');

$feedback = '';
$feedback_type = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_qa_review'])) {
    $pentester_report_id = (int) ($_POST['pentester_report_id'] ?? 0);
    $cve_id = trim($_POST['cve_id'] ?? '');
    $severity = strtolower(trim($_POST['severity'] ?? ''));
    $status = strtolower(trim($_POST['status'] ?? ''));

    $valid_severity = ['low', 'medium', 'high', 'critical'];
    $valid_status = ['approved', 'rejected'];

    if ($pentester_report_id <= 0) {
        $feedback = 'Invalid pentester report selected.';
        $feedback_type = 'error';
    } elseif ($cve_id === '') {
        $feedback = 'CVE ID is required.';
        $feedback_type = 'error';
    } elseif (!in_array($severity, $valid_severity, true)) {
        $feedback = 'Invalid severity selected.';
        $feedback_type = 'error';
    } elseif (!in_array($status, $valid_status, true)) {
        $feedback = 'Invalid status selected.';
        $feedback_type = 'error';
    } else {
        $conn = db_connect();

        $check = $conn->prepare('SELECT id FROM pentester_reports WHERE id = ? LIMIT 1');
        $check->bind_param('i', $pentester_report_id);
        $check->execute();
        $report_result = $check->get_result();

        $already = $conn->prepare('SELECT id FROM qa_reviews WHERE pentester_report_id = ? LIMIT 1');
        $already->bind_param('i', $pentester_report_id);
        $already->execute();
        $already_result = $already->get_result();

        if (!$report_result || $report_result->num_rows === 0) {
            $feedback = 'Pentester report not found.';
            $feedback_type = 'error';
        } elseif ($already_result && $already_result->num_rows > 0) {
            $feedback = 'QA review already exists for this report.';
            $feedback_type = 'error';
        } else {
            $qa_id = (int) $_SESSION['user_id'];
            $bounty = bounty_for_severity($severity);

            $stmt = $conn->prepare(
                'INSERT INTO qa_reviews (pentester_report_id, qa_id, cve_id, severity, bounty, status, created_at) ' .
                'VALUES (?, ?, ?, ?, ?, ?, NOW())'
            );
            $stmt->bind_param('iissis', $pentester_report_id, $qa_id, $cve_id, $severity, $bounty, $status);

            if ($stmt->execute()) {
                $qa_review_id = $conn->insert_id;

                $payout = $conn->prepare('INSERT INTO payouts (qa_review_id, admin_id, payout_status, updated_at) VALUES (?, NULL, ?, NOW())');
                $payout_status = 'unpaid';
                $payout->bind_param('is', $qa_review_id, $payout_status);
                $payout->execute();
                $payout->close();

                set_flash('QA review submitted successfully.', 'success');
                $stmt->close();
                $already->close();
                $check->close();
                $conn->close();
                redirect('app/views/dashboard/qa.php');
            }

            $feedback = 'Failed to save QA review.';
            $feedback_type = 'error';
            $stmt->close();
        }

        $already->close();
        $check->close();
        $conn->close();
    }
}

$flash = get_flash();
$reports = [];

$conn = db_connect();
$stmt = $conn->prepare(
    'SELECT pr.id AS pentester_report_id, pr.pdf_path AS pentester_pdf, pr.notes AS pentester_notes, pr.created_at AS pentester_created, ' .
    'hr.pdf_path AS hacker_pdf, hr.notes AS hacker_notes, ' .
    't.target_url, u.name AS customer_name, ' .
    'qr.id AS qa_review_id, qr.cve_id, qr.severity, qr.bounty, qr.status, qr.created_at AS qa_created ' .
    'FROM pentester_reports pr ' .
    'JOIN hacker_reports hr ON hr.id = pr.hacker_report_id ' .
    'JOIN targets t ON t.id = hr.target_id ' .
    'JOIN users u ON u.id = t.user_id ' .
    'LEFT JOIN qa_reviews qr ON qr.pentester_report_id = pr.id ' .
    'ORDER BY pr.created_at DESC'
);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $reports[] = $row;
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QA Dashboard | <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/style.css">
</head>
<body class="role-qa">
    <div class="page-shell">
        <header class="header-bar">
            <div>
                <h1>QA Review Console</h1>
                <p class="muted">Validate verified reports, set severity, and approve or reject.</p>
            </div>
            <nav class="nav-links">
                <a class="btn btn-ghost" href="<?php echo BASE_URL; ?>index.php">Home</a>
                <a class="btn btn-danger" href="<?php echo BASE_URL; ?>index.php?logout=1">Logout</a>
            </nav>
        </header>

        <section class="grid-3">
            <article class="card">
                <h3>Mandatory CVE</h3>
                <p>CVE ID is required for all reviews. Severity drives bounty.</p>
                <ul class="list">
                    <li>low: $100</li>
                    <li>medium: $500</li>
                    <li>high: $5000</li>
                    <li>critical: $25000</li>
                </ul>
            </article>
            <article class="card">
                <h3>Decision Gate</h3>
                <p>Approve or reject after reviewing pentester evidence.</p>
                <ul class="list">
                    <li>Status required</li>
                    <li>Audit trail saved</li>
                    <li>Customer visibility only on approval</li>
                </ul>
            </article>
            <article class="card">
                <h3>Traceable Data</h3>
                <p>All reports remain immutable for compliance.</p>
                <ul class="list">
                    <li>PDF locked</li>
                    <li>Notes preserved</li>
                    <li>Role-based review</li>
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
            <h2>Pentester Verified Reports</h2>
            <p class="muted">Submit QA review with CVE and severity.</p>
            <div class="stack">
                <?php if (empty($reports)) : ?>
                    <div class="card">
                        <h3>No pentester reports yet</h3>
                        <p>Waiting for verified submissions.</p>
                    </div>
                <?php else : ?>
                    <?php foreach ($reports as $report) : ?>
                        <article class="card">
                            <div class="card-row">
                                <div>
                                    <h3><?php echo e($report['target_url']); ?></h3>
                                    <p>Customer: <?php echo e($report['customer_name']); ?></p>
                                    <p>Pentester report: <?php echo e($report['pentester_created']); ?></p>
                                    <a class="btn btn-ghost" href="<?php echo BASE_URL . e($report['pentester_pdf']); ?>" target="_blank">View Pentester PDF</a>
                                </div>
                                <div>
                                    <?php if (!empty($report['qa_review_id'])) : ?>
                                        <span class="badge badge-success">Reviewed</span>
                                        <p class="muted">Status: <?php echo e($report['status']); ?></p>
                                        <p class="muted">Severity: <?php echo e($report['severity']); ?></p>
                                        <p class="muted">Bounty: $<?php echo e($report['bounty']); ?></p>
                                    <?php else : ?>
                                        <span class="badge badge-warning">Awaiting QA</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if (empty($report['qa_review_id'])) : ?>
                                <form method="post" class="stack">
                                    <input type="hidden" name="pentester_report_id" value="<?php echo (int) $report['pentester_report_id']; ?>">
                                    <label>
                                        <span>CVE ID</span>
                                        <input type="text" name="cve_id" required placeholder="CVE-2024-12345">
                                    </label>
                                    <label>
                                        <span>Severity</span>
                                        <select name="severity" required>
                                            <option value="">Select severity</option>
                                            <option value="low">Low</option>
                                            <option value="medium">Medium</option>
                                            <option value="high">High</option>
                                            <option value="critical">Critical</option>
                                        </select>
                                    </label>
                                    <label>
                                        <span>Status</span>
                                        <select name="status" required>
                                            <option value="">Select status</option>
                                            <option value="approved">Approved</option>
                                            <option value="rejected">Rejected</option>
                                        </select>
                                    </label>
                                    <button type="submit" name="submit_qa_review" class="btn btn-primary">Submit QA Review</button>
                                </form>
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
