<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container" style="padding: 2rem 0;">
    <h1>Add New Employee</h1>

    <div style="max-width: 800px;">
        <form id="employeeForm">
            <div id="errorContainer"></div>

            <div class="form-row">
                <div class="form-group">
                    <label for="fullName">Full Name *</label>
                    <input type="text" id="fullName" name="fullName" required>
                </div>

                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="phone">Phone Number *</label>
                    <input type="tel" id="phone" name="phone" required>
                </div>

                <div class="form-group">
                    <label for="position">Position *</label>
                    <input type="text" id="position" name="position" required placeholder="e.g., Software Developer">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="department">Department *</label>
                    <select id="department" name="department" required>
                        <option value="">Select Department</option>
                        <option value="Engineering">Engineering</option>
                        <option value="Sales">Sales</option>
                        <option value="Marketing">Marketing</option>
                        <option value="HR">HR</option>
                        <option value="Finance">Finance</option>
                        <option value="Operations">Operations</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="salary">Salary ($) *</label>
                    <input type="number" id="salary" name="salary" required min="0" step="0.01">
                </div>
            </div>

            <div class="form-group">
                <label for="startDate">Start Date *</label>
                <input type="date" id="startDate" name="startDate" required>
            </div>

            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-success" style="flex: 1;">Add Employee</button>
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
        const response = await fetch('index.php?page=employee/store', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (response.ok) {
            alert('Employee added successfully!');
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
        alert('An error occurred while adding the employee');
    }
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
