<?php require_once __DIR__ . '/../shared/header.php'; ?>

<h2>Security Engineer Dashboard</h2>

<p>Review and approve reports:</p>

<?php if (isset($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<h3>Reports to Review</h3>
<?php if (count($reports) > 0): ?>
    <table>
        <tr>
            <th>Task</th>
            <th>Findings</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($reports as $report): ?>
        <tr>
            <td><?php echo $report['task_title']; ?></td>
            <td><?php echo substr($report['findings'], 0, 100) . '...'; ?></td>
            <td><?php echo $report['status']; ?></td>
            <td>
                <a href="/?action=approve_report&id=<?php echo $report['id']; ?>" 
                   class="btn btn-success">Approve</a>
                <a href="/?action=reject_report&id=<?php echo $report['id']; ?>" 
                   class="btn btn-danger">Reject</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
<?php else: ?>
    <p>No reports found.</p>
<?php endif; ?>

<?php require_once __DIR__ . '/../shared/footer.php'; ?>