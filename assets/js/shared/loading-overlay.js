(function () {
  function ensureOverlay() {
    if (document.getElementById('safeLoadingOverlay')) {
      return document.getElementById('safeLoadingOverlay');
    }

    const overlay = document.createElement('div');
    overlay.id = 'safeLoadingOverlay';
    overlay.className = 'safe-loading-overlay hidden';
    overlay.innerHTML = `
      <div class="safe-loading-content">
        <div class="safe-loading-spinner"></div>
        <div class="safe-loading-text">Processing</div>
        <div class="safe-loading-dots" aria-hidden="true">
          <span></span>
          <span></span>
          <span></span>
        </div>
      </div>
    `;

    document.body.appendChild(overlay);
    return overlay;
  }

  function showLoadingOverlay() {
    const overlay = ensureOverlay();
    overlay.classList.remove('hidden');
  }

  function hideLoadingOverlay() {
    const overlay = document.getElementById('safeLoadingOverlay');
    if (overlay) {
      overlay.classList.add('hidden');
    }
  }

  window.showLoadingOverlay = showLoadingOverlay;
  window.hideLoadingOverlay = hideLoadingOverlay;

  window.addEventListener('beforeunload', function () {
    showLoadingOverlay();
  });

  document.addEventListener('DOMContentLoaded', function () {
    const overlay = ensureOverlay();
    overlay.classList.add('hidden');
  });
})();
