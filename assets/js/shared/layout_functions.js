document.addEventListener('DOMContentLoaded', () => {
  if (!document.querySelector('.sidebar-backdrop') && document.querySelector('.sidebar')) {
    const backdrop = document.createElement('div');
    backdrop.className = 'sidebar-backdrop';
    document.body.appendChild(backdrop);
  }
});
