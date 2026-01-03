<?php
require_once __DIR__ . '/../../../config/config.php';
require_role('user');

$feedback = '';
$feedback_type = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $target_url = trim($_POST['target_url'] ?? '');

    if ($target_url === '') {
        $feedback = 'Target URL is required.';
        $feedback_type = 'error';
    } elseif (!filter_var($target_url, FILTER_VALIDATE_URL)) {
        $feedback = 'Please enter a valid URL (include http/https).';
        $feedback_type = 'error';
    } else {
        $conn = db_connect();
        $user_id = (int) $_SESSION['user_id'];
        $stmt = $conn->prepare('INSERT INTO targets (user_id, target_url, created_at) VALUES (?, ?, NOW())');
        $stmt->bind_param('is', $user_id, $target_url);

        if ($stmt->execute()) {
            set_flash('Target submitted successfully.', 'success');
            $stmt->close();
            $conn->close();
            redirect('app/views/dashboard/user.php');
        }

        $feedback = 'Failed to submit target. Please try again.';
        $feedback_type = 'error';
        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Target | <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/style.css">
</head>
<body class="role-user">
    <div class="page-shell">
        <header class="header-bar">
            <div>
                <h1>Submit Target URL</h1>
                <p class="muted">Customers can submit only URLs. No report uploads here.</p>
            </div>
            <nav class="nav-links">
                <a class="btn btn-ghost" href="<?php echo BASE_URL; ?>app/views/dashboard/user.php">Dashboard</a>
                <a class="btn btn-ghost" href="<?php echo BASE_URL; ?>app/views/reports/view.php">Approved Reports</a>
                <a class="btn btn-danger" href="<?php echo BASE_URL; ?>index.php?logout=1">Logout</a>
            </nav>
        </header>

        <?php if ($feedback !== '') : ?>
            <div class="alert alert-<?php echo e($feedback_type); ?>">
                <?php echo e($feedback); ?>
            </div>
        <?php endif; ?>

        <section class="panel">
            <h2>Target Details</h2>
            <form method="post" class="stack">
                <label>
                    <span>Target URL</span>
                    <input type="url" name="target_url" required placeholder="https://example.com">
                </label>
                <button type="submit" class="btn btn-primary">Submit Target</button>
            </form>
        </section>
    </div>
    <script src="<?php echo BASE_URL; ?>public/js/app.js"></script>
</body>
</html>
