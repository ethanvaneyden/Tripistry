const API_BASE = 'http://localhost/Tripistry/server/api.php';

const packageSection = document.getElementById('package-section');

// --- Filter / sort elements ---
const searchInput    = document.getElementById('search');
const priceSelect    = document.getElementById('filter-price');
const ratingSelect   = document.getElementById('filter-rating');
const durationSelect = document.getElementById('filter-duration');
const sortSelect     = document.getElementById('filter-sort');

// -----------------------------------------------------------------------
// Fetch packages from the API
// -----------------------------------------------------------------------
async function fetchPackages() {
    const body = {
        search:   searchInput.value.trim(),
        price:    priceSelect.value,
        rating:   ratingSelect.value ? parseInt(ratingSelect.value) : 0,
        duration: durationSelect.value,
        sort:     sortSelect.value || 'recommended',
    };

    try {
        const res  = await fetch(`${API_BASE}/api/packages`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
            body: JSON.stringify(body),
        });
        const data = await res.json();

        if (!res.ok) {
            showError(data.error || 'Failed to load packages.');
            return;
        }

        renderPackages(data.packages);
    } catch (err) {
        showError('Could not connect to the server. Please try again.');
    }
}

// -----------------------------------------------------------------------
// Helpers
// -----------------------------------------------------------------------
function formatDate(dateStr) {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    return d.toLocaleDateString('en-ZA', { day: 'numeric', month: 'short', year: 'numeric' });
}

function formatPrice(price) {
    return new Intl.NumberFormat('en-ZA', {
        style: 'currency', currency: 'ZAR', maximumFractionDigits: 0
    }).format(price);
}

function renderStars(rating) {
    const full  = Math.round(rating);
    let stars   = '';
    for (let i = 1; i <= 5; i++) {
        stars += i <= full ? '★' : '☆';
    }
    return stars;
}

function locationLabel(pkg) {
    const city    = pkg.City    || '';
    const country = pkg.Country || '';
    if (city && country) return `${city}, ${country}`;
    return city || country || 'Unknown destination';
}

// -----------------------------------------------------------------------
// Create a package card element
// -----------------------------------------------------------------------
function createCard(pkg) {
    const card      = document.createElement('div');
    const nights    = pkg.Nights    || 0;
    const avgRating = parseFloat(pkg.AvgRating) || 0;
    const reviewCount = parseInt(pkg.ReviewCount) || 0;
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
            <h3 class="card-destination">${locationLabel(pkg)}</h3>
            <p class="card-description">${pkg.Description || ''}</p>
            <ul class="card-meta">
                <li class="card-date">
                    ${formatDate(pkg.StartDate)}
                    <small>(${nights} night${nights !== 1 ? 's' : ''})</small>
                </li>
                <li class="card-price">
                    ${formatPrice(pkg.TotalPrice)} <small>/ person</small>
                </li>
                <li class="card-agency">Offered by ${pkg.AgencyName}</li>
                <li class="rating">
                    <span class="stars">${renderStars(avgRating)}</span>
                    <span>${avgRating > 0 ? avgRating.toFixed(1) : 'No reviews'}</span>
                    ${reviewCount > 0 ? `<small>(${reviewCount})</small>` : ''}
                </li>
            </ul>
            <a href="packagedetails.html?id=${pkg.PackageID}" class="btn-outline">
                View package <i class="bi bi-chevron-double-right"></i>
            </a>
        </div>`;
    return card;
}

// -----------------------------------------------------------------------
// Render helpers
// -----------------------------------------------------------------------
function renderPackages(packages) {
    packageSection.innerHTML = '';

    if (!packages || packages.length === 0) {
        packageSection.innerHTML = `
            <div class="no-results">
                <i class="bi bi-search icon-2rem icon-amber"></i>
                <p>No packages match your search. Try adjusting your filters.</p>
            </div>`;
        return;
    }

    packages.forEach(pkg => packageSection.appendChild(createCard(pkg)));
}

function showError(message) {
    packageSection.innerHTML = `
        <div class="no-results">
            <i class="bi bi-exclamation-circle icon-2rem icon-error"></i>
            <p>${message}</p>
        </div>`;
}

function showLoading() {
    packageSection.innerHTML = `
        <div class="no-results">
            <i class="bi bi-arrow-repeat icon-2rem icon-amber"></i>
            <p>Loading packages…</p>
        </div>`;
}

// -----------------------------------------------------------------------
// Debounce for search input
// -----------------------------------------------------------------------
let debounceTimer;
function debounce(fn, delay = 350) {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(fn, delay);
}

// -----------------------------------------------------------------------
// Wire up filter controls
// -----------------------------------------------------------------------
searchInput.addEventListener('input',    () => debounce(() => { showLoading(); fetchPackages(); }));
priceSelect.addEventListener('change',   () => { showLoading(); fetchPackages(); });
ratingSelect.addEventListener('change',  () => { showLoading(); fetchPackages(); });
durationSelect.addEventListener('change',() => { showLoading(); fetchPackages(); });
sortSelect.addEventListener('change',    () => { showLoading(); fetchPackages(); });

// --- Initial load ---
showLoading();
fetchPackages();
