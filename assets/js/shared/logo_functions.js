(function () {
  function handleLogoClick(event) {
    const logoLink = event.target.closest('.header-logo, .brand');

    if (!logoLink) return;

    const href = logoLink.getAttribute('href') || '';

    if (!href || href === '#' || href.startsWith('#')) {
      event.preventDefault();
      window.location.href = 'index.php';
      return;
    }

    event.preventDefault();
    window.location.href = href;
  }

  function initLogoHandlers() {
    document.addEventListener('click', handleLogoClick);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initLogoHandlers);
  } else {
    initLogoHandlers();
  }
})();
