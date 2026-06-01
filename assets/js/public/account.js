// Simple password confirmation check
document.getElementById('accountForm').addEventListener('submit', function(e) {
  const newPass = document.getElementById('newPassword').value;
  const confirmPass = document.getElementById('confirmPassword').value;

  if (newPass !== confirmPass) {
    e.preventDefault();
    alert('New password and confirm password do not match.');
  }
});
