<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container" style="padding: 2rem 0;">
    <h1>Dashboard</h1>
    
    <div class="grid grid-3" style="margin: 2rem 0;">
        <div class="card">
            <div class="card-header">
                <h3 style="color: white; margin: 0;">Active Offers</h3>
            </div>
            <div class="card-body">
                <h2 style="font-size: 2.5rem; color: #0891b2;"><?php echo count($jobOffers); ?></h2>
                <p>Total job offers posted</p>
                <a href="index.php?page=job-offer/myOffers&action=myOffers" class="btn btn-secondary">View All</a>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 style="color: white; margin: 0;">Total Applications</h3>
            </div>
            <div class="card-body">
                <h2 style="font-size: 2.5rem; color: #22c55e;"><?php echo $totalApplications; ?></h2>
                <p>Received from candidates</p>
                <a href="index.php?page=job-offer/myOffers&action=myOffers" class="btn btn-success">View Applications</a>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 style="color: white; margin: 0;">Employees</h3>
            </div>
            <div class="card-body">
                <h2 style="font-size: 2.5rem; color: #1e3a8a;"><?php echo $totalEmployees; ?></h2>
                <p>Current workforce</p>
                <a href="index.php?page=employee/index" class="btn btn-primary">Manage</a>
            </div>
        </div>
    </div>

    <div class="grid grid-2" style="margin: 2rem 0;">
        <div class="card">
            <div class="card-header">
                <h3 style="color: white; margin: 0;">Quick Actions</h3>
            </div>
            <div class="card-body">
                <ul style="list-style: none; padding: 0;">
                    <li style="margin-bottom: 0.5rem;">
                        <a href="index.php?page=job-offer/create&action=create" class="btn btn-primary">Post New Job</a>
                    </li>
                    <li style="margin-bottom: 0.5rem;">
                        <a href="index.php?page=employee/create&action=create" class="btn btn-success">Add Employee</a>
                    </li>
                    <li>
                        <a href="index.php?page=job-offer/myOffers&action=myOffers" class="btn btn-secondary">View All Offers</a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 style="color: white; margin: 0;">Recent Activity</h3>
            </div>
            <div class="card-body">
                <?php if (!empty($recentApplications)): ?>
                    <p><strong>Latest Applications:</strong></p>
                    <ul style="list-style: none; padding: 0;">
                        <?php foreach (array_slice($recentApplications, 0, 5) as $app): ?>
                            <li style="margin-bottom: 0.5rem;">
                                - <?php echo htmlspecialchars($app['full_name']); ?> applied for 
                                <strong><?php echo htmlspecialchars($app['title']); ?></strong>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>No recent applications.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
