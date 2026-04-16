/**
 * Main JavaScript File
 * Global utilities and helpers
 */

// Show notification
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type}`;
    notification.textContent = message;
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.zIndex = '9999';
    notification.style.minWidth = '300px';
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 5000);
}

// Format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
}

// Format date
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

// Validate email format
function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Validate phone format
function isValidPhone(phone) {
    const re = /^[0-9\s\-\+\(\)]{10,20}$/;
    return re.test(phone);
}

// Toggle element visibility
function toggleElement(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.style.display = element.style.display === 'none' ? 'block' : 'none';
    }
}

// Load content via AJAX
async function loadContent(url, targetId) {
    try {
        const response = await fetch(url);
        if (response.ok) {
            const html = await response.text();
            document.getElementById(targetId).innerHTML = html;
        }
    } catch (error) {
        console.error('Error loading content:', error);
        showNotification('Failed to load content', 'error');
    }
}

// Confirm action
function confirmAction(message = 'Are you sure?') {
    return confirm(message);
}

// Disable button on submit
function disableButtonOnSubmit(formId, buttonSelector = 'button[type="submit"]') {
    const form = document.getElementById(formId);
    if (form) {
        form.addEventListener('submit', function() {
            const button = form.querySelector(buttonSelector);
            if (button) {
                button.disabled = true;
                button.textContent = 'Processing...';
            }
        });
    }
}

// Form validation helper
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;
    
    const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.style.borderColor = '#ef4444';
            isValid = false;
        } else {
            input.style.borderColor = '';
        }
    });
    
    return isValid;
}

// Clear form
function clearForm(formId) {
    const form = document.getElementById(formId);
    if (form) {
        form.reset();
    }
}

// Document ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('StartSmart HR Application Ready');
});
