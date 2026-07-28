/**
 * Shared Sidebar JavaScript
 * Handles sidebar highlighting and desktop collapsing while leaving mobile navigation to the shared layout script.
 */

class SharedSidebar {
  constructor() {
    this.sidebar = document.querySelector('.sidebar');
    this.mainContent = document.querySelector('.main-content');
    this.toggleBtn = document.querySelector('.sidebar-toggle');
    this.menuItems = document.querySelectorAll('.sidebar-menu > li > a, .sidebar-menu > li > .menu-item');
    this.isMobile = window.innerWidth <= 768;

    this.init();
  }

  init() {
    if (!this.sidebar) return;

    this.attachEventListeners();
    this.setActiveMenuItem();
    this.handleResize();
  }

  attachEventListeners() {
    if (this.isMobile) {
      this.closeSidebarMobile();
      return;
    }

    if (this.toggleBtn) {
      this.toggleBtn.addEventListener('click', () => this.toggleSidebar());
    }

    this.menuItems.forEach((item) => {
      item.addEventListener('click', (e) => this.handleMenuItemClick(e, item));
    });

    this.menuItems.forEach((item) => {
      const submenu = item.nextElementSibling;
      if (submenu && submenu.classList.contains('submenu')) {
        item.style.cursor = 'pointer';
        item.addEventListener('click', (e) => {
          if (item.tagName === 'DIV') {
            e.preventDefault();
            this.toggleSubmenu(submenu);
          }
        });
      }
    });

    window.addEventListener('resize', () => this.handleResize());
  }

  handleMenuItemClick(event, item) {
    const submenu = item.nextElementSibling;

    if (submenu && submenu.classList.contains('submenu')) {
      event.preventDefault();
      this.toggleSubmenu(submenu);
      return;
    }

    if (item.tagName === 'A') {
      this.menuItems.forEach((m) => m.classList.remove('active'));
      item.classList.add('active');
    }
  }

  toggleSubmenu(submenu) {
    const isActive = submenu.classList.contains('active');

    document.querySelectorAll('.submenu').forEach((menu) => {
      menu.classList.remove('active');
    });

    if (!isActive) {
      submenu.classList.add('active');
    }
  }

  toggleSidebar() {
    if (this.isMobile) {
      this.closeSidebarMobile();
      return;
    }

    this.sidebar.classList.toggle('collapsed');
    this.mainContent?.classList.toggle('collapsed');
  }

  closeSidebarMobile() {
    this.sidebar.classList.remove('open');
    this.mainContent?.classList.remove('sidebar-open');
  }

  setActiveMenuItem() {
    const currentPath = window.location.pathname;
    const fileName = currentPath.substring(currentPath.lastIndexOf('/') + 1) || 'index.php';

    this.menuItems.forEach((item) => {
      const href = item.getAttribute('href');
      if (href && href.includes(fileName)) {
        this.menuItems.forEach((m) => {
          m.classList.remove('active');

          const submenu = m.nextElementSibling;
          if (submenu && submenu.classList.contains('submenu')) {
            submenu.querySelectorAll('a').forEach((link) => link.classList.remove('active'));
          }
        });

        item.classList.add('active');

        const parent = item.closest('.submenu')?.previousElementSibling;
        if (parent) {
          parent.classList.add('active');
          const submenu = item.closest('.submenu');
          if (submenu) {
            submenu.classList.add('active');
          }
        }
      }
    });

    document.querySelectorAll('.submenu a').forEach((link) => {
      const href = link.getAttribute('href');
      if (href && href.includes(fileName)) {
        link.classList.add('active');

        const submenu = link.closest('.submenu');
        if (submenu) {
          submenu.classList.add('active');
          const parent = submenu.previousElementSibling;
          if (parent) {
            parent.classList.add('active');
          }
        }
      }
    });
  }

  handleResize() {
    const wasOnMobile = this.isMobile;
    this.isMobile = window.innerWidth <= 768;

    if (wasOnMobile !== this.isMobile) {
      this.sidebar.classList.remove('open', 'collapsed');
      this.mainContent?.classList.remove('sidebar-open', 'collapsed');
    }
  }

  collapse() {
    if (!this.isMobile) {
      this.sidebar.classList.add('collapsed');
      this.mainContent?.classList.add('collapsed');
    }
  }

  expand() {
    if (!this.isMobile) {
      this.sidebar.classList.remove('collapsed');
      this.mainContent?.classList.remove('collapsed');
    }
  }

  open() {
    if (this.isMobile) {
      this.sidebar.classList.add('open');
      this.mainContent?.classList.add('sidebar-open');
    } else {
      this.expand();
    }
  }

  close() {
    if (this.isMobile) {
      this.sidebar.classList.remove('open');
      this.mainContent?.classList.remove('sidebar-open');
    } else {
      this.collapse();
    }
  }
}

document.addEventListener('DOMContentLoaded', () => {
  window.sharedSidebar = new SharedSidebar();
});
