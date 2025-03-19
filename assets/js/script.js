document.addEventListener('DOMContentLoaded', function() {
    // Toggle sidebar on mobile
    const toggleSidebarBtn = document.getElementById('toggle-sidebar');
    if (toggleSidebarBtn) {
        toggleSidebarBtn.addEventListener('click', function() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('active');
        });
    }
    
    // Handle form validation
    const forms = document.querySelectorAll('.validate-form');
    forms.forEach(function(form) {
        form.addEventListener('submit', function(event) {
            let valid = true;
            
            // Email validation
            const emailField = form.querySelector('input[type="email"]');
            if (emailField && emailField.value.trim() === '') {
                showError(emailField, 'Email is required');
                valid = false;
            } else if (emailField && !isValidEmail(emailField.value)) {
                showError(emailField, 'Please enter a valid email');
                valid = false;
            } else if (emailField) {
                clearError(emailField);
            }
            
            // Password validation
            const passwordField = form.querySelector('input[type="password"]');
            if (passwordField && passwordField.value.trim() === '') {
                showError(passwordField, 'Password is required');
                valid = false;
            } else if (passwordField && passwordField.value.length < 6) {
                showError(passwordField, 'Password must be at least 6 characters');
                valid = false;
            } else if (passwordField) {
                clearError(passwordField);
            }
            
            // Required fields validation
            const requiredFields = form.querySelectorAll('[required]');
            requiredFields.forEach(function(field) {
                if (field.value.trim() === '') {
                    showError(field, 'This field is required');
                    valid = false;
                } else {
                    clearError(field);
                }
            });
            
            if (!valid) {
                event.preventDefault();
            }
        });
    });
    
    // Setup rich text editors
    const textareas = document.querySelectorAll('.rich-text-editor');
    if (textareas.length > 0) {
        textareas.forEach(function(textarea) {
            // You can replace this with a full-featured rich text editor like TinyMCE or CKEditor
            textarea.style.minHeight = '200px';
        });
    }
    
    // Setup file upload previews
    const fileInputs = document.querySelectorAll('.file-upload');
    fileInputs.forEach(function(input) {
        input.addEventListener('change', function() {
            const preview = document.querySelector('#' + input.dataset.preview);
            if (preview) {
                const file = input.files[0];
                const reader = new FileReader();
                
                reader.addEventListener('load', function() {
                    preview.src = reader.result;
                    preview.style.display = 'block';
                });
                
                if (file) {
                    reader.readAsDataURL(file);
                }
            }
        });
    });
    
    // Handle delete confirmations
    const deleteButtons = document.querySelectorAll('.delete-btn');
    deleteButtons.forEach(function(button) {
        button.addEventListener('click', function(event) {
            if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                event.preventDefault();
            }
        });
    });
    
    // Handle notification dismissals
    const dismissButtons = document.querySelectorAll('.dismiss-notification');
    dismissButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            const notification = button.closest('.notification');
            notification.style.display = 'none';
        });
    });
});

// Helper functions
function showError(field, message) {
    const errorElement = field.nextElementSibling;
    if (errorElement && errorElement.classList.contains('text-danger')) {
        errorElement.textContent = message;
    } else {
        const error = document.createElement('span');
        error.classList.add('text-danger');
        error.textContent = message;
        field.parentNode.insertBefore(error, field.nextSibling);
    }
    field.classList.add('is-invalid');
}

function clearError(field) {
    const errorElement = field.nextElementSibling;
    if (errorElement && errorElement.classList.contains('text-danger')) {
        errorElement.textContent = '';
    }
    field.classList.remove('is-invalid');
}

function isValidEmail(email) {
    const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(String(email).toLowerCase());
}