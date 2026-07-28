document.addEventListener('DOMContentLoaded', function () {
  const header = document.querySelector('.header, .site-header');
  const sidebar = document.querySelector('.sidebar');
  const mainContent = document.querySelector('.main-content');
  const sidebarToggle = document.querySelector('.sidebar-toggle');
  const landingToggle = document.querySelector('.nav-toggle, [data-nav-toggle]');
  const toggleButton = sidebarToggle || landingToggle;
  let backdrop = document.querySelector('.sidebar-backdrop');
  let mobileNav = document.querySelector('.mobile-nav');

  if (!backdrop && sidebar) {
    backdrop = document.createElement('div');
    backdrop.className = 'sidebar-backdrop';
    document.body.appendChild(backdrop);
  }

  if (!mobileNav && header) {
    mobileNav = document.createElement('nav');
    mobileNav.className = 'mobile-nav';
    mobileNav.id = 'mobileNav';
    header.appendChild(mobileNav);
  }

  const buildMobileNav = () => {
    if (!mobileNav) {
      return;
    }

    mobileNav.innerHTML = '';

    const sidebarLinks = Array.from(document.querySelectorAll('.sidebar-menu a'));
    let linksToUse = sidebarLinks;

    if (!linksToUse.length) {
      linksToUse = Array.from(document.querySelectorAll('.main-nav a, .admin-nav a, .header-nav a'));
    }

    if (!linksToUse.length) {
      linksToUse = Array.from(document.querySelectorAll('.header-actions a'));
    }

    linksToUse.forEach((link) => {
      const clone = link.cloneNode(true);
      clone.classList.remove('btn-outline', 'btn-primary', 'active');
      clone.classList.add('mobile-nav-link');
      mobileNav.appendChild(clone);
    });

    if (!mobileNav.children.length) {
      const fallbackLink = document.createElement('a');
      fallbackLink.href = 'index.php';
      fallbackLink.textContent = 'Home';
      fallbackLink.className = 'mobile-nav-link';
      mobileNav.appendChild(fallbackLink);
    }
  };

  buildMobileNav();

  const closeMobileNav = () => {
    mobileNav?.classList.remove('open');
  };

  const closeSidebar = () => {
    if (sidebar) {
      sidebar.classList.remove('open');
    }
    if (mainContent) {
      mainContent.classList.remove('sidebar-open');
    }
    if (backdrop) {
      backdrop.classList.remove('show');
    }
  };

  const openSidebar = () => {
    if (sidebar) {
      sidebar.classList.add('open');
    }
    if (mainContent) {
      mainContent.classList.add('sidebar-open');
    }
    if (backdrop) {
      backdrop.classList.add('show');
    }
  };

  if (toggleButton) {
    toggleButton.addEventListener('click', function (event) {
      event.stopPropagation();

      if (window.innerWidth <= 768) {
        const shouldOpen = !mobileNav?.classList.contains('open');
        closeSidebar();
        mobileNav?.classList.toggle('open', shouldOpen);
        return;
      }

      if (sidebar && sidebar.classList.contains('open')) {
        closeSidebar();
      } else if (sidebar) {
        openSidebar();
      }
      closeMobileNav();
    });
  }

  if (backdrop) {
    backdrop.addEventListener('click', function () {
      closeSidebar();
      closeMobileNav();
    });
  }

  document.addEventListener('click', function (event) {
    if (!event.target.closest('.mobile-nav') && !event.target.closest('.sidebar-toggle') && !event.target.closest('.nav-toggle') && !event.target.closest('[data-nav-toggle]')) {
      closeMobileNav();
    }
    if (!event.target.closest('.sidebar') && !event.target.closest('.sidebar-toggle')) {
      closeSidebar();
    }
  });

  window.addEventListener('resize', function () {
    if (window.innerWidth > 768) {
      closeSidebar();
      closeMobileNav();
    }
  });
});
