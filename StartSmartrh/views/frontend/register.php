<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container" style="padding: 4rem 0;">
    <div style="max-width: 600px; margin: 0 auto;">
        <h2 class="text-center">Create Account</h2>
        
        <?php if (isset($errors)): ?>
            <?php foreach ($errors as $error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endforeach; ?>
        <?php endif; ?>

        <form method="POST" action="index.php?page=auth/register&action=register">
            <div class="form-row">
                <div class="form-group">
                    <label for="fullName">Full Name *</label>
                    <input type="text" id="fullName" name="fullName" required 
                        value="<?php echo htmlspecialchars($form_data['fullName'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="userRole">User Type *</label>
                    <select id="userRole" name="userRole" required onchange="toggleCompanyField()">
                        <option value="">Select User Type</option>
                        <option value="job_seeker" <?php echo (isset($form_data['userRole']) && $form_data['userRole'] === 'job_seeker') ? 'selected' : ''; ?>>Job Seeker</option>
                        <option value="startup" <?php echo (isset($form_data['userRole']) && $form_data['userRole'] === 'startup') ? 'selected' : ''; ?>>Startup/Company</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" required 
                    value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="phone">Phone Number *</label>
                <input type="tel" id="phone" name="phone" required 
                    value="<?php echo htmlspecialchars($form_data['phone'] ?? ''); ?>">
            </div>

            <div class="form-group" id="companyField" style="display: none;">
                <label for="companyName">Company Name</label>
                <input type="text" id="companyName" name="companyName" 
                    value="<?php echo htmlspecialchars($form_data['companyName'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="password">Password *</label>
                <input type="password" id="password" name="password" required>
                <small style="color: #666;">Min 8 chars, must include uppercase, lowercase, and number</small>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">Create Account</button>
        </form>

        <p class="text-center" style="margin-top: 1.5rem;">
            Already have an account? 
            <a href="index.php?page=auth/login">Login here</a>
        </p>
    </div>
</div>

<script>
function toggleCompanyField() {
    const userType = document.getElementById('userRole').value;
    const companyField = document.getElementById('companyField');
    companyField.style.display = userType === 'startup' ? 'block' : 'none';
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
