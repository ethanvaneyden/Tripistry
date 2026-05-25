const API_BASE      = 'http://localhost/Tripistry/server/api.php';
const packageSection = document.getElementById('package-section');

// -----------------------------------------------------------------------
// Auth guard — must be logged in as an agency
// -----------------------------------------------------------------------
const user = JSON.parse(sessionStorage.getItem('user') || 'null');
if (!user || user.role !== 'agency') {
    window.location.href = '../index.html';
}

// -----------------------------------------------------------------------
// Helpers
// -----------------------------------------------------------------------
function formatDate(dateStr) {
    if (!dateStr) return '';
    return new Date(dateStr).toLocaleDateString('en-ZA', {
        day: 'numeric', month: 'short', year: 'numeric'
    });
}

function formatPrice(price) {
    return new Intl.NumberFormat('en-ZA', {
        style: 'currency', currency: 'ZAR', maximumFractionDigits: 0
    }).format(price);
}

function renderStars(rating) {
    let s = '';
    for (let i = 1; i <= 5; i++) s += i <= Math.round(rating) ? '★' : '☆';
    return s;
}

// -----------------------------------------------------------------------
// Update stat cards
// -----------------------------------------------------------------------
function updateStats(totalPackages, avgRating) {
    const statCards = document.querySelectorAll('.stat-card p');
    if (statCards[0]) statCards[0].textContent = totalPackages;
    if (statCards[1]) statCards[1].textContent = avgRating > 0 ? `${avgRating}/5` : 'N/A';
}

// -----------------------------------------------------------------------
// Delete a package
// -----------------------------------------------------------------------
async function deletePackage(packageId, cardEl) {
    if (!confirm('Are you sure you want to delete this package? This cannot be undone.')) return;

    try {
        const res  = await fetch(`${API_BASE}/api/agency/packages/delete`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ agency_id: user.id, package_id: packageId }),
        });
        const data = await res.json();

        if (!res.ok) {
            alert(data.error || 'Failed to delete package.');
            return;
        }

        cardEl.classList.add('fade-out');
            setTimeout(() => { cardEl.remove(); fetchPackages(); }, 300);

    } catch (err) {
        alert('Could not connect to the server.');
    }
}

// -----------------------------------------------------------------------
// Build a package card
// -----------------------------------------------------------------------
function createCard(pkg) {
    const card      = document.createElement('div');
    const nights    = pkg.Nights || 0;
    const location  = [pkg.City, pkg.Country].filter(Boolean).join(', ') || 'Unknown destination';
    const thumbnail = pkg.ThumbnailURL
        || 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=600';

    card.classList.add('package-card');
    card.innerHTML = `
        <img
            class="package-image"
            src="${thumbnail}"
            alt="${pkg.Title}"
            onerror="this.src='https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=600'"
        />
        <div class="card-content">
            <h2 class="card-title">${pkg.Title}</h2>
            <h3 class="card-destination">${location}</h3>
            <p class="card-description">${pkg.Description || ''}</p>
            <ul class="card-meta">
                <li class="card-date">
                    ${formatDate(pkg.StartDate)}
                    <small>(${nights} night${nights !== 1 ? 's' : ''})</small>
                </li>
                <li class="card-price">${formatPrice(pkg.TotalPrice)} <small>/ person</small></li>
                <li class="rating">
                    <span class="stars">${renderStars(pkg.AvgRating)}</span>
                    <span>${parseFloat(pkg.AvgRating) > 0 ? parseFloat(pkg.AvgRating).toFixed(1) : 'No reviews'}</span>
                </li>
                <li><i class="bi bi-people"></i> ${pkg.BookingCount} booking${pkg.BookingCount != 1 ? 's' : ''}</li>
            </ul>
            <div class="side-by-side-btn">
                <a href="createpackage.html?edit=${pkg.PackageID}" class="btn-outline">
                    Edit <i class="bi bi-pencil-square"></i>
                </a>
                <button class="btn-outline btn-delete" data-id="${pkg.PackageID}">
                    Delete <i class="bi bi-trash3"></i>
                </button>
            </div>
        </div>`;

    card.querySelector('.btn-delete').addEventListener('click', () => {
        deletePackage(pkg.PackageID, card);
    });

    return card;
}

// -----------------------------------------------------------------------
// Fetch and render
// -----------------------------------------------------------------------
async function fetchPackages() {
    packageSection.innerHTML = `
        <div class="no-results">
            <i class="bi bi-arrow-repeat icon-2rem icon-amber"></i>
            <p>Loading your packages…</p>
        </div>`;

    try {
        const res  = await fetch(`${API_BASE}/api/agency/packages`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ agency_id: user.id }),
        });
        const data = await res.json();

        if (!res.ok) {
            packageSection.innerHTML = `<div class="no-results"><p>${data.error}</p></div>`;
            return;
        }

        updateStats(data.total_packages, data.avg_rating);

        packageSection.innerHTML = '';

        if (!data.packages || data.packages.length === 0) {
            packageSection.innerHTML = `
                <div class="no-results">
                    <i class="bi bi-inbox icon-2rem icon-amber"></i>
                    <p>You haven't created any packages yet.
                       <a href="createpackage.html">Create one now</a>
                    </p>
                </div>`;
            return;
        }

        data.packages.forEach(pkg => packageSection.appendChild(createCard(pkg)));

    } catch (err) {
        packageSection.innerHTML = `
            <div class="no-results">
                <p>Could not connect to the server. Make sure XAMPP is running.</p>
            </div>`;
    }
}

fetchPackages();
