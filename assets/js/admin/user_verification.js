// Handle approve/reject actions
document.addEventListener('DOMContentLoaded', () => {
  // Existing code can stay
});

const verificationEndpoint = '/safebrgy/admin';

function viewUser(userId) {
  fetch(`${verificationEndpoint}/view_user`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ user_id: userId })
  })
  .then(response => {
    if (!response.ok) {
      throw new Error(`Request failed with status ${response.status}`);
    }
    return response.json();
  })
  .then(data => {
    if (data.success) {
      document.getElementById('userDetails').innerHTML = data.html;
      new bootstrap.Modal(document.getElementById('viewUserModal')).show();
    } else {
      alert('Error: ' + data.message);
    }
  })
  .catch(error => {
    console.error('Error loading user:', error);
    alert('An error occurred while loading the user details.');
  });
}

function verifyUser(userId) {
  if (window.showLoadingOverlay) {
    window.showLoadingOverlay();
  }

  fetch(`${verificationEndpoint}/verify_user`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ user_id: userId, action: 'approve' })
  })
  .then(response => response.json())
  .then(data => {
    if (window.hideLoadingOverlay) {
      window.hideLoadingOverlay();
    }

    if (data.success) {
      new bootstrap.Modal(document.getElementById('approveModal')).show();
      setTimeout(() => location.reload(), 2000);
    } else {
      alert('Error: ' + data.message);
    }
  })
  .catch(error => {
    if (window.hideLoadingOverlay) {
      window.hideLoadingOverlay();
    }
    console.error('Error:', error);
    alert('An error occurred while verifying the user.');
  });
}

function rejectUser(userId) {
  document.getElementById('rejectReason').value = '';
  window.currentRejectUserId = userId;
  new bootstrap.Modal(document.getElementById('rejectModal')).show();
}

function confirmReject() {
  if (window.showLoadingOverlay) {
    window.showLoadingOverlay();
  }

  const reason = document.getElementById('rejectReason').value.trim();
  if (!reason) {
    alert('Please enter a reason for rejection');
    return;
  }

  fetch(`${verificationEndpoint}/verify_user`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ 
      user_id: window.currentRejectUserId, 
      action: 'reject', 
      reason: reason 
    })
  })
  .then(response => response.json())
  .then(data => {
    if (window.hideLoadingOverlay) {
      window.hideLoadingOverlay();
    }

    if (data.success) {
      bootstrap.Modal.getInstance(document.getElementById('rejectModal')).hide();
      location.reload();
    } else {
      alert('Error: ' + data.message);
    }
  })
  .catch(error => {
    if (window.hideLoadingOverlay) {
      window.hideLoadingOverlay();
    }
    console.error('Error:', error);
    alert('An error occurred while rejecting the user.');
  });
}
