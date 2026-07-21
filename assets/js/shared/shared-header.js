/**
 * Shared Header JavaScript
 * Handles header functionality including notifications and user profile menu
 */

class SharedHeader {
  constructor() {
    this.notificationBtn = document.querySelector('.notifications');
    this.notificationDropdown = document.querySelector('.notification-dropdown');
    this.userProfile = document.querySelector('.user-profile');
    this.profileDropdown = document.querySelector('.profile-dropdown');
    this.searchBox = document.querySelector('.search-box input');
    this.hamburgerBtn = document.querySelector('.header-hamburger');
    this.mobileNav = document.querySelector('.header-nav-mobile');

    this.init();
  }

  init() {
    this.attachEventListeners();
    this.loadNotifications();
  }

  attachEventListeners() {
    // Notification dropdown toggle
    if (this.notificationBtn && this.notificationDropdown) {
      this.notificationBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        this.toggleNotificationDropdown();
      });
    }

    // User profile dropdown toggle
    if (this.userProfile && this.profileDropdown) {
      this.userProfile.addEventListener('click', (e) => {
        e.stopPropagation();
        this.toggleProfileDropdown();
      });
    }

    // Search box functionality
    if (this.searchBox) {
      this.searchBox.addEventListener('keyup', (e) => this.handleSearch(e));
    }

    // Hamburger menu toggle (mobile)
    if (this.hamburgerBtn && this.mobileNav) {
      this.hamburgerBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        this.hamburgerBtn.classList.toggle('active');
        this.mobileNav.classList.toggle('open');
      });
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', () => {
      this.closeDropdowns();
    });

    // Logout button
    const logoutBtn = this.profileDropdown?.querySelector('.logout');
    if (logoutBtn) {
      logoutBtn.addEventListener('click', () => this.logout());
    }
  }

  toggleNotificationDropdown() {
    if (!this.notificationDropdown) return;

    this.profileDropdown?.classList.remove('active');
    this.notificationDropdown.classList.toggle('active');
  }

  toggleProfileDropdown() {
    if (!this.profileDropdown) return;

    this.notificationDropdown?.classList.remove('active');
    this.profileDropdown.classList.toggle('active');
  }

  closeDropdowns() {
    this.notificationDropdown?.classList.remove('active');
    this.profileDropdown?.classList.remove('active');
  }

  loadNotifications() {
    if (!this.notificationDropdown) return;

    // This is a placeholder - replace with actual API call
    // Example structure:
    /*
    fetch('/api/notifications')
      .then(response => response.json())
      .then(data => {
        this.renderNotifications(data);
      })
      .catch(error => console.error('Error loading notifications:', error));
    */
  }

  renderNotifications(notifications) {
    if (!this.notificationDropdown || !Array.isArray(notifications)) return;

    let html = '';
    if (notifications.length === 0) {
      html = '<div style="padding: 20px; text-align: center; color: #999;">No notifications</div>';
    } else {
      notifications.forEach((notification) => {
        const time = this.formatTime(notification.timestamp);
        html += `
          <div class="notification-item" data-id="${notification.id}">
            <div class="notification-item-title">${notification.title}</div>
            <div class="notification-item-text">${notification.message}</div>
            <div class="notification-item-time">${time}</div>
          </div>
        `;
      });
    }

    this.notificationDropdown.innerHTML = html;

    // Attach click handlers to notification items
    this.notificationDropdown.querySelectorAll('.notification-item').forEach((item) => {
      item.addEventListener('click', (e) => {
        const notificationId = item.getAttribute('data-id');
        this.handleNotificationClick(notificationId);
      });
    });
  }

  handleNotificationClick(notificationId) {
    // Handle notification click - could navigate to a detail page
    console.log('Notification clicked:', notificationId);
    this.closeDropdowns();
  }

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

    // This is a placeholder - replace with actual search functionality
    console.log('Searching for:', searchTerm);
    // Could redirect to a search results page or filter current page
  }

  showSearchSuggestions(searchTerm) {
    if (!searchTerm.trim()) {
      // Hide suggestions if search is empty
      return;
    }

    // This is a placeholder - replace with actual suggestion endpoint
    console.log('Loading suggestions for:', searchTerm);
  }

  logout() {
    // Clear any stored data and redirect to the proper logout endpoint
    localStorage.clear();
    sessionStorage.clear();
    window.location.href = '../logout.php';
  }

  formatTime(timestamp) {
    if (!timestamp) return 'Just now';

    const date = new Date(timestamp);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);

    if (diffMins < 1) {
      return 'Just now';
    } else if (diffMins < 60) {
      return `${diffMins}m ago`;
    } else if (diffHours < 24) {
      return `${diffHours}h ago`;
    } else if (diffDays < 30) {
      return `${diffDays}d ago`;
    } else {
      return date.toLocaleDateString();
    }
  }

  // Public method to update user info in header
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

  // Public method to update notification badge
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

  // Public method to add a new notification
  addNotification(notification) {
    if (!this.notificationDropdown) return;

    const item = document.createElement('div');
    item.className = 'notification-item';
    item.setAttribute('data-id', notification.id);

    const time = this.formatTime(notification.timestamp);
    item.innerHTML = `
      <div class="notification-item-title">${notification.title}</div>
      <div class="notification-item-text">${notification.message}</div>
      <div class="notification-item-time">${time}</div>
    `;

    // Add to top of notifications list
    const firstItem = this.notificationDropdown.querySelector('.notification-item');
    if (firstItem) {
      firstItem.parentNode.insertBefore(item, firstItem);
    } else {
      this.notificationDropdown.innerHTML = '';
      this.notificationDropdown.appendChild(item);
    }

    item.addEventListener('click', () => {
      this.handleNotificationClick(notification.id);
    });

    // Update badge
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
