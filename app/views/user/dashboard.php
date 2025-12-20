<?php require_once __DIR__ . '/../shared/header.php'; ?>

<h2>User Dashboard</h2>

<p>Welcome to your dashboard! Here are your recent requests:</p>

<?php if (isset($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<a href="/?action=create_request" class="btn">New Request</a>

<h3>Your Requests</h3>
<?php if (count($requests) > 0): ?>
    <table>
        <tr>
            <th>Title</th>
            <th>Description</th>
            <th>Status</th>
            <th>Date</th>
        </tr>
        <?php foreach ($requests as $request): ?>
        <tr>
            <td><?php echo $request['title']; ?></td>
            <td><?php echo $request['description']; ?></td>
            <td><?php echo $request['status']; ?></td>
            <td><?php echo $request['created_at']; ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
<?php else: ?>
    <p>No requests found.</p>
<?php endif; ?>

<?php require_once __DIR__ . '/../shared/footer.php'; ?>