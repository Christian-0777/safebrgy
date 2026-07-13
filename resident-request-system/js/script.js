document.addEventListener('DOMContentLoaded', function () {

  /* ---------------------------------------------------------
     1. Open / close modals
  --------------------------------------------------------- */
  const requestButtons = document.querySelectorAll('[data-modal]');
  const closeButtons   = document.querySelectorAll('[data-close]');
  const overlays       = document.querySelectorAll('.modal-overlay');

  requestButtons.forEach(function (btn) {
    btn.addEventListener('click', function () {
      const modal = document.getElementById(btn.getAttribute('data-modal'));
      if (modal) openModal(modal);
    });
  });

  closeButtons.forEach(function (btn) {
    btn.addEventListener('click', function () {
      const overlay = btn.closest('.modal-overlay');
      if (overlay) closeModal(overlay);
    });
  });

  overlays.forEach(function (overlay) {
    overlay.addEventListener('click', function (e) {
      if (e.target === overlay) closeModal(overlay);
    });
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      overlays.forEach(function (overlay) {
        if (overlay.classList.contains('active')) closeModal(overlay);
      });
    }
  });

  function openModal(modal) {
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
  }

  function closeModal(modal) {
    modal.classList.remove('active');
    document.body.style.overflow = '';
  }

  /* ---------------------------------------------------------
     2. Barangay Indigency: show "Other" text field when picked
  --------------------------------------------------------- */
  const indigencyPurpose  = document.getElementById('indigency-purpose');
  const indigencyOtherWrap = document.getElementById('indigency-other-wrap');
  const indigencyOtherInput = document.getElementById('indigency-other');

  if (indigencyPurpose) {
    indigencyPurpose.addEventListener('change', function () {
      if (this.value === 'Other') {
        indigencyOtherWrap.style.display = 'block';
      } else {
        indigencyOtherWrap.style.display = 'none';
        indigencyOtherInput.value = '';
      }
    });
  }

  /* ---------------------------------------------------------
     3. Handle submission for all 4 request forms via AJAX
  --------------------------------------------------------- */
  const forms = document.querySelectorAll('.request-form');

  forms.forEach(function (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      submitRequest(form);
    });
  });

  function submitRequest(form) {
    const alertBox   = form.querySelector('.form-alert');
    const submitBtn  = form.querySelector('button[type="submit"]');
    const docType    = form.getAttribute('data-doctype');
    const formData   = new FormData(form);

    formData.append('document_type', docType);

    if (alertBox) {
      alertBox.style.display = 'none';
      alertBox.textContent = '';
    }
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.textContent = 'Submitting...';
    }

    fetch('submit_request.php', {
      method: 'POST',
      body: formData
    })
      .then(function (res) { return res.json(); })
      .then(function (data) {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.textContent = 'Submit Request';
        }

        if (data.success) {
          // close the request modal, reset the form
          const overlay = form.closest('.modal-overlay');
          closeModal(overlay);
          form.reset();
          if (indigencyOtherWrap) indigencyOtherWrap.style.display = 'none';

          showConfirmation(docType, data.reference_no);
          addRowToTable(data.reference_no, docType, data.submitted_at);
        } else if (alertBox) {
          alertBox.textContent = data.message || 'Something went wrong. Please try again.';
          alertBox.style.display = 'block';
        } else {
          alert(data.message || 'Something went wrong. Please try again.');
        }
      })
      .catch(function () {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.textContent = 'Submit Request';
        }
        if (alertBox) {
          alertBox.textContent = 'Could not reach the server. Please try again.';
          alertBox.style.display = 'block';
        } else {
          alert('Could not reach the server. Please try again.');
        }
      });
  }

  /* ---------------------------------------------------------
     4. Confirmation modal content + open
  --------------------------------------------------------- */
  function showConfirmation(docType, referenceNo) {
    const confirmModal = document.getElementById('modal-confirm');
    document.getElementById('confirm-title').textContent = 'Request Submitted';
    document.getElementById('confirm-message').textContent =
      'Your ' + docType + ' has pending to review by our officials. We will send an email for updates.';
    document.getElementById('confirm-ref').textContent = 'Reference No: ' + referenceNo;
    openModal(confirmModal);
  }

  const confirmOkBtn = document.getElementById('confirm-ok');
  if (confirmOkBtn) {
    confirmOkBtn.addEventListener('click', function () {
      closeModal(document.getElementById('modal-confirm'));
    });
  }

  /* ---------------------------------------------------------
     5. Add the new request to the table without a page reload
  --------------------------------------------------------- */
  function addRowToTable(referenceNo, docType, submittedAt) {
    const tbody = document.getElementById('requestsTableBody');

    // remove the "no requests yet" placeholder row if present
    const emptyRow = tbody.querySelector('.empty-row');
    if (emptyRow) emptyRow.remove();

    const tr = document.createElement('tr');
    tr.innerHTML =
      '<td class="ref-no">' + referenceNo + '</td>' +
      '<td>' + docType + '</td>' +
      '<td>' + submittedAt + '</td>' +
      '<td><span class="status-pill status-pending">Pending</span></td>' +
      '<td><a href="#" class="action-link" onclick="return false;">View</a></td>';

    tbody.prepend(tr);
  }

});