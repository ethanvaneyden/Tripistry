const API_BASE = 'http://localhost/Tripistry/server/api.php';

const form      = document.querySelector('.auth-form');
const submitBtn = form.querySelector('button[type="submit"]');

function showError(message) {
    let container = document.getElementById('error-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'error-container';
        form.insertBefore(container, submitBtn);
    }
    container.className = 'error-container error-visible';
    container.innerHTML = `<i class="bi bi-exclamation-circle-fill"></i> <span>${message}</span>`;
}

function clearError() {
    const container = document.getElementById('error-container');
    if (container) container.className = 'error-container';
}

form.addEventListener('submit', async (e) => {
    e.preventDefault();
    clearError();

    const name     = document.getElementById('name').value.trim();
    const email    = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    const phone    = document.getElementById('phone').value.trim();
    const street   = document.getElementById('street').value.trim();
    const city     = document.getElementById('city').value.trim();
    const country  = document.getElementById('country').value.trim();

    // Client-side validation
    if (!name || !email || !password || !phone || !street || !city || !country) {
        showError('All fields are required.');
        return;
    }
    if (password.length < 6) {
        showError('Password must be at least 6 characters.');
        return;
    }

    submitBtn.disabled    = true;
    submitBtn.textContent = 'Registering…';

    try {
        const res  = await fetch(`${API_BASE}/api/register/agency`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
            body: JSON.stringify({ name, email, password, phone, street, city, country }),
        });
        const data = await res.json();

        if (!res.ok) {
            showError(data.error || 'Registration failed. Please try again.');
            return;
        }

        // Success — go straight to login
        window.location.href = '../index.html?registered=1';

    } catch (err) {
        showError('Could not connect to the server. Make sure XAMPP is running.');
    } finally {
        submitBtn.disabled    = false;
        submitBtn.textContent = 'Register';
    }
});
