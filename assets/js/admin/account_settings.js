document.querySelectorAll('.settings-tab').forEach((tab) => {
  tab.addEventListener('click', () => {
    const target = tab.dataset.tab;
    document.querySelectorAll('.settings-tab').forEach((item) => item.classList.toggle('active', item === tab));
    document.querySelectorAll('.settings-panel').forEach((panel) => panel.classList.toggle('active', panel.dataset.panel === target));
    history.replaceState(null, '', `#${target}`);
  });
});

const initialTab = window.location.hash.slice(1);
document.querySelector(`.settings-tab[data-tab="${initialTab}"]`)?.click();

document.querySelector('[data-tab-target="security"]')?.addEventListener('click', () => {
  document.querySelector('.settings-tab[data-tab="security"]')?.click();
});

document.getElementById('profileImage')?.addEventListener('change', (event) => {
  const file = event.target.files[0];
  if (!file) return;
  const preview = document.getElementById('profilePreview');
  if (preview) preview.src = URL.createObjectURL(file);
});

document.getElementById('coverPhoto')?.addEventListener('change', (event) => {
  const file = event.target.files[0];
  if (!file) return;
  const preview = document.getElementById('coverPreview');
  if (preview) preview.src = URL.createObjectURL(file);
});

document.querySelectorAll('.coming-soon').forEach((button) => {
  button.addEventListener('click', () => document.getElementById('maintenanceModal').classList.add('open'));
});
document.getElementById('closeMaintenanceModal')?.addEventListener('click', () => document.getElementById('maintenanceModal').classList.remove('open'));
document.getElementById('closeMaintenanceModalButton')?.addEventListener('click', () => document.getElementById('maintenanceModal').classList.remove('open'));
document.getElementById('maintenanceModal')?.addEventListener('click', (event) => {
  if (event.target.id === 'maintenanceModal') event.currentTarget.classList.remove('open');
});
document.getElementById('requestOtp')?.addEventListener('click', () => alert('An email OTP flow will be available when email delivery is configured.'));
document.getElementById('logoutDevices')?.addEventListener('click', () => alert('All other administrator sessions will be signed out in the next security update.'));

document.querySelector('.switch input[name="twoFactor"]')?.addEventListener('change', async (event) => {
  const formData = new FormData();
  formData.append('section', 'security');
  formData.append('action', 'two_factor');
  formData.append('enabled', event.target.checked ? '1' : '0');
  try {
    const response = await fetch('../update_settings.php', { method: 'POST', body: formData });
    if (!response.ok) throw new Error('Unable to save security setting');
  } catch (error) {
    event.target.checked = !event.target.checked;
    alert('The 2FA setting could not be saved.');
  }
});
