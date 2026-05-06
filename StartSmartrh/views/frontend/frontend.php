<?php 
// Merged Frontend Views
// Access via: $page variable
include __DIR__ . '/../layouts/header.php'; 
$page = $currentView ?? 'home';
?>

<?php if ($page === 'home'): ?>
    <!-- HOME PAGE -->
    <div class="container" style="padding: 2rem 0;">
        <h1>Welcome to StartSmart HR</h1>
        <p>Find your next job opportunity with leading startups</p>

        <div class="grid grid-3" style="margin: 3rem 0;">
            <div class="card">
                <div class="card-header">
                    <h3 style="color: white; margin: 0;">1000+</h3>
                </div>
                <div class="card-body">
                    <p><strong>Job Offers</strong></p>
                    <p>Explore opportunities from innovative startups</p>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 style="color: white; margin: 0;">500+</h3>
                </div>
                <div class="card-body">
                    <p><strong>Startups</strong></p>
                    <p>Connect with leading companies</p>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 style="color: white; margin: 0;">Easy Apply</h3>
                </div>
                <div class="card-body">
                    <p><strong>Simple Process</strong></p>
                    <p>Apply in minutes and get noticed</p>
                </div>
            </div>
        </div>

        <div class="text-center">
            <a href="index.php?page=job-offer/index&action=index" class="btn btn-primary">Browse Jobs</a>
        </div>
    </div>

<?php elseif ($page === 'register'): ?>
    <!-- REGISTER PAGE -->
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

<?php elseif ($page === 'login'): ?>
    <!-- LOGIN PAGE -->
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

<?php elseif ($page === 'jobs'): ?>
    <!-- JOBS LISTING PAGE -->
    <div class="container" style="padding: 2rem 0;">
        <h1>Available Job Offers</h1>

        <div style="background-color: white; padding: 2rem; border-radius: 0.5rem; margin: 2rem 0;">
            <form method="GET" action="index.php">
                <input type="hidden" name="page" value="job-offer/index">
                <input type="hidden" name="action" value="index">
                <div class="form-row">
                    <div class="form-group">
                        <input type="text" name="keyword" placeholder="Job title or keyword..." 
                            value="<?php echo htmlspecialchars($keyword ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <input type="text" name="location" placeholder="Location..." 
                            value="<?php echo htmlspecialchars($location ?? ''); ?>">
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

<?php elseif ($page === 'job-detail'): ?>
    <!-- JOB DETAIL PAGE -->
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

