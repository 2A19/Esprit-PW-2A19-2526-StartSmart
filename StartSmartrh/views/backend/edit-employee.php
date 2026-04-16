<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container" style="padding: 2rem 0;">
    <h1>Edit Employee: <?php echo htmlspecialchars($employee['full_name']); ?></h1>

    <div style="max-width: 800px;">
        <form id="employeeForm">
            <input type="hidden" name="id" value="<?php echo $employee['id']; ?>">

            <div id="errorContainer"></div>

            <div class="form-row">
                <div class="form-group">
                    <label for="fullName">Full Name *</label>
                    <input type="text" id="fullName" name="fullName" value="<?php echo htmlspecialchars($employee['full_name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($employee['email']); ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="phone">Phone Number *</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($employee['phone']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="position">Position *</label>
                    <input type="text" id="position" name="position" value="<?php echo htmlspecialchars($employee['position']); ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="department">Department *</label>
                    <select id="department" name="department" required>
                        <option value="Engineering" <?php echo ($employee['department'] === 'Engineering') ? 'selected' : ''; ?>>Engineering</option>
                        <option value="Sales" <?php echo ($employee['department'] === 'Sales') ? 'selected' : ''; ?>>Sales</option>
                        <option value="Marketing" <?php echo ($employee['department'] === 'Marketing') ? 'selected' : ''; ?>>Marketing</option>
                        <option value="HR" <?php echo ($employee['department'] === 'HR') ? 'selected' : ''; ?>>HR</option>
                        <option value="Finance" <?php echo ($employee['department'] === 'Finance') ? 'selected' : ''; ?>>Finance</option>
                        <option value="Operations" <?php echo ($employee['department'] === 'Operations') ? 'selected' : ''; ?>>Operations</option>
                        <option value="Other" <?php echo ($employee['department'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="salary">Salary ($) *</label>
                    <input type="number" id="salary" name="salary" value="<?php echo htmlspecialchars($employee['salary']); ?>" required min="0" step="0.01">
                </div>
            </div>

            <div class="form-group">
                <label for="status">Status *</label>
                <select id="status" name="status" required>
                    <option value="active" <?php echo ($employee['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo ($employee['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>

            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">Update Employee</button>
                <a href="index.php?page=employee/index" class="btn btn-outline" style="flex: 1; text-align: center;">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('employeeForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    try {
        const response = await fetch('index.php?page=employee/update', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (response.ok) {
            alert('Employee updated successfully!');
            window.location.href = 'index.php?page=employee/index';
        } else {
            const errorContainer = document.getElementById('errorContainer');
            errorContainer.innerHTML = '<div class="alert alert-error">';
            if (data.errors) {
                data.errors.forEach(error => {
                    errorContainer.innerHTML += '<p>' + error + '</p>';
                });
            } else {
                errorContainer.innerHTML += '<p>' + (data.error || 'An error occurred') + '</p>';
            }
            errorContainer.innerHTML += '</div>';
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while updating the employee');
    }
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
