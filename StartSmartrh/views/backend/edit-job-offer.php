<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container" style="padding: 2rem 0;">
    <h1>Edit Job Offer: <?php echo htmlspecialchars($jobOffer['title']); ?></h1>

    <div style="max-width: 800px;">
        <form id="jobOfferForm">
            <input type="hidden" name="id" value="<?php echo $jobOffer['id']; ?>">

            <div id="errorContainer"></div>

            <div class="form-group">
                <label for="title">Job Title *</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($jobOffer['title']); ?>" required>
            </div>

            <div class="form-group">
                <label for="type">Job Type *</label>
                <select id="type" name="type" required>
                    <option value="Full-time" <?php echo ($jobOffer['type'] === 'Full-time') ? 'selected' : ''; ?>>Full-time</option>
                    <option value="Part-time" <?php echo ($jobOffer['type'] === 'Part-time') ? 'selected' : ''; ?>>Part-time</option>
                    <option value="Contract" <?php echo ($jobOffer['type'] === 'Contract') ? 'selected' : ''; ?>>Contract</option>
                    <option value="Freelance" <?php echo ($jobOffer['type'] === 'Freelance') ? 'selected' : ''; ?>>Freelance</option>
                </select>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="salaryMin">Minimum Salary ($) *</label>
                    <input type="number" id="salaryMin" name="salaryMin" value="<?php echo htmlspecialchars($jobOffer['salary_min']); ?>" required min="0" step="0.01">
                </div>

                <div class="form-group">
                    <label for="salaryMax">Maximum Salary ($) *</label>
                    <input type="number" id="salaryMax" name="salaryMax" value="<?php echo htmlspecialchars($jobOffer['salary_max']); ?>" required min="0" step="0.01">
                </div>
            </div>

            <div class="form-group">
                <label for="location">Location *</label>
                <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($jobOffer['location']); ?>" required>
            </div>

            <div class="form-group">
                <label for="description">Job Description *</label>
                <textarea id="description" name="description" required><?php echo htmlspecialchars($jobOffer['description']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="requirements">Requirements *</label>
                <textarea id="requirements" name="requirements" required><?php echo htmlspecialchars($jobOffer['requirements']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="status">Status *</label>
                <select id="status" name="status" required>
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
document.getElementById('jobOfferForm').addEventListener('submit', async function(e) {
    e.preventDefault();

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

<?php include __DIR__ . '/../layouts/footer.php'; ?>
