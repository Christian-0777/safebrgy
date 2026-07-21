// Admin landing page scripts
document.addEventListener('DOMContentLoaded', function() {
  // --- Nav toggle (burger menu) ---
  var navToggle = document.getElementById('navToggle');
  var adminNav = document.getElementById('adminNav');

  if (navToggle && adminNav) {
    navToggle.addEventListener('click', function() {
      adminNav.classList.toggle('open');
    });
  }

  // Close menu when a nav link is clicked (mobile)
  if (adminNav) {
    adminNav.querySelectorAll('a').forEach(function(link) {
      link.addEventListener('click', function() {
        adminNav.classList.remove('open');
      });
    });
  }
});
