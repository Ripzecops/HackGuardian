<?php
require_once __DIR__ . '/config/config.php';

$roles = [
    'user' => 'Customer',
    'hacker' => 'Ethical Hacker',
    'pentester' => 'Penetration Tester',
    'qa' => 'Quality Analyst',
    'admin' => 'Administrator'
];

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    redirect('index.php');
}

$error = '';
$flash = get_flash();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = strtolower(trim($_POST['role'] ?? ''));

    if ($email === '' || $password === '' || $role === '') {
        $error = 'Please provide email, password, and role.';
    } elseif (!is_valid_role($role)) {
        $error = 'Invalid role selected.';
    } else {
        $conn = db_connect();
        $stmt = $conn->prepare('SELECT id, name, email, password, role FROM users WHERE email = ? AND role = ? LIMIT 1');

        if ($stmt) {
            $stmt->bind_param('ss', $email, $role);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result ? $result->fetch_assoc() : null;

            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = (int) $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];

                $stmt->close();
                $conn->close();
                redirect('app/views/dashboard/' . $user['role'] . '.php');
            }

            $stmt->close();
        }

        $conn->close();
        $error = 'Invalid credentials or role.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> | Secure Login</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/style.css">
</head>
<body class="role-guest">
    <div class="page-shell">
        <header class="hero">
            <div>
                <p class="eyebrow">Zero-trust bug bounty workflow</p>
                <h1><?php echo APP_NAME; ?></h1>
                <p class="subtitle">Role-based access for customers, hackers, pentesters, QA, and admins.</p>
            </div>
            <div class="hero-panel">
                <h2>Secure Login</h2>
                <p class="muted">Authenticate with your assigned role.</p>
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
                    <label>
                        <span>Email</span>
                        <input type="email" name="email" required placeholder="name@example.com">
                    </label>
                    <label>
                        <span>Password</span>
                        <input type="password" name="password" required placeholder="********">
                    </label>
                    <label>
                        <span>Role</span>
                        <select name="role" required>
                            <option value="">Select role</option>
                            <?php foreach ($roles as $value => $label) : ?>
                                <option value="<?php echo e($value); ?>"><?php echo e($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <button type="submit" class="btn btn-primary">Login</button>
                </form>
                <div class="split">
                    <a class="btn btn-ghost" href="<?php echo BASE_URL; ?>app/views/auth/register.php">Customer Registration</a>
                    <a class="btn btn-ghost" href="<?php echo BASE_URL; ?>index.php?logout=1">Clear Session</a>
                </div>
            </div>
        </header>

        <section class="grid-3">
            <article class="card">
                <h3>Controlled Intake</h3>
                <p>Customers submit targets only. Reports move across the secure pipeline.</p>
                <ul class="list">
                    <li>URL validation</li>
                    <li>Per-role access control</li>
                    <li>Audit-friendly records</li>
                </ul>
            </article>
            <article class="card">
                <h3>Tiered Verification</h3>
                <p>Hacker, pentester, and QA stages ensure verified security findings.</p>
                <ul class="list">
                    <li>PDF-only uploads</li>
                    <li>Sanitized filenames</li>
                    <li>Structured notes</li>
                </ul>
            </article>
            <article class="card">
                <h3>Outcome Visibility</h3>
                <p>Admins and customers get clear status visibility without altering reports.</p>
                <ul class="list">
                    <li>Approved-only views</li>
                    <li>Payout tracking</li>
                    <li>Secure chat</li>
                </ul>
            </article>
        </section>
    </div>
    <script src="<?php echo BASE_URL; ?>public/js/app.js"></script>
</body>
</html>
