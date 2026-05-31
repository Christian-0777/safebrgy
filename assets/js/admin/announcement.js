document.addEventListener('DOMContentLoaded', () => {
  const createForm = document.getElementById('createAnnouncementForm');
  const pinButtons = document.querySelectorAll('.pin-btn');
  const archiveButtons = document.querySelectorAll('.archive-btn');
  const deleteButtons = document.querySelectorAll('.delete-btn');

  // Create announcement
  if (createForm) {
    createForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      
      const formData = new FormData(createForm);
      formData.append('action', 'create');

      try {
        const response = await fetch('announcement.php', {
          method: 'POST',
          body: formData
        });

        const data = await response.json();
        if (data.success) {
          alert('Announcement created successfully!');
          location.reload();
        } else {
          alert('Failed to create announcement.');
        }
      } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while creating the announcement.');
      }
    });
  }

  // Pin/Unpin announcement
  pinButtons.forEach(btn => {
    btn.addEventListener('click', async () => {
      const id = btn.dataset.id;
      const pinned = btn.dataset.pinned === '1' ? 0 : 1;

      try {
        const response = await fetch('announcement.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `action=pin&id=${id}&pinned=${pinned}`
        });

        const data = await response.json();
        if (data.success) {
          alert(pinned ? 'Announcement pinned!' : 'Announcement unpinned!');
          location.reload();
        }
      } catch (error) {
        console.error('Error:', error);
      }
    });
  });

  // Archive announcement
  archiveButtons.forEach(btn => {
    btn.addEventListener('click', async () => {
      if (!confirm('Archive this announcement?')) return;

      const id = btn.dataset.id;

      try {
        const response = await fetch('announcement.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `action=archive&id=${id}`
        });

        const data = await response.json();
        if (data.success) {
          alert('Announcement archived!');
          location.reload();
        }
      } catch (error) {
        console.error('Error:', error);
      }
    });
  });

  // Delete announcement
  deleteButtons.forEach(btn => {
    btn.addEventListener('click', async () => {
      if (!confirm('Are you sure you want to delete this announcement? This action cannot be undone.')) return;

      const id = btn.dataset.id;

      try {
        const response = await fetch('announcement.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `action=delete&id=${id}`
        });

        const data = await response.json();
        if (data.success) {
          alert('Announcement deleted!');
          location.reload();
        }
      } catch (error) {
        console.error('Error:', error);
      }
    });
  });
});
