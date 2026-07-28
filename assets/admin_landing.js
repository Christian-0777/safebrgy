// Example: add smooth scroll or interactivity
document.addEventListener('DOMContentLoaded', () => {
  const navToggle = document.getElementById('navToggle');
  const mobileNav = document.getElementById('mobileNav');

  if (navToggle && mobileNav) {
    navToggle.addEventListener('click', (event) => {
      event.stopPropagation();
      mobileNav.classList.toggle('open');
    });

    document.addEventListener('click', (event) => {
      if (!event.target.closest('.mobile-nav') && !event.target.closest('#navToggle')) {
        mobileNav.classList.remove('open');
      }
    });
  }
});
