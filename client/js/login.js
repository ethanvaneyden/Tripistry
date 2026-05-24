const submit = document.getElementById('submit');
const passwordField = document.getElementById('password');
const emailField = document.getElementById('email');
const authFooter = document.getElementById('auth-footer')

async function login() {
    const email = emailField.value;
    const password = passwordField.value;

    const body = {
        email: email,
        password: password
    }
    const settings = {
        method: 'POST',
        body: JSON.stringify(body),
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
        }
    };
    try {
        const fetchResponse = await fetch('http://localhost/Tripistry/server/api.php/api/login', settings);
        const data = await fetchResponse.json();
        return data;
    } catch (e) {
        return e;
    }
}

submit.addEventListener('click', async (e) => {
    e.preventDefault();
    const response = await login();
    if (response.message !== "Login successful!") {
        const error = document.getElementById('error-container');
        error.classList.add('error-visible')
        error.innerHTML = `
            <i class="bi bi-exclamation-circle-fill"></i>
            <span id="error-text">Incorrect email or password</span>`;
        return;
    }

    if (response.user.role === 'agency') {
        window.location.href = "../client/agency/agencydashboard.html";
    }
    else {
        window.location.href = "../client/traveller/browsepackages.html";
    }

})