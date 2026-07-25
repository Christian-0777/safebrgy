document.addEventListener('DOMContentLoaded', function () {
  const sidebar = document.querySelector('.sidebar');
  const mainContent = document.querySelector('.main-content');
  const sidebarToggle = document.querySelector('.sidebar-toggle');
  let backdrop = document.querySelector('.sidebar-backdrop');
  const navToggle = document.querySelector('[data-nav-toggle]');
  let mobileNav = document.querySelector('.mobile-nav');

  if (!backdrop && sidebar) {
    backdrop = document.createElement('div');
    backdrop.className = 'sidebar-backdrop';
    document.body.appendChild(backdrop);
  }

  if (!mobileNav && document.querySelector('.main-nav')) {
    mobileNav = document.createElement('nav');
    mobileNav.className = 'mobile-nav';
    const links = Array.from(document.querySelectorAll('.main-nav a'));
    links.forEach((link) => {
      const clone = link.cloneNode(true);
      clone.classList.remove('btn-outline', 'btn-primary');
      mobileNav.appendChild(clone);
    });
    document.querySelector('.site-header')?.appendChild(mobileNav);
  }

  const closeMobileNav = () => {
    if (mobileNav) {
      mobileNav.classList.remove('open');
    }
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

  if (sidebarToggle) {
    sidebarToggle.addEventListener('click', function (event) {
      event.stopPropagation();
      if (sidebar && sidebar.classList.contains('open')) {
        closeSidebar();
      } else {
        openSidebar();
        closeMobileNav();
      }
    });
  }

  if (backdrop) {
    backdrop.addEventListener('click', closeSidebar);
  }

  if (navToggle && mobileNav) {
    navToggle.addEventListener('click', function (event) {
      event.stopPropagation();
      mobileNav.classList.toggle('open');
      closeSidebar();
    });
  }

  document.addEventListener('click', function (event) {
    if (!event.target.closest('.mobile-nav') && !event.target.closest('[data-nav-toggle]')) {
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