<?php elseif ($page === 'apply'): ?>
    <!-- APPLY FOR JOB PAGE -->
    <div class="container" style="padding: 2rem 0;">
        <h1>Apply for Job: <?php echo htmlspecialchars($jobOffer['title']); ?></h1>
        
        <div style="max-width: 700px;">
            <div class="card" style="margin-bottom: 2rem;">
                <div class="card-body">
                    <h4>Job Information</h4>
                    <p><strong>Position:</strong> <?php echo htmlspecialchars($jobOffer['title']); ?></p>
                    <p><strong>Company:</strong> <?php echo htmlspecialchars($jobOffer['company_name']); ?></p>
                    <p><strong>Location:</strong> <?php echo htmlspecialchars($jobOffer['location']); ?></p>
                </div>
            </div>

            <form id="applicationForm" method="POST" action="index.php?page=application/store" enctype="multipart/form-data" novalidate>
                <input type="hidden" name="jobOfferId" value="<?php echo $jobOffer['id']; ?>">

                <div id="errorContainer"></div>

                <div class="form-group">
                    <label for="fullName">Full Name *</label>
                    <input type="text" id="fullName" name="fullName" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number *</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="position">Desired Position</label>
                    <input type="text" id="position" name="position" placeholder="e.g., Software Developer" value="<?php echo htmlspecialchars($user['profession'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="experience">Years of Experience *</label>
                    <input type="text" id="experience" name="experience" placeholder="e.g., 5 years" value="<?php echo htmlspecialchars($user['experience'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="coverLetter">Cover Letter *</label>
                    <textarea id="coverLetter" name="coverLetter" placeholder="Tell us why you're a great fit for this position..."></textarea>
                </div>

                <div class="form-group">
                    <label for="resume">Resume (PDF, DOC, or DOCX)</label>
                    <input type="file" id="resume" name="resume" accept=".pdf,.doc,.docx">
                </div>

                <button type="button" id="suggestJobsButton" class="btn btn-secondary" style="width: 100%; padding: 1rem; margin-bottom: 1rem;">Voir métiers suggérés</button>
                <div id="jobSuggestionsContainer" style="margin-bottom: 1rem;"></div>
                <button type="submit" class="btn btn-success" style="width: 100%; padding: 1rem;">Submit Application</button>
                <a href="index.php?page=job-offer/view&id=<?php echo $jobOffer['id']; ?>&action=view" class="btn btn-outline" style="width: 100%; padding: 1rem; margin-top: 1rem; text-align: center;">Cancel</a>
            </form>
        </div>
    </div>

    <script>
    function validateApplicationForm() {
        const errors = [];
        const fullName = document.getElementById('fullName').value.trim();
        const email = document.getElementById('email').value.trim();
        const phone = document.getElementById('phone').value.trim();
        const position = document.getElementById('position').value.trim();
        const experience = document.getElementById('experience').value.trim();
        const coverLetter = document.getElementById('coverLetter').value.trim();
        const resume = document.getElementById('resume').files.length;

        if (!fullName) {
            errors.push('Veuillez entrer votre nom complet.');
        }
        if (!email) {
            errors.push('Veuillez entrer votre adresse e-mail.');
        } else if (!/^\S+@\S+\.\S+$/.test(email)) {
            errors.push('Veuillez entrer une adresse e-mail valide.');
        }
        if (!phone) {
            errors.push('Veuillez entrer votre numéro de téléphone.');
        }
        if (!position) {
            // Position is optional for suggestions
        }
        if (!experience) {
            errors.push('Veuillez indiquer votre expérience.');
        }
        if (!coverLetter) {
            errors.push('Veuillez entrer une lettre de motivation.');
        }
        if (resume === 0) {
            // Resume is optional
        }

        return errors;
    }

    async function suggestJobsForCandidate() {
        const suggestionContainer = document.getElementById('jobSuggestionsContainer');
        const errorContainer = document.getElementById('errorContainer');
        const position = document.getElementById('position').value.trim();
        const experience = document.getElementById('experience').value.trim();
        const coverLetter = document.getElementById('coverLetter').value.trim();

        suggestionContainer.innerHTML = '';
        errorContainer.innerHTML = '';

        if (!position && !experience && !coverLetter) {
            errorContainer.innerHTML = '<div class="alert alert-error"><p>Veuillez renseigner votre poste recherché, expérience ou lettre de motivation pour obtenir des suggestions.</p></div>';
            return;
        }

        try {
            const formData = new FormData();
            formData.append('position', position);
            formData.append('experience', experience);
            formData.append('coverLetter', coverLetter);

            const response = await fetch('index.php?page=application/suggestJobs', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();

            if (!response.ok) {
                errorContainer.innerHTML = '<div class="alert alert-error"><p>' + (data.error || 'Impossible de charger les suggestions.') + '</p></div>';
                return;
            }

            if (!data.jobs.length) {
                suggestionContainer.innerHTML = '<div class="alert alert-info">Aucun métier suggéré pour le moment.</div>';
                return;
            }

            suggestionContainer.innerHTML = '<div class="card"><div class="card-header"><h4>Métiers suggérés pour votre CV</h4></div><div class="card-body">' +
                data.jobs.map(job =>
                    '<div style="margin-bottom: 1rem;"><strong>' + job.title + '</strong><p>' + job.company_name + ' – ' + job.location + '</p><p>' +
                    (job.description ? job.description.substring(0, 120) + '...' : '') + '</p>' +
                    '<a href="index.php?page=job-offer/view&id=' + job.id + '&action=view" class="btn btn-primary">Voir le poste</a></div>'
                ).join('') +
                '</div></div>';
        } catch (error) {
            console.error(error);
            errorContainer.innerHTML = '<div class="alert alert-error"><p>Erreur lors de la récupération des suggestions.</p></div>';
        }
    }

    document.getElementById('suggestJobsButton').addEventListener('click', function() {
        suggestJobsForCandidate();
    });

    document.getElementById('applicationForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const errorContainer = document.getElementById('errorContainer');
        const errors = validateApplicationForm();

        if (errors.length > 0) {
            errorContainer.innerHTML = '<div class="alert alert-error">' + errors.map(error => '<p>' + error + '</p>').join('') + '</div>';
            return;
        }

        const formData = new FormData(this);

        try {
            const response = await fetch('index.php?page=application/store', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (response.ok) {
                alert('Application submitted successfully!');
                window.location.href = 'index.php?page=application/myApplications';
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
            alert('An error occurred while submitting your application');
        }
    });
    </script>

<?php elseif ($page === 'my-applications'): ?>
    <!-- MY APPLICATIONS PAGE -->
    <div class="container" style="padding: 2rem 0;">
        <h1>My Job Applications</h1>

        <?php if (empty($applications)): ?>
            <div class="alert alert-info">
                You haven't applied for any jobs yet. 
                <a href="index.php?page=job-offer/index">Browse available jobs</a>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Job Title</th>
                            <th>Company</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Applied On</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($applications as $app): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($app['title']); ?></td>
                                <td><?php echo htmlspecialchars($app['company_name']); ?></td>
                                <td><?php echo htmlspecialchars($app['location']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo ($app['status'] === 'accepted') ? 'success' : (($app['status'] === 'rejected') ? 'danger' : 'warning'); ?>">
                                        <?php echo htmlspecialchars(ucfirst($app['status'])); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($app['created_at'])); ?></td>
                                <td>
                                    <button class="btn btn-danger" onclick="deleteApplication(<?php echo $app['id']; ?>)">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <script>
    function deleteApplication(id) {
        if (confirm('Are you sure you want to delete this application?')) {
            fetch('index.php?page=application/delete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'id=' + id
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Application deleted successfully');
                    location.reload();
                } else {
                    alert(data.error || 'An error occurred');
                }
            });
        }
    }
    </script>

