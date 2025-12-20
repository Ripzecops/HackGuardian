<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HackGuardian</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; }
        .header { background: #333; color: white; padding: 15px; }
        .nav { display: flex; justify-content: space-between; }
        .nav-links a { color: white; margin-left: 20px; text-decoration: none; }
        .container { max-width: 1200px; margin: 20px auto; padding: 20px; }
        .btn { padding: 8px 16px; background: blue; color: white; border: none; cursor: pointer; }
        .btn-danger { background: red; }
        .btn-success { background: green; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .form-group { margin-bottom: 15px; }
        input, textarea, select { width: 100%; padding: 8px; margin-top: 5px; }
        .alert { padding: 10px; margin: 10px 0; border-radius: 4px; }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="header">
        <div class="nav">
            <h1>HackGuardian</h1>
            <div class="nav-links">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span>Welcome, <?php echo $_SESSION['username']; ?> (<?php echo $_SESSION['user_role']; ?>)</span>
                    <a href="/?action=logout">Logout</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="container">
