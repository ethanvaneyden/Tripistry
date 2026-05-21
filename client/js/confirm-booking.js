const conmfirmButton = document.getElementById('confirm-booking-btn');
const bookingModal = document.getElementById('bookingModal');
const xButton = document.getElementById('close-btn');
const totalPassengers = document.getElementById('passengerCount');

const pricePerPerson = 14500;

function openBookingModal() {

    bookingModal.showModal(); 
}

function openBookingModal() {
    document.getElementById("passengerCount").value = 1;
    calculateTotalPrice();
    bookingModal.showModal(); 
}

function closeBookingModal() {
    bookingModal.close();
}

function calculateTotalPrice() {
    const passengers = parseInt(document.getElementById("passengerCount").value) || 1;
    const finalTotal = pricePerPerson * passengers;
    
    document.getElementById("totalPriceDisplay").innerText = `R${finalTotal.toLocaleString(undefined, {minimumFractionDigits: 2})}`;
}

conmfirmButton.addEventListener('click', openBookingModal);
xButton.addEventListener('click', closeBookingModal);
totalPassengers.addEventListener('input', calculateTotalPrice)
