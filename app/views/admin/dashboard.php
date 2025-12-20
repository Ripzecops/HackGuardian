<?php require_once __DIR__ . '/../shared/header.php'; ?>

<h2>Admin Dashboard</h2>

<p>System overview:</p>

<div style="display: flex; gap: 20px; margin: 20px 0;">
    <div style="background: #e3f2fd; padding: 20px; border-radius: 5px;">
        <h3>Users</h3>
        <p>Total: <?php echo count($users); ?></p>
    </div>
    <div style="background: #f3e5f5; padding: 20px; border-radius: 5px;">
        <h3>Requests</h3>
        <p>Total: <?php echo count($requests); ?></p>
    </div>
    <div style="background: #e8f5e8; padding: 20px; border-radius: 5px;">
        <h3>Tasks</h3>
        <p>Total: <?php echo count($tasks); ?></p>
    </div>
</div>

<a href="/?action=manage_users" class="btn">Manage Users</a>

<h3>Recent Users</h3>
<?php if (count($users) > 0): ?>
    <table>
        <tr>
            <th>Username</th>
            <th>Email</th>
            <th>Role</th>
            <th>Joined</th>
        </tr>
        <?php foreach (array_slice($users, 0, 5) as $user): ?>
        <tr>
            <td><?php echo $user['username']; ?></td>
            <td><?php echo $user['email']; ?></td>
            <td><?php echo $user['role']; ?></td>
            <td><?php echo $user['created_at']; ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
<?php else: ?>
    <p>No users found.</p>
<?php endif; ?>

<?php require_once __DIR__ . '/../shared/footer.php'; ?>