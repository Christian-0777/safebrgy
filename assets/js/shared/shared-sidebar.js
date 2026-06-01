/**
 * Shared Sidebar JavaScript
 * Handles sidebar functionality including collapsing, menu navigation, and mobile responsiveness
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
    // Toggle sidebar on button click
    if (this.toggleBtn) {
      this.toggleBtn.addEventListener('click', () => this.toggleSidebar());
    }

    // Menu item click handlers
    this.menuItems.forEach((item) => {
      item.addEventListener('click', (e) => {
        this.handleMenuItemClick(e, item);
      });
    });

    // Submenu toggle
    this.menuItems.forEach((item) => {
      const submenu = item.nextElementSibling;
      if (submenu && submenu.classList.contains('submenu')) {
        item.style.cursor = 'pointer';
        item.addEventListener('click', (e) => {
          // Only prevent default if it's not a link
          if (item.tagName === 'DIV') {
            e.preventDefault();
            this.toggleSubmenu(submenu);
          }
        });
      }
    });

    // Close sidebar when clicking on main content (mobile)
    if (this.isMobile && this.mainContent) {
      this.mainContent.addEventListener('click', () => this.closeSidebarMobile());
    }

    // Handle window resize
    window.addEventListener('resize', () => this.handleResize());

    // Close sidebar on link click (mobile)
    const sidebarLinks = this.sidebar.querySelectorAll('a');
    sidebarLinks.forEach((link) => {
      link.addEventListener('click', () => {
        if (this.isMobile && this.sidebar.classList.contains('open')) {
          this.closeSidebarMobile();
        }
      });
    });
  }

  handleMenuItemClick(event, item) {
    const submenu = item.nextElementSibling;

    // If there's a submenu, toggle it
    if (submenu && submenu.classList.contains('submenu')) {
      event.preventDefault();
      this.toggleSubmenu(submenu);
      return;
    }

    // Set active state for regular links
    if (item.tagName === 'A') {
      this.menuItems.forEach((m) => m.classList.remove('active'));
      item.classList.add('active');
    }
  }

  toggleSubmenu(submenu) {
    const isActive = submenu.classList.contains('active');

    // Close all other submenus
    document.querySelectorAll('.submenu').forEach((menu) => {
      menu.classList.remove('active');
    });

    // Toggle current submenu
    if (!isActive) {
      submenu.classList.add('active');
    }
  }

  toggleSidebar() {
    if (this.isMobile) {
      this.sidebar.classList.toggle('open');
      this.mainContent?.classList.toggle('sidebar-open');
    } else {
      this.sidebar.classList.toggle('collapsed');
      this.mainContent?.classList.toggle('collapsed');
    }
  }

  closeSidebarMobile() {
    if (this.isMobile) {
      this.sidebar.classList.remove('open');
      this.mainContent?.classList.remove('sidebar-open');
    }
  }

  setActiveMenuItem() {
    const currentPath = window.location.pathname;
    const fileName = currentPath.substring(currentPath.lastIndexOf('/') + 1) || 'index.php';

    this.menuItems.forEach((item) => {
      const href = item.getAttribute('href');
      if (href && href.includes(fileName)) {
        this.menuItems.forEach((m) => {
          m.classList.remove('active');

          // Remove active from submenu items
          const submenu = m.nextElementSibling;
          if (submenu && submenu.classList.contains('submenu')) {
            submenu.querySelectorAll('a').forEach((link) => link.classList.remove('active'));
          }
        });

        item.classList.add('active');

        // Activate parent menu if item is in submenu
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

    // Check submenu items
    document.querySelectorAll('.submenu a').forEach((link) => {
      const href = link.getAttribute('href');
      if (href && href.includes(fileName)) {
        link.classList.add('active');

        // Activate parent menu
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

    // If switching from desktop to mobile or vice versa
    if (wasOnMobile !== this.isMobile) {
      this.sidebar.classList.remove('open', 'collapsed');
      this.mainContent?.classList.remove('sidebar-open', 'collapsed');
    }
  }

  // Public methods to control sidebar from other scripts
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

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
  window.sharedSidebar = new SharedSidebar();
});
