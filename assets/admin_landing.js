// SafeBrgy Admin Landing Page — burger menu toggle
document.addEventListener('DOMContentLoaded', () => {
  console.log("SafeBrgy Admin Landing Page loaded.");

  const navToggle = document.getElementById('navToggle');
  const adminNav  = document.getElementById('adminNav');

  if (!navToggle || !adminNav) return;

  // Toggle dropdown menu on burger click
  navToggle.addEventListener('click', (e) => {
    e.stopPropagation();
    const isOpen = adminNav.classList.toggle('open');
    navToggle.classList.toggle('active', isOpen);
    navToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
  });

  // Close menu when a nav link is clicked
  adminNav.querySelectorAll('a').forEach(link => {
    link.addEventListener('click', () => {
      adminNav.classList.remove('open');
      navToggle.classList.remove('active');
      navToggle.setAttribute('aria-expanded', 'false');
    });
  });

  // Close menu on outside click
  document.addEventListener('click', (e) => {
    if (!adminNav.contains(e.target) && !navToggle.contains(e.target)) {
      adminNav.classList.remove('open');
      navToggle.classList.remove('active');
      navToggle.setAttribute('aria-expanded', 'false');
    }
  });
});
