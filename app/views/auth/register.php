<?php
require_once __DIR__ . '/../../../config/config.php';

if (!empty($_SESSION['user_id']) && ($_SESSION['user_role'] ?? '') !== 'user') {
    set_flash('Access denied', 'error');
    redirect('index.php');
}

$error = '';
$flash = get_flash();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $role = strtolower(trim($_POST['role'] ?? 'user'));

    if ($role !== 'user') {
        $error = 'Only customers can register.';
    } elseif ($name === '' || $email === '' || $password === '') {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please provide a valid email address.';
    } elseif (strlen($password) < 3) {
        $error = 'Password must be at least 3 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $conn = db_connect();

        $check = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $check->bind_param('s', $email);
        $check->execute();
        $existing = $check->get_result();

        if ($existing && $existing->num_rows > 0) {
            $error = 'Email already registered.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)');
            $stmt->bind_param('ssss', $name, $email, $hash, $role);

            if ($stmt->execute()) {
                $stmt->close();
                $check->close();
                $conn->close();
                set_flash('Registration successful. Please log in.', 'success');
                redirect('index.php');
            }

            $stmt->close();
        }

        $check->close();
        $conn->close();

        if ($error === '') {
            $error = 'Registration failed. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/style.css">
</head>
<body class="role-user">
    <div class="page-shell">
        <header class="header-bar">
            <a class="logo" href="<?php echo BASE_URL; ?>index.php"><?php echo APP_NAME; ?></a>
            <nav class="nav-links">
                <a href="<?php echo BASE_URL; ?>index.php">Login</a>
            </nav>
        </header>

        <section class="panel">
            <h1>Customer Registration</h1>
            <p class="muted">Only customers (role: user) can create new accounts.</p>

            <?php if ($flash) : ?>
                <div class="alert alert-<?php echo e($flash['type']); ?>">
                    <?php echo e($flash['message']); ?>
                </div>
            <?php endif; ?>
            <?php if ($error !== '') : ?>
                <div class="alert alert-error">
                    <?php echo e($error); ?>
                </div>
            <?php endif; ?>

            <form method="post" class="stack">
                <input type="hidden" name="role" value="user">
                <label>
                    <span>Full Name</span>
                    <input type="text" name="name" required placeholder="Customer name">
                </label>
                <label>
                    <span>Email</span>
                    <input type="email" name="email" required placeholder="customer@example.com">
                </label>
                <label>
                    <span>Password</span>
                    <input type="password" name="password" required placeholder="Create a password">
                </label>
                <label>
                    <span>Confirm Password</span>
                    <input type="password" name="confirm_password" required placeholder="Re-enter password">
                </label>
                <button type="submit" class="btn btn-primary">Create Account</button>
            </form>
        </section>
    </div>
    <script src="<?php echo BASE_URL; ?>public/js/app.js"></script>
</body>
</html>
