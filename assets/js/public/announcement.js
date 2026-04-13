// Example: filter announcements by search
document.addEventListener('DOMContentLoaded', () => {
  const searchInput = document.querySelector('input[placeholder="Search announcements..."]');
  const cards = document.querySelectorAll('.card');

  searchInput.addEventListener('input', () => {
    const query = searchInput.value.toLowerCase();
    cards.forEach(card => {
      const text = card.innerText.toLowerCase();
      card.style.display = text.includes(query) ? '' : 'none';
    });
  });
});
