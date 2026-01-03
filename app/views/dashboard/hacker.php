<?php
require_once __DIR__ . '/../../../config/config.php';
require_role('hacker');

$feedback = '';
$feedback_type = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_hacker_report'])) {
    $target_id = (int) ($_POST['target_id'] ?? 0);
    $notes = trim($_POST['notes'] ?? '');

    if ($target_id <= 0) {
        $feedback = 'Invalid target selected.';
        $feedback_type = 'error';
    } else {
        $conn = db_connect();

        $check = $conn->prepare('SELECT id FROM targets WHERE id = ? LIMIT 1');
        $check->bind_param('i', $target_id);
        $check->execute();
        $target_result = $check->get_result();

        $already = $conn->prepare('SELECT id FROM hacker_reports WHERE target_id = ? LIMIT 1');
        $already->bind_param('i', $target_id);
        $already->execute();
        $already_result = $already->get_result();

        if (!$target_result || $target_result->num_rows === 0) {
            $feedback = 'Target not found.';
            $feedback_type = 'error';
        } elseif ($already_result && $already_result->num_rows > 0) {
            $feedback = 'A hacker report already exists for this target.';
            $feedback_type = 'error';
        } else {
            $upload_error = '';
            $pdf_path = store_pdf_upload($_FILES['pdf_report'] ?? null, 'storage/reports', $upload_error);

            if ($pdf_path === '') {
                $feedback = $upload_error;
                $feedback_type = 'error';
            } else {
                $hacker_id = (int) $_SESSION['user_id'];
                $stmt = $conn->prepare('INSERT INTO hacker_reports (target_id, hacker_id, pdf_path, notes, created_at) VALUES (?, ?, ?, ?, NOW())');
                $stmt->bind_param('iiss', $target_id, $hacker_id, $pdf_path, $notes);

                if ($stmt->execute()) {
                    set_flash('Hacker report uploaded successfully.', 'success');
                    $stmt->close();
                    $already->close();
                    $check->close();
                    $conn->close();
                    redirect('app/views/dashboard/hacker.php');
                }

                $feedback = 'Failed to save report. Please try again.';
                $feedback_type = 'error';
                $stmt->close();
            }
        }

        $already->close();
        $check->close();
        $conn->close();
    }
}

$flash = get_flash();
$targets = [];

$conn = db_connect();
$stmt = $conn->prepare(
    'SELECT t.id, t.target_url, t.created_at, u.name, u.email, ' .
    'hr.id AS hacker_report_id, hr.pdf_path, hr.notes, hr.created_at AS report_created ' .
    'FROM targets t ' .
    'JOIN users u ON u.id = t.user_id ' .
    'LEFT JOIN hacker_reports hr ON hr.target_id = t.id ' .
    'ORDER BY t.created_at DESC'
);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $targets[] = $row;
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hacker Dashboard | <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/style.css">
</head>
<body class="role-hacker">
    <div class="page-shell">
        <header class="header-bar">
            <div>
                <h1>Ethical Hacker Ops</h1>
                <p class="muted">Review incoming targets and upload hacker reports.</p>
            </div>
            <nav class="nav-links">
                <a class="btn btn-ghost" href="<?php echo BASE_URL; ?>app/views/tools/list.php">Tool Library</a>
                <a class="btn btn-ghost" href="<?php echo BASE_URL; ?>index.php">Home</a>
                <a class="btn btn-danger" href="<?php echo BASE_URL; ?>index.php?logout=1">Logout</a>
            </nav>
        </header>

        <section class="grid-3">
            <article class="card">
                <h3>Target Queue</h3>
                <p>Targets are read-only. Review details before uploading your PDF report.</p>
                <ul class="list">
                    <li>Customer context</li>
                    <li>Time stamped</li>
                    <li>Immutable URLs</li>
                </ul>
            </article>
            <article class="card">
                <h3>Report Format</h3>
                <p>Only PDF uploads are accepted with verified notes.</p>
                <ul class="list">
                    <li>PDF only</li>
                    <li>Sanitized filenames</li>
                    <li>Secure storage</li>
                </ul>
            </article>
            <article class="card">
                <h3>Next Step</h3>
                <p>Pentesters will verify your report immediately after upload.</p>
                <ul class="list">
                    <li>Auto pipeline</li>
                    <li>Status tracking</li>
                    <li>Notes preserved</li>
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
            <h2>Incoming Targets</h2>
            <p class="muted">Upload a hacker report for each target. Only one report per target.</p>
            <div class="stack">
                <?php if (empty($targets)) : ?>
                    <div class="card">
                        <h3>No targets yet</h3>
                        <p>Waiting for customers to submit URLs.</p>
                    </div>
                <?php else : ?>
                    <?php foreach ($targets as $target) : ?>
                        <article class="card">
                            <div class="card-row">
                                <div>
                                    <h3><?php echo e($target['target_url']); ?></h3>
                                    <p>Customer: <?php echo e($target['name']); ?> (<?php echo e($target['email']); ?>)</p>
                                    <p>Submitted: <?php echo e($target['created_at']); ?></p>
                                </div>
                                <div>
                                    <?php if (!empty($target['hacker_report_id'])) : ?>
                                        <span class="badge badge-success">Report Uploaded</span>
                                        <p class="muted">Uploaded: <?php echo e($target['report_created']); ?></p>
                                        <a class="btn btn-ghost" href="<?php echo BASE_URL . e($target['pdf_path']); ?>" target="_blank">View PDF</a>
                                    <?php else : ?>
                                        <span class="badge badge-warning">Awaiting Upload</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if (empty($target['hacker_report_id'])) : ?>
                                <form method="post" enctype="multipart/form-data" class="stack">
                                    <input type="hidden" name="target_id" value="<?php echo (int) $target['id']; ?>">
                                    <label>
                                        <span>Hacker Report (PDF)</span>
                                        <input type="file" name="pdf_report" accept="application/pdf" required>
                                    </label>
                                    <label>
                                        <span>Notes</span>
                                        <textarea name="notes" rows="3" placeholder="Key findings and summary."></textarea>
                                    </label>
                                    <button type="submit" name="upload_hacker_report" class="btn btn-primary">Upload Hacker Report</button>
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
