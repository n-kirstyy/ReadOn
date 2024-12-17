document.addEventListener('DOMContentLoaded', function() {
    const signupForm = document.querySelector('.form');
    
    signupForm.addEventListener('submit', function(event) {
        // Reset previous error messages
        clearErrors();
        
        // Validate username
        const username = document.getElementById('username');
        const trimmedUsername = username.value.trim(); // Trim any leading/trailing whitespace
        
        console.log('Trimmed Username:', trimmedUsername);
        
        if (!validateUsername(trimmedUsername)) {
            showError(username, 'Username must be 3-20 characters long and contain only letters, numbers, underscores, and periods');
            event.preventDefault();
            return;
        }
        
        // Validate email
        const email = document.getElementById('email');
        if (!validateEmail(email.value)) {
            showError(email, 'Please enter a valid email address');
            event.preventDefault();
            return;
        }
        
        // Validate password
        const password = document.getElementById('password');
        if (!validatePassword(password.value)) {
            showError(password, 'Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, and one number');
            event.preventDefault();
            return;
        }
        
        // Validate password confirmation
        const passwordConfirm = document.getElementById('password-confirm');
        if (password.value !== passwordConfirm.value) {
            showError(passwordConfirm, 'Passwords do not match');
            event.preventDefault();
            return;
        }
    });

    function validateUsername(username) {

        console.log('Validating username with regex:', /^[a-zA-Z0-9_.]{3,20}$/.test(username));
        
        // Username can contain letters, numbers, underscores, and periods
        // Ensure username is between 3 and 20 characters long
        const usernameRegex = /^[a-zA-Z0-9_.]{3,20}$/;
        return usernameRegex.test(username); // Return true if the username matches the pattern
    }

    function validateEmail(email) {
        // Basic email validation regex
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email); // Return true if the email matches the pattern
    }

    function validatePassword(password) {
        // Password must be at least 8 characters long and contain at least one uppercase, 
        // one lowercase letter, and one number
        const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$/;
        return passwordRegex.test(password); // Return true if the password matches the pattern
    }

    function showError(inputElement, message) {
        let errorElement = inputElement.closest('.input-field').nextElementSibling;
        if (!errorElement || !errorElement.classList.contains('text-danger')) {
            errorElement = document.createElement('span');
            errorElement.classList.add('text-danger');
            inputElement.closest('.input-field').insertAdjacentElement('afterend', errorElement);
        }
        
    
        errorElement.textContent = message;
        inputElement.style.borderColor = 'red'; 
    }
    
    function clearErrors() {
        // Remove all existing error messages
        const errorElements = document.querySelectorAll('.validation-error');
        errorElements.forEach(el => el.remove());
        
        // Reset input border colors
        const inputs = document.querySelectorAll('input');
        inputs.forEach(input => {
            input.style.borderColor = '';
        });
    }
});
