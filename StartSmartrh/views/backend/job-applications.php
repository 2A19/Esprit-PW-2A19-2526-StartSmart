<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container" style="padding: 2rem 0;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>Applications for: <?php echo htmlspecialchars($jobOffer['title']); ?></h1>
        <a href="index.php?page=job-offer/myOffers" class="btn btn-outline">Back to Job Offers</a>
    </div>

    <?php if (empty($applications)): ?>
        <div class="alert alert-info">
            No applications received yet for this job offer.
        </div>
    <?php else: ?>
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>Candidate Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Experience</th>
                        <th>Status</th>
                        <th>Applied On</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($applications as $app): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($app['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($app['email']); ?></td>
                            <td><?php echo htmlspecialchars($app['phone']); ?></td>
                            <td><?php echo htmlspecialchars($app['experience']); ?></td>
                            <td>
                                <select onchange="updateStatus(<?php echo $app['id']; ?>, this.value)" style="padding: 0.5rem;">
                                    <option value="pending" <?php echo ($app['status'] === 'pending') ? 'selected' : ''; ?>>Pending</option>
                                    <option value="accepted" <?php echo ($app['status'] === 'accepted') ? 'selected' : ''; ?>>Accepted</option>
                                    <option value="rejected" <?php echo ($app['status'] === 'rejected') ? 'selected' : ''; ?>>Rejected</option>
                                </select>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($app['created_at'])); ?></td>
                            <td>
                                <button class="btn btn-primary" onclick="viewApplication(<?php echo $app['id']; ?>)">View</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<script>
function updateStatus(id, status) {
    fetch('index.php?page=application/updateStatus', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'id=' + id + '&status=' + status
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Status updated successfully');
        } else {
            alert(data.error || 'An error occurred');
        }
    });
}

function viewApplication(id) {
    alert('Full application view coming soon!');
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
