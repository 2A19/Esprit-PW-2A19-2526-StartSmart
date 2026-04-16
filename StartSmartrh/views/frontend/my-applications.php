<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container" style="padding: 2rem 0;">
    <h1>My Job Applications</h1>

    <?php if (empty($applications)): ?>
        <div class="alert alert-info">
            You haven't applied for any jobs yet. 
            <a href="index.php?page=job-offer/index">Browse available jobs</a>
        </div>
    <?php else: ?>
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>Job Title</th>
                        <th>Company</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Applied On</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($applications as $app): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($app['title']); ?></td>
                            <td><?php echo htmlspecialchars($app['company_name']); ?></td>
                            <td><?php echo htmlspecialchars($app['location']); ?></td>
                            <td>
                                <span class="badge badge-<?php echo ($app['status'] === 'accepted') ? 'success' : (($app['status'] === 'rejected') ? 'danger' : 'warning'); ?>">
                                    <?php echo htmlspecialchars(ucfirst($app['status'])); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($app['created_at'])); ?></td>
                            <td>
                                <button class="btn btn-danger" onclick="deleteApplication(<?php echo $app['id']; ?>)">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<script>
function deleteApplication(id) {
    if (confirm('Are you sure you want to delete this application?')) {
        fetch('index.php?page=application/delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'id=' + id
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Application deleted successfully');
                location.reload();
            } else {
                alert(data.error || 'An error occurred');
            }
        });
    }
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
