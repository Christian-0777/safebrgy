// SafeBrgy Resident Dashboard JavaScript

document.addEventListener('DOMContentLoaded', () => {
  // Initialize date and time display
  updateDateTime();
  setInterval(updateDateTime, 1000);

  // Handle sidebar toggle on mobile
  const sidebar = document.querySelector('.sidebar');
  if (window.innerWidth < 768 && sidebar) {
    sidebar.classList.add('collapse');
  }

  // Handle logout button
  const logoutBtn = document.querySelector('.logout');
  if (logoutBtn) {
    logoutBtn.addEventListener('click', handleLogout);
  }

  // Smooth scroll for announcement links
  const announcementLinks = document.querySelectorAll('.announcement-link');
  announcementLinks.forEach(link => {
    link.addEventListener('click', handleAnnouncementClick);
  });

  // Prevent link from navigating if it has an anchor
  const trackerLinks = document.querySelectorAll('.tracker-link');
  trackerLinks.forEach(link => {
    link.addEventListener('click', (e) => {
      // Allow default navigation
      return true;
    });
  });
});

/**
 * Update the current date and time display
 */
function updateDateTime() {
  const dateTimeElement = document.getElementById('current-date-time');
  if (!dateTimeElement) return;

  const now = new Date();
  
  // Format: "Monday, June 1, 2026 - 2:34 PM"
  const options = {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
    hour12: true
  };
  
  const formattedDateTime = now.toLocaleDateString('en-US', options);
  dateTimeElement.textContent = formattedDateTime;
}

/**
 * Handle logout action
 */
function handleLogout(e) {
  e.preventDefault();
  
  if (confirm('Are you sure you want to logout?')) {
    window.location.href = new URL('../logout.php', document.baseURI).href;
  }
}

/**
 * Handle announcement link click
 */
function handleAnnouncementClick(e) {
  // Allow default navigation to the announcement detail page
  return true;
}

/**
 * Handle window resize for responsive sidebar
 */
window.addEventListener('resize', () => {
  const sidebar = document.querySelector('.sidebar');
  if (!sidebar) return;

  if (window.innerWidth >= 768) {
    sidebar.classList.remove('collapse');
  }
});
