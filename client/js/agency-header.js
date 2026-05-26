const navbar = document.getElementById('navbar');

function createNavbar() {
    navbar.innerHTML = `
        <div class="nav-brand">
        <img src="../assets/logo.png" alt="Triptistry" class="nav-logo">
        </div>
        <div class="nav-links">
            <a href="../agency/agencydashboard.html" class="nav-link">
            <i class="bi bi-briefcase"></i>My Packages
            </a>
            <a href="../agency/createpackage.html" class="nav-link">
            <i class="bi bi-plus-circle"></i>Create</a>
            <a href="../agency/grouptrips.html" class="nav-link">
            <i class="bi bi-people"></i>Group Trips</a>
            <a href="../agency/agencyreviews.html" class="nav-link">
            <i class="bi bi-chat-left-quote"></i></i>Reviews</a>
            <a href="../index.html" id="logoutBtn" class="nav-link logout-btn">
            <i class="bi bi-box-arrow-right"></i>Logout</a>
        </div>
    `;
    highlightActiveLink();
}

function highlightActiveLink() {
    const currentPath = window.location.pathname.split("/").pop();

    const navLinks = document.querySelectorAll('.nav-link');

    navLinks.forEach(link => {
        const linkPath = link.getAttribute('href');

        if (linkPath.endsWith(currentPath)) {
            link.classList.add('active');
        } else {
            link.classList.remove('active');
        }
    });
}

createNavbar();