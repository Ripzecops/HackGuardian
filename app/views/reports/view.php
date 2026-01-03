<?php
require_once __DIR__ . '/../../../config/config.php';
require_role('user');

$user_id = (int) $_SESSION['user_id'];
$reports = [];

$conn = db_connect();
$stmt = $conn->prepare(
    'SELECT t.target_url, qr.cve_id, qr.severity, qr.bounty, qr.created_at AS qa_created, ' .
    'pr.pdf_path AS pentester_pdf ' .
    'FROM targets t ' .
    'JOIN hacker_reports hr ON hr.target_id = t.id ' .
    'JOIN pentester_reports pr ON pr.hacker_report_id = hr.id ' .
    'JOIN qa_reviews qr ON qr.pentester_report_id = pr.id ' .
    'WHERE t.user_id = ? AND qr.status = ? ' .
    'ORDER BY qr.created_at DESC'
);
$status = 'approved';
$stmt->bind_param('is', $user_id, $status);
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
    <title>Approved Reports | <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/style.css">
</head>
<body class="role-user">
    <div class="page-shell">
        <header class="header-bar">
            <div>
                <h1>QA Approved Reports</h1>
                <p class="muted">Only approved reports are shown here.</p>
            </div>
            <nav class="nav-links">
                <a class="btn btn-ghost" href="<?php echo BASE_URL; ?>app/views/dashboard/user.php">Dashboard</a>
                <a class="btn btn-ghost" href="<?php echo BASE_URL; ?>app/views/reports/submit.php">Submit Target</a>
                <a class="btn btn-danger" href="<?php echo BASE_URL; ?>index.php?logout=1">Logout</a>
            </nav>
        </header>

        <section class="panel">
            <h2>Approved Findings</h2>
            <div class="grid-2">
                <?php if (empty($reports)) : ?>
                    <div class="card">
                        <h3>No approved reports yet</h3>
                        <p>QA-approved reports will appear here when ready.</p>
                    </div>
                <?php else : ?>
                    <?php foreach ($reports as $report) : ?>
                        <article class="card">
                            <h3><?php echo e($report['target_url']); ?></h3>
                            <p>CVE: <?php echo e($report['cve_id']); ?></p>
                            <p>Severity: <?php echo e($report['severity']); ?></p>
                            <p>Bounty: $<?php echo e($report['bounty']); ?></p>
                            <p>Approved: <?php echo e($report['qa_created']); ?></p>
                            <a class="btn btn-primary" href="<?php echo BASE_URL . e($report['pentester_pdf']); ?>" target="_blank">Download PDF</a>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </div>
    <script src="<?php echo BASE_URL; ?>public/js/app.js"></script>
</body>
</html>
