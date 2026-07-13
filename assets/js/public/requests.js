document.addEventListener('DOMContentLoaded', function() {
  const documentCards = document.querySelectorAll('.document-card');
  const requestForm = document.getElementById('requestForm');
  const step1 = document.getElementById('step1');
  const step2 = document.getElementById('step2');
  const step3 = document.getElementById('step3');
  const step2Title = document.getElementById('step2Title');
  const backStep1 = document.getElementById('backStep1');
  const requestType = document.getElementById('requestType');
  const indigencyPurpose = document.getElementById('indigencyPurpose');
  const othersReasonDiv = document.getElementById('othersReasonDiv');
  const submitBtn = document.getElementById('submitBtn');
  const closeRequestModal = document.getElementById('closeRequestModal');

  let selectedType = null;

  // Step 1: Document Type Selection
  documentCards.forEach(card => {
    card.addEventListener('click', function() {
      selectedType = this.dataset.type;
      requestType.value = selectedType;
      step2Title.textContent = selectedType;
      
      // Show selected form section
      document.querySelectorAll('.form-section').forEach(section => {
        section.style.display = 'none';
      });
      document.getElementById('form-' + selectedType).style.display = 'block';
      
      // Move to Step 2
      step1.style.display = 'none';
      step2.style.display = 'block';
    });
  });

  // Back to Step 1
  backStep1.addEventListener('click', function() {
    step2.style.display = 'none';
    step1.style.display = 'block';
    requestForm.reset();
  });

  // Handle "Others" option for Indigency Purpose
  if (indigencyPurpose) {
    indigencyPurpose.addEventListener('change', function() {
      if (this.value === 'Others') {
        othersReasonDiv.style.display = 'block';
      } else {
        othersReasonDiv.style.display = 'none';
      }
    });
  }

  // Submit Form
  if (requestForm) {
    requestForm.addEventListener('submit', async function(e) {
      e.preventDefault();
      
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';

      const formData = new FormData(this);
      
      try {
        const response = await fetch('../../api/requests/create.php', {
          method: 'POST',
          body: formData
        });

        const data = await response.json();

        if (data.success) {
          // Show Step 3 Confirmation
          step2.style.display = 'none';
          step3.style.display = 'block';
          
          document.getElementById('confirmRefNo').textContent = data.request_number;
          document.getElementById('confirmDate').textContent = data.created_at;
          document.getElementById('confirmMessage').textContent = `Your '${selectedType}' is pending to review by our officials, We will send you an email for updates.`;
          
          // Close modal on final step
          closeRequestModal.style.display = 'none';
        } else {
          alert('Error: ' + (data.message || 'Failed to create request'));
          submitBtn.disabled = false;
          submitBtn.innerHTML = 'Submit Request';
        }
      } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while submitting your request');
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Submit Request';
      }
    });
  }

  // Reset form when modal is closed
  const requestModal = document.getElementById('requestModal');
  if (requestModal) {
    requestModal.addEventListener('hidden.bs.modal', function() {
      // Reset all steps
      step1.style.display = 'block';
      step2.style.display = 'none';
      step3.style.display = 'none';
      closeRequestModal.style.display = '';
      requestForm.reset();
      selectedType = null;
      submitBtn.disabled = false;
      submitBtn.innerHTML = 'Submit Request';
    });
  }
});
