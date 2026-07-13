// SafeBrgy Admin Profile Script

document.addEventListener('DOMContentLoaded', function() {
  initializeProfilePage();
});

function initializeProfilePage() {
  // Download Information Button
  const downloadBtn = document.getElementById('downloadInfoBtn');
  if (downloadBtn) {
    downloadBtn.addEventListener('click', downloadProfileInformation);
  }

  // Logout Button
  const logoutForm = document.getElementById('logoutForm');
  if (logoutForm) {
    logoutForm.addEventListener('submit', function(e) {
      if (!confirm('Are you sure you want to logout?')) {
        e.preventDefault();
      }
    });
  }
}

function downloadProfileInformation() {
  // Get profile information from the page
  const adminName = document.querySelector('.admin-name').textContent;
  const profileDetails = {
    name: adminName,
    email: getDetailValue('Email:'),
    phone: getDetailValue('Contact Number:'),
    dateJoined: getDetailValue('Date Joined:'),
    lastLogin: getDetailValue('Last Login:'),
    residents: getStatValue('Total Residents Managed'),
    documents: getStatValue('Documents Processed'),
    announcements: getStatValue('Announcements Posted'),
    cases: getStatValue('Cases Handled'),
    timestamp: new Date().toLocaleString()
  };

  // Generate CSV content
  const csvContent = generateCSV(profileDetails);

  // Create and download file
  const element = document.createElement('a');
  element.setAttribute('href', 'data:text/csv;charset=utf-8,' + encodeURIComponent(csvContent));
  element.setAttribute('download', `admin-profile-${Date.now()}.csv`);
  element.style.display = 'none';
  document.body.appendChild(element);
  element.click();
  document.body.removeChild(element);

  // Show notification
  showNotification('Profile information downloaded successfully!', 'success');
}

function getDetailValue(label) {
  const items = document.querySelectorAll('.detail-item');
  for (let item of items) {
    if (item.textContent.includes(label)) {
      return item.querySelector('.detail-value').textContent.trim();
    }
  }
  return 'N/A';
}

function getStatValue(label) {
  const cards = document.querySelectorAll('.stat-card');
  for (let card of cards) {
    if (card.textContent.includes(label)) {
      return card.querySelector('.stat-value').textContent.trim();
    }
  }
  return '0';
}

function generateCSV(data) {
  const rows = [
    ['Admin Profile Information', new Date().toLocaleString()],
    [],
    ['Profile Information', ''],
    ['Name', data.name],
    ['Email', data.email],
    ['Contact Number', data.phone],
    ['Date Joined', data.dateJoined],
    ['Last Login', data.lastLogin],
    [],
    ['Account Statistics', ''],
    ['Total Residents Managed', data.residents],
    ['Documents Processed', data.documents],
    ['Announcements Posted', data.announcements],
    ['Cases Handled', data.cases]
  ];

  return rows.map(row => row.map(cell => `"${cell}"`).join(',')).join('\n');
}

function showNotification(message, type = 'info') {
  // Create notification element
  const notification = document.createElement('div');
  notification.className = `notification notification-${type}`;
  notification.textContent = message;
  notification.style.cssText = `
    position: fixed;
    top: 80px;
    right: 20px;
    padding: 15px 20px;
    background-color: ${type === 'success' ? '#28a745' : '#007bff'};
    color: white;
    border-radius: 6px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    z-index: 9999;
    animation: slideInRight 0.3s ease;
  `;

  document.body.appendChild(notification);

  // Remove notification after 3 seconds
  setTimeout(() => {
    notification.style.animation = 'slideOutRight 0.3s ease';
    setTimeout(() => {
      document.body.removeChild(notification);
    }, 300);
  }, 3000);
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
  @keyframes slideInRight {
    from {
      transform: translateX(100%);
      opacity: 0;
    }
    to {
      transform: translateX(0);
      opacity: 1;
    }
  }
  @keyframes slideOutRight {
    from {
      transform: translateX(0);
      opacity: 1;
    }
    to {
      transform: translateX(100%);
      opacity: 0;
    }
  }
`;
document.head.appendChild(style);
