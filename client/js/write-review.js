const writeReviewBtn = document.getElementById('write-review');
let xButton = document.getElementById('close-btn');
const modal = document.getElementById('review-modal');


function renderModal(packageName) {
    modal.innerHTML = `<div class="modal-header">
        <h2><i class="bi bi-pen"></i>Write a review</h2>
        <button class="close-btn" id="close-btn">×</button>
      </div>
      <hr class='modal-divider'>
      <div class="rating-section">
        <div class="rating-header">
          <h3>How was your "${packageName}" trip?</h3>
        </div>
        <div class="stars">
          <input type="radio" name="rating" id="star5" value="5" />
          <label for="star5"><i class="bi bi-star"></i></label>

          <input type="radio" name="rating" id="star4" value="4" />
          <label for="star4"><i class="bi bi-star"></i></label>

          <input type="radio" name="rating" id="star3" value="3" />
          <label for="star3"><i class="bi bi-star"></i></label>

          <input type="radio" name="rating" id="star2" value="2" />
          <label for="star2"><i class="bi bi-star"></i></label>

          <input type="radio" name="rating" id="star1" value="1" />
          <label for="star1"><i class="bi bi-star"></i></label>
        </div>
      </div>
      <div class="text-box">
        <textarea
          class="review-text"
          id="review-text"
          placeholder="Write review"
        ></textarea>
      </div>
      <div class="modal-footer">
        <button class="publish-btn" id="cancel-btn">Submit review</button>
      </div>`
}

writeReviewBtn.addEventListener('click', () => {
    renderModal('Cape Town');
    xButton = document.getElementById('close-btn');
    modal.showModal()
})

modal.addEventListener('click', (event) => {
    if (event.target.id === 'close-btn') {
        modal.close();
    }
});


