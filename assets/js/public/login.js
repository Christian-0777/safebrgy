// Simple client-side validation
document.getElementById('loginForm').addEventListener('submit', function(e) {
  const contact = document.getElementById('contactNumber').value.trim();
  const password = document.getElementById('password').value.trim();

  if (contact === '' || password === '') {
    e.preventDefault();
    alert('Please fill in all fields.');
  }
});
