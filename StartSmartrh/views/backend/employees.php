<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container" style="padding: 2rem 0;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>Employees</h1>
        <a href="index.php?page=employee/create&action=create" class="btn btn-success">Add Employee</a>
    </div>

    <div style="background-color: white; padding: 2rem; border-radius: 0.5rem; margin: 2rem 0;">
        <form method="GET" action="index.php?page=employee/index&action=index">
            <div class="form-row">
                <div class="form-group">
                    <input type="text" name="keyword" placeholder="Search by name, email, or position..." 
                        value="<?php echo htmlspecialchars($keyword); ?>">
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
    </div>

    <div class="grid grid-3" style="margin: 2rem 0;">
        <div class="card">
            <div class="card-body">
                <h4>Total Employees</h4>
                <h2 style="color: #0891b2; font-size: 2rem;"><?php echo $totalCount; ?></h2>
            </div>
        </div>
        <?php foreach ($departments as $dept): ?>
            <div class="card">
                <div class="card-body">
                    <h4><?php echo htmlspecialchars($dept['department']); ?></h4>
                    <h2 style="color: #22c55e; font-size: 2rem;"><?php echo $dept['count']; ?></h2>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (empty($employees)): ?>
        <div class="alert alert-info">
            No employees found. 
            <a href="index.php?page=employee/create&action=create">Add your first employee</a>
        </div>
    <?php else: ?>
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Position</th>
                        <th>Department</th>
                        <th>Salary</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($employees as $emp): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($emp['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($emp['email']); ?></td>
                            <td><?php echo htmlspecialchars($emp['position']); ?></td>
                            <td><?php echo htmlspecialchars($emp['department']); ?></td>
                            <td>$<?php echo number_format($emp['salary']); ?></td>
                            <td>
                                <span class="badge badge-<?php echo ($emp['status'] === 'active') ? 'success' : 'warning'; ?>">
                                    <?php echo htmlspecialchars(ucfirst($emp['status'])); ?>
                                </span>
                            </td>
                            <td>
                                <a href="index.php?page=employee/view&id=<?php echo $emp['id']; ?>&action=view" class="btn btn-secondary">View</a>
                                <a href="index.php?page=employee/edit&id=<?php echo $emp['id']; ?>&action=edit" class="btn btn-primary">Edit</a>
                                <button class="btn btn-danger" onclick="deleteEmployee(<?php echo $emp['id']; ?>)">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
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
                location.reload();
            } else {
                alert(data.error || 'An error occurred');
            }
        });
    }
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
