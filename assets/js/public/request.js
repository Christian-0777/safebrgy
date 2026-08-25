document.addEventListener('DOMContentLoaded', function () {
  const overlays = document.querySelectorAll('.modal-overlay');

  window.addEventListener('beforeunload', function () {
    if (window.showLoadingOverlay) {
      window.showLoadingOverlay();
    }
  });
  const requestButtons = document.querySelectorAll('[data-modal]');
  const closeButtons = document.querySelectorAll('[data-close]');
  const forms = document.querySelectorAll('.request-form');
  const confirmOkButton = document.getElementById('confirm-ok');
  const requestsTableBody = document.getElementById('requests-table-body');
  const copyReferenceButton = document.getElementById('copy-reference-btn');

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

  if (requestsTableBody) {
    requestsTableBody.addEventListener('click', function (event) {
      const button = event.target.closest('.view-request-btn');
      if (!button) return;

      const modal = document.getElementById('modal-request-details');
      const title = document.getElementById('requestDetailsTitle');
      const referenceNo = document.getElementById('detail-reference-no');
      const documentType = document.getElementById('detail-document-type');
      const submittedAt = document.getElementById('detail-submitted-at');
      const purpose = document.getElementById('detail-purpose');
      const status = document.getElementById('detail-status');

      if (title) title.textContent = 'Request Details';
      if (referenceNo) referenceNo.textContent = button.dataset.referenceNo || 'N/A';
      if (documentType) documentType.textContent = button.dataset.documentType || 'N/A';
      if (submittedAt) {
        submittedAt.textContent = button.dataset.submittedAt || 'N/A';
      }
      if (purpose) {
        purpose.textContent = button.dataset.purpose || 'N/A';
      }
      if (status) {
        status.textContent = button.dataset.status || 'N/A';
      }

      openModal(modal);
    });
  }

  if (copyReferenceButton) {
    copyReferenceButton.addEventListener('click', function () {
      const referenceElement = document.getElementById('detail-reference-no');
      const referenceNo = referenceElement ? referenceElement.textContent.trim() : '';
      if (!referenceNo || referenceNo === 'N/A') return;

      const showCopiedState = function () {
        copyReferenceButton.innerHTML = '<i class="fas fa-check"></i>';
        copyReferenceButton.title = 'Copied';
        window.setTimeout(function () {
          copyReferenceButton.innerHTML = '<i class="fas fa-copy"></i>';
          copyReferenceButton.title = 'Copy reference number';
        }, 1500);
      };

      if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(referenceNo).then(showCopiedState);
        return;
      }

      const temporaryInput = document.createElement('textarea');
      temporaryInput.value = referenceNo;
      temporaryInput.style.position = 'fixed';
      temporaryInput.style.opacity = '0';
      document.body.appendChild(temporaryInput);
      temporaryInput.select();
      if (document.execCommand('copy')) showCopiedState();
      temporaryInput.remove();
    });
  }

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
    const formPurpose = getFormPurpose(form);

    if (alertBox) {
      alertBox.style.display = 'none';
      alertBox.textContent = '';
    }

    if (window.showLoadingOverlay) {
      window.showLoadingOverlay();
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
        if (window.hideLoadingOverlay) {
          window.hideLoadingOverlay();
        }

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
          addRowToTable(data.reference_no, docType, data.submitted_at, formPurpose, 'Pending');
        } else if (alertBox) {
          alertBox.textContent = data.message || 'Unable to submit your request right now.';
          alertBox.style.display = 'block';
        } else {
          alert(data.message || 'Unable to submit your request right now.');
        }
      })
      .catch(function () {
        if (window.hideLoadingOverlay) {
          window.hideLoadingOverlay();
        }

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

  function getFormPurpose(form) {
    const purposeField = form.querySelector('[name="purpose"]');
    let purpose = purposeField ? purposeField.value.trim() : '';

    if (!purpose) {
      return 'No purpose provided';
    }

    if (purpose === 'Other') {
      const otherField = form.querySelector('[name="purpose_other"]');
      return otherField && otherField.value.trim() ? otherField.value.trim() : 'Other';
    }

    return purpose;
  }

  function escapeHtml(text) {
    return String(text).replace(/[&<>"']/g, function (match) {
      return {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;'
      }[match];
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

  function addRowToTable(referenceNo, docType, submittedAt, purpose, status) {
    const tbody = document.getElementById('requests-table-body');
    if (!tbody) return;

    const emptyRow = tbody.querySelector('.empty-state-row');
    if (emptyRow) {
      emptyRow.remove();
    }

    const row = document.createElement('tr');
    row.innerHTML = [
      '<td><strong>' + escapeHtml(referenceNo) + '</strong></td>',
      '<td>' + escapeHtml(docType) + '</td>',
      '<td>' + escapeHtml(submittedAt) + '</td>',
      '<td><span class="status-pill status-pending">' + escapeHtml(status) + '</span></td>',
      '<td><button type="button" class="btn btn-outline view-request-btn" '
        + 'data-document-type="' + escapeHtml(docType) + '" '
        + 'data-reference-no="' + escapeHtml(referenceNo) + '" '
        + 'data-submitted-at="' + escapeHtml(submittedAt) + '" '
        + 'data-status="' + escapeHtml(status) + '" '
        + 'data-purpose="' + escapeHtml(purpose) + '">View</button></td>'
    ].join('');

    tbody.prepend(row);
  }

  if (confirmOkButton) {
    confirmOkButton.addEventListener('click', function () {
      closeModal(document.getElementById('modal-confirm'));
    });
  }
});
