<?php require_once __DIR__ . '/../shared/header.php'; ?>

<h2>Hacker Dashboard</h2>

<p>Your assigned tasks:</p>

<?php if (isset($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<a href="/?action=submit_findings" class="btn">Submit Findings</a>

<h3>Your Tasks</h3>
<?php if (count($tasks) > 0): ?>
    <table>
        <tr>
            <th>Title</th>
            <th>Description</th>
            <th>Assigned By</th>
            <th>Status</th>
        </tr>
        <?php foreach ($tasks as $task): ?>
        <tr>
            <td><?php echo $task['title']; ?></td>
            <td><?php echo $task['description']; ?></td>
            <td><?php echo $task['assigned_by_name']; ?></td>
            <td><?php echo $task['status']; ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
<?php else: ?>
    <p>No tasks assigned.</p>
<?php endif; ?>

<?php require_once __DIR__ . '/../shared/footer.php'; ?>