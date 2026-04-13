// Example: handle approve/reject actions
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.btn-success').forEach(btn => {
    btn.addEventListener('click', () => alert('User Approved'));
  });
  document.querySelectorAll('.btn-danger').forEach(btn => {
    btn.addEventListener('click', () => alert('User Rejected'));
  });
});
