<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container" style="padding: 4rem 0;">
    <div style="max-width: 400px; margin: 0 auto;">
        <h2 class="text-center">User Login</h2>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <?php if (isset($errors)): ?>
            <?php foreach ($errors as $error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endforeach; ?>
        <?php endif; ?>

        <form method="POST" action="index.php?page=auth/login&action=login">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required 
                    value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
        </form>

        <p class="text-center" style="margin-top: 1.5rem;">
            Don't have an account? 
            <a href="index.php?page=auth/register">Register here</a>
        </p>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
