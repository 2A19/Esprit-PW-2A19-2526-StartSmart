<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container" style="padding: 2rem 0;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>My Job Offers</h1>
        <a href="index.php?page=job-offer/create&action=create" class="btn btn-success">Post New Job</a>
    </div>

    <?php if (empty($jobOffers)): ?>
        <div class="alert alert-info">
            You haven't posted any job offers yet. 
            <a href="index.php?page=job-offer/create">Post a new job offer</a>
        </div>
    <?php else: ?>
        <div class="grid grid-2" style="margin: 2rem 0;">
            <?php foreach ($jobOffers as $job): ?>
                <div class="card">
                    <div class="card-header">
                        <h3 style="color: white; margin: 0;"><?php echo htmlspecialchars($job['title']); ?></h3>
                    </div>
                    <div class="card-body">
                        <p>
                            <strong>Status:</strong> 
                            <span class="badge badge-<?php echo ($job['status'] === 'active') ? 'success' : 'warning'; ?>">
                                <?php echo htmlspecialchars(ucfirst($job['status'])); ?>
                            </span>
                        </p>
                        <p>
                            <strong>Job Type:</strong> <?php echo htmlspecialchars($job['type']); ?>
                        </p>
                        <p>
                            <strong>Location:</strong> <?php echo htmlspecialchars($job['location']); ?>
                        </p>
                        <p>
                            <strong>Salary:</strong> $<?php echo number_format($job['salary_min']); ?> - $<?php echo number_format($job['salary_max']); ?>
                        </p>
                        <p>
                            <strong>Posted:</strong> <?php echo date('M d, Y', strtotime($job['created_at'])); ?>
                        </p>
                    </div>
                    <div class="card-footer">
                        <a href="index.php?page=application/jobApplications&id=<?php echo $job['id']; ?>&action=jobApplications" class="btn btn-secondary">View Applications</a>
                        <div style="margin-top: 0.5rem;">
                            <a href="index.php?page=job-offer/edit&id=<?php echo $job['id']; ?>&action=edit" class="btn btn-primary">Edit</a>
                            <button class="btn btn-danger" onclick="deleteOffer(<?php echo $job['id']; ?>)">Delete</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function deleteOffer(id) {
    if (confirm('Are you sure you want to delete this job offer?')) {
        fetch('index.php?page=job-offer/delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'id=' + id
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Job offer deleted successfully');
                location.reload();
            } else {
                alert(data.error || 'An error occurred');
            }
        });
    }
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
