const navbar = document.getElementById('navbar');

function createNavbar() {
    navbar.innerHTML = `
        <div class="nav-brand">
        <img src="../assets/logo.png" alt="Triptistry" class="nav-logo"> 
        </div>
        <div class="nav-links">
            <a href="../traveller/browsepackages.html" class="nav-link">
            <i class="bi bi-compass"></i>Browse
            </a>
            <a href="../traveller/travellerpackages.html" class="nav-link">
            <i class="bi bi-suitcase-lg"></i>My Bookings</a>
            <a href="../traveller/travellerreviews.html" class="nav-link">
            <i class="bi bi-chat-left-heart"></i>My Reviews</a>
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