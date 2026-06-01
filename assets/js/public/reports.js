// Search filter for reports
document.addEventListener('DOMContentLoaded', () => {
  const searchInput = document.getElementById('searchReports');
  const rows = document.querySelectorAll('#reportsTable tr');

  searchInput.addEventListener('input', () => {
    const query = searchInput.value.toLowerCase();
    rows.forEach(row => {
      const text = row.innerText.toLowerCase();
      row.style.display = text.includes(query) ? '' : 'none';
    });
  });
});
