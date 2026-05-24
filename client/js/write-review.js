const writeReviewBtn = document.getElementById('write-review');
let xButton = document.getElementById('close-btn');
const modal = document.getElementById('review-modal');


function renderModal(packageName, existingReview = null) {
    const isEdit = existingReview !== null;
    const modalTitle = isEdit ? `Edit your review` : `Write a review`;
    const currentText = isEdit ? existingReview.text : '';
    const currentRating = isEdit ? existingReview.rating : 0;

    modal.innerHTML = `
      <div class="modal-header">
        <h2><i class="bi bi-pen"></i> ${modalTitle}</h2>
        <button class="close-btn" id="close-btn">×</button>
      </div>
      <hr class='modal-divider'>
      <div class="rating-section">
        <h3>How was your "${packageName} Trip"?</h3>
        <div class="stars-review">
          <input type="radio" name="rating" id="star5" value="5" ${currentRating === 5 ? 'checked' : ''} />
          <label for="star5"><i class="bi bi-star"></i></label>
          
          <input type="radio" name="rating" id="star4" value="4" ${currentRating === 4 ? 'checked' : ''} />
          <label for="star4"><i class="bi bi-star"></i></label>

          <input type="radio" name="rating" id="star3" value="3" ${currentRating === 3 ? 'checked' : ''} />
          <label for="star3"><i class="bi bi-star"></i></label>

          <input type="radio" name="rating" id="star2" value="2" ${currentRating === 2 ? 'checked' : ''} />
          <label for="star2"><i class="bi bi-star"></i></label>

          <input type="radio" name="rating" id="star1" value="1" ${currentRating === 1 ? 'checked' : ''} />
          <label for="star1"><i class="bi bi-star"></i></label>
        </div>
      </div>
      <div class="text-box">
        <textarea class="review-text" id="review-text" placeholder="Write review">${currentText}</textarea>
      </div>
      <div class="modal-footer">
        <button class="publish-btn" id="submit-btn" data-action="${isEdit ? 'update' : 'create'}" data-id="${isEdit ? existingReview.id : ''}">
            ${isEdit ? 'Save Changes' : 'Submit review'}
        </button>
      </div>`;
}


writeReviewBtn.addEventListener('click', showModal)

modal.addEventListener('click', (event) => {
    if (event.target.id === 'close-btn') {
        modal.close();
    }
});

function showModal() {
    renderModal('Cape Town');
    xButton = document.getElementById('close-btn');
    modal.showModal()
}

