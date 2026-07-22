/**
 * Shared Header JavaScript
 * Handles header functionality including burger menu dropdown,
 * notifications, and user profile menu.
 */

class SharedHeader {
  constructor() {
    this.notificationBtn = document.querySelector('.notifications');
    this.notificationDropdown = document.querySelector('.notification-dropdown');
    this.userProfile = document.querySelector('.user-profile');
    this.profileDropdown = document.querySelector('.profile-dropdown');
    this.searchBox = document.querySelector('.search-box input');

    // Burger menu elements
    this.burgerToggle = document.querySelector('.burger-menu, .nav-toggle, #navToggle');
    this.navMenu = document.querySelector('.nav-dropdown, .main-nav, .admin-nav, #adminNav');
    this.mobileNav = document.querySelector('.mobile-horizontal-nav');

    this.isMobile = window.innerWidth <= 768;

    this.init();
  }

  init() {
    this.attachEventListeners();
    this.loadNotifications();
    this.handleResize();
  }

  attachEventListeners() {
    // ── Burger menu toggle ──
    if (this.burgerToggle && this.navMenu) {
      this.burgerToggle.addEventListener('click', (e) => {
        e.stopPropagation();
        this.toggleBurgerMenu();
      });
    }

    // Also toggle mobile horizontal nav if present
    if (this.burgerToggle && this.mobileNav) {
      this.burgerToggle.addEventListener('click', (e) => {
        e.stopPropagation();
        this.mobileNav.classList.toggle('open');
      });
    }

    // Close burger menu when a nav link is clicked
    if (this.navMenu) {
      this.navMenu.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => this.closeBurgerMenu());
      });
    }

    // ── Notification dropdown toggle ──
    if (this.notificationBtn && this.notificationDropdown) {
      this.notificationBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        this.toggleNotificationDropdown();
      });
    }

    // ── User profile dropdown toggle ──
    if (this.userProfile && this.profileDropdown) {
      this.userProfile.addEventListener('click', (e) => {
        e.stopPropagation();
        this.toggleProfileDropdown();
      });
    }

    // ── Search box ──
    if (this.searchBox) {
      this.searchBox.addEventListener('keyup', (e) => this.handleSearch(e));
    }

    // ── Close all dropdowns/menus when clicking outside ──
    document.addEventListener('click', () => {
      this.closeDropdowns();
      this.closeBurgerMenu();
    });

    // ── Logout button ──
    const logoutBtn = this.profileDropdown?.querySelector('.logout');
    if (logoutBtn) {
      logoutBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        this.logout();
      });
    }

    // ── Resize handler ──
    window.addEventListener('resize', () => this.handleResize());
  }

  // ──────────────────────────────────────
  //  Burger Menu
  // ──────────────────────────────────────

  toggleBurgerMenu() {
    if (!this.navMenu) return;
    const isOpen = this.navMenu.classList.contains('active');
    this.closeDropdowns();
    if (!isOpen) {
      this.navMenu.classList.add('active');
      this.burgerToggle?.classList.add('active');
      this.burgerToggle?.setAttribute('aria-expanded', 'true');
    } else {
      this.closeBurgerMenu();
    }
  }

  closeBurgerMenu() {
    this.navMenu?.classList.remove('active');
    this.burgerToggle?.classList.remove('active');
    this.burgerToggle?.setAttribute('aria-expanded', 'false');
    this.mobileNav?.classList.remove('open');
  }

  // ──────────────────────────────────────
  //  Notification Dropdown
  // ──────────────────────────────────────

  toggleNotificationDropdown() {
    if (!this.notificationDropdown) return;
    this.profileDropdown?.classList.remove('active');
    this.notificationDropdown.classList.toggle('active');
  }

  // ──────────────────────────────────────
  //  Profile Dropdown
  // ──────────────────────────────────────

  toggleProfileDropdown() {
    if (!this.profileDropdown) return;
    this.notificationDropdown?.classList.remove('active');
    this.profileDropdown.classList.toggle('active');
  }

  closeDropdowns() {
    this.notificationDropdown?.classList.remove('active');
    this.profileDropdown?.classList.remove('active');
  }

  // ──────────────────────────────────────
  //  Notifications Data
  // ──────────────────────────────────────

  loadNotifications() {
    if (!this.notificationDropdown) return;
    // Placeholder — replace with actual API call
  }

  renderNotifications(notifications) {
    if (!this.notificationDropdown || !Array.isArray(notifications)) return;

    let html = '';
    if (notifications.length === 0) {
      html = '<div style="padding:20px;text-align:center;color:var(--color-neutral-medium-gray);">No notifications</div>';
    } else {
      notifications.forEach((notification) => {
        const time = this.formatTime(notification.timestamp);
        html += `
          <div class="notification-item" data-id="${notification.id}">
            <div class="notification-item-title">${notification.title}</div>
            <div class="notification-item-text">${notification.message}</div>
            <div class="notification-item-time">${time}</div>
          </div>`;
      });
    }

    this.notificationDropdown.innerHTML = html;

    this.notificationDropdown.querySelectorAll('.notification-item').forEach((item) => {
      item.addEventListener('click', () => {
        const notificationId = item.getAttribute('data-id');
        this.handleNotificationClick(notificationId);
      });
    });
  }

  handleNotificationClick(notificationId) {
    console.log('Notification clicked:', notificationId);
    this.closeDropdowns();
  }

  // ──────────────────────────────────────
  //  Search
  // ──────────────────────────────────────

  handleSearch(event) {
    const searchTerm = event.target.value;
    if (event.key === 'Enter') {
      this.performSearch(searchTerm);
    } else {
      this.showSearchSuggestions(searchTerm);
    }
  }

  performSearch(searchTerm) {
    if (!searchTerm.trim()) return;
    console.log('Searching for:', searchTerm);
  }

  showSearchSuggestions(searchTerm) {
    if (!searchTerm.trim()) return;
    console.log('Loading suggestions for:', searchTerm);
  }

  // ──────────────────────────────────────
  //  Resize
  // ──────────────────────────────────────

  handleResize() {
    const wasMobile = this.isMobile;
    this.isMobile = window.innerWidth <= 768;

    // When switching to desktop, close burger menu
    if (wasMobile && !this.isMobile) {
      this.closeBurgerMenu();
      this.closeDropdowns();
    }
  }

  // ──────────────────────────────────────
  //  Logout
  // ──────────────────────────────────────

  logout() {
    localStorage.clear();
    sessionStorage.clear();
    window.location.href = '../logout.php';
  }

  // ──────────────────────────────────────
  //  Utilities
  // ──────────────────────────────────────

  formatTime(timestamp) {
    if (!timestamp) return 'Just now';
    const date = new Date(timestamp);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);

    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return `${diffMins}m ago`;
    if (diffHours < 24) return `${diffHours}h ago`;
    if (diffDays < 30) return `${diffDays}d ago`;
    return date.toLocaleDateString();
  }

  updateUserInfo(userInfo) {
    const profileName = this.userProfile?.querySelector('.profile-name');
    const profileRole = this.userProfile?.querySelector('.profile-role');
    const profileAvatar = this.userProfile?.querySelector('.profile-avatar');

    if (profileName) profileName.textContent = userInfo.name || 'User';
    if (profileRole) profileRole.textContent = userInfo.role || 'Role';
    if (profileAvatar) {
      if (userInfo.avatar) {
        profileAvatar.innerHTML = `<img src="${userInfo.avatar}" alt="Avatar">`;
      } else {
        profileAvatar.textContent = (userInfo.name || 'U').charAt(0).toUpperCase();
      }
    }
  }

  updateNotificationBadge(count) {
    const badge = this.notificationBtn?.querySelector('.notification-badge');
    if (badge) {
      if (count > 0) {
        badge.textContent = count > 9 ? '9+' : count;
        badge.style.display = 'flex';
      } else {
        badge.style.display = 'none';
      }
    }
  }

  addNotification(notification) {
    if (!this.notificationDropdown) return;

    const item = document.createElement('div');
    item.className = 'notification-item';
    item.setAttribute('data-id', notification.id);

    const time = this.formatTime(notification.timestamp);
    item.innerHTML = `
      <div class="notification-item-title">${notification.title}</div>
      <div class="notification-item-text">${notification.message}</div>
      <div class="notification-item-time">${time}</div>`;

    const firstItem = this.notificationDropdown.querySelector('.notification-item');
    if (firstItem) {
      firstItem.parentNode.insertBefore(item, firstItem);
    } else {
      this.notificationDropdown.innerHTML = '';
      this.notificationDropdown.appendChild(item);
    }

    item.addEventListener('click', () => this.handleNotificationClick(notification.id));

    const badge = this.notificationBtn?.querySelector('.notification-badge');
    if (badge) {
      const currentCount = parseInt(badge.textContent) || 0;
      this.updateNotificationBadge(currentCount + 1);
    }
  }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
  window.sharedHeader = new SharedHeader();
});
