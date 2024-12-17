document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.querySelector('.form');
    
    loginForm.addEventListener('submit', function(event) {
        
        clearErrors();
        
        const username = document.getElementById('username');
        if (!validateUsername(username.value)) {
            showError(username, 'Username must contain only letters, numbers, underscores, and periods');
            event.preventDefault();
            return;
        }
        
        const password = document.getElementById('password');
        if (!validatePassword(password.value)) {
            showError(password, 'Password cannot be empty');
            event.preventDefault();
            return;
        }
    });

    // Validate if the username is not empty and consists of valid characters (letters, numbers, underscores, and periods)
    function validateUsername(username) {
        const usernameRegex = /^[a-zA-Z0-9_\.]+$/;
        return username.length > 0 && usernameRegex.test(username);
    }

    // Validate if the password is not empty (no character type checks)
    function validatePassword(password) {
        return password.length > 0;
    }

    function showError(inputElement, message) {
        let errorElement = inputElement.closest('.input-field').nextElementSibling;
        if (!errorElement || !errorElement.classList.contains('text-danger')) {
            errorElement = document.createElement('span');
            errorElement.classList.add('text-danger');
            inputElement.closest('.input-field').insertAdjacentElement('afterend', errorElement);
        }
        
        errorElement.textContent = message;
        inputElement.style.borderColor = 'red'; // Optional: You can style the input field itself (e.g., red border)
    }

    function clearErrors() {
        const errorElements = document.querySelectorAll('.validation-error');
        errorElements.forEach(el => el.remove());

        const inputs = document.querySelectorAll('input');
        inputs.forEach(input => {
            input.style.borderColor = '';
        });
    }
});
