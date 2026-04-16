<?php include __DIR__ . '/../layouts/header.php'; ?>

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

        <form id="applicationForm" enctype="multipart/form-data">
            <input type="hidden" name="jobOfferId" value="<?php echo $jobOffer['id']; ?>">

            <div id="errorContainer"></div>

            <div class="form-group">
                <label for="fullName">Full Name *</label>
                <input type="text" id="fullName" name="fullName" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number *</label>
                    <input type="tel" id="phone" name="phone" required>
                </div>
            </div>

            <div class="form-group">
                <label for="experience">Years of Experience *</label>
                <input type="text" id="experience" name="experience" placeholder="e.g., 5 years" required>
            </div>

            <div class="form-group">
                <label for="coverLetter">Cover Letter *</label>
                <textarea id="coverLetter" name="coverLetter" required placeholder="Tell us why you're a great fit for this position..."></textarea>
            </div>

            <div class="form-group">
                <label for="resume">Resume (PDF, DOC, or DOCX) *</label>
                <input type="file" id="resume" name="resume" accept=".pdf,.doc,.docx" required>
            </div>

            <button type="submit" class="btn btn-success" style="width: 100%; padding: 1rem;">Submit Application</button>
            <a href="index.php?page=job-offer/view&id=<?php echo $jobOffer['id']; ?>&action=view" class="btn btn-outline" style="width: 100%; padding: 1rem; margin-top: 1rem; text-align: center;">Cancel</a>
        </form>
    </div>
</div>

<script>
document.getElementById('applicationForm').addEventListener('submit', async function(e) {
    e.preventDefault();

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

<?php include __DIR__ . '/../layouts/footer.php'; ?>
