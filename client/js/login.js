const API_BASE = "/api.php";

const submit = document.getElementById("submit");
const passwordField = document.getElementById("password");
const emailField = document.getElementById("email");

// Show a success message if arriving after registration
const params = new URLSearchParams(window.location.search);
if (params.get('registered') === '1') {
    const error = document.getElementById('error-container');
    if (error) {
        error.classList.add('error-visible');
        error.classList.add('success');
        error.innerHTML = `
            <i class="bi bi-check-circle-fill"></i>
            <span>Account created! You can now log in.</span>`;
  }
}

async function login() {
  const email = emailField.value.trim();
  const password = passwordField.value;

  try {
    const res = await fetch(`${API_BASE}/api/login`, {
      method: "POST",
      headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ email, password }),
    });
    return await res.json();
  } catch (e) {
    return { error: "Could not connect to the server." };
  }
}

submit.addEventListener("click", async (e) => {
  e.preventDefault();

  submit.disabled = true;
  submit.textContent = "Logging in…";

  const response = await login();

  submit.disabled = false;
  submit.textContent = "Login";

    if (response.message !== 'Login successful!') {
        const error = document.getElementById('error-container');
        error.classList.add('error-visible');
        error.classList.remove('success');
        error.innerHTML = `
            <i class="bi bi-exclamation-circle-fill"></i>
            <span id="error-text">${response.error || "Incorrect email or password"}</span>`;
    return;
  }

  sessionStorage.setItem("user", JSON.stringify(response.user));

  if (response.user.role === "agency") {
    window.location.href = "agency/agencydashboard.html";
  } else {
    window.location.href = "traveller/browsepackages.html";
  }
});
