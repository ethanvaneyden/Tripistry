const API_BASE_URL = '/server/api.php';

// Safely parse user storage
const userStorage = JSON.parse(sessionStorage.getItem('user'));
if (!userStorage) {
    console.error("No user found in session storage. Please log in.");
}

// =========================================================================
// UI Helpers
// =========================================================================
function generateStarsHTML(rating) {
    let starsHtml = '';
    for (let i = 1; i <= 5; i++) {
        starsHtml += `<i class="bi ${i <= rating ? 'bi-star-fill' : 'bi-star'}"></i>`;
    }
    return `<div class="stars">${starsHtml}</div>`;
}

function formatDate(dateString) {
    if (!dateString) return '';
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString(undefined, options);
}

// =========================================================================
// Modal Rendering & State
// =========================================================================
const modal = document.getElementById('review-modal');

function renderModal(packageId, packageName, existingReview = null) {
    if (!modal) return;

    const isEdit = existingReview !== null;
    const modalTitle = isEdit ? `Edit your review` : `Write a review`;
    const currentText = isEdit ? existingReview.text : '';
    const currentRating = isEdit ? existingReview.rating : 0;

    modal.innerHTML = `
      <div class="modal-header">
        <h2><i class="bi bi-pen"></i> ${modalTitle}</h2>
        <button class="close-btn" id="close-modal-btn"><i class="bi bi-x-lg"></i></button>
      </div>
      <hr class='modal-divider'>
      <div class="rating-section">
        <h3>How was your "${packageName}"?</h3>
        <div class="stars-review">
          ${[5, 4, 3, 2, 1].map(num => `
            <input type="radio" name="rating" id="star${num}" value="${num}" ${currentRating === num ? 'checked' : ''} />
            <label for="star${num}"><i class="bi bi-star-fill"></i></label>
          `).join('')}
        </div>
      </div>
      <div class="text-box">
        <textarea class="review-text" id="review-text-input" placeholder="Write your thoughts here...">${currentText}</textarea>
      </div>
      <div class="modal-footer">
        <button class="review-btn" id="submit-review-btn" 
            data-action="${isEdit ? 'update' : 'create'}" 
            data-rid="${isEdit ? existingReview.id : ''}"
            data-pid="${packageId}">
            ${isEdit ? 'Save Changes' : 'Submit review'}
        </button>
      </div>`;

    modal.showModal();
}

// =========================================================================
// Global Event Delegation (Prevents duplicate listeners & memory leaks)
// =========================================================================
document.addEventListener('click', async (e) => {

    // 1. Close Modal Logic
    if (e.target.closest('#close-modal-btn') || e.target === modal) {
        modal.close();
    }

    // 2. Submit Review Logic
    if (e.target.closest('#submit-review-btn')) {
        const btn = e.target.closest('#submit-review-btn');
        const action = btn.getAttribute('data-action');
        const pid = btn.getAttribute('data-pid');
        const rid = btn.getAttribute('data-rid');

        const ratingElement = document.querySelector('input[name="rating"]:checked');
        const rating = ratingElement ? parseInt(ratingElement.value) : 0;
        const comment = document.getElementById('review-text-input').value;

        if (rating === 0) {
            showToast("Please select a star rating.", 'info');
            return;
        }

        const endpoint = action === 'create' ? '?route=/api/review/create' : '?route=/api/review/update';
        const payload = action === 'create'
            ? { traveller_id: userStorage.id, package_id: pid, rating, comment }
            : { review_id: rid, traveller_id: userStorage.id, rating, comment };

        try {
            const res = await fetch(API_BASE_URL + endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            if (res.ok) {
                modal.close();
                loadTravellerData();
            } else {
                const err = await res.json();
                showToast("Failed to save review.", 'error');
            }
        } catch (error) {
            console.error("Submission error:", error);
            showToast("Network error. Please try again.", 'error');
        }
    }

    // 3. Open "Write Review" Modal
    if (e.target.closest('.write-review-trigger')) {
        const btn = e.target.closest('.write-review-trigger');
        renderModal(btn.dataset.pid, btn.dataset.title);
    }

    // 4. Open "Edit Review" Modal
    if (e.target.closest('.edit-review-trigger')) {
        const btn = e.target.closest('.edit-review-trigger');
        renderModal(btn.dataset.pid, btn.dataset.title, {
            id: btn.dataset.rid,
            rating: parseInt(btn.dataset.rating),
            text: btn.dataset.comment
        });
    }

    // 5. Delete Review Logic
    if (e.target.closest('.delete-review-trigger')) {
        if (confirm('Are you sure you want to delete this review?')) {
            const rid = e.target.closest('.delete-review-trigger').dataset.rid;
            try {
                await fetch(API_BASE_URL + '?route=/api/review/delete', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ review_id: rid, traveller_id: userStorage.id })
                });
                loadTravellerData();
            } catch (error) {
                console.error("Deletion error:", error);
            }
        }
    }
});

