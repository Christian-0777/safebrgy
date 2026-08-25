document.addEventListener('DOMContentLoaded', function() {
  const modal = document.getElementById('loginModal');
  const reportModal = document.getElementById('reportModal');
  const btns = document.querySelectorAll('.btn-request');
  const closeButtons = document.querySelectorAll('.close');
  const submitReportNow = document.getElementById('submitReportNow');
  const reportModalMessage = document.getElementById('reportModalMessage');

  btns.forEach(btn => {
    btn.addEventListener('click', function(e) {
      if (btn.dataset.service === 'Incident Report' || btn.dataset.service === 'Lost Property') {
        e.preventDefault();
        reportModalMessage.textContent = 'You can click Report Now to submit your immediate report.';
        reportModal.style.display = 'block';
        return;
      }

      // Only show modal if the button is NOT inside a form (i.e., for "Request now" buttons, not "Create Account")
      if (!btn.closest('form')) {
        e.preventDefault();
        modal.style.display = 'block';
      }
      // If inside a form, let the form submit naturally
    });
  });

  closeButtons.forEach(close => {
    close.addEventListener('click', function() {
      close.closest('.modal').style.display = 'none';
    });
  });

  window.addEventListener('click', function(event) {
    if (event.target === modal || event.target === reportModal) event.target.style.display = 'none';
  });

  if (submitReportNow) {
    submitReportNow.addEventListener('click', function() {
      reportModalMessage.textContent = 'Comming soon...';
    });
  }
});