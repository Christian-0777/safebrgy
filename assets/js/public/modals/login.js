document.addEventListener('DOMContentLoaded', function() {
  // --- Nav toggle (burger menu) ---
  var navToggle = document.getElementById('navToggle');
  var mainNav = document.querySelector('.main-nav');

  if (navToggle && mainNav) {
    navToggle.addEventListener('click', function() {
      mainNav.classList.toggle('open');
    });

    // Close menu when a nav link is clicked (mobile)
    mainNav.querySelectorAll('a').forEach(function(link) {
      link.addEventListener('click', function() {
        mainNav.classList.remove('open');
      });
    });
  }

  // --- Login modal ---
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