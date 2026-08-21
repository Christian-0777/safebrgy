document.addEventListener('DOMContentLoaded', () => {
  const createForm = document.getElementById('createAnnouncementForm');
  const pinButtons = document.querySelectorAll('.pin-btn');
  const archiveButtons = document.querySelectorAll('.archive-btn');
  const deleteButtons = document.querySelectorAll('.delete-btn');
  const typeImmediateRadio = document.getElementById('typeImmediate');
  const typeScheduledRadio = document.getElementById('typeScheduled');
  const scheduleDateWrapper = document.getElementById('scheduleDateWrapper');
  const targetAudienceSelect = document.getElementById('announcementTargetAudience');
  const otherAudienceWrapper = document.getElementById('otherAudienceWrapper');
  const announcementAttachments = document.getElementById('announcementAttachments');
  const filePreview = document.getElementById('filePreview');
  const announcementEndpoint = new URL('/safebrgy/admin/announcement', window.location.origin).href;

  // File preview for multiple uploads
  if (announcementAttachments) {
    announcementAttachments.addEventListener('change', (e) => {
      filePreview.innerHTML = '';
      const files = e.target.files;
      
      if (files.length > 0) {
        const previewContainer = document.createElement('div');
        previewContainer.className = 'row g-2';
        
        for (let i = 0; i < files.length; i++) {
          const file = files[i];
          const reader = new FileReader();
          
          reader.onload = (event) => {
            const col = document.createElement('div');
            col.className = 'col-md-3';
            col.innerHTML = `
              <div class="position-relative">
                <img src="${event.target.result}" class="img-fluid rounded" style="max-height: 100px; object-fit: cover;" alt="Preview">
                <small class="d-block text-truncate mt-1">${file.name}</small>
              </div>
            `;
            previewContainer.appendChild(col);
          };
          
          reader.readAsDataURL(file);
        }
        
        filePreview.appendChild(previewContainer);
      }
    });
  }

  // Toggle scheduled date visibility
  if (typeImmediateRadio && typeScheduledRadio) {
    typeImmediateRadio.addEventListener('change', () => {
      scheduleDateWrapper.style.display = 'none';
    });

    typeScheduledRadio.addEventListener('change', () => {
      scheduleDateWrapper.style.display = 'block';
    });
  }

  // Toggle other audience message
  if (targetAudienceSelect) {
    targetAudienceSelect.addEventListener('change', () => {
      if (targetAudienceSelect.value === 'other') {
        otherAudienceWrapper.style.display = 'block';
      } else {
        otherAudienceWrapper.style.display = 'none';
      }
    });
  }

  // Create announcement
  if (createForm) {
    createForm.addEventListener('submit', async (e) => {
      e.preventDefault();

      if (window.showLoadingOverlay) {
        window.showLoadingOverlay();
      }
      
      const formData = new FormData(createForm);
      formData.append('action', 'create');

      try {
        const response = await fetch(announcementEndpoint, {
          method: 'POST',
          body: formData
        });

        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
          if (window.hideLoadingOverlay) {
            window.hideLoadingOverlay();
          }
          console.error('Invalid response:', response.statusText);
          alert('Server error. Please check the console for details.');
          return;
        }

        const data = await response.json();
        if (data.success) {
          // Close the create modal
          const createModal = bootstrap.Modal.getInstance(document.getElementById('createAnnouncementModal'));
          if (createModal) createModal.hide();

          // Reset form
          createForm.reset();
          filePreview.innerHTML = '';
          scheduleDateWrapper.style.display = 'none';
          otherAudienceWrapper.style.display = 'none';

          // Show success modal
          // Ensure loading overlay is hidden so the modal is clickable
          if (window.hideLoadingOverlay) {
            window.hideLoadingOverlay();
          }

          const successModal = new bootstrap.Modal(document.getElementById('successModal'));
          successModal.show();

          // Reload page after modal closes
          document.getElementById('successModal').addEventListener('hidden.bs.modal', () => {
            location.reload();
          }, { once: true });
        } else {
          if (window.hideLoadingOverlay) {
            window.hideLoadingOverlay();
          }
          alert('Failed to create announcement: ' + (data.error || 'Unknown error'));
        }
      } catch (error) {
        if (window.hideLoadingOverlay) {
          window.hideLoadingOverlay();
        }
        console.error('Error:', error);
        alert(`Unable to reach the announcement server: ${error.message}`);
      }
    });
  }

  // Pin/Unpin announcement
  pinButtons.forEach(btn => {
    btn.addEventListener('click', async () => {
      const id = btn.dataset.id;
      const pinned = btn.dataset.pinned === '1' ? 0 : 1;

      if (window.showLoadingOverlay) {
        window.showLoadingOverlay();
      }

      try {
        const response = await fetch(announcementEndpoint, {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `action=pin&id=${id}&pinned=${pinned}`
        });

        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
          console.error('Invalid response:', response.statusText);
          alert('Server error. Please check the console for details.');
          return;
        }

        const data = await response.json();
        if (data.success) {
          alert(pinned ? 'Announcement pinned!' : 'Announcement unpinned!');
          location.reload();
        } else {
          alert('Error: ' + (data.error || 'Failed to update announcement'));
        }
      } catch (error) {
        console.error('Error:', error);
        alert('An error occurred.');
      }
    });
  });

  // Archive announcement
  archiveButtons.forEach(btn => {
    btn.addEventListener('click', async () => {
      if (!confirm('Archive this announcement?')) return;

      const id = btn.dataset.id;

      try {
        const response = await fetch(announcementEndpoint, {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `action=archive&id=${id}`
        });

        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
          console.error('Invalid response:', response.statusText);
          alert('Server error. Please check the console for details.');
          return;
        }

        const data = await response.json();
        if (data.success) {
          alert('Announcement archived!');
          location.reload();
        } else {
          alert('Error: ' + (data.error || 'Failed to archive announcement'));
        }
      } catch (error) {
        console.error('Error:', error);
        alert('An error occurred.');
      }
    });
  });

  // Delete announcement
  deleteButtons.forEach(btn => {
    btn.addEventListener('click', async () => {
      if (!confirm('Are you sure you want to delete this announcement? This action cannot be undone.')) return;

      const id = btn.dataset.id;

      try {
        const response = await fetch(announcementEndpoint, {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `action=delete&id=${id}`
        });

        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
          console.error('Invalid response:', response.statusText);
          alert('Server error. Please check the console for details.');
          return;
        }

        const data = await response.json();
        if (data.success) {
          alert('Announcement deleted!');
          location.reload();
        } else {
          alert('Error: ' + (data.error || 'Failed to delete announcement'));
        }
      } catch (error) {
        console.error('Error:', error);
        alert('An error occurred.');
      }
    });
  });
});
