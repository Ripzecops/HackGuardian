<?php
require_once __DIR__ . '/../../../config/config.php';
require_login();

$tool_dir = ROOT_PATH . '/storage/tools';
$tools = [];

if (is_dir($tool_dir)) {
    $files = scandir($tool_dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }
        $path = $tool_dir . '/' . $file;
        if (is_file($path) && strtolower(pathinfo($file, PATHINFO_EXTENSION)) === 'pdf') {
            $tools[] = [
                'name' => $file,
                'path' => 'storage/tools/' . $file,
                'updated' => date('Y-m-d H:i:s', filemtime($path))
            ];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tool Library | <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/style.css">
</head>
<body class="role-<?php echo e($_SESSION['user_role'] ?? 'user'); ?>">
    <div class="page-shell">
        <header class="header-bar">
            <div>
                <h1>Shared Tool Library</h1>
                <p class="muted">PDF tools uploaded by admins for the team.</p>
            </div>
            <nav class="nav-links">
                <a class="btn btn-ghost" href="<?php echo BASE_URL; ?>index.php">Home</a>
                <?php if (($_SESSION['user_role'] ?? '') === 'admin') : ?>
                    <a class="btn btn-ghost" href="<?php echo BASE_URL; ?>app/views/tools/upload.php">Upload Tool</a>
                <?php endif; ?>
                <a class="btn btn-danger" href="<?php echo BASE_URL; ?>index.php?logout=1">Logout</a>
            </nav>
        </header>

        <section class="panel">
            <h2>Available Tools</h2>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>File</th>
                            <th>Last Updated</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($tools)) : ?>
                            <tr>
                                <td colspan="3">No tools uploaded yet.</td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($tools as $tool) : ?>
                                <tr>
                                    <td><?php echo e($tool['name']); ?></td>
                                    <td><?php echo e($tool['updated']); ?></td>
                                    <td><a class="btn btn-ghost" href="<?php echo BASE_URL . e($tool['path']); ?>" target="_blank">Download</a></td>
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