// =========================================================================
// TRAVELLER DASHBOARD LOGIC
// =========================================================================
async function loadTravellerData() {
    const workspace = document.querySelector('.reviews-workspace');
    if (!workspace) return;

    workspace.innerHTML = '<p style="padding: 20px; color: white;">Loading your reviews...</p>';

    try {
        // FIX: Points to the correct review endpoint instead of /api/packages
        const res = await fetch(`${API_BASE_URL}?route=/api/review/traveller`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ traveller_id: userStorage.id })
        });
        const data = await res.json();

        if (!res.ok) throw new Error(data.error || "Failed to fetch data");

        let html = '';

        // Render Eligible Packages
        html += `<div class="review-card">
                    <h2 class="section-title"><i class="bi bi-clock-history"></i> Share Your Experience</h2>`;

        if (data.eligible_packages && data.eligible_packages.length > 0) {
            data.eligible_packages.forEach(pkg => {
                html += `
                <div class="review-bubble">
                    <div>
                        <h2 class="reviewer-name-light">${pkg.Title}</h2>
                        <p class="review-text-light">You returned from this trip on ${formatDate(pkg.EndDate)}. Share your thoughts with the community!</p>
                    </div>
                    <button class="review-btn write-review-trigger" data-pid="${pkg.PackageID}" data-title="${pkg.Title}">
                        Write Review <i class="bi bi-pencil-square"></i>
                    </button>
                </div>`;
            });
        } else {
            html += `<p class="review-text-light" style="margin-bottom:20px;">You have no pending reviews to write.</p>`;
        }
        html += `</div>`;

        // Render Review History
        html += `<div class="review-card">
                    <h3 class="section-title"><i class="bi bi-chat-left-check"></i> Your Review History</h3>`;

        if (data.review_history && data.review_history.length > 0) {
            data.review_history.forEach(rev => {
                html += `
                <div class="review-bubble">
                    <div class="review-header">
                        <div class="reviewer-info">
                            <div>
                                <h4 class="reviewer-name-light">You</h4>
                                <span class="review-date-light">${formatDate(rev.CreatedAt)}</span>
                            </div>
                        </div>
                        ${generateStarsHTML(rev.Rating)}
                    </div>
                    <p class="review-text-light">"${rev.Comment}"</p>
                    <div class="review-footer">
                        <span class="badge-package">${rev.Title}</span>
                        <div class="review-actions">
                            <button class="review-action edit-review-trigger" 
                                data-rid="${rev.ReviewID}" data-pid="${rev.PackageID}" data-title="${rev.Title}" data-rating="${rev.Rating}" data-comment="${rev.Comment}">
                                <i class="bi bi-pencil"></i> Edit
                            </button>
                            <button class="review-action delete-review-trigger" data-rid="${rev.ReviewID}">
                                <i class="bi bi-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                </div>`;
            });
        } else {
            html += `<p class="review-text-light">You haven't written any reviews yet.</p>`;
        }
        html += `</div>`;

        workspace.innerHTML = html;

    } catch (error) {
        console.error("Error loading traveller data:", error);
        workspace.innerHTML = '<p style="padding: 20px; color: #ff6b6b;">Failed to load reviews. Please try again.</p>';
    }
}

