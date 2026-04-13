// Search filter for requests
document.addEventListener('DOMContentLoaded', () => {
  const searchInput = document.getElementById('searchRequests');
  const rows = document.querySelectorAll('#requestsTable tr');

  searchInput.addEventListener('input', () => {
    const query = searchInput.value.toLowerCase();
    rows.forEach(row => {
      const text = row.innerText.toLowerCase();
      row.style.display = text.includes(query) ? '' : 'none';
    });
  });
});
