document.addEventListener('DOMContentLoaded', () => {
  if (!document.querySelector('.sidebar-backdrop') && document.querySelector('.sidebar')) {
    const backdrop = document.createElement('div');
    backdrop.className = 'sidebar-backdrop';
    document.body.appendChild(backdrop);
  }

  const buttonSelector = 'button, input[type="submit"], input[type="button"], a.btn';

  const getButtonText = (button) => {
    if (button.tagName === 'INPUT') {
      return button.value || 'Submit';
    }

    return (button.dataset.originalText || button.textContent.trim() || button.getAttribute('aria-label') || 'Continue');
  };

  const setLoadingState = (button) => {
    if (!button || button.classList.contains('is-loading')) {
      return;
    }

    const labelText = getButtonText(button);
    const delay = Number(button.dataset.loadingDelay || button.getAttribute('data-loading-delay') || 2200);

    button.dataset.originalText = button.dataset.originalText || labelText;
    button.dataset.originalMarkup = button.dataset.originalMarkup || button.innerHTML;

    button.classList.add('is-loading');
    button.setAttribute('aria-busy', 'true');
    button.disabled = true;

    const label = document.createElement('span');
    label.className = 'btn-loader-label';
    label.textContent = labelText;

    const dots = document.createElement('span');
    dots.className = 'btn-loader-dots';
    dots.setAttribute('aria-hidden', 'true');
    dots.innerHTML = '<span></span><span></span><span></span>';

    if (button.tagName === 'INPUT') {
      button.value = labelText;
      button.setAttribute('data-safebrgy-original-value', button.value);
      button.setAttribute('data-safebrgy-loading-value', labelText);
      button.dataset.isLoaderApplied = 'true';
      return;
    }

    button.innerHTML = '';
    button.appendChild(label);
    button.appendChild(dots);
    button.dataset.isLoaderApplied = 'true';

    if (button.tagName === 'A') {
      button.setAttribute('aria-live', 'polite');
    }

    window.setTimeout(() => {
      if (document.body.contains(button) && !button.dataset.keepLoading) {
        clearLoadingState(button);
      }
    }, delay);
  };

  const clearLoadingState = (button) => {
    if (!button) {
      return;
    }

    button.classList.remove('is-loading');
    button.removeAttribute('aria-busy');
    button.disabled = false;

    if (button.tagName === 'INPUT') {
      const originalValue = button.dataset.originalText || button.getAttribute('data-safebrgy-original-value');
      if (originalValue) {
        button.value = originalValue;
      }
      delete button.dataset.isLoaderApplied;
      return;
    }

    if (button.dataset.originalMarkup) {
      button.innerHTML = button.dataset.originalMarkup;
    }

    delete button.dataset.isLoaderApplied;
  };

  window.setButtonLoading = setLoadingState;
  window.clearButtonLoading = clearLoadingState;

  document.addEventListener('click', (event) => {
    const trigger = event.target.closest(buttonSelector);
    if (!trigger || trigger.classList.contains('is-loading')) {
      return;
    }

    if (trigger.matches('button[type="submit"], input[type="submit"], .sidebar-toggle, .logout, .nav-toggle, .btn-close, [data-bs-dismiss], [data-bs-toggle], .modal-close')) {
      return;
    }

    if (trigger.tagName === 'A' && trigger.getAttribute('href') && trigger.getAttribute('href').startsWith('#')) {
      return;
    }

    setLoadingState(trigger);
  });

  document.addEventListener('submit', (event) => {
    const form = event.target;
    const submitButton = form.querySelector('button[type="submit"], input[type="submit"]');

    if (submitButton) {
      submitButton.dataset.keepLoading = 'true';
      setLoadingState(submitButton);
    }
  });

  window.addEventListener('beforeunload', () => {
    if (window.showLoadingOverlay) {
      window.showLoadingOverlay();
    }
  });
});