// =========================================================================
// AGENCY DASHBOARD LOGIC
// =========================================================================
async function loadAgencyData() {
    const dashboard = document.querySelector('.review-summary-dashboard');
    const reviewSection = document.querySelector('.review-section');
    if (!dashboard || !reviewSection) return;

    try {
        const res = await fetch(API_BASE_URL + '?route=/api/review/agency', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ agency_id: userStorage.id })
        });
        const data = await res.json();

        if (!res.ok) throw new Error(data.error);

        // Render Metrics
        dashboard.innerHTML = `
            <div class="metric-card">
                <span class="metric-label">Total Reviews</span>
                <span class="metric-value">${data.metrics.total_reviews || 0}</span>
            </div>
            <div class="metric-card">
                <span class="metric-label">Avg. Package Rating</span>
                <div class="metric-stars-row">
                    <span class="metric-value">${data.metrics.avg_rating || 0}</span>
                    ${generateStarsHTML(Math.round(data.metrics.avg_rating || 0))}
                </div>
            </div>`;

        // Render Review Feed
        let feedHtml = `<h2 class="section-title"><i class="bi bi-chat-left-text"></i> Package Reviews</h2>`;

        if (data.reviews && data.reviews.length > 0) {
            data.reviews.forEach(rev => {
                feedHtml += `
                <div class="review-bubble">
                    <div class="review-header">
                        <div class="reviewer-info">
                            <div>
                                <h4 class="reviewer-name-light">${rev.FirstName} ${rev.Surname.charAt(0)}.</h4>
                                <span class="review-date-light">${formatDate(rev.CreatedAt)}</span>
                            </div>
                        </div>
                        ${generateStarsHTML(rev.Rating)}
                    </div>
                    <p class="review-text-light">"${rev.Comment}"</p>
                    <div class="review-footer">
                        <span class="badge-package">${rev.PackageName}</span>
                    </div>
                </div>`;
            });
        } else {
            feedHtml += `<p style="padding: 20px; color: white;">No reviews yet.</p>`;
        }
        reviewSection.innerHTML = feedHtml;

    } catch (error) {
        console.error("Error loading agency data:", error);
        dashboard.innerHTML = '<p style="color: #ff6b6b;">Error loading metrics.</p>';
        reviewSection.innerHTML = '<p style="color: #ff6b6b;">Error loading reviews.</p>';
    }
}

// =========================================================================
// Bootstrapper
// =========================================================================
document.addEventListener("DOMContentLoaded", () => {
    if (userStorage) {
        if (userStorage.role === 'traveller') {
            loadTravellerData();
        } else if (userStorage.role === 'agency') {
            loadAgencyData();
        }
    }
});

function showToast(message, type = 'info') {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        document.body.appendChild(container);
    }

    let iconClass = 'bi-info-circle';
    if (type === 'success') iconClass = 'bi-check-circle';
    if (type === 'error') iconClass = 'bi bi-exclamation-circle';

    const toast = document.createElement('div');
    toast.className = `custom-toast ${type}`;
    toast.innerHTML = `
        <i class="bi ${iconClass}" style="font-size: 1.2rem;"></i>
        <div class="toast-message" style="flex-grow: 1;">${message}</div>
    `;

    container.appendChild(toast);

    setTimeout(() => toast.classList.add('show'), 50);

    setTimeout(() => {
        toast.classList.remove('show');
        toast.addEventListener('transitionend', () => toast.remove());
    }, 4000);
}