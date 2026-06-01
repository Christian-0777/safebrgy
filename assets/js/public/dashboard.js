// Example: toggle sidebar on mobile
document.addEventListener('DOMContentLoaded', () => {
  const sidebar = document.getElementById('sidebar');
  if (window.innerWidth < 768) {
    sidebar.classList.add('collapse');
  }
});
