// Matching System JavaScript

/**
 * Record a user action (interested, skipped, applied)
 */
function recordAction(projectId, action) {
    fetch('index.php?controller=matching&action=recordAction', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ project_id: projectId, action: action })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (action === 'interested') {
                showNotification('❤️ Projet sauvegardé!', 'success');
            } else if (action === 'applied') {
                showNotification('✅ Candidature envoyée!', 'success');
            }
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showNotification(data.message || 'Erreur', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Erreur réseau', 'error');
    });
}

/**
 * Skip project (in discover mode)
 */
function skipProject() {
    const card = document.getElementById('swipeCard');
    if (card) {
        const projectId = card.dataset.projectId;
        recordAction(projectId, 'skipped');
    }
}

/**
 * Mark project as interested (in discover mode)
 */
function interestedProject() {
    const card = document.getElementById('swipeCard');
    if (card) {
        const projectId = card.dataset.projectId;
        recordAction(projectId, 'interested');
    }
}

/**
 * Add skill to user
 */
function addSkill(skillId, skillName) {
    fetch('index.php?controller=matching&action=addSkill', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ 
            skill_id: skillId, 
            proficiency: 'beginner' 
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(`✓ ${skillName} ajoutée!`, 'success');
            setTimeout(() => location.reload(), 500);
        } else {
            showNotification('Erreur: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Erreur réseau', 'error');
    });
}

/**
 * Remove skill from user
 */
function removeSkill(skillId) {
    if (!confirm('Êtes-vous sûr?')) return;

    fetch('index.php?controller=matching&action=removeSkill', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ skill_id: skillId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('✓ Compétence supprimée', 'success');
            setTimeout(() => location.reload(), 500);
        } else {
            showNotification('Erreur: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Erreur réseau', 'error');
    });
}

/**
 * Add category interest
 */
function addInterest(categoryId, categoryName) {
    fetch('index.php?controller=matching&action=addInterest', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ 
            category_id: categoryId,
            score: 3
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(`❤️ ${categoryName} ajoutée!`, 'success');
            setTimeout(() => location.reload(), 500);
        } else {
            showNotification('Erreur: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Erreur réseau', 'error');
    });
}

/**
 * Remove category interest
 */
function removeInterest(categoryId) {
    if (!confirm('Êtes-vous sûr?')) return;

    fetch('index.php?controller=matching&action=removeInterest', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ category_id: categoryId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('✓ Intérêt supprimé', 'success');
            setTimeout(() => location.reload(), 500);
        } else {
            showNotification('Erreur: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Erreur réseau', 'error');
    });
}

/**
 * Update interest score (stars)
 */
function updateInterestScore(categoryId, score) {
    fetch('index.php?controller=matching&action=addInterest', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ 
            category_id: categoryId,
            score: score
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('✓ Score mis à jour', 'success');
            // Update UI
            const interestItem = document.querySelector(`[data-category-id="${categoryId}"]`);
            if (interestItem) {
                const stars = interestItem.querySelectorAll('.star');
                stars.forEach((star, index) => {
                    if (index < score) {
                        star.classList.add('filled');
                    } else {
                        star.classList.remove('filled');
                    }
                });
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Erreur réseau', 'error');
    });
}

/**
 * Show notification toast
 */
function showNotification(message, type = 'info') {
    // Remove existing notification
    const existing = document.querySelector('.notification');
    if (existing) existing.remove();

    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    
    const style = document.createElement('style');
    style.textContent = `
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            z-index: 9999;
            animation: slideIn 0.3s ease-out;
        }
        .notification-success {
            background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
        }
        .notification-error {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        }
        .notification-info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(400px);
                opacity: 0;
            }
        }
    `;
    
    document.head.appendChild(style);
    document.body.appendChild(notification);

    // Auto remove after 3 seconds
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

/**
 * Get match statistics
 */
function getMatchStats() {
    fetch('index.php?controller=matching&action=getMatchStats')
    .then(response => response.json())
    .then(data => {
        console.log('Match Stats:', data);
        // Update UI if needed
        document.getElementById('skillCount').textContent = data.skills_count;
        document.getElementById('interestCount').textContent = data.interests_count;
    })
    .catch(error => console.error('Error:', error));
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    // Add keyboard shortcuts for discover mode
    document.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowLeft') {
            skipProject();
        } else if (e.key === 'ArrowRight') {
            interestedProject();
        }
    });
});
