// -----------------------------------------------------------------------
// State — populated by package-details.js via initBookingModal()
// -----------------------------------------------------------------------
let _packageId   = null;
let _packageTitle = '';
let _packageDates = '';
let _pricePerPerson = 0;
let _maxParticipants = null;

// -----------------------------------------------------------------------
// Called by package-details.js once the package data is loaded
// -----------------------------------------------------------------------
function initBookingModal({ packageId, title, startDate, endDate, nights, pricePerPerson, maxParticipants }) {
    _packageId       = packageId;
    _packageTitle    = title;
    _pricePerPerson  = pricePerPerson;
    _maxParticipants = maxParticipants;
    _packageDates    = `📅 ${formatDateRange(startDate, endDate, nights)}`;

    if (maxParticipants) {
        document.getElementById('passengerCount').max = maxParticipants;
    }
}

// -----------------------------------------------------------------------
// Helpers
// -----------------------------------------------------------------------
function formatDateRange(start, end, nights) {
    const fmt = (s) => new Date(s).toLocaleDateString('en-ZA', { day: 'numeric', month: 'long' });
    return `${fmt(start)} – ${fmt(end)} (${nights} night${nights !== 1 ? 's' : ''})`;
}

function formatPrice(amount) {
    return new Intl.NumberFormat('en-ZA', {
        style: 'currency', currency: 'ZAR', maximumFractionDigits: 2
    }).format(amount);
}

// -----------------------------------------------------------------------
// Modal open / close
// -----------------------------------------------------------------------
function openBookingModal() {
    const user = JSON.parse(sessionStorage.getItem('user') || 'null');
    if (!user || user.role !== 'traveller') {
        alert('Please log in as a traveller to book this package.');
        return;
    }

    // Populate modal header
    document.getElementById('modalPackageTitle').textContent = _packageTitle;
    document.getElementById('modalPackageDates').textContent = _packageDates;

    // Reset inputs
    document.getElementById('passengerCount').value = 1;
    calculateTotalPrice();

    // Clear any previous feedback
    setFeedback('', '');

    document.getElementById('bookingModal').showModal();
}

function closeBookingModal() {
    document.getElementById('bookingModal').close();
}

// -----------------------------------------------------------------------
// Live price recalculation
// -----------------------------------------------------------------------
function calculateTotalPrice() {
    const passengers = parseInt(document.getElementById('passengerCount').value) || 1;
    const total = _pricePerPerson * passengers;
    document.getElementById('pricePerPersonDisplay').textContent = formatPrice(_pricePerPerson);
    document.getElementById('totalPriceDisplay').textContent     = formatPrice(total);
}

// -----------------------------------------------------------------------
// Feedback helper
// -----------------------------------------------------------------------
function setFeedback(message, type) {
    const el = document.getElementById('bookingFeedback');
    if (!el) return;
    el.textContent  = message;
    el.className    = `booking-feedback ${type}`;  // 'success' | 'error' | ''
    el.style.display = message ? 'block' : 'none';
}

// -----------------------------------------------------------------------
// Submit booking to backend
// -----------------------------------------------------------------------
async function submitBookingToBackend() {
    const user = JSON.parse(sessionStorage.getItem('user') || 'null');
    if (!user || user.role !== 'traveller') {
        alert('Session expired. Please log in again.');
        return;
    }

    const passengers = parseInt(document.getElementById('passengerCount').value) || 1;
    if (passengers < 1) {
        setFeedback('Please enter at least 1 traveller.', 'error');
        return;
    }
    if (_maxParticipants && passengers > _maxParticipants) {
        setFeedback(`This package allows a maximum of ${_maxParticipants} participants.`, 'error');
        return;
    }

    const finalizeBtn = document.querySelector('.finalize-btn');
    finalizeBtn.disabled   = true;
    finalizeBtn.textContent = 'Processing…';
    setFeedback('', '');

    try {
        const res = await fetch(`${API_BASE}/api/booking/create`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
            body: JSON.stringify({
                traveller_id:     user.id,
                package_id:       _packageId,
                number_of_people: passengers
            })
        });

        const data = await res.json();

        if (!res.ok) {
            const msg = data.spots_left !== undefined
                ? `Only ${data.spots_left} spot${data.spots_left !== 1 ? 's' : ''} left — please reduce your group size.`
                : (data.error || 'Booking failed. Please try again.');
            setFeedback(msg, 'error');
            return;
        }

        // Success — show confirmation then redirect to bookings page
        setFeedback(`Booking confirmed! Your reference: #${data.booking_id}`, 'success');
        finalizeBtn.textContent = 'Booked!';

        setTimeout(() => {
            closeBookingModal();
            window.location.href = 'travellerpackages.html';
        }, 2000);

    } catch (err) {
        setFeedback('Network error. Please check your connection and try again.', 'error');
        console.error('Booking error:', err);
    } finally {
        if (finalizeBtn.textContent === 'Processing…') {
            finalizeBtn.disabled    = false;
            finalizeBtn.textContent = 'Confirm & Pay';
        }
    }
}

// -----------------------------------------------------------------------
// Event listeners
// -----------------------------------------------------------------------
document.getElementById('confirm-booking-btn').addEventListener('click', openBookingModal);
document.getElementById('close-btn').addEventListener('click', closeBookingModal);
document.getElementById('passengerCount').addEventListener('input', calculateTotalPrice);
