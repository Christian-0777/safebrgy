// SafeBrgy Account Settings JavaScript

// ============== TAB NAVIGATION ==============
document.addEventListener('DOMContentLoaded', function() {
  
  // Tab switching functionality
  const tabButtons = document.querySelectorAll('.settings-tab');
  
  tabButtons.forEach(button => {
    button.addEventListener('click', function() {
      const tabName = this.getAttribute('data-tab');
      
      // Remove active class from all tabs and hide all contents
      tabButtons.forEach(btn => btn.classList.remove('active'));
      document.querySelectorAll('.settings-content').forEach(content => {
        content.style.display = 'none';
      });
      
      // Add active class to clicked tab and show corresponding content
      this.classList.add('active');
      const targetTab = document.getElementById(tabName + '-tab');
      if (targetTab) {
        targetTab.style.display = 'block';
      }
      
      // Scroll to top of content
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  });

  // Profile picture preview
  const profilePictureInput = document.getElementById('profilePictureInput');
  if (profilePictureInput) {
    profilePictureInput.addEventListener('change', handleProfilePictureChange);
  }

  // Cover photo preview
  const coverPhotoInput = document.getElementById('coverPhotoInput');
  if (coverPhotoInput) {
    coverPhotoInput.addEventListener('change', handleCoverPhotoChange);
  }

  // Valid ID file preview
  const validIdFile = document.getElementById('validIdFile');
  if (validIdFile) {
    validIdFile.addEventListener('change', handleIdFileChange);
  }

  // Drag and drop for ID upload
  const idUploadArea = document.getElementById('idUploadArea');
  if (idUploadArea) {
    idUploadArea.addEventListener('dragover', handleDragOver);
    idUploadArea.addEventListener('dragleave', handleDragLeave);
    idUploadArea.addEventListener('drop', handleDrop);
    idUploadArea.addEventListener('click', () => validIdFile.click());
  }

  // Form validations
  const personalInfoForm = document.getElementById('personalInfoForm');
  if (personalInfoForm) {
    personalInfoForm.addEventListener('submit', handlePersonalInfoSubmit);
  }

  const contactForm = document.getElementById('contactForm');
  if (contactForm) {
    contactForm.addEventListener('submit', handleContactFormSubmit);
  }

  const notificationForm = document.getElementById('notificationForm');
  if (notificationForm) {
    notificationForm.addEventListener('submit', handleNotificationSubmit);
  }

});

// ============== FILE UPLOAD HANDLERS ==============

function handleProfilePictureChange(e) {
  const file = e.target.files[0];
  if (file) {
    // Check file size (max 5MB)
    if (file.size > 5 * 1024 * 1024) {
      alert('Profile picture must be less than 5MB');
      return;
    }
    
    // Check file type
    if (!file.type.startsWith('image/')) {
      alert('Please select an image file');
      return;
    }

    const reader = new FileReader();
    reader.onload = function(event) {
      const preview = document.getElementById('profilePreview');
      if (preview) {
        preview.src = event.target.result;
      }
    };
    reader.readAsDataURL(file);
  }
}

function handleCoverPhotoChange(e) {
  const file = e.target.files[0];
  if (file) {
    // Check file size (max 10MB)
    if (file.size > 10 * 1024 * 1024) {
      alert('Cover photo must be less than 10MB');
      return;
    }
    
    // Check file type
    if (!file.type.startsWith('image/')) {
      alert('Please select an image file');
      return;
    }

    const uploadData = new FormData();
    uploadData.append('coverPhoto', file);
    fetch('../../api/account/update_cover.php', {
      method: 'POST',
      body: uploadData
    })
      .then(response => response.json())
      .then(result => {
        if (!result.success) {
          throw new Error(result.message || 'Cover photo upload failed');
        }
      })
      .catch(error => {
        alert(error.message);
        e.target.value = '';
      });

    const reader = new FileReader();
    reader.onload = function(event) {
      const placeholder = document.querySelector('.cover-preview .cover-placeholder');
      if (placeholder) {
        placeholder.style.backgroundImage = `url('${event.target.result}')`;
        placeholder.style.backgroundSize = 'cover';
        placeholder.style.backgroundPosition = 'center';
        placeholder.querySelectorAll('i, p').forEach(element => element.remove());
      }
    };
    reader.readAsDataURL(file);
  }
}

function handleIdFileChange(e) {
  const file = e.target.files[0];
  if (file) {
    // Check file size (max 10MB)
    if (file.size > 10 * 1024 * 1024) {
      alert('ID file must be less than 10MB');
      return;
    }
    
    // Check file type
    const allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
    if (!allowedTypes.includes(file.type)) {
      alert('Please select a valid image or PDF file');
      return;
    }

    // Show preview for images
    if (file.type.startsWith('image/')) {
      const reader = new FileReader();
      reader.onload = function(event) {
        const previewNew = document.getElementById('idPreviewNew');
        const newIdPreview = document.getElementById('newIdPreview');
        if (previewNew && newIdPreview) {
          newIdPreview.src = event.target.result;
          previewNew.style.display = 'block';
        }
      };
      reader.readAsDataURL(file);
    } else {
      // For PDFs, show a generic message
      const previewNew = document.getElementById('idPreviewNew');
      if (previewNew) {
        previewNew.innerHTML = '<h6 class="mb-2">PDF Selected</h6><p class="text-muted">PDF file selected for upload</p>';
        previewNew.style.display = 'block';
      }
    }
  }
}

// ============== DRAG AND DROP ==============

function handleDragOver(e) {
  e.preventDefault();
  e.stopPropagation();
  document.getElementById('idUploadArea').style.background = '#f0f7ff';
  document.getElementById('idUploadArea').style.borderColor = '#0b63d6';
}

function handleDragLeave(e) {
  e.preventDefault();
  e.stopPropagation();
  document.getElementById('idUploadArea').style.background = '#fafafa';
  document.getElementById('idUploadArea').style.borderColor = '#d0d0d0';
}

function handleDrop(e) {
  e.preventDefault();
  e.stopPropagation();
  document.getElementById('idUploadArea').style.background = '#fafafa';
  document.getElementById('idUploadArea').style.borderColor = '#d0d0d0';
  
  const files = e.dataTransfer.files;
  if (files.length > 0) {
    document.getElementById('validIdFile').files = files;
    handleIdFileChange({ target: { files: files } });
  }
}

// ============== FORM VALIDATIONS ==============

function handlePersonalInfoSubmit(e) {
  const firstName = document.getElementById('firstName').value.trim();
  const lastName = document.getElementById('lastName').value.trim();
  
  if (!firstName || !lastName) {
    e.preventDefault();
    alert('First name and last name are required');
    return false;
  }
}

function handleContactFormSubmit(e) {
  const phone = document.getElementById('phone').value.trim();
  const email = document.getElementById('email').value.trim();
  
  if (!phone || !email) {
    e.preventDefault();
    alert('Phone and email are required');
    return false;
  }
  
  // Validate email format
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(email)) {
    e.preventDefault();
    alert('Please enter a valid email address');
    return false;
  }
}

function handleNotificationSubmit(e) {
  // All notification preferences are optional, just submit
  return true;
}

// ============== SECURITY FUNCTIONS ==============

function toggleTwoFactor(checkbox) {
  const status = document.getElementById('twoFactorStatus');
  if (checkbox.checked) {
    status.textContent = 'Disable';
    showNotification('2FA has been enabled', 'success');
    // TODO: Call API to enable 2FA
  } else {
    status.textContent = 'Enable';
    showNotification('2FA has been disabled', 'warning');
    // TODO: Call API to disable 2FA
  }
}

function logoutAllDevices() {
  if (confirm('Are you sure you want to logout from all other devices? You will remain logged in on this device.')) {
    // TODO: Call API to logout all other devices
    showNotification('Logging out from all other devices...', 'info');
  }
}

// ============== ACCOUNT ACTIONS ==============

function downloadPersonalData() {
  if (confirm('Download your personal data? This will include all your information in JSON format.')) {
    showNotification('Preparing your data for download...', 'info');
    window.location.href = '../../api/account/download_data.php';
  }
}

function showDeactivateConfirm() {
  const modal = document.createElement('div');
  modal.className = 'modal fade';
  modal.id = 'deactivateConfirmModal';
  modal.innerHTML = `
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fas fa-pause-circle"></i> Deactivate Account</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p>Are you sure you want to deactivate your account?</p>
          <p class="text-muted"><strong>What happens:</strong></p>
          <ul class="text-muted">
            <li>Your profile will be hidden from other residents</li>
            <li>You can reactivate it anytime by logging in</li>
            <li>Your data will be preserved</li>
          </ul>
          <div class="mb-3">
            <label for="deactivateReason" class="form-label">Reason for deactivation (optional)</label>
            <textarea class="form-control" id="deactivateReason" rows="2" placeholder="Tell us why..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-warning" onclick="confirmDeactivate()">Deactivate Account</button>
        </div>
      </div>
    </div>
  `;
  
  document.body.appendChild(modal);
  const bsModal = new bootstrap.Modal(modal);
  bsModal.show();
  
  modal.addEventListener('hidden.bs.modal', () => modal.remove());
}

function confirmDeactivate() {
  const reason = document.getElementById('deactivateReason').value;
  
  // Create and submit form
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = '../../api/account/deactivate_account.php';
  
  const reasonInput = document.createElement('input');
  reasonInput.type = 'hidden';
  reasonInput.name = 'reason';
  reasonInput.value = reason;
  form.appendChild(reasonInput);
  
  document.body.appendChild(form);
  form.submit();
}

function showDeleteConfirm() {
  const modal = document.createElement('div');
  modal.className = 'modal fade';
  modal.id = 'deleteConfirmModal';
  modal.innerHTML = `
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header danger-header">
          <h5 class="modal-title"><i class="fas fa-trash-alt"></i> Delete Account</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="alert alert-danger">
            <strong><i class="fas fa-exclamation-triangle"></i> Warning:</strong> This action cannot be undone!
          </div>
          <p>Are you sure you want to permanently delete your account?</p>
          <p class="text-muted"><strong>What will happen:</strong></p>
          <ul class="text-muted">
            <li>Your account and all data will be permanently deleted</li>
            <li>You will not be able to recover your data</li>
            <li>All your requests and reports will be removed</li>
          </ul>
          <div class="mb-3">
            <label for="deleteConfirm" class="form-label">Type "<strong>DELETE</strong>" to confirm</label>
            <input type="text" class="form-control" id="deleteConfirm" placeholder="Type DELETE">
          </div>
          <div class="mb-3">
            <label for="deleteReason" class="form-label">Reason for deletion (optional)</label>
            <textarea class="form-control" id="deleteReason" rows="2" placeholder="Tell us why..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-danger" onclick="confirmDelete()">Delete Account Permanently</button>
        </div>
      </div>
    </div>
  `;
  
  document.body.appendChild(modal);
  const bsModal = new bootstrap.Modal(modal);
  bsModal.show();
  
  modal.addEventListener('hidden.bs.modal', () => modal.remove());
}

function confirmDelete() {
  const confirmText = document.getElementById('deleteConfirm').value;
  
  if (confirmText !== 'DELETE') {
    alert('Please type DELETE to confirm');
    return;
  }
  
  const reason = document.getElementById('deleteReason').value;
  
  // Create and submit form
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = '../../api/account/delete_account.php';
  
  const confirmInput = document.createElement('input');
  confirmInput.type = 'hidden';
  confirmInput.name = 'confirm';
  confirmInput.value = confirmText;
  form.appendChild(confirmInput);
  
  const reasonInput = document.createElement('input');
  reasonInput.type = 'hidden';
  reasonInput.name = 'reason';
  reasonInput.value = reason;
  form.appendChild(reasonInput);
  
  document.body.appendChild(form);
  showNotification('Permanently deleting your account...', 'danger');
  
  setTimeout(() => {
    form.submit();
  }, 1500);
}

// ============== UTILITY FUNCTIONS ==============

function showNotification(message, type = 'info') {
  const alertDiv = document.createElement('div');
  alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
  alertDiv.setAttribute('role', 'alert');
  
  let icon = 'fa-info-circle';
  if (type === 'success') icon = 'fa-check-circle';
  else if (type === 'danger') icon = 'fa-exclamation-circle';
  else if (type === 'warning') icon = 'fa-exclamation-triangle';
  
  alertDiv.innerHTML = `
    <i class="fas ${icon} me-2"></i>${message}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  `;
  
  // Insert at the top of main content
  const mainContent = document.querySelector('.main-content');
  const container = mainContent.querySelector('.container-fluid');
  if (container) {
    container.insertBefore(alertDiv, container.firstChild);
  }
  
  // Auto-dismiss after 5 seconds
  setTimeout(() => {
    alertDiv.remove();
  }, 5000);
}

// ============== PASSWORD STRENGTH INDICATOR ==============

document.addEventListener('DOMContentLoaded', function() {
  const newPasswordInput = document.getElementById('newPassword');
  if (newPasswordInput) {
    newPasswordInput.addEventListener('input', function() {
      const strength = calculatePasswordStrength(this.value);
      updatePasswordStrengthIndicator(strength);
    });
  }
});

function calculatePasswordStrength(password) {
  let strength = 0;
  
  if (password.length >= 8) strength++;
  if (password.match(/[a-z]+/)) strength++;
  if (password.match(/[A-Z]+/)) strength++;
  if (password.match(/[0-9]+/)) strength++;
  if (password.match(/[$@#&!]+/)) strength++;
  
  return strength;
}

function updatePasswordStrengthIndicator(strength) {
  let indicator = document.getElementById('passwordStrengthIndicator');
  
  if (!indicator) {
    const newPasswordInput = document.getElementById('newPassword');
    const nextElement = newPasswordInput.nextElementSibling;
    
    indicator = document.createElement('div');
    indicator.id = 'passwordStrengthIndicator';
    indicator.className = 'password-strength-indicator mt-2';
    
    if (nextElement && nextElement.tagName === 'SMALL') {
      nextElement.parentNode.insertBefore(indicator, nextElement.nextSibling);
    } else {
      newPasswordInput.parentNode.appendChild(indicator);
    }
  }
  
  const strengthLabels = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong', 'Very Strong'];
  const strengthColors = ['#f32b36', '#ff9800', '#ffc107', '#8bc34a', '#4caf50', '#2e7d32'];
  
  const label = strengthLabels[strength] || 'Very Weak';
  const color = strengthColors[strength] || '#f32b36';
  
  indicator.innerHTML = `
    <div style="display: flex; align-items: center; gap: 8px;">
      <div style="flex: 1; height: 4px; background: #e0e0e0; border-radius: 2px; overflow: hidden;">
        <div style="width: ${(strength / 5) * 100}%; height: 100%; background: ${color}; transition: width 0.3s;"></div>
      </div>
      <small style="color: ${color}; font-weight: 600; white-space: nowrap;">${label}</small>
    </div>
  `;
}
