(() => {
  const initializeCookieConsent = () => {
  const consentCookieName = 'safebrgy_cookie_consent';
  const consentCookie = document.cookie
    .split('; ')
    .find((cookie) => cookie.startsWith(`${consentCookieName}=`));
  const modal = document.getElementById('cookieConsentModal');

    if (!modal || consentCookie) {
      return;
    }

    const closeModal = () => {
      modal.hidden = true;
    };

    const allowButton = document.getElementById('cookieConsentAllow');
    const denyButton = document.getElementById('cookieConsentDeny');

    if (!allowButton || !denyButton) {
      return;
    }

    modal.hidden = false;
    allowButton.addEventListener('click', () => {
      document.cookie = `${consentCookieName}=allowed; Max-Age=31536000; Path=/; SameSite=Lax`;
      closeModal();
    });
    denyButton.addEventListener('click', closeModal);
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeCookieConsent, { once: true });
  } else {
    initializeCookieConsent();
  }
})();