<?php elseif ($page === 'profile'): ?>
    <!-- PROFILE PAGE -->
    <div class="container" style="padding: 2rem 0;">
        <h1>My Profile</h1>

        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <p><?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <p><?php echo htmlspecialchars($success); ?></p>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h3>Personal Information</h3>
            </div>
            <div class="card-body">
                <button type="button" id="profileSuggestButton" class="btn btn-secondary" style="margin-bottom: 1rem;">Voir métiers suggérés</button>
                <form action="index.php?page=user/profile" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="fullName">Full Name *</label>
                        <input type="text" id="fullName" name="fullName" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" readonly>
                        <small style="color: #666;">Email cannot be changed</small>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="profession">Profession</label>
                        <input type="text" id="profession" name="profession" value="<?php echo htmlspecialchars($user['profession'] ?? ''); ?>" placeholder="e.g. Software Developer">
                    </div>

                    <div class="form-group">
                        <label for="experience">Experience</label>
                        <input type="text" id="experience" name="experience" value="<?php echo htmlspecialchars($user['experience'] ?? ''); ?>" placeholder="e.g. 5 years">
                    </div>

                    <div class="form-group">
                        <label for="resume">Resume/CV (PDF, DOC, or DOCX)</label>
                        <input type="file" id="resume" name="resume" accept=".pdf,.doc,.docx">
                        <?php if (!empty($user['resume'])): ?>
                            <small>Current file: <a href="<?php echo htmlspecialchars($user['resume']); ?>" target="_blank">View Resume</a></small>
                        <?php endif; ?>
                    </div>

                    <div id="profileErrorContainer"></div>
                    <button type="submit" class="btn btn-success">Update Profile</button>
                    <a href="index.php?page=frontend/home" class="btn btn-outline">Back to Home</a>
                </form>
                <div id="profileSuggestionsContainer" style="margin-top: 1.5rem;"></div>
            </div>
        </div>
    </div>

    <script>
        const profileForm = document.querySelector('form[action="index.php?page=user/profile"]');
        if (profileForm) {
            profileForm.addEventListener('submit', function(e) {
                const fullName = document.getElementById('fullName').value.trim();
                const profession = document.getElementById('profession').value.trim();
                const experience = document.getElementById('experience').value.trim();
                const errorContainer = document.getElementById('profileErrorContainer');
                const errors = [];

                errorContainer.innerHTML = '';

                if (!fullName) {
                    errors.push('Veuillez entrer votre nom complet.');
                }
                if (profession && profession.length < 2) {
                    errors.push('Veuillez entrer un métier valide.');
                }
                if (experience && experience.length < 2) {
                    errors.push('Veuillez entrer une expérience valide.');
                }

                if (errors.length > 0) {
                    e.preventDefault();
                    errorContainer.innerHTML = '<div class="alert alert-error">' + errors.map(error => '<p>' + error + '</p>').join('') + '</div>';
                }
            });
        }

        async function suggestJobsFromProfile() {
            console.log('suggestJobsFromProfile called');
            const suggestionContainer = document.getElementById('profileSuggestionsContainer');
            const errorContainer = document.getElementById('profileErrorContainer');
            const profession = document.getElementById('profession').value.trim();
            const experience = document.getElementById('experience').value.trim();
            
            console.log('Profession:', profession);
            console.log('Experience:', experience);

            suggestionContainer.innerHTML = '';
            errorContainer.innerHTML = '';

            if (!profession && !experience) {
                errorContainer.innerHTML = '<div class="alert alert-error"><p>Veuillez entrer votre profession ou votre expérience pour obtenir des suggestions.</p></div>';
                return;
            }

            try {
                const formData = new FormData();
                formData.append('position', profession);
                formData.append('experience', experience);
                formData.append('coverLetter', '');

                const response = await fetch('index.php?page=application/suggestJobs', {
                    method: 'POST',
                    credentials: 'include',
                    body: formData
                });
                
                console.log('Response status:', response.status);
                console.log('Response ok:', response.ok);
                
                const responseText = await response.text();
                console.log('Response text:', responseText);
                
                let data;
                try {
                    data = JSON.parse(responseText);
                } catch(e) {
                    console.error('JSON parse error:', e);
                    errorContainer.innerHTML = '<div class="alert alert-error"><p>Invalid response from server: ' + responseText.substring(0, 100) + '</p></div>';
                    return;
                }
                
                console.log('Response data:', data);

                if (!response.ok) {
                    console.error('Error response:', data);
                    errorContainer.innerHTML = '<div class="alert alert-error"><p>' + (data.error || 'Impossible de charger les suggestions.') + '</p></div>';
                    return;
                }

                if (!data.jobs.length) {
                    suggestionContainer.innerHTML = '<div class="alert alert-info">Aucune suggestion de métier trouvée pour le moment.</div>';
                    return;
                }

                suggestionContainer.innerHTML = '<div class="card"><div class="card-header"><h4>Métiers suggérés</h4></div><div class="card-body">' +
                    data.jobs.map(job =>
                        '<div style="margin-bottom: 1rem;"><strong>' + job.title + '</strong><p>' + job.company_name + ' – ' + job.location + '</p><p>' +
                        (job.description ? job.description.substring(0, 120) + '...' : '') + '</p>' +
                        '<a href="index.php?page=job-offer/view&id=' + job.id + '&action=view" class="btn btn-primary">Voir le poste</a></div>'
                    ).join('') +
                    '</div></div>';
                suggestionContainer.scrollIntoView({ behavior: 'smooth' });
            } catch (error) {
                console.error('Fetch error:', error);
                console.error('Error message:', error.message);
                errorContainer.innerHTML = '<div class="alert alert-error"><p>Erreur lors de la récupération des suggestions: ' + error.message + '</p></div>';
            }
        }

        const profileSuggestButton = document.getElementById('profileSuggestButton');
        console.log('profileSuggestButton element:', profileSuggestButton);
        if (profileSuggestButton) {
            console.log('Button found, adding listener');
            profileSuggestButton.addEventListener('click', suggestJobsFromProfile);
        } else {
            console.error('profileSuggestButton NOT found');
        }
    </script>

<?php endif; ?>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
