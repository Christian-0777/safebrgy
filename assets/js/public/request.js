document.addEventListener('DOMContentLoaded', function () {
  const overlays = document.querySelectorAll('.modal-overlay');
  const requestButtons = document.querySelectorAll('[data-modal]');
  const closeButtons = document.querySelectorAll('[data-close]');
  const forms = document.querySelectorAll('.request-form');
  const confirmOkButton = document.getElementById('confirm-ok');

  function openModal(modal) {
    if (!modal) return;
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
  }

  function closeModal(modal) {
    if (!modal) return;
    modal.classList.remove('active');
    document.body.style.overflow = '';
  }

  requestButtons.forEach(function (button) {
    button.addEventListener('click', function () {
      const modal = document.getElementById(this.getAttribute('data-modal'));
      openModal(modal);
    });
  });

  closeButtons.forEach(function (button) {
    button.addEventListener('click', function () {
      const modal = button.closest('.modal-overlay');
      closeModal(modal);
    });
  });

  overlays.forEach(function (overlay) {
    overlay.addEventListener('click', function (event) {
      if (event.target === overlay) {
        closeModal(overlay);
      }
    });
  });

  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
      overlays.forEach(function (overlay) {
        if (overlay.classList.contains('active')) {
          closeModal(overlay);
        }
      });
    }
  });

  const indigencyPurpose = document.getElementById('indigency-purpose');
  const indigencyOtherWrap = document.getElementById('indigency-other-wrap');
  const indigencyOtherInput = document.getElementById('indigency-other');

  if (indigencyPurpose) {
    indigencyPurpose.addEventListener('change', function () {
      if (this.value === 'Other') {
        indigencyOtherWrap.style.display = 'block';
      } else {
        indigencyOtherWrap.style.display = 'none';
        if (indigencyOtherInput) indigencyOtherInput.value = '';
      }
    });
  }

  forms.forEach(function (form) {
    form.addEventListener('submit', function (event) {
      event.preventDefault();
      submitRequest(form);
    });
  });

  function submitRequest(form) {
    const alertBox = form.querySelector('.form-alert');
    const submitButton = form.querySelector('button[type="submit"]');
    const formData = new FormData(form);
    const docType = form.getAttribute('data-doctype');

    if (alertBox) {
      alertBox.style.display = 'none';
      alertBox.textContent = '';
    }

    if (submitButton) {
      submitButton.disabled = true;
      submitButton.textContent = 'Submitting...';
    }

    formData.append('document_type', docType);

    fetch('../../api/requests/create.php', {
      method: 'POST',
      body: formData
    })
      .then(function (response) {
        return response.json();
      })
      .then(function (data) {
        if (submitButton) {
          submitButton.disabled = false;
          submitButton.textContent = 'Submit Request';
        }

        if (data.success) {
          const overlay = form.closest('.modal-overlay');
          closeModal(overlay);
          form.reset();
          if (indigencyOtherWrap) indigencyOtherWrap.style.display = 'none';

          showSuccess(docType, data.reference_no);
          addRowToTable(data.reference_no, docType, data.submitted_at);
        } else if (alertBox) {
          alertBox.textContent = data.message || 'Unable to submit your request right now.';
          alertBox.style.display = 'block';
        } else {
          alert(data.message || 'Unable to submit your request right now.');
        }
      })
      .catch(function () {
        if (submitButton) {
          submitButton.disabled = false;
          submitButton.textContent = 'Submit Request';
        }
        if (alertBox) {
          alertBox.textContent = 'Could not reach the server. Please try again.';
          alertBox.style.display = 'block';
        }
      });
  }

  function showSuccess(docType, referenceNo) {
    const confirmModal = document.getElementById('modal-confirm');
    const title = document.getElementById('confirm-title');
    const message = document.getElementById('confirm-message');
    const reference = document.getElementById('confirm-ref');

    if (title) {
      title.textContent = 'Request Submitted';
    }
    if (message) {
      message.textContent = 'Your ' + docType + ' request is now pending review by the barangay staff.';
    }
    if (reference) {
      reference.textContent = 'Reference No: ' + referenceNo;
    }

    openModal(confirmModal);
  }

  function addRowToTable(referenceNo, docType, submittedAt) {
    const tbody = document.getElementById('requests-table-body');
    if (!tbody) return;

    const emptyRow = tbody.querySelector('.empty-state-row');
    if (emptyRow) {
      emptyRow.remove();
    }

    const row = document.createElement('tr');
    row.innerHTML = [
      '<td><strong>' + referenceNo + '</strong></td>',
      '<td>' + docType + '</td>',
      '<td>' + submittedAt + '</td>',
      '<td><span class="status-pill status-pending">Pending</span></td>',
      '<td><span class="text-muted">Submitted</span></td>'
    ].join('');

    tbody.prepend(row);
  }

  if (confirmOkButton) {
    confirmOkButton.addEventListener('click', function () {
      closeModal(document.getElementById('modal-confirm'));
    });
  }
});
