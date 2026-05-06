<?php 
// Merged Backend Views
// Access via: $currentView variable
include __DIR__ . '/../layouts/header.php'; 
$page = $currentView ?? 'dashboard';
?>

<?php if ($page === 'dashboard'): ?>
    <!-- DASHBOARD PAGE -->
    <div class="container" style="padding: 2rem 0;">
        <h1>Dashboard</h1>
        
        <div class="grid grid-3" style="margin: 2rem 0;">
            <div class="card">
                <div class="card-header">
                    <h3 style="color: white; margin: 0;">Active Offers</h3>
                </div>
                <div class="card-body">
                    <h2 style="font-size: 2.5rem; color: #0891b2;"><?php echo count($jobOffers ?? []); ?></h2>
                    <p>Total job offers posted</p>
                    <a href="index.php?page=job-offer/myOffers&action=myOffers" class="btn btn-secondary">View All</a>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 style="color: white; margin: 0;">Total Applications</h3>
                </div>
                <div class="card-body">
                    <h2 style="font-size: 2.5rem; color: #22c55e;"><?php echo $totalApplications ?? 0; ?></h2>
                    <p>Received from candidates</p>
                    <a href="index.php?page=job-offer/myOffers&action=myOffers" class="btn btn-success">View Applications</a>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 style="color: white; margin: 0;">Employees</h3>
                </div>
                <div class="card-body">
                    <h2 style="font-size: 2.5rem; color: #1e3a8a;"><?php echo $totalEmployees ?? 0; ?></h2>
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
                                    - <?php echo htmlspecialchars($app['full_name'] ?? 'Unknown applicant'); ?> applied for 
                                    <strong><?php echo htmlspecialchars($app['title'] ?? $app['job_title'] ?? 'Unknown position'); ?></strong>
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

