// Announcement page interactions
document.addEventListener('DOMContentLoaded', () => {
  const notedBtns = document.querySelectorAll('.noted-btn, .noted-btn-modal');
  
  notedBtns.forEach(btn => {
    btn.addEventListener('click', async (e) => {
      e.preventDefault();
      const announcementId = btn.getAttribute('data-id');
      
      try {
        // Optional: Send to backend to log the action
        const response = await fetch('../../api/announcement-noted.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: `announcement_id=${announcementId}`
        });

        if (response.ok) {
          // Show success feedback
          const originalHTML = btn.innerHTML;
          btn.innerHTML = '<i class="fas fa-check-circle"></i> Noted';
          btn.classList.add('btn-success');
          btn.classList.remove('btn-outline-primary', 'btn-outline-secondary');
          btn.disabled = true;

          // Restore after 2 seconds
          setTimeout(() => {
            btn.innerHTML = originalHTML;
            btn.classList.remove('btn-success');
            btn.classList.add('btn-outline-secondary');
            btn.disabled = false;
          }, 2000);
        }
      } catch (error) {
        console.error('Error marking as noted:', error);
        alert('Error marking as noted. Please try again.');
      }
    });
  });
});

