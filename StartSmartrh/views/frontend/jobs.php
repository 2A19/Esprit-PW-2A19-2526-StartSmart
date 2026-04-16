<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container" style="padding: 2rem 0;">
    <h1>Available Job Offers</h1>

    <div style="background-color: white; padding: 2rem; border-radius: 0.5rem; margin: 2rem 0;">
        <form method="GET" action="index.php?page=job-offer/index&action=index">
            <div class="form-row">
                <div class="form-group">
                    <input type="text" name="keyword" placeholder="Job title or keyword..." 
                        value="<?php echo htmlspecialchars($keyword); ?>">
                </div>
                <div class="form-group">
                    <input type="text" name="location" placeholder="Location..." 
                        value="<?php echo htmlspecialchars($location); ?>">
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
    </div>

    <?php if (empty($jobOffers)): ?>
        <div class="alert alert-info">No job offers found. Try different search criteria.</div>
    <?php else: ?>
        <div class="grid grid-2">
            <?php foreach ($jobOffers as $job): ?>
                <div class="card">
                    <div class="card-header">
                        <h3 style="color: white; margin: 0;"><?php echo htmlspecialchars($job['title']); ?></h3>
                    </div>
                    <div class="card-body">
                        <p><strong><?php echo htmlspecialchars($job['company_name']); ?></strong></p>
                        <p><span class="badge badge-primary"><?php echo htmlspecialchars($job['type']); ?></span></p>
                        <p><?php echo htmlspecialchars(substr($job['description'], 0, 100) . '...'); ?></p>
                        <p>
                            <strong>Salary:</strong> $<?php echo number_format($job['salary_min']); ?> - $<?php echo number_format($job['salary_max']); ?>
                        </p>
                        <p>
                            <strong>Location:</strong> <?php echo htmlspecialchars($job['location']); ?>
                        </p>
                    </div>
                    <div class="card-footer">
                        <a href="index.php?page=job-offer/view&id=<?php echo $job['id']; ?>&action=view" class="btn btn-primary">View Details</a>
                        <a href="index.php?page=application/apply&id=<?php echo $job['id']; ?>&action=apply" class="btn btn-success">Apply Now</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
