document.addEventListener('DOMContentLoaded', function() {
  const modal = document.getElementById('loginModal');
  const btns = document.querySelectorAll('.btn-request');
  const close = document.querySelector('.close');

  btns.forEach(btn => {
    btn.addEventListener('click', function() {
      modal.style.display = 'block';
    });
  });

  close.addEventListener('click', function() {
    modal.style.display = 'none';
  });

  window.addEventListener('click', function(event) {
    if (event.target == modal) {
      modal.style.display = 'none';
    }
  });
});