<?php elseif ($page === 'create-job-offer'): ?>
    <!-- CREATE JOB OFFER PAGE -->
    <div class="container" style="padding: 2rem 0;">
        <h1>Post New Job Offer</h1>

        <div style="max-width: 800px;">
            <form id="jobOfferForm" novalidate>
                <div id="errorContainer"></div>

                <div class="form-group">
                    <label for="title">Job Title *</label>
                    <input type="text" id="title" name="title">
                </div>

                <div class="form-group">
                    <label for="type">Job Type *</label>
                    <select id="type" name="type">
                        <option value="">Select Job Type</option>
                        <option value="Full-time">Full-time</option>
                        <option value="Part-time">Part-time</option>
                        <option value="Contract">Contract</option>
                        <option value="Freelance">Freelance</option>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="salaryMin">Minimum Salary ($) *</label>
                        <input type="number" id="salaryMin" name="salaryMin" min="0" step="0.01">
                    </div>

                    <div class="form-group">
                        <label for="salaryMax">Maximum Salary ($) *</label>
                        <input type="number" id="salaryMax" name="salaryMax" min="0" step="0.01">
                    </div>
                </div>

                <div class="form-group">
                    <label for="location">Location *</label>
                    <input type="text" id="location" name="location" placeholder="e.g., New York, NY or Remote">
                </div>

                <div class="form-group">
                    <label for="description">Job Description *</label>
                    <textarea id="description" name="description" placeholder="Detailed job description..."></textarea>
                </div>

                <div class="form-group">
                    <label for="requirements">Requirements *</label>
                    <textarea id="requirements" name="requirements" placeholder="List the key requirements and qualifications..."></textarea>
                </div>

                <div style="display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-success" style="flex: 1;">Post Job Offer</button>
                    <a href="index.php?page=job-offer/myOffers&action=myOffers" class="btn btn-outline" style="flex: 1; text-align: center;">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script>
    function validateJobOfferForm() {
        const errors = [];
        const title = document.getElementById('title').value.trim();
        const type = document.getElementById('type').value;
        const salaryMin = document.getElementById('salaryMin').value.trim();
        const salaryMax = document.getElementById('salaryMax').value.trim();
        const location = document.getElementById('location').value.trim();
        const description = document.getElementById('description').value.trim();
        const requirements = document.getElementById('requirements').value.trim();

        if (!title) {
            errors.push('Le titre du poste est requis.');
        }
        if (!type) {
            errors.push('Le type d\'emploi est requis.');
        }
        if (salaryMin === '') {
            errors.push('Le salaire minimum est requis.');
        } else if (isNaN(salaryMin)) {
            errors.push('Le salaire minimum doit être un nombre.');
        }
        if (salaryMax === '') {
            errors.push('Le salaire maximum est requis.');
        } else if (isNaN(salaryMax)) {
            errors.push('Le salaire maximum doit être un nombre.');
        }
        if (salaryMin !== '' && salaryMax !== '' && !isNaN(salaryMin) && !isNaN(salaryMax)) {
            if (parseFloat(salaryMin) < 0) {
                errors.push('Le salaire minimum doit être supérieur ou égal à 0.');
            }
            if (parseFloat(salaryMax) < parseFloat(salaryMin)) {
                errors.push('Le salaire maximum doit être supérieur ou égal au salaire minimum.');
            }
        }
        if (!location) {
            errors.push('Le lieu est requis.');
        }
        if (!description) {
            errors.push('La description du poste est requise.');
        }
        if (!requirements) {
            errors.push('Les exigences sont requises.');
        }

        return errors;
    }

    document.getElementById('jobOfferForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const errorContainer = document.getElementById('errorContainer');
        const errors = validateJobOfferForm();

        if (errors.length > 0) {
            errorContainer.innerHTML = '<div class="alert alert-error">' + errors.map(error => '<p>' + error + '</p>').join('') + '</div>';
            return;
        }

        const formData = new FormData(this);

        try {
            const response = await fetch('index.php?page=job-offer/store', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (response.ok) {
                alert('Job offer posted successfully!');
                window.location.href = 'index.php?page=job-offer/myOffers';
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
            alert('An error occurred while posting the job offer');
        }
    });
    </script>

<?php elseif ($page === 'edit-job-offer'): ?>
    <!-- EDIT JOB OFFER PAGE -->
    <div class="container" style="padding: 2rem 0;">
        <h1>Edit Job Offer: <?php echo htmlspecialchars($jobOffer['title']); ?></h1>

        <div style="max-width: 800px;">
            <form id="jobOfferForm" novalidate>
                <input type="hidden" name="id" value="<?php echo $jobOffer['id']; ?>">

                <div id="errorContainer"></div>

                <div class="form-group">
                    <label for="title">Job Title *</label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($jobOffer['title']); ?>">
                </div>

                <div class="form-group">
                    <label for="type">Job Type *</label>
                    <select id="type" name="type">
                        <option value="Full-time" <?php echo ($jobOffer['type'] === 'Full-time') ? 'selected' : ''; ?>>Full-time</option>
                        <option value="Part-time" <?php echo ($jobOffer['type'] === 'Part-time') ? 'selected' : ''; ?>>Part-time</option>
                        <option value="Contract" <?php echo ($jobOffer['type'] === 'Contract') ? 'selected' : ''; ?>>Contract</option>
                        <option value="Freelance" <?php echo ($jobOffer['type'] === 'Freelance') ? 'selected' : ''; ?>>Freelance</option>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="salaryMin">Minimum Salary ($) *</label>
                        <input type="number" id="salaryMin" name="salaryMin" value="<?php echo htmlspecialchars($jobOffer['salary_min']); ?>" min="0" step="0.01">
                    </div>

                    <div class="form-group">
                        <label for="salaryMax">Maximum Salary ($) *</label>
                        <input type="number" id="salaryMax" name="salaryMax" value="<?php echo htmlspecialchars($jobOffer['salary_max']); ?>" min="0" step="0.01">
                    </div>
                </div>

                <div class="form-group">
                    <label for="location">Location *</label>
                    <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($jobOffer['location']); ?>">
                </div>

                <div class="form-group">
                    <label for="description">Job Description *</label>
                    <textarea id="description" name="description"><?php echo htmlspecialchars($jobOffer['description']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="requirements">Requirements *</label>
                    <textarea id="requirements" name="requirements"><?php echo htmlspecialchars($jobOffer['requirements']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="status">Status *</label>
                    <select id="status" name="status">
                        <option value="active" <?php echo ($jobOffer['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo ($jobOffer['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>

                <div style="display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">Update Job Offer</button>
                    <a href="index.php?page=job-offer/myOffers" class="btn btn-outline" style="flex: 1; text-align: center;">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script>
    function validateJobOfferForm() {
        const errors = [];
        const title = document.getElementById('title').value.trim();
        const type = document.getElementById('type').value;
        const salaryMin = document.getElementById('salaryMin').value.trim();
        const salaryMax = document.getElementById('salaryMax').value.trim();
        const location = document.getElementById('location').value.trim();
        const description = document.getElementById('description').value.trim();
        const requirements = document.getElementById('requirements').value.trim();

        if (!title) {
            errors.push('Le titre du poste est requis.');
        }
        if (!type) {
            errors.push('Le type d\'emploi est requis.');
        }
        if (salaryMin === '') {
            errors.push('Le salaire minimum est requis.');
        } else if (isNaN(salaryMin)) {
            errors.push('Le salaire minimum doit être un nombre.');
        }
        if (salaryMax === '') {
            errors.push('Le salaire maximum est requis.');
        } else if (isNaN(salaryMax)) {
            errors.push('Le salaire maximum doit être un nombre.');
        }
        if (salaryMin !== '' && salaryMax !== '' && !isNaN(salaryMin) && !isNaN(salaryMax)) {
            if (parseFloat(salaryMin) < 0) {
                errors.push('Le salaire minimum doit être supérieur ou égal à 0.');
            }
            if (parseFloat(salaryMax) < parseFloat(salaryMin)) {
                errors.push('Le salaire maximum doit être supérieur ou égal au salaire minimum.');
            }
        }
        if (!location) {
            errors.push('Le lieu est requis.');
        }
        if (!description) {
            errors.push('La description du poste est requise.');
        }
        if (!requirements) {
            errors.push('Les exigences sont requises.');
        }

        return errors;
    }

    document.getElementById('jobOfferForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const errorContainer = document.getElementById('errorContainer');
        const errors = validateJobOfferForm();

        if (errors.length > 0) {
            errorContainer.innerHTML = '<div class="alert alert-error">' + errors.map(error => '<p>' + error + '</p>').join('') + '</div>';
            return;
        }

        const formData = new FormData(this);

        try {
            const response = await fetch('index.php?page=job-offer/update', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (response.ok) {
                alert('Job offer updated successfully!');
                window.location.href = 'index.php?page=job-offer/myOffers';
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
            alert('An error occurred while updating the job offer');
        }
    });
    </script>

<?php elseif ($page === 'job-offers'): ?>
    <!-- JOB OFFERS LISTING PAGE -->
    <div class="container" style="padding: 2rem 0;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1>My Job Offers</h1>
            <a href="index.php?page=job-offer/create&action=create" class="btn btn-success">Post New Job</a>
        </div>

        <?php if (!empty($employeeSuggestions) && !empty($suggestedJob)): ?>
            <div class="card" style="margin-bottom: 2rem;">
                <div class="card-header">
                    <h3 style="color: white; margin: 0;">Employés suggérés pour "<?php echo htmlspecialchars($suggestedJob['title']); ?>"</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($employeeSuggestions)): ?>
                        <div class="grid grid-2" style="gap: 1rem;">
                            <?php foreach ($employeeSuggestions as $employee): ?>
                                <div class="card" style="margin-bottom: 1rem;">
                                    <div class="card-body">
                                        <h4><?php echo htmlspecialchars($employee['full_name']); ?></h4>
                                        <p><strong>Position:</strong> <?php echo htmlspecialchars($employee['position']); ?></p>
                                        <p><strong>Department:</strong> <?php echo htmlspecialchars($employee['department']); ?></p>
                                        <p><strong>Email:</strong> <?php echo htmlspecialchars($employee['email']); ?></p>
                                        <p><strong>Score:</strong> <?php echo htmlspecialchars($employee['match_score']); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">Aucun employé suggéré pour le moment.</div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (empty($jobOffers)): ?>
            <div class="alert alert-info">
                You haven't posted any job offers yet. 
                <a href="index.php?page=job-offer/create">Post a new job offer</a>
            </div>
        <?php else: ?>
            <div class="grid grid-2" style="margin: 2rem 0;">
                <?php foreach ($jobOffers as $job): ?>
                    <div class="card">
                        <div class="card-header">
                            <h3 style="color: white; margin: 0;"><?php echo htmlspecialchars($job['title']); ?></h3>
                        </div>
                        <div class="card-body">
                            <p>
                                <strong>Status:</strong> 
                                <span class="badge badge-<?php echo ($job['status'] === 'active') ? 'success' : 'warning'; ?>">
                                    <?php echo htmlspecialchars(ucfirst($job['status'])); ?>
                                </span>
                            </p>
                            <p>
                                <strong>Job Type:</strong> <?php echo htmlspecialchars($job['type']); ?>
                            </p>
                            <p>
                                <strong>Location:</strong> <?php echo htmlspecialchars($job['location']); ?>
                            </p>
                            <p>
                                <strong>Salary:</strong> $<?php echo number_format($job['salary_min']); ?> - $<?php echo number_format($job['salary_max']); ?>
                            </p>
                            <p>
                                <strong>Posted:</strong> <?php echo date('M d, Y', strtotime($job['created_at'])); ?>
                            </p>
                        </div>
                        <div class="card-footer">
                            <a href="index.php?page=application/jobApplications&id=<?php echo $job['id']; ?>&action=jobApplications" class="btn btn-secondary">View Applications</a>
                            <div style="margin-top: 0.5rem;">
                                <a href="index.php?page=job-offer/edit&id=<?php echo $job['id']; ?>&action=edit" class="btn btn-primary">Edit</a>
                                <button class="btn btn-danger" onclick="deleteOffer(<?php echo $job['id']; ?>)">Delete</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
    function deleteOffer(id) {
        if (confirm('Are you sure you want to delete this job offer?')) {
            fetch('index.php?page=job-offer/delete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'id=' + id
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Job offer deleted successfully');
                    location.reload();
                } else {
                    alert(data.error || 'An error occurred');
                }
            });
        }
    }
    </script>

<?php elseif ($page === 'job-applications'): ?>
    <!-- JOB APPLICATIONS PAGE -->
    <div class="container" style="padding: 2rem 0;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1>Applications for: <?php echo htmlspecialchars($jobOffer['title']); ?></h1>
            <a href="index.php?page=job-offer/myOffers" class="btn btn-outline">Back to Job Offers</a>
        </div>

        <?php if (empty($applications)): ?>
            <div class="alert alert-info">
                No applications received yet for this job offer.
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Candidate Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Experience</th>
                            <th>Status</th>
                            <th>Applied On</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($applications as $app): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($app['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($app['email']); ?></td>
                                <td><?php echo htmlspecialchars($app['phone']); ?></td>
                                <td><?php echo htmlspecialchars($app['experience']); ?></td>
                                <td>
                                    <select onchange="updateStatus(<?php echo $app['id']; ?>, this.value)" style="padding: 0.5rem;">
                                        <option value="pending" <?php echo ($app['status'] === 'pending') ? 'selected' : ''; ?>>Pending</option>
                                        <option value="accepted" <?php echo ($app['status'] === 'accepted') ? 'selected' : ''; ?>>Accepted</option>
                                        <option value="rejected" <?php echo ($app['status'] === 'rejected') ? 'selected' : ''; ?>>Rejected</option>
                                    </select>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($app['created_at'])); ?></td>
                                <td>
                                    <button class="btn btn-primary" onclick="viewApplication(<?php echo $app['id']; ?>)">View</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <script>
    function updateStatus(id, status) {
        fetch('index.php?page=application/updateStatusApi', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'id=' + id + '&status=' + encodeURIComponent(status)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update badge on the row without reloading
                const select = event ? event.target : null;
                if (select) {
                    const row = select.closest('tr');
                    // Visual feedback
                    select.style.borderColor = status === 'accepted' ? '#22c55e' : status === 'rejected' ? '#ef4444' : '#94a3b8';
                }
            } else {
                alert(data.error || 'An error occurred');
            }
        })
        .catch(() => alert('Network error. Please try again.'));
    }

    function viewApplication(id) {
        window.location.href = 'index.php?page=application/viewApplication&id=' + id;
    }
    </script>

<?php elseif ($page === 'view-application'): ?>
    <!-- VIEW APPLICATION PAGE -->
    <div class="container" style="padding: 2rem 0; max-width: 860px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1>Application Detail</h1>
            <a href="javascript:history.back()" class="btn btn-outline">← Back</a>
        </div>

        <div style="background: var(--card-bg, #fff); border: 1px solid var(--border-color, #e2e8f0); border-radius: 12px; padding: 2rem; margin-bottom: 1.5rem;">
            <h2 style="margin-bottom: 0.25rem;"><?php echo htmlspecialchars($application['full_name']); ?></h2>
            <p style="color: #64748b; margin-bottom: 1.5rem;">Applied for: <strong><?php echo htmlspecialchars($application['title']); ?></strong> at <?php echo htmlspecialchars($application['company_name']); ?></p>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                <div>
                    <label style="font-size: 0.75rem; text-transform: uppercase; color: #94a3b8; font-weight: 600;">Email</label>
                    <p style="margin: 0.25rem 0 0;"><?php echo htmlspecialchars($application['email']); ?></p>
                </div>
                <div>
                    <label style="font-size: 0.75rem; text-transform: uppercase; color: #94a3b8; font-weight: 600;">Phone</label>
                    <p style="margin: 0.25rem 0 0;"><?php echo htmlspecialchars($application['phone'] ?: '—'); ?></p>
                </div>
                <div>
                    <label style="font-size: 0.75rem; text-transform: uppercase; color: #94a3b8; font-weight: 600;">Location</label>
                    <p style="margin: 0.25rem 0 0;"><?php echo htmlspecialchars($application['location'] ?: '—'); ?></p>
                </div>
                <div>
                    <label style="font-size: 0.75rem; text-transform: uppercase; color: #94a3b8; font-weight: 600;">Applied On</label>
                    <p style="margin: 0.25rem 0 0;"><?php echo date('M d, Y', strtotime($application['created_at'])); ?></p>
                </div>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label style="font-size: 0.75rem; text-transform: uppercase; color: #94a3b8; font-weight: 600;">Experience</label>
                <p style="margin: 0.5rem 0 0; white-space: pre-line;"><?php echo htmlspecialchars($application['experience'] ?: '—'); ?></p>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label style="font-size: 0.75rem; text-transform: uppercase; color: #94a3b8; font-weight: 600;">Cover Letter</label>
                <p style="margin: 0.5rem 0 0; white-space: pre-line;"><?php echo htmlspecialchars($application['cover_letter'] ?: '—'); ?></p>
            </div>

            <?php if (!empty($application['resume'])): ?>
            <div style="margin-bottom: 1.5rem;">
                <label style="font-size: 0.75rem; text-transform: uppercase; color: #94a3b8; font-weight: 600;">Resume</label>
                <div style="margin-top: 0.5rem;">
                    <a href="<?php echo htmlspecialchars($application['resume']); ?>" target="_blank" class="btn btn-outline" style="font-size: 0.875rem;">📄 View Resume</a>
                </div>
            </div>
            <?php endif; ?>

            <!-- Status & Actions -->
            <div style="border-top: 1px solid #e2e8f0; padding-top: 1.5rem; display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                <div id="statusBadge" style="padding: 0.4rem 1rem; border-radius: 999px; font-weight: 600; font-size: 0.875rem;
                    background: <?php echo $application['status'] === 'accepted' ? '#dcfce7' : ($application['status'] === 'rejected' ? '#fee2e2' : '#fef9c3'); ?>;
                    color: <?php echo $application['status'] === 'accepted' ? '#166534' : ($application['status'] === 'rejected' ? '#991b1b' : '#854d0e'); ?>;">
                    <?php echo ucfirst($application['status']); ?>
                </div>

                <?php if ($application['status'] !== 'accepted'): ?>
                <button id="btnAccept" class="btn btn-primary" onclick="changeStatus(<?php echo $application['id']; ?>, 'accepted')" style="background:#22c55e; border-color:#22c55e;">
                    ✓ Accept
                </button>
                <?php endif; ?>

                <?php if ($application['status'] !== 'rejected'): ?>
                <button id="btnReject" class="btn btn-outline" onclick="changeStatus(<?php echo $application['id']; ?>, 'rejected')" style="color:#ef4444; border-color:#ef4444;">
                    ✗ Reject
                </button>
                <?php endif; ?>

                <?php if ($application['status'] !== 'pending'): ?>
                <button class="btn btn-outline" onclick="changeStatus(<?php echo $application['id']; ?>, 'pending')" style="font-size:0.8rem;">
                    Reset to Pending
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
    function changeStatus(id, status) {
        fetch('index.php?page=application/updateStatusApi', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id=' + id + '&status=' + encodeURIComponent(status)
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                // Reload to update buttons & badge
                window.location.reload();
            } else {
                alert(data.error || 'An error occurred');
            }
        })
        .catch(() => alert('Network error. Please try again.'));
    }
    </script>

<?php elseif ($page === 'create-employee'): ?>
    <!-- CREATE EMPLOYEE PAGE -->
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

<?php elseif ($page === 'edit-employee'): ?>
    <!-- EDIT EMPLOYEE PAGE -->
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

<?php elseif ($page === 'employees'): ?>
    <!-- EMPLOYEES LISTING PAGE -->
    <div class="container" style="padding: 2rem 0;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1>Employees</h1>
            <a href="index.php?page=employee/create&action=create" class="btn btn-success">Add Employee</a>
        </div>

        <div style="background-color: white; padding: 2rem; border-radius: 0.5rem; margin: 2rem 0;">
            <form method="GET" action="index.php">
                <input type="hidden" name="page" value="employee/index">
                <input type="hidden" name="action" value="index">
                <div class="form-row">
                    <div class="form-group">
                        <input type="text" name="keyword" placeholder="Search by name, email, or position..." 
                            value="<?php echo htmlspecialchars($keyword ?? ''); ?>">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Search</button>
            </form>
        </div>

        <div class="grid grid-3" style="margin: 2rem 0;">
            <div class="card">
                <div class="card-body">
                    <h4>Total Employees</h4>
                    <h2 style="color: #0891b2; font-size: 2rem;"><?php echo $totalCount ?? 0; ?></h2>
                </div>
            </div>
            <?php foreach ($departments ?? [] as $dept): ?>
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

<?php elseif ($page === 'employee-detail'): ?>
    <!-- EMPLOYEE DETAIL PAGE -->
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

<?php endif; ?>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
