/**
 * Shared Sidebar JavaScript
 * Handles sidebar functionality including collapsing, menu navigation,
 * and mobile responsiveness.
 *
 * Mobile behavior (≤768px): sidebar items are cloned into a horizontal
 * scrollable nav bar (.mobile-horizontal-nav) fixed below the header.
 * The vertical sidebar is hidden; no slide-out on mobile.
 * Desktop (>768px): standard collapsible vertical sidebar.
 */

class SharedSidebar {
  constructor() {
    this.sidebar = document.querySelector('.sidebar');
    this.mainContent = document.querySelector('.main-content');
    this.toggleBtn = document.querySelector('.sidebar-toggle');
    this.menuItems = document.querySelectorAll('.sidebar-menu > li > a, .sidebar-menu > li > .menu-item');
    this.isMobile = window.innerWidth <= 768;

    this.mobileNav = null; // horizontal nav element (created on mobile)

    this.init();
  }

  init() {
    if (!this.sidebar) return;

    this.attachEventListeners();
    this.setActiveMenuItem();
    this.handleResize();
  }

  attachEventListeners() {
    // Toggle sidebar on button click (desktop only)
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
          if (item.tagName === 'DIV') {
            e.preventDefault();
            this.toggleSubmenu(submenu);
          }
        });
      }
    });

    // Handle window resize
    window.addEventListener('resize', () => this.handleResize());

    // Close sidebar on link click (desktop collapsed mode)
    const sidebarLinks = this.sidebar.querySelectorAll('a');
    sidebarLinks.forEach((link) => {
      link.addEventListener('click', () => {
        if (!this.isMobile && this.sidebar.classList.contains('collapsed')) {
          // briefly expand so user sees where they're going
        }
      });
    });
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
      // Also update mobile nav active state
      this.updateMobileNavActive(item.getAttribute('href'));
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

  // ──────────────────────────────────────
  //  Desktop toggle (collapse/expand)
  // ──────────────────────────────────────

  toggleSidebar() {
    if (this.isMobile) return; // no toggle on mobile — horizontal nav instead
    this.sidebar.classList.toggle('collapsed');
    this.mainContent?.classList.toggle('collapsed');
  }

  // ──────────────────────────────────────
  //  Mobile: build / destroy horizontal nav
  // ──────────────────────────────────────

  buildMobileNav() {
    if (this.mobileNav) return; // already built

    const nav = document.createElement('nav');
    nav.className = 'mobile-horizontal-nav';
    nav.setAttribute('aria-label', 'Mobile navigation');

    // Clone top-level menu items (links + icons + labels)
    this.menuItems.forEach((item) => {
      const clone = item.cloneNode(true);
      clone.classList.remove('active');

      // Preserve icon + label structure
      const icon = clone.querySelector('i, .material-icons');
      const label = clone.querySelector('.menu-label') || this.extractLabel(clone);

      // Rebuild as clean inline element
      clone.innerHTML = '';
      if (icon) clone.appendChild(icon);
      if (label) {
        const span = document.createElement('span');
        span.className = 'menu-label';
        span.textContent = label;
        clone.appendChild(span);
      }

      // Click handler — set active
      clone.addEventListener('click', (e) => {
        if (clone.tagName !== 'A') return;
        nav.querySelectorAll('a, .menu-item').forEach((m) => m.classList.remove('active'));
        clone.classList.add('active');
      });

      nav.appendChild(clone);
    });

    // Insert after the header (header is fixed at top:0, height 56px)
    const header = document.querySelector('.shared-header, .site-header, .admin-header, header');
    if (header && header.parentNode) {
      header.parentNode.insertBefore(nav, header.nextSibling);
    } else {
      document.body.insertBefore(nav, document.body.firstChild);
    }

    this.mobileNav = nav;

    // Push main content down
    this.mainContent?.classList.add('with-mobile-nav');

    // Apply active state based on current page
    this.setMobileNavActive();
  }

  destroyMobileNav() {
    if (this.mobileNav) {
      this.mobileNav.remove();
      this.mobileNav = null;
    }
    this.mainContent?.classList.remove('with-mobile-nav');
  }

  extractLabel(el) {
    // Try to get text content excluding icon text
    const clone = el.cloneNode(true);
    const icon = clone.querySelector('i, .material-icons');
    if (icon) icon.remove();
    return clone.textContent.trim();
  }

  setMobileNavActive() {
    if (!this.mobileNav) return;
    const currentPath = window.location.pathname;
    const fileName = currentPath.substring(currentPath.lastIndexOf('/') + 1) || 'index.php';

    this.mobileNav.querySelectorAll('a').forEach((link) => {
      const href = link.getAttribute('href');
      if (href && href.includes(fileName)) {
        this.mobileNav.querySelectorAll('a, .menu-item').forEach((m) => m.classList.remove('active'));
        link.classList.add('active');
      }
    });
  }

  updateMobileNavActive(href) {
    if (!this.mobileNav || !href) return;
    this.mobileNav.querySelectorAll('a, .menu-item').forEach((m) => m.classList.remove('active'));
    this.mobileNav.querySelectorAll('a').forEach((link) => {
      if (link.getAttribute('href') === href) link.classList.add('active');
    });
  }

  // ──────────────────────────────────────
  //  Active menu item (desktop)
  // ──────────────────────────────────────

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
          if (submenu) submenu.classList.add('active');
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
          if (parent) parent.classList.add('active');
        }
      }
    });
  }

  // ──────────────────────────────────────
  //  Resize — switch between desktop/mobile
  // ──────────────────────────────────────

  handleResize() {
    const wasMobile = this.isMobile;
    this.isMobile = window.innerWidth <= 768;

    if (wasMobile !== this.isMobile) {
      // Clear all state classes
      this.sidebar.classList.remove('open', 'collapsed');
      this.mainContent?.classList.remove('sidebar-open', 'collapsed');

      if (this.isMobile) {
        // Switching to mobile — build horizontal nav
        this.buildMobileNav();
      } else {
        // Switching to desktop — destroy horizontal nav
        this.destroyMobileNav();
      }
    }
  }

  // ──────────────────────────────────────
  //  Public API
  // ──────────────────────────────────────

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
    if (!this.isMobile) this.expand();
  }

  close() {
    if (!this.isMobile) this.collapse();
  }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
  window.sharedSidebar = new SharedSidebar();
});
