// Handle approve/reject actions
document.addEventListener('DOMContentLoaded', () => {
  // Existing code can stay
});

function viewUser(userId) {
  fetch('view_user.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ user_id: userId })
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      document.getElementById('userDetails').innerHTML = data.html;
      new bootstrap.Modal(document.getElementById('viewUserModal')).show();
    } else {
      alert('Error: ' + data.message);
    }
  });
}

function verifyUser(userId) {
  fetch('verify_user.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ user_id: userId, action: 'approve' })
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      new bootstrap.Modal(document.getElementById('approveModal')).show();
      setTimeout(() => location.reload(), 2000);
    } else {
      alert('Error: ' + data.message);
    }
  });
}

function rejectUser(userId) {
  document.getElementById('rejectReason').value = '';
  window.currentRejectUserId = userId;
  new bootstrap.Modal(document.getElementById('rejectModal')).show();
}

function confirmReject() {
  const reason = document.getElementById('rejectReason').value.trim();
  if (!reason) {
    alert('Please enter a reason for rejection');
    return;
  }

  fetch('verify_user.php', {
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
    if (data.success) {
      bootstrap.Modal.getInstance(document.getElementById('rejectModal')).hide();
      location.reload();
    } else {
      alert('Error: ' + data.message);
    }
  });
}
