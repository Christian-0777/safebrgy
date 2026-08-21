/**
 * main.js — Resident Registration wizard
 * All step panes live in the DOM at once; this file only toggles
 * visibility, validates, and (at the very end) submits everything
 * to the PHP API. Field values are therefore never lost while a
 * user moves back and forth between steps.
 */
(function () {
  'use strict';

  const TOTAL_STEPS = 7;
  let currentStep = 1;
  let residencyTouched = false;
  let resendTimerId = null;

  const form = document.getElementById('registrationForm');
  const formAlert = document.getElementById('formAlert');

  const uploadedFiles = { id_front: null, id_back: null, profile: null, cover: null };
  let activeCameraKey = null;
  let cameraStream = null;

  /* ------------------------------------------------------------------
   * Small helpers
   * ---------------------------------------------------------------- */

  const $ = (id) => document.getElementById(id);
  const val = (id) => ($(id) ? $(id).value.trim() : '');

  function showAlert(message, type) {
    formAlert.textContent = message;
    formAlert.className = 'alert form-alert alert-' + (type || 'danger');
    formAlert.classList.remove('d-none');
    formAlert.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }

  function hideAlert() {
    formAlert.classList.add('d-none');
  }

  function setFieldInvalid(el, invalid) {
    if (!el) return;
    el.classList.toggle('is-invalid', invalid);
    if (!invalid) el.classList.remove('is-invalid');
  }

  /* ------------------------------------------------------------------
   * Step navigation
   * ---------------------------------------------------------------- */

  function goToStep(step) {
    step = Math.min(Math.max(step, 1), TOTAL_STEPS);
    currentStep = step;

    document.querySelectorAll('.step-pane').forEach((pane) => {
      pane.classList.toggle('is-visible', Number(pane.dataset.step) === step);
    });

    document.querySelectorAll('.ledger-item').forEach((item) => {
      const n = Number(item.dataset.ledgerStep);
      item.classList.toggle('is-done', n < step);
      item.classList.toggle('is-active', n === step);
    });

    document.querySelectorAll('#dotProgress span').forEach((dot) => {
      const n = Number(dot.dataset.dot);
      dot.classList.toggle('is-done', n < step);
      dot.classList.toggle('is-active', n === step);
    });

    const mobileCount = $('mobileStepCount');
    if (mobileCount) mobileCount.textContent = 'Step ' + step + ' of ' + TOTAL_STEPS;

    if (step === 5) populateReview();

    hideAlert();
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  document.querySelectorAll('[data-next]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const pane = btn.closest('.step-pane');
      const step = Number(pane.dataset.step);
      if (validateStep(step)) {
        goToStep(step + 1);
      } else {
        showAlert('Please complete the required fields before continuing.', 'danger');
      }
    });
  });

  document.querySelectorAll('[data-back]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const pane = btn.closest('.step-pane');
      goToStep(Number(pane.dataset.step) - 1);
    });
  });

  // Review step "Edit" links
  document.addEventListener('click', (e) => {
    const editLink = e.target.closest('[data-edit-step]');
    if (editLink) {
      goToStep(Number(editLink.dataset.editStep));
    }
  });

  // Prevent Enter from submitting the form early on any step but the last.
  form.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && currentStep !== TOTAL_STEPS) {
      e.preventDefault();
      const pane = document.querySelector('.step-pane[data-step="' + currentStep + '"]');
      const nextBtn = pane.querySelector('[data-next]');
      if (nextBtn) nextBtn.click();
    }
  });

  /* ------------------------------------------------------------------
   * Step validation
   * ---------------------------------------------------------------- */

  function validateStep(step) {
    const pane = document.querySelector('.step-pane[data-step="' + step + '"]');
    let valid = true;

    // Generic required-field sweep for plain inputs/selects in this pane.
    pane.querySelectorAll('input[required], select[required]').forEach((el) => {
      if (el.type === 'checkbox') return; // handled separately where relevant
      if (el.hasAttribute('readonly')) return; // e.g. auto-filled age
      const isEmpty = el.value.trim() === '';
      setFieldInvalid(el, isEmpty);
      if (isEmpty) valid = false;
    });

    if (step === 1) {
      // Age is derived — make sure a birthdate actually produced one.
      if (!val('age')) {
        setFieldInvalid($('birthdate'), true);
        valid = false;
      }
    }

    if (step === 2) {
      const mobileDigits = $('mobile_number_local').value.replace(/\D/g, '');
      const mobileOk = /^9\d{9}$/.test(mobileDigits);
      $('mobileNumberError').classList.toggle('d-none', mobileOk);
      setFieldInvalid($('mobile_number_local'), !mobileOk);
      if (!mobileOk) valid = false;

      const emailOk = isValidEmail(val('email'));
      setFieldInvalid($('email'), !emailOk);
      if (!emailOk) valid = false;
    }

    if (step === 3) {
      const naChecked = $('occupation_na').checked;
      if (!naChecked) {
        const occOk = val('occupation') !== '';
        setFieldInvalid($('occupation'), !occOk);
        if (!occOk) valid = false;
      } else {
        setFieldInvalid($('occupation'), false);
      }
    }

    if (step === 4) {
      const emergencyDigits = $('emergency_contact_number_local').value.replace(/\D/g, '');
      const emergencyOk = /^9\d{9}$/.test(emergencyDigits);
      $('emergencyNumberError').classList.toggle('d-none', emergencyOk);
      setFieldInvalid($('emergency_contact_number_local'), !emergencyOk);
      if (!emergencyOk) valid = false;

      ['id_front', 'id_back', 'profile'].forEach((key) => {
        if (!uploadedFiles[key]) {
          valid = false;
          $('status_' + key).textContent = 'This photo is required.';
          $('status_' + key).classList.remove('ok');
        }
      });
    }

    return valid;
  }

  function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  }

  /* ------------------------------------------------------------------
   * Step 1 — age auto-calculation
   * ---------------------------------------------------------------- */

  $('birthdate').addEventListener('change', () => {
    const dob = new Date($('birthdate').value + 'T00:00:00');
    if (isNaN(dob.getTime()) || dob > new Date()) {
      $('age').value = '';
      return;
    }
    const today = new Date();
    let age = today.getFullYear() - dob.getFullYear();
    const hasHadBirthdayThisYear =
      today.getMonth() > dob.getMonth() ||
      (today.getMonth() === dob.getMonth() && today.getDate() >= dob.getDate());
    if (!hasHadBirthdayThisYear) age -= 1;

    $('age').value = age >= 0 ? age : '';
    setFieldInvalid($('birthdate'), false);

    if (!residencyTouched) {
      $('years_of_residency').value = age >= 0 ? age : '';
    }
  });

  $('years_of_residency').addEventListener('input', () => {
    residencyTouched = true;
  });

  /* ------------------------------------------------------------------
   * Step 2 & 4 — PH mobile number handling (+63 fixed prefix)
   * ---------------------------------------------------------------- */

  function bindPhoneField(localId, hiddenId, errorId) {
    const localEl = $(localId);
    localEl.addEventListener('input', () => {
      localEl.value = localEl.value.replace(/\D/g, '').slice(0, 10);
      const ok = /^9\d{9}$/.test(localEl.value);
      $(hiddenId).value = ok ? '+63' + localEl.value : '';
      if (localEl.value.length === 10) {
        $(errorId).classList.toggle('d-none', ok);
        setFieldInvalid(localEl, !ok);
      } else {
        $(errorId).classList.add('d-none');
        setFieldInvalid(localEl, false);
      }
    });
  }

  bindPhoneField('mobile_number_local', 'mobileNumberFull', 'mobileNumberError');
  bindPhoneField('emergency_contact_number_local', 'emergencyNumberFull', 'emergencyNumberError');

  /* ------------------------------------------------------------------
   * Step 3 — Occupation N/A checkbox
   * ---------------------------------------------------------------- */

  $('occupation_na').addEventListener('change', function () {
    const occInput = $('occupation');
    $('occupationNaFlag').value = this.checked ? '1' : '0';
    occInput.disabled = this.checked;
    if (this.checked) {
      occInput.value = '';
      setFieldInvalid(occInput, false);
    }
  });

  /* ------------------------------------------------------------------
   * Step 4 — Uploads (file picker + camera)
   * ---------------------------------------------------------------- */

  document.querySelectorAll('[data-upload-trigger]').forEach((btn) => {
    btn.addEventListener('click', () => {
      $('file_' + btn.dataset.uploadTrigger).click();
    });
  });

  document.querySelectorAll('input[type="file"][data-file-key]').forEach((input) => {
    input.addEventListener('change', () => {
      const file = input.files[0];
      if (!file) return;
      handleIncomingImage(input.dataset.fileKey, file);
      input.value = ''; // allow re-selecting the same file later
    });
  });

  function handleIncomingImage(key, file) {
    if (!file.type.startsWith('image/')) {
      setUploadStatus(key, 'Please choose an image file.', false);
      return;
    }
    if (file.size > 5 * 1024 * 1024) {
      setUploadStatus(key, 'That file is larger than 5MB.', false);
      return;
    }
    uploadedFiles[key] = file;
    const url = URL.createObjectURL(file);
    const preview = $('preview_' + key);
    preview.style.backgroundImage = "url('" + url + "')";
    preview.textContent = '';
    setUploadStatus(key, 'Photo added', true);
  }

  function setUploadStatus(key, message, ok) {
    const statusEl = $('status_' + key);
    statusEl.textContent = message;
    statusEl.classList.toggle('ok', !!ok);
  }

  // -- Camera modal --
  const cameraModalEl = $('cameraModal');
  const cameraModal = new bootstrap.Modal(cameraModalEl);
  const cameraVideo = $('cameraVideo');
  const cameraCanvas = $('cameraCanvas');
  const cameraError = $('cameraError');

  document.querySelectorAll('[data-camera-trigger]').forEach((btn) => {
    btn.addEventListener('click', async () => {
      activeCameraKey = btn.dataset.cameraTrigger;
      cameraError.classList.add('d-none');
      cameraModal.show();
      try {
        cameraStream = await navigator.mediaDevices.getUserMedia({
          video: { facingMode: 'environment' },
          audio: false,
        });
        cameraVideo.srcObject = cameraStream;
      } catch (err) {
        cameraError.textContent =
          'Could not access your camera. Please allow camera access, or use Upload instead.';
        cameraError.classList.remove('d-none');
      }
    });
  });

  $('captureBtn').addEventListener('click', () => {
    if (!cameraStream) return;
    const track = cameraStream.getVideoTracks()[0];
    const settings = track.getSettings ? track.getSettings() : {};
    cameraCanvas.width = settings.width || cameraVideo.videoWidth || 640;
    cameraCanvas.height = settings.height || cameraVideo.videoHeight || 480;
    cameraCanvas.getContext('2d').drawImage(cameraVideo, 0, 0, cameraCanvas.width, cameraCanvas.height);

    cameraCanvas.toBlob(
      (blob) => {
        if (blob && activeCameraKey) {
          const file = new File([blob], activeCameraKey + '.jpg', { type: 'image/jpeg' });
          handleIncomingImage(activeCameraKey, file);
        }
        cameraModal.hide();
      },
      'image/jpeg',
      0.92
    );
  });

  cameraModalEl.addEventListener('hidden.bs.modal', () => {
    if (cameraStream) {
      cameraStream.getTracks().forEach((t) => t.stop());
      cameraStream = null;
    }
    cameraVideo.srcObject = null;
  });

  /* ------------------------------------------------------------------
   * Step 5 — Review
   * ---------------------------------------------------------------- */

  function displayValue(id) {
    const el = $(id);
    if (!el) return '—';
    return el.value.trim() === '' ? '—' : el.value.trim();
  }

  function buildGroup(title, step, rows) {
    const rowsHtml = rows
      .map(
        (r) =>
          '<tr><td class="rlabel">' +
          r[0] +
          '</td><td class="rvalue">' +
          (r[1] || '—') +
          '</td></tr>'
      )
      .join('');
    return (
      '<div class="review-group">' +
      '<div class="review-group-title"><span>' +
      title +
      '</span><a class="edit-link" data-edit-step="' +
      step +
      '">Edit</a></div>' +
      '<table class="review-table">' +
      rowsHtml +
      '</table>' +
      '</div>'
    );
  }

  function populateReview() {
    const occupation = $('occupation_na').checked ? 'N/A' : displayValue('occupation');
    const mobile = $('mobileNumberFull').value || '—';
    const emergencyNumber = $('emergencyNumberFull').value || '—';

    let html = '';

    html += buildGroup('Personal Information', 1, [
      ['Full name', [displayValue('first_name'), displayValue('middle_name'), displayValue('last_name')].filter((v) => v !== '—').join(' ')],
      ['Birthdate', displayValue('birthdate')],
      ['Age', displayValue('age')],
      ['Place of birth', displayValue('place_of_birth')],
      ['Gender', displayValue('gender')],
      ['Civil status', displayValue('civil_status')],
      ['Nationality', displayValue('nationality')],
      ['Religion', displayValue('religion')],
    ]);

    html += buildGroup('Contact &amp; Location', 2, [
      ['Street / Purok', displayValue('purok')],
      ['Complete address', displayValue('complete_address')],
      ['Years of residency', displayValue('years_of_residency')],
      ['Mobile number', mobile],
      ['Email', displayValue('email')],
    ]);

    html += buildGroup('Economic Profile', 3, [
      ['Voter status', displayValue('voter_status')],
      ['Educational attainment', displayValue('educational_attainment')],
      ['Employment status', displayValue('employment_status')],
      ['Occupation', occupation],
      ['Household head', displayValue('household_head')],
      ['Number of family members', displayValue('number_of_family_members')],
    ]);

    html += buildGroup('Other Information', 4, [
      ['Emergency contact name', displayValue('emergency_contact_name')],
      ['Emergency contact number', emergencyNumber],
      ['Blood type', displayValue('blood_type')],
      ['Disability', displayValue('disability') === '—' ? 'N/A (None)' : displayValue('disability')],
    ]);

    // Photo thumbnails
    const photoLabels = { id_front: 'ID front', id_back: 'ID back', profile: 'Profile photo', cover: 'Cover photo' };
    const thumbs = Object.keys(photoLabels)
      .filter((key) => uploadedFiles[key])
      .map((key) => {
        const url = URL.createObjectURL(uploadedFiles[key]);
        return '<img class="rp-thumb" src="' + url + '" alt="' + photoLabels[key] + '" title="' + photoLabels[key] + '">';
      })
      .join('');

    html +=
      '<div class="review-group"><div class="review-group-title"><span>Documents &amp; Photos</span><a class="edit-link" data-edit-step="4">Edit</a></div><div class="review-photos">' +
      (thumbs || '<span class="field-hint">No photos uploaded.</span>') +
      '</div></div>';

    $('reviewContent').innerHTML = html;
  }

  /* ------------------------------------------------------------------
   * Step 6 — Password
   * ---------------------------------------------------------------- */

  document.querySelectorAll('[data-toggle-password]').forEach((toggle) => {
    toggle.addEventListener('click', () => {
      const input = $(toggle.dataset.togglePassword);
      const icon = toggle.querySelector('i');
      const show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      icon.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
    });
  });

  $('password').addEventListener('input', () => {
    const p = $('password').value;
    let score = 0;
    if (p.length >= 8) score++;
    if (/[a-z]/.test(p) && /[A-Z]/.test(p)) score++;
    if (/\d/.test(p)) score++;
    if (/[^A-Za-z0-9]/.test(p)) score++;
    const meter = $('passwordStrength');
    meter.className = 'password-strength' + (score > 0 ? ' s' + score : '');
    $('passwordError').classList.add('d-none');
    setFieldInvalid($('password'), false);
  });

  $('confirm_password').addEventListener('input', () => {
    $('confirmPasswordError').classList.add('d-none');
    setFieldInvalid($('confirm_password'), false);
  });

  function validateStep6() {
    let valid = true;
    const pwd = $('password').value;
    const confirm = $('confirm_password').value;
    const termsChecked = $('terms').checked;

    const pwdOk = pwd.length >= 8;
    $('passwordError').classList.toggle('d-none', pwdOk);
    setFieldInvalid($('password'), !pwdOk);
    if (!pwdOk) valid = false;

    const matchOk = pwdOk && pwd === confirm && confirm !== '';
    $('confirmPasswordError').classList.toggle('d-none', matchOk);
    setFieldInvalid($('confirm_password'), !matchOk);
    if (!matchOk) valid = false;

    $('termsError').classList.toggle('d-none', termsChecked);
    if (!termsChecked) valid = false;

    return valid;
  }

  $('createAccountBtn').addEventListener('click', async () => {
    if (!validateStep6()) {
      showAlert('Please fix the highlighted fields before continuing.', 'danger');
      return;
    }

    const btn = $('createAccountBtn');
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border" role="status"></span>Sending code…';

    try {
      const fd = new FormData();
      fd.append('email', val('email'));
      fd.append('csrf_token', $('csrfToken').value);

      const res = await fetch('api/send_otp.php', { method: 'POST', body: fd });
      const data = await res.json();

      if (!data.success) {
        showAlert(data.message || 'Could not send a verification code. Please try again.', 'danger');
        return;
      }

      if (data.devOtp) {
        // Local testing convenience only (DEV_MODE) — never shown in production.
        console.info('[DEV_MODE] OTP for testing:', data.devOtp);
      }

      $('otpEmailDisplay').textContent = val('email');
      document.querySelectorAll('#otpInputs input').forEach((i) => (i.value = ''));
      $('otpFull').value = '';
      $('otpError').classList.add('d-none');
      startResendTimer(data.expiresInSeconds ? Math.min(60, data.expiresInSeconds) : 60);
      goToStep(7);
      document.querySelector('#otpInputs input').focus();
    } catch (err) {
      showAlert('A network error occurred. Please check your connection and try again.', 'danger');
    } finally {
      btn.disabled = false;
      btn.innerHTML = originalHtml;
    }
  });

  /* ------------------------------------------------------------------
   * Step 7 — OTP entry, resend timer, and final submission
   * ---------------------------------------------------------------- */

  const otpBoxes = Array.from(document.querySelectorAll('#otpInputs input'));

  function syncOtpHidden() {
    $('otpFull').value = otpBoxes.map((b) => b.value).join('');
  }

  // Soft, non-consuming check once all 6 digits are in — gives the user
  // immediate feedback without waiting for the final Submit round-trip.
  async function checkOtpInline() {
    const otp = $('otpFull').value;
    if (!/^\d{6}$/.test(otp)) return;
    try {
      const fd = new FormData();
      fd.append('email', val('email'));
      fd.append('otp', otp);
      fd.append('csrf_token', $('csrfToken').value);
      const res = await fetch('api/verify_otp.php', { method: 'POST', body: fd });
      const data = await res.json();
      $('otpError').textContent = data.message || 'That code is invalid or has expired.';
      $('otpError').classList.toggle('d-none', !!data.success);
      otpBoxes.forEach((b) => setFieldInvalid(b, !data.success));
    } catch (err) {
      // Silent — the authoritative check happens again on final Submit.
    }
  }

  otpBoxes.forEach((box, i) => {
    box.addEventListener('input', () => {
      box.value = box.value.replace(/\D/g, '').slice(0, 1);
      if (box.value && i < otpBoxes.length - 1) otpBoxes[i + 1].focus();
      syncOtpHidden();
      $('otpError').classList.add('d-none');
      setFieldInvalid(box, false);
      checkOtpInline();
    });
    box.addEventListener('keydown', (e) => {
      if (e.key === 'Backspace' && !box.value && i > 0) {
        otpBoxes[i - 1].focus();
      }
    });
    box.addEventListener('paste', (e) => {
      const text = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');
      if (text.length) {
        e.preventDefault();
        text
          .slice(0, otpBoxes.length)
          .split('')
          .forEach((digit, idx) => {
            if (otpBoxes[idx]) otpBoxes[idx].value = digit;
          });
        syncOtpHidden();
        const nextEmpty = otpBoxes.find((b) => !b.value);
        (nextEmpty || otpBoxes[otpBoxes.length - 1]).focus();
        checkOtpInline();
      }
    });
  });

  function startResendTimer(seconds) {
    clearInterval(resendTimerId);
    let remaining = seconds;
    const resendBtn = $('resendOtpBtn');
    const timerEl = $('resendTimer');
    resendBtn.disabled = true;
    timerEl.textContent = remaining;

    resendTimerId = setInterval(() => {
      remaining -= 1;
      if (remaining <= 0) {
        clearInterval(resendTimerId);
        resendBtn.disabled = false;
        resendBtn.textContent = 'Resend code';
      } else {
        timerEl.textContent = remaining;
      }
    }, 1000);
  }

  $('resendOtpBtn').addEventListener('click', async () => {
    const btn = $('resendOtpBtn');
    btn.disabled = true;
    try {
      const fd = new FormData();
      fd.append('email', val('email'));
      fd.append('csrf_token', $('csrfToken').value);
      const res = await fetch('api/send_otp.php', { method: 'POST', body: fd });
      const data = await res.json();
      if (data.success) {
        otpBoxes.forEach((b) => (b.value = ''));
        syncOtpHidden();
        otpBoxes[0].focus();
        startResendTimer(60);
        btn.innerHTML = 'Resend code (<span id="resendTimer">60</span>s)';
      } else {
        showAlert(data.message || 'Could not resend the code.', 'danger');
        btn.disabled = false;
      }
    } catch (err) {
      showAlert('A network error occurred while resending the code.', 'danger');
      btn.disabled = false;
    }
  });

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (currentStep !== TOTAL_STEPS) return;

    syncOtpHidden();
    const otp = $('otpFull').value;
    if (!/^\d{6}$/.test(otp)) {
      $('otpError').textContent = 'Enter the 6-digit code sent to your email.';
      $('otpError').classList.remove('d-none');
      return;
    }

    if (!uploadedFiles.id_front || !uploadedFiles.id_back || !uploadedFiles.profile) {
      showAlert('Some required photos are missing. Please go back to Step 4.', 'danger');
      goToStep(4);
      return;
    }

    const submitBtn = $('submitOtpBtn');
    const originalHtml = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border" role="status"></span>Creating account…';

    try {
      const fd = new FormData(form);
      fd.set('terms', $('terms').checked ? '1' : '0');
      fd.append('valid_id_front', uploadedFiles.id_front, 'id_front.jpg');
      fd.append('valid_id_back', uploadedFiles.id_back, 'id_back.jpg');
      fd.append('profile_photo', uploadedFiles.profile, 'profile.jpg');
      if (uploadedFiles.cover) {
        fd.append('cover_photo', uploadedFiles.cover, 'cover.jpg');
      }

      const res = await fetch('api/register.php', { method: 'POST', body: fd });
      const responseText = await res.text();
      let data;
      try {
        data = JSON.parse(responseText);
      } catch (parseError) {
        console.error('[SafeBrgy registration] Server returned a non-JSON response:', {
          status: res.status,
          response: responseText,
        });
        showAlert('The server returned an unexpected error. Press F12 and open the Console or Network response for details.', 'danger');
        return;
      }

      if (!data.success) {
        console.error('[SafeBrgy registration] Request failed:', {
          status: res.status,
          message: data.message,
          debug: data.debug || null,
        });
        if (data.debug) {
          console.error('[SafeBrgy registration]', data.debug);
        }
        if (data.errors && data.errors.otp) {
          $('otpError').textContent = data.errors.otp;
          $('otpError').classList.remove('d-none');
        } else if (data.errors) {
          const firstStepWithError = findStepForErrors(Object.keys(data.errors));
          showAlert(data.message || 'Please correct the highlighted fields.', 'danger');
          if (firstStepWithError && firstStepWithError !== TOTAL_STEPS) goToStep(firstStepWithError);
        } else {
          showAlert(data.message || 'Something went wrong. Please try again.', 'danger');
        }
        return;
      }

      const successModal = new bootstrap.Modal($('successModal'));
      successModal.show();
      window.setTimeout(() => {
        window.location.href = '/safebrgy/login';
      }, 4000);
    } catch (err) {
      showAlert('A network error occurred. Please try again.', 'danger');
    } finally {
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalHtml;
    }
  });

  const FIELD_STEP_MAP = {
    first_name: 1, last_name: 1, birthdate: 1, place_of_birth: 1, gender: 1,
    civil_status: 1, nationality: 1, religion: 1,
    purok: 2, complete_address: 2, years_of_residency: 2, mobile_number: 2, email: 2,
    voter_status: 3, educational_attainment: 3, employment_status: 3, occupation: 3,
    household_head: 3, number_of_family_members: 3,
    emergency_contact_name: 4, emergency_contact_number: 4, blood_type: 4, disability: 4,
    valid_id_front: 4, valid_id_back: 4, profile_photo: 4,
    password: 6, confirm_password: 6, terms: 6,
  };

  function findStepForErrors(errorKeys) {
    for (const key of errorKeys) {
      if (FIELD_STEP_MAP[key]) return FIELD_STEP_MAP[key];
    }
    return null;
  }

  /* ------------------------------------------------------------------
   * Init
   * ---------------------------------------------------------------- */
  goToStep(1);
})();
