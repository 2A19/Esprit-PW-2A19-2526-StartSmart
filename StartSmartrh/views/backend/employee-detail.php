<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container" style="padding: 2rem 0;">
    <a href="index.php?page=employee/index" class="btn btn-outline" style="margin-bottom: 2rem;">Back to Employees</a>

    <div class="grid grid-2">
        <div class="card">
            <div class="card-header">
                <h2 style="color: white; margin: 0;"><?php echo htmlspecialchars($employee['full_name']); ?></h2>
            </div>
            <div class="card-body">
                <p><strong>Position:</strong> <?php echo htmlspecialchars($employee['position']); ?></p>
                <p><strong>Department:</strong> <?php echo htmlspecialchars($employee['department']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($employee['email']); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($employee['phone']); ?></p>
                <p>
                    <strong>Status:</strong> 
                    <span class="badge badge-<?php echo ($employee['status'] === 'active') ? 'success' : 'warning'; ?>">
                        <?php echo htmlspecialchars(ucfirst($employee['status'])); ?>
                    </span>
                </p>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 style="color: white; margin: 0;">Employment Details</h3>
            </div>
            <div class="card-body">
                <p><strong>Salary:</strong> $<?php echo number_format($employee['salary']); ?></p>
                <p><strong>Start Date:</strong> <?php echo date('M d, Y', strtotime($employee['start_date'])); ?></p>
                <p><strong>Company:</strong> <?php echo htmlspecialchars($employee['company_name']); ?></p>
                <?php if ($employee['job_offer_id']): ?>
                    <p><strong>Hired For:</strong> <?php echo htmlspecialchars($employee['title']); ?></p>
                <?php endif; ?>
                <p><strong>Joined:</strong> <?php echo date('M d, Y', strtotime($employee['created_at'])); ?></p>
            </div>
            <div class="card-footer">
                <a href="index.php?page=employee/edit&id=<?php echo $employee['id']; ?>&action=edit" class="btn btn-primary">Edit</a>
                <button class="btn btn-danger" onclick="deleteEmployee(<?php echo $employee['id']; ?>)">Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
function deleteEmployee(id) {
    if (confirm('Are you sure you want to delete this employee?')) {
        fetch('index.php?page=employee/delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'id=' + id
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Employee deleted successfully');
                window.location.href = 'index.php?page=employee/index';
            } else {
                alert(data.error || 'An error occurred');
            }
        });
    }
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
