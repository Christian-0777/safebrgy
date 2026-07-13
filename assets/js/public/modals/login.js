document.addEventListener('DOMContentLoaded', function() {
  const modal = document.getElementById('loginModal');
  const btns = document.querySelectorAll('.btn-request');
  const close = document.querySelector('.close');

  btns.forEach(btn => {
    btn.addEventListener('click', function(e) {
      // Only show modal if the button is NOT inside a form (i.e., for "Request now" buttons, not "Create Account")
      if (!btn.closest('form')) {
        e.preventDefault();
        modal.style.display = 'block';
      }
      // If inside a form, let the form submit naturally
    });
  });

  close.addEventListener('click', function() {
    modal.style.display = 'none';
  });

  window.addEventListener('click', function(event) {
    if (event.target == modal) {
      modal.style.display = 'none';
    }
  });
});