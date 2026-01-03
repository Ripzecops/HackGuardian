<?php
require_once __DIR__ . '/../../../config/config.php';
require_role('admin');

$feedback = '';
$feedback_type = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $upload_error = '';
    $pdf_path = store_pdf_upload($_FILES['tool_pdf'] ?? null, 'storage/tools', $upload_error);

    if ($pdf_path === '') {
        $feedback = $upload_error;
        $feedback_type = 'error';
    } else {
        set_flash('Tool uploaded successfully.', 'success');
        redirect('app/views/tools/list.php');
    }
}

$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Tool | <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/style.css">
</head>
<body class="role-admin">
    <div class="page-shell">
        <header class="header-bar">
            <div>
                <h1>Upload Security Tool</h1>
                <p class="muted">Admin-only uploads for the shared tool library.</p>
            </div>
            <nav class="nav-links">
                <a class="btn btn-ghost" href="<?php echo BASE_URL; ?>app/views/dashboard/admin.php">Admin Dashboard</a>
                <a class="btn btn-ghost" href="<?php echo BASE_URL; ?>app/views/tools/list.php">View Tools</a>
                <a class="btn btn-danger" href="<?php echo BASE_URL; ?>index.php?logout=1">Logout</a>
            </nav>
        </header>

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
            <h2>Upload PDF Tool</h2>
            <form method="post" enctype="multipart/form-data" class="stack">
                <label>
                    <span>Tool PDF</span>
                    <input type="file" name="tool_pdf" accept="application/pdf" required>
                </label>
                <button type="submit" class="btn btn-primary">Upload Tool</button>
            </form>
        </section>
    </div>
    <script src="<?php echo BASE_URL; ?>public/js/app.js"></script>
</body>
</html>
