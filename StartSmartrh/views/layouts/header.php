<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StartSmart HR - Human Resources Management</title>
    <link rel="stylesheet" href="/StartSmartrh/public/css/styles.css">
</head>
<body>
    <nav class="navbar">
        <div class="navbar-container">
            <div class="navbar-brand">Start<span>Smart</span> HR</div>
            <ul class="nav-menu">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($_SESSION['user_role'] === 'startup'): ?>
                        <li><a href="index.php?page=backend/dashboard" class="nav-link">Dashboard</a></li>
                        <li><a href="index.php?page=job-offer/myOffers&action=myOffers" class="nav-link">My Job Offers</a></li>
                        <li><a href="index.php?page=employee/index" class="nav-link">Employees</a></li>
                    <?php else: ?>
                        <li><a href="index.php?page=frontend/home" class="nav-link">Home</a></li>
                        <li><a href="index.php?page=job-offer/index&action=index" class="nav-link">Job Offers</a></li>
                        <li><a href="index.php?page=application/myApplications&action=myApplications" class="nav-link">My Applications</a></li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>
            <div class="nav-user">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    <a href="index.php?page=auth/logout" class="nav-link">Logout</a>
                <?php else: ?>
                    <a href="index.php?page=auth/login" class="nav-link">Login</a>
                    <a href="index.php?page=auth/register" class="nav-link">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
