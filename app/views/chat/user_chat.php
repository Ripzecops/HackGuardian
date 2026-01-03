<?php
require_once __DIR__ . '/../../../config/config.php';
require_role('user');

$feedback = '';
$feedback_type = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = trim($_POST['message'] ?? '');

    if ($message === '') {
        $feedback = 'Message cannot be empty.';
        $feedback_type = 'error';
    } elseif (strlen($message) > 1000) {
        $feedback = 'Message is too long.';
        $feedback_type = 'error';
    } else {
        $conn = db_connect();
        $user_id = (int) $_SESSION['user_id'];
        $role = 'user';
        $stmt = $conn->prepare('INSERT INTO chat (user_id, sender_role, message, created_at) VALUES (?, ?, ?, NOW())');
        $stmt->bind_param('iss', $user_id, $role, $message);

        if ($stmt->execute()) {
            set_flash('Message sent.', 'success');
            $stmt->close();
            $conn->close();
            redirect('app/views/chat/user_chat.php');
        }

        $feedback = 'Failed to send message.';
        $feedback_type = 'error';
        $stmt->close();
        $conn->close();
    }
}

$flash = get_flash();
$messages = [];

$conn = db_connect();
$user_id = (int) $_SESSION['user_id'];
$stmt = $conn->prepare('SELECT sender_role, message, created_at FROM chat WHERE user_id = ? ORDER BY created_at ASC');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Chat | <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/style.css">
</head>
<body class="role-user">
    <div class="page-shell">
        <header class="header-bar">
            <div>
                <h1>Chat with Admin</h1>
                <p class="muted">Secure messages between you and the admin team.</p>
            </div>
            <nav class="nav-links">
                <a class="btn btn-ghost" href="<?php echo BASE_URL; ?>app/views/dashboard/user.php">Dashboard</a>
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
            <h2>Conversation</h2>
            <div class="chat-window">
                <?php if (empty($messages)) : ?>
                    <p class="muted">No messages yet. Start the conversation below.</p>
                <?php else : ?>
                    <?php foreach ($messages as $msg) : ?>
                        <div class="chat-bubble <?php echo ($msg['sender_role'] === 'user') ? 'chat-user' : 'chat-admin'; ?>">
                            <p><?php echo e($msg['message']); ?></p>
                            <span><?php echo e($msg['sender_role']); ?> - <?php echo e($msg['created_at']); ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <form method="post" class="stack">
                <label>
                    <span>Message</span>
                    <textarea name="message" rows="4" placeholder="Type your message..."></textarea>
                </label>
                <button type="submit" class="btn btn-primary">Send Message</button>
            </form>
        </section>
    </div>
    <script src="<?php echo BASE_URL; ?>public/js/app.js"></script>
</body>
</html>
