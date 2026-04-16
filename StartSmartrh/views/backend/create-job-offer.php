<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container" style="padding: 2rem 0;">
    <h1>Post New Job Offer</h1>

    <div style="max-width: 800px;">
        <form id="jobOfferForm">
            <div id="errorContainer"></div>

            <div class="form-group">
                <label for="title">Job Title *</label>
                <input type="text" id="title" name="title" required>
            </div>

            <div class="form-group">
                <label for="type">Job Type *</label>
                <select id="type" name="type" required>
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
                    <input type="number" id="salaryMin" name="salaryMin" required min="0" step="0.01">
                </div>

                <div class="form-group">
                    <label for="salaryMax">Maximum Salary ($) *</label>
                    <input type="number" id="salaryMax" name="salaryMax" required min="0" step="0.01">
                </div>
            </div>

            <div class="form-group">
                <label for="location">Location *</label>
                <input type="text" id="location" name="location" required placeholder="e.g., New York, NY or Remote">
            </div>

            <div class="form-group">
                <label for="description">Job Description *</label>
                <textarea id="description" name="description" required placeholder="Detailed job description..."></textarea>
            </div>

            <div class="form-group">
                <label for="requirements">Requirements *</label>
                <textarea id="requirements" name="requirements" required placeholder="List the key requirements and qualifications..."></textarea>
            </div>

            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-success" style="flex: 1;">Post Job Offer</button>
                <a href="index.php?page=job-offer/myOffers&action=myOffers" class="btn btn-outline" style="flex: 1; text-align: center;">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('jobOfferForm').addEventListener('submit', async function(e) {
    e.preventDefault();

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

<?php include __DIR__ . '/../layouts/footer.php'; ?>
