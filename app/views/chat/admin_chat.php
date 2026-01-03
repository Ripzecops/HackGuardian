<?php
require_once __DIR__ . '/../../../config/config.php';
require_role('admin');

$feedback = '';
$feedback_type = 'info';

$selected_user_id = (int) ($_GET['user_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected_user_id = (int) ($_POST['user_id'] ?? 0);
    $message = trim($_POST['message'] ?? '');

    if ($selected_user_id <= 0) {
        $feedback = 'Select a customer before sending a message.';
        $feedback_type = 'error';
    } elseif ($message === '') {
        $feedback = 'Message cannot be empty.';
        $feedback_type = 'error';
    } elseif (strlen($message) > 1000) {
        $feedback = 'Message is too long.';
        $feedback_type = 'error';
    } else {
        $conn = db_connect();
        $role = 'admin';
        $stmt = $conn->prepare('INSERT INTO chat (user_id, sender_role, message, created_at) VALUES (?, ?, ?, NOW())');
        $stmt->bind_param('iss', $selected_user_id, $role, $message);

        if ($stmt->execute()) {
            set_flash('Message sent.', 'success');
            $stmt->close();
            $conn->close();
            redirect('app/views/chat/admin_chat.php?user_id=' . $selected_user_id);
        }

        $feedback = 'Failed to send message.';
        $feedback_type = 'error';
        $stmt->close();
        $conn->close();
    }
}

$flash = get_flash();
$users = [];
$messages = [];

$conn = db_connect();
$user_stmt = $conn->prepare('SELECT id, name, email FROM users WHERE role = ? ORDER BY name ASC');
$role = 'user';
$user_stmt->bind_param('s', $role);
$user_stmt->execute();
$user_result = $user_stmt->get_result();

while ($row = $user_result->fetch_assoc()) {
    $users[] = $row;
}

$user_stmt->close();

if ($selected_user_id > 0) {
    $msg_stmt = $conn->prepare('SELECT sender_role, message, created_at FROM chat WHERE user_id = ? ORDER BY created_at ASC');
    $msg_stmt->bind_param('i', $selected_user_id);
    $msg_stmt->execute();
    $msg_result = $msg_stmt->get_result();

    while ($row = $msg_result->fetch_assoc()) {
        $messages[] = $row;
    }

    $msg_stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Chat | <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/style.css">
</head>
<body class="role-admin">
    <div class="page-shell">
        <header class="header-bar">
            <div>
                <h1>Admin Chat Console</h1>
                <p class="muted">Select a customer and respond to messages.</p>
            </div>
            <nav class="nav-links">
                <a class="btn btn-ghost" href="<?php echo BASE_URL; ?>app/views/dashboard/admin.php">Admin Dashboard</a>
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

        <section class="grid-2">
            <div class="panel">
                <h2>Customers</h2>
                <div class="stack">
                    <?php if (empty($users)) : ?>
                        <p class="muted">No customer accounts yet.</p>
                    <?php else : ?>
                        <?php foreach ($users as $user) : ?>
                            <a class="card <?php echo ($selected_user_id === (int) $user['id']) ? 'card-active' : ''; ?>" href="<?php echo BASE_URL; ?>app/views/chat/admin_chat.php?user_id=<?php echo (int) $user['id']; ?>">
                                <strong><?php echo e($user['name']); ?></strong>
                                <span class="muted"><?php echo e($user['email']); ?></span>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="panel">
                <h2>Conversation</h2>
                <?php if ($selected_user_id <= 0) : ?>
                    <p class="muted">Select a customer to view messages.</p>
                <?php else : ?>
                    <div class="chat-window">
                        <?php if (empty($messages)) : ?>
                            <p class="muted">No messages yet for this customer.</p>
                        <?php else : ?>
                            <?php foreach ($messages as $msg) : ?>
                                <div class="chat-bubble <?php echo ($msg['sender_role'] === 'admin') ? 'chat-admin' : 'chat-user'; ?>">
                                    <p><?php echo e($msg['message']); ?></p>
                                    <span><?php echo e($msg['sender_role']); ?> - <?php echo e($msg['created_at']); ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <form method="post" class="stack">
                        <input type="hidden" name="user_id" value="<?php echo (int) $selected_user_id; ?>">
                        <label>
                            <span>Message</span>
                            <textarea name="message" rows="4" placeholder="Type your response..."></textarea>
                        </label>
                        <button type="submit" class="btn btn-primary">Send Message</button>
                    </form>
                <?php endif; ?>
            </div>
        </section>
    </div>
    <script src="<?php echo BASE_URL; ?>public/js/app.js"></script>
</body>
</html>
