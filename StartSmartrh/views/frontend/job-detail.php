<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container" style="padding: 2rem 0;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1><?php echo htmlspecialchars($jobOffer['title']); ?></h1>
        <a href="index.php?page=job-offer/index&action=index" class="btn btn-outline">Back to Jobs</a>
    </div>

    <div class="grid grid-2" style="margin-bottom: 2rem;">
        <div>
            <div class="card">
                <div class="card-body">
                    <h3>Job Details</h3>
                    <p>
                        <strong>Company:</strong> <?php echo htmlspecialchars($jobOffer['company_name']); ?>
                    </p>
                    <p>
                        <strong>Job Type:</strong> <span class="badge badge-primary"><?php echo htmlspecialchars($jobOffer['type']); ?></span>
                    </p>
                    <p>
                        <strong>Location:</strong> <?php echo htmlspecialchars($jobOffer['location']); ?>
                    </p>
                    <p>
                        <strong>Posted:</strong> <?php echo date('M d, Y', strtotime($jobOffer['created_at'])); ?>
                    </p>
                    <p>
                        <strong>Status:</strong> <span class="badge badge-success"><?php echo htmlspecialchars($jobOffer['status']); ?></span>
                    </p>
                </div>
            </div>
        </div>

        <div>
            <div class="card">
                <div class="card-body">
                    <h3>Salary Range</h3>
                    <p style="font-size: 1.5rem; color: #22c55e; font-weight: bold;">
                        $<?php echo number_format($jobOffer['salary_min']); ?> - $<?php echo number_format($jobOffer['salary_max']); ?>
                    </p>
                    <hr>
                    <h4>Contact</h4>
                    <p>
                        <strong>Phone:</strong> <?php echo htmlspecialchars($jobOffer['phone']); ?>
                    </p>
                    <p>
                        <strong>Email:</strong> <?php echo htmlspecialchars($jobOffer['email']); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="card" style="margin-bottom: 2rem;">
        <div class="card-body">
            <h3>Description</h3>
            <p><?php echo nl2br(htmlspecialchars($jobOffer['description'])); ?></p>
        </div>
    </div>

    <div class="card" style="margin-bottom: 2rem;">
        <div class="card-body">
            <h3>Requirements</h3>
            <p><?php echo nl2br(htmlspecialchars($jobOffer['requirements'])); ?></p>
        </div>
    </div>

    <div class="text-center">
        <a href="index.php?page=application/apply&id=<?php echo $jobOffer['id']; ?>&action=apply" class="btn btn-success btn-large" style="padding: 1rem 2rem; font-size: 1.1rem;">Apply for This Job</a>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
