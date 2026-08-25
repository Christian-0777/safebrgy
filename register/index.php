<?php
/**
 * index.php
 * Resident Registration — multi-step wizard.
 * All steps are rendered into the DOM up front and shown/hidden by
 * assets/js/main.js, so field values are never lost while a user
 * moves back and forth between steps.
 */

declare(strict_types=1);

require __DIR__ . '/includes/functions.php';

$csrfToken = csrf_token();
$today = (new DateTime('today'))->format('Y-m-d');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>Resident Registration</title>
<meta name="description" content="Register as a resident — personal, contact, and household information collected in one guided form.">
<base href="/register/">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Public+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="assets/css/style.css" rel="stylesheet">
</head>
<body>

<div class="app-shell">

  <!-- ============ Mobile condensed top bar ============ -->
  <div class="brand-bar">
    <div class="brand-mark">
      <span class="brand-seal">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 2L20 6V11C20 16 16.5 20.2 12 22C7.5 20.2 4 16 4 11V6L12 2Z" stroke="#FDBA74" stroke-width="1.4"/><path d="M8.5 12L11 14.5L16 9" stroke="#FDBA74" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </span>
      <span class="brand-wordmark">
        <span class="line1">Barangay Resident</span>
        <span class="line2">Registry</span>
      </span>
    </div>
    <span class="step-count" id="mobileStepCount">Step 1 of 7</span>
  </div>

  <!-- ============ Left branding panel (desktop) ============ -->
  <aside class="brand-panel">
    <div class="brand-mark">
      <span class="brand-seal">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 2L20 6V11C20 16 16.5 20.2 12 22C7.5 20.2 4 16 4 11V6L12 2Z" stroke="#FDBA74" stroke-width="1.4"/><path d="M8.5 12L11 14.5L16 9" stroke="#FDBA74" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </span>
      <span class="brand-wordmark">
        <span class="line1">Barangay Resident</span>
        <span class="line2">Registry</span>
      </span>
    </div>

    <div class="brand-copy">
      <h1>Your record in the resident registry, filed in seven short steps.</h1>
      <p>We'll walk you through your personal, contact, and household details. Nothing is submitted until you confirm everything on the review step.</p>
    </div>

    <ol class="ledger" id="ledger">
      <li class="ledger-item is-active" data-ledger-step="1">
        <span class="ledger-stamp">1</span>
        <span class="ledger-text"><span class="label">Step 1</span><span class="title">Personal Information</span></span>
      </li>
      <li class="ledger-item" data-ledger-step="2">
        <span class="ledger-stamp">2</span>
        <span class="ledger-text"><span class="label">Step 2</span><span class="title">Contact &amp; Location</span></span>
      </li>
      <li class="ledger-item" data-ledger-step="3">
        <span class="ledger-stamp">3</span>
        <span class="ledger-text"><span class="label">Step 3</span><span class="title">Economic Profile</span></span>
      </li>
      <li class="ledger-item" data-ledger-step="4">
        <span class="ledger-stamp">4</span>
        <span class="ledger-text"><span class="label">Step 4</span><span class="title">Other Information</span></span>
      </li>
      <li class="ledger-item" data-ledger-step="5">
        <span class="ledger-stamp">5</span>
        <span class="ledger-text"><span class="label">Step 5</span><span class="title">Review</span></span>
      </li>
      <li class="ledger-item" data-ledger-step="6">
        <span class="ledger-stamp">6</span>
        <span class="ledger-text"><span class="label">Step 6</span><span class="title">Password</span></span>
      </li>
      <li class="ledger-item" data-ledger-step="7">
        <span class="ledger-stamp">7</span>
        <span class="ledger-text"><span class="label">Step 7</span><span class="title">Verification</span></span>
      </li>
    </ol>

    <p class="brand-footnote">Your information is used only to process your resident record and is never shared without your consent.</p>
  </aside>

  <!-- ============ Right content panel ============ -->
  <main class="content-panel">
    <div class="form-card">

      <div class="dot-progress" id="dotProgress">
        <span class="is-active" data-dot="1"></span>
        <span data-dot="2"></span>
        <span data-dot="3"></span>
        <span data-dot="4"></span>
        <span data-dot="5"></span>
        <span data-dot="6"></span>
        <span data-dot="7"></span>
      </div>

      <div id="formAlert" class="alert form-alert d-none" role="alert"></div>

      <form id="registrationForm" novalidate autocomplete="off">
        <input type="hidden" name="csrf_token" id="csrfToken" value="<?= htmlspecialchars($csrfToken) ?>">
        <input type="hidden" name="mobile_number" id="mobileNumberFull">
        <input type="hidden" name="emergency_contact_number" id="emergencyNumberFull">
        <input type="hidden" name="occupation_na" id="occupationNaFlag" value="0">
        <input type="hidden" name="otp" id="otpFull">

        <!-- ===================== STEP 1 — Personal Information ===================== -->
        <section class="step-pane is-visible" data-step="1">
          <p class="step-eyebrow">Step 1 of 7</p>
          <h2 class="step-heading">Personal information</h2>
          <p class="step-subheading">Let's start with your name and basic personal details.</p>

          <div class="row g-3">
            <div class="col-md-4">
              <label class="field-label" for="first_name">First name<span class="req">*</span></label>
              <input type="text" class="form-control" id="first_name" name="first_name" required>
              <div class="invalid-feedback">First name is required.</div>
            </div>
            <div class="col-md-4">
              <label class="field-label" for="middle_name">Middle name</label>
              <input type="text" class="form-control" id="middle_name" name="middle_name">
            </div>
            <div class="col-md-4">
              <label class="field-label" for="last_name">Last name<span class="req">*</span></label>
              <input type="text" class="form-control" id="last_name" name="last_name" required>
              <div class="invalid-feedback">Last name is required.</div>
            </div>

            <div class="col-md-4">
              <label class="field-label" for="birthdate">Birthdate<span class="req">*</span></label>
              <input type="date" class="form-control" id="birthdate" name="birthdate" max="<?= htmlspecialchars($today) ?>" required>
              <div class="invalid-feedback">Select a valid birthdate.</div>
            </div>
            <div class="col-md-4">
              <label class="field-label" for="age">Age</label>
              <input type="text" class="form-control" id="age" name="age" placeholder="Auto-calculated" readonly>
              <p class="field-hint">Filled in automatically from your birthdate.</p>
            </div>
            <div class="col-md-4">
              <label class="field-label" for="place_of_birth">Place of birth<span class="req">*</span></label>
              <input type="text" class="form-control" id="place_of_birth" name="place_of_birth" placeholder="City / Province" required>
              <div class="invalid-feedback">Place of birth is required.</div>
            </div>

            <div class="col-md-6">
              <label class="field-label" for="gender">Gender<span class="req">*</span></label>
              <select class="form-select" id="gender" name="gender" required>
                <?php render_options(GENDER_OPTIONS, 'Select gender'); ?>
              </select>
              <div class="invalid-feedback">Select a gender.</div>
            </div>
            <div class="col-md-6">
              <label class="field-label" for="civil_status">Civil status<span class="req">*</span></label>
              <select class="form-select" id="civil_status" name="civil_status" required>
                <?php render_options(CIVIL_STATUS_OPTIONS, 'Select civil status'); ?>
              </select>
              <div class="invalid-feedback">Select a civil status.</div>
            </div>

            <div class="col-md-6">
              <label class="field-label" for="nationality">Nationality<span class="req">*</span></label>
              <select class="form-select" id="nationality" name="nationality" required>
                <?php render_options(NATIONALITY_OPTIONS, 'Select nationality'); ?>
              </select>
              <div class="invalid-feedback">Select a nationality.</div>
            </div>
            <div class="col-md-6">
              <label class="field-label" for="religion">Religion<span class="req">*</span></label>
              <select class="form-select" id="religion" name="religion" required>
                <?php render_options(RELIGION_OPTIONS, 'Select religion'); ?>
              </select>
              <div class="invalid-feedback">Select a religion.</div>
            </div>
          </div>

          <div class="step-nav justify-content-end">
            <button type="button" class="btn btn-primary" data-next>Next <i class="bi bi-arrow-right ms-1"></i></button>
          </div>
        </section>

        <!-- ===================== STEP 2 — Contact & Location ===================== -->
        <section class="step-pane" data-step="2">
          <p class="step-eyebrow">Step 2 of 7</p>
          <h2 class="step-heading">Contact &amp; location</h2>
          <p class="step-subheading">Where you live and how we can reach you.</p>

          <div class="row g-3">
            <div class="col-md-6">
              <label class="field-label" for="purok">Street / Purok<span class="req">*</span></label>
              <select class="form-select" id="purok" name="purok" required>
                <?php render_options(PUROK_OPTIONS, 'Select street / purok'); ?>
              </select>
              <div class="invalid-feedback">Select a street/purok.</div>
            </div>
            <div class="col-md-6">
              <label class="field-label" for="years_of_residency">Years of residency<span class="req">*</span></label>
              <input type="number" min="0" max="130" class="form-control" id="years_of_residency" name="years_of_residency" required>
              <p class="field-hint">Defaults to your age — edit if you moved here later.</p>
              <div class="invalid-feedback">Enter a valid number of years.</div>
            </div>

            <div class="col-12">
              <label class="field-label" for="complete_address">Complete address<span class="req">*</span></label>
              <input type="text" class="form-control" id="complete_address" name="complete_address" placeholder="House/Lot/Block No., Street" required>
              <div class="invalid-feedback">Complete address is required.</div>
            </div>

            <div class="col-md-6">
              <label class="field-label" for="mobile_number_local">Mobile number<span class="req">*</span></label>
              <div class="input-group">
                <span class="input-group-text">+63</span>
                <input type="tel" class="form-control" id="mobile_number_local" placeholder="9XXXXXXXXX" inputmode="numeric" maxlength="10" required>
              </div>
              <p class="field-hint">Philippine mobile numbers only, e.g. 9171234567.</p>
              <div class="invalid-feedback d-block d-none" id="mobileNumberError">Enter a valid 10-digit mobile number.</div>
            </div>
            <div class="col-md-6">
              <label class="field-label" for="email">Email address<span class="req">*</span></label>
              <input type="email" class="form-control" id="email" name="email" placeholder="you@example.com" required>
              <div class="invalid-feedback">Enter a valid email address.</div>
            </div>
          </div>

          <div class="step-nav">
            <button type="button" class="btn btn-outline-secondary" data-back><i class="bi bi-arrow-left me-1"></i> Back</button>
            <button type="button" class="btn btn-primary" data-next>Next <i class="bi bi-arrow-right ms-1"></i></button>
          </div>
        </section>

        <!-- ===================== STEP 3 — Economic Profile ===================== -->
        <section class="step-pane" data-step="3">
          <p class="step-eyebrow">Step 3 of 7</p>
          <h2 class="step-heading">Economic profile</h2>
          <p class="step-subheading">A few details about your work, education, and household.</p>

          <div class="row g-3">
            <div class="col-md-6">
              <label class="field-label" for="voter_status">Voter status<span class="req">*</span></label>
              <select class="form-select" id="voter_status" name="voter_status" required>
                <?php render_options(VOTER_STATUS_OPTIONS, 'Select voter status'); ?>
              </select>
              <div class="invalid-feedback">Select a voter status.</div>
            </div>
            <div class="col-md-6">
              <label class="field-label" for="educational_attainment">Educational attainment<span class="req">*</span></label>
              <select class="form-select" id="educational_attainment" name="educational_attainment" required>
                <?php render_options(EDUCATION_OPTIONS, 'Select educational attainment'); ?>
              </select>
              <div class="invalid-feedback">Select educational attainment.</div>
            </div>

            <div class="col-md-6">
              <label class="field-label" for="employment_status">Employment status<span class="req">*</span></label>
              <select class="form-select" id="employment_status" name="employment_status" required>
                <?php render_options(EMPLOYMENT_OPTIONS, 'Select employment status'); ?>
              </select>
              <div class="invalid-feedback">Select an employment status.</div>
            </div>
            <div class="col-md-6">
              <label class="field-label" for="occupation">Occupation</label>
              <input type="text" class="form-control" id="occupation" name="occupation" placeholder="e.g. Teacher">
              <div class="form-check mt-2">
                <input class="form-check-input" type="checkbox" id="occupation_na">
                <label class="form-check-label" for="occupation_na" style="font-size:13px;">Not applicable</label>
              </div>
              <div class="invalid-feedback">Enter an occupation, or check N/A.</div>
            </div>

            <div class="col-md-6">
              <label class="field-label" for="household_head">Household head<span class="req">*</span></label>
              <input type="text" class="form-control" id="household_head" name="household_head" placeholder="Full name" required>
              <div class="invalid-feedback">Household head is required.</div>
            </div>
            <div class="col-md-6">
              <label class="field-label" for="number_of_family_members">Number of family members<span class="req">*</span></label>
              <input type="number" min="1" max="50" class="form-control" id="number_of_family_members" name="number_of_family_members" required>
              <div class="invalid-feedback">Enter a valid number of family members.</div>
            </div>
          </div>

          <div class="step-nav">
            <button type="button" class="btn btn-outline-secondary" data-back><i class="bi bi-arrow-left me-1"></i> Back</button>
            <button type="button" class="btn btn-primary" data-next>Next <i class="bi bi-arrow-right ms-1"></i></button>
          </div>
        </section>

        <!-- ===================== STEP 4 — Other Information ===================== -->
        <section class="step-pane" data-step="4">
          <p class="step-eyebrow">Step 4 of 7</p>
          <h2 class="step-heading">Other information</h2>
          <p class="step-subheading">Emergency contact, health details, and identification photos.</p>

          <div class="row g-3">
            <div class="col-md-6">
              <label class="field-label" for="emergency_contact_name">Emergency contact name<span class="req">*</span></label>
              <input type="text" class="form-control" id="emergency_contact_name" name="emergency_contact_name" required>
              <div class="invalid-feedback">Emergency contact name is required.</div>
            </div>
            <div class="col-md-6">
              <label class="field-label" for="emergency_contact_number_local">Emergency contact number<span class="req">*</span></label>
              <div class="input-group">
                <span class="input-group-text">+63</span>
                <input type="tel" class="form-control" id="emergency_contact_number_local" placeholder="9XXXXXXXXX" inputmode="numeric" maxlength="10" required>
              </div>
              <div class="invalid-feedback d-block d-none" id="emergencyNumberError">Enter a valid 10-digit mobile number.</div>
            </div>

            <div class="col-md-6">
              <label class="field-label" for="blood_type">Blood type<span class="req">*</span></label>
              <select class="form-select" id="blood_type" name="blood_type" required>
                <?php render_options(BLOOD_TYPE_OPTIONS, 'Select blood type'); ?>
              </select>
              <div class="invalid-feedback">Select a blood type.</div>
            </div>
            <div class="col-md-6">
              <label class="field-label" for="disability">Disability</label>
              <select class="form-select" id="disability" name="disability" required>
                <?php render_options(DISABILITY_OPTIONS, 'Select disability status'); ?>
              </select>
              <div class="invalid-feedback">Select an option — choose "N/A (None)" if this doesn't apply.</div>
              <p class="field-hint">Choose "N/A (None)" to skip this.</p>
            </div>
          </div>

          <p class="field-section-title">Valid ID</p>
          <div class="row g-3">
            <div class="col-md-6">
              <div class="upload-card">
                <p class="upload-title">ID — front side<span class="req">*</span></p>
                <p class="upload-hint">JPG, PNG, or WebP. Max 5MB.</p>
                <div class="upload-preview" id="preview_id_front">No photo yet</div>
                <div class="upload-actions">
                  <button type="button" class="btn btn-outline-secondary" data-upload-trigger="id_front"><i class="bi bi-upload me-1"></i>Upload</button>
                  <button type="button" class="btn btn-outline-secondary" data-camera-trigger="id_front"><i class="bi bi-camera me-1"></i>Camera</button>
                </div>
                <input type="file" accept="image/*" class="d-none" id="file_id_front" data-file-key="id_front">
                <div class="upload-status" id="status_id_front"></div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="upload-card">
                <p class="upload-title">ID — back side<span class="req">*</span></p>
                <p class="upload-hint">JPG, PNG, or WebP. Max 5MB.</p>
                <div class="upload-preview" id="preview_id_back">No photo yet</div>
                <div class="upload-actions">
                  <button type="button" class="btn btn-outline-secondary" data-upload-trigger="id_back"><i class="bi bi-upload me-1"></i>Upload</button>
                  <button type="button" class="btn btn-outline-secondary" data-camera-trigger="id_back"><i class="bi bi-camera me-1"></i>Camera</button>
                </div>
                <input type="file" accept="image/*" class="d-none" id="file_id_back" data-file-key="id_back">
                <div class="upload-status" id="status_id_back"></div>
              </div>
            </div>
          </div>

          <p class="field-section-title">Photos</p>
          <div class="row g-3">
            <div class="col-md-6">
              <div class="upload-card">
                <p class="upload-title">Profile photo<span class="req">*</span></p>
                <p class="upload-hint">A clear photo of your face.</p>
                <div class="upload-preview is-round" id="preview_profile">No photo</div>
                <div class="upload-actions">
                  <button type="button" class="btn btn-outline-secondary" data-upload-trigger="profile"><i class="bi bi-upload me-1"></i>Upload</button>
                  <button type="button" class="btn btn-outline-secondary" data-camera-trigger="profile"><i class="bi bi-camera me-1"></i>Camera</button>
                </div>
                <input type="file" accept="image/*" class="d-none" id="file_profile" data-file-key="profile">
                <div class="upload-status" id="status_profile"></div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="upload-card">
                <p class="upload-title">Cover photo</p>
                <p class="upload-hint">Optional banner image.</p>
                <div class="upload-preview" id="preview_cover">No photo yet</div>
                <div class="upload-actions">
                  <button type="button" class="btn btn-outline-secondary" data-upload-trigger="cover"><i class="bi bi-upload me-1"></i>Upload</button>
                  <button type="button" class="btn btn-outline-secondary" data-camera-trigger="cover"><i class="bi bi-camera me-1"></i>Camera</button>
                </div>
                <input type="file" accept="image/*" class="d-none" id="file_cover" data-file-key="cover">
                <div class="upload-status" id="status_cover"></div>
              </div>
            </div>
          </div>

          <div class="step-nav">
            <button type="button" class="btn btn-outline-secondary" data-back><i class="bi bi-arrow-left me-1"></i> Back</button>
            <button type="button" class="btn btn-primary" data-next>Next <i class="bi bi-arrow-right ms-1"></i></button>
          </div>
        </section>

        <!-- ===================== STEP 5 — Review ===================== -->
        <section class="step-pane" data-step="5">
          <p class="step-eyebrow">Step 5 of 7</p>
          <h2 class="step-heading">Review your information</h2>
          <p class="step-subheading">Check every section carefully. Use "Edit" to jump back and fix anything.</p>

          <div id="reviewContent"><!-- populated by JS --></div>

          <div class="step-nav">
            <button type="button" class="btn btn-outline-secondary" data-back><i class="bi bi-arrow-left me-1"></i> Back</button>
            <button type="button" class="btn btn-primary" data-next>Confirm &amp; continue <i class="bi bi-arrow-right ms-1"></i></button>
          </div>
        </section>

        <!-- ===================== STEP 6 — Password ===================== -->
        <section class="step-pane" data-step="6">
          <p class="step-eyebrow">Step 6 of 7</p>
          <h2 class="step-heading">Create your password</h2>
          <p class="step-subheading">You'll use this together with your email to sign in.</p>

          <div class="row g-3">
            <div class="col-md-6">
              <label class="field-label" for="password">Password<span class="req">*</span></label>
              <div class="input-group">
                <input type="password" class="form-control" id="password" name="password" minlength="8" required>
                <span class="input-group-text toggle-visibility" data-toggle-password="password"><i class="bi bi-eye"></i></span>
              </div>
              <div class="password-strength" id="passwordStrength"><span></span><span></span><span></span><span></span></div>
              <p class="field-hint">At least 8 characters. Mix letters, numbers, and symbols for a stronger password.</p>
              <div class="invalid-feedback d-block d-none" id="passwordError">Password must be at least 8 characters.</div>
            </div>
            <div class="col-md-6">
              <label class="field-label" for="confirm_password">Re-enter password<span class="req">*</span></label>
              <div class="input-group">
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                <span class="input-group-text toggle-visibility" data-toggle-password="confirm_password"><i class="bi bi-eye"></i></span>
              </div>
              <div class="invalid-feedback d-block d-none" id="confirmPasswordError">Passwords do not match.</div>
            </div>

            <div class="col-12">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="terms" required>
                <label class="form-check-label" for="terms" style="font-size:13.5px;">
                  I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms &amp; Conditions</a> and confirm that the information I provided is true and correct.
                </label>
                <div class="invalid-feedback d-block d-none" id="termsError">You must accept the Terms &amp; Conditions.</div>
              </div>
            </div>
          </div>

          <div class="step-nav">
            <button type="button" class="btn btn-outline-secondary" data-back><i class="bi bi-arrow-left me-1"></i> Back</button>
            <button type="button" class="btn btn-accent" id="createAccountBtn">Create account <i class="bi bi-arrow-right ms-1"></i></button>
          </div>
        </section>

        <!-- ===================== STEP 7 — OTP Verification ===================== -->
        <section class="step-pane" data-step="7">
          <p class="step-eyebrow">Step 7 of 7</p>
          <h2 class="step-heading">Verify your email</h2>
          <p class="step-subheading">Enter the 6-digit code we sent to <strong id="otpEmailDisplay">your email</strong>.</p>

          <div class="otp-inputs" id="otpInputs">
            <input type="text" inputmode="numeric" maxlength="1" class="form-control" data-otp-index="0">
            <input type="text" inputmode="numeric" maxlength="1" class="form-control" data-otp-index="1">
            <input type="text" inputmode="numeric" maxlength="1" class="form-control" data-otp-index="2">
            <input type="text" inputmode="numeric" maxlength="1" class="form-control" data-otp-index="3">
            <input type="text" inputmode="numeric" maxlength="1" class="form-control" data-otp-index="4">
            <input type="text" inputmode="numeric" maxlength="1" class="form-control" data-otp-index="5">
          </div>
          <div class="invalid-feedback d-block d-none" id="otpError">That code is invalid or has expired.</div>
          <p class="field-hint">
            Didn't get a code? <button type="button" class="btn btn-link small-link p-0" id="resendOtpBtn" disabled>Resend code (<span id="resendTimer">60</span>s)</button>
          </p>

          <div class="step-nav">
            <button type="button" class="btn btn-outline-secondary" data-back><i class="bi bi-arrow-left me-1"></i> Back</button>
            <button type="submit" class="btn btn-primary" id="submitOtpBtn">Submit <i class="bi bi-check2 ms-1"></i></button>
          </div>
        </section>

      </form>
    </div>
  </main>
</div>

<!-- ============ Camera capture modal ============ -->
<div class="modal fade" id="cameraModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Take a photo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <video class="camera-video" id="cameraVideo" autoplay playsinline></video>
        <canvas class="camera-canvas" id="cameraCanvas"></canvas>
        <p class="form-alert alert alert-warning d-none mt-3" id="cameraError"></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="captureBtn"><i class="bi bi-camera-fill me-1"></i>Capture</button>
      </div>
    </div>
  </div>
</div>

<!-- ============ Terms & Conditions modal ============ -->
<div class="modal fade" id="termsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Terms &amp; Conditions</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" style="font-size:13.5px;">
        <p>By registering, you confirm that the personal, contact, and household information you provide is accurate and current, and you authorize its use for maintaining your resident record and for the delivery of related community services.</p>
        <p>Your uploaded identification and photos are used solely to verify your identity for this registration and are handled in accordance with applicable data privacy regulations. You may request a copy or correction of your record at any time.</p>
        <p>Submitting false information may result in your registration being rejected or your account being deactivated.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- ============ Success modal ============ -->
<div class="modal fade" id="successModal" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center">
      <div class="modal-body py-5 px-4">
        <div class="success-seal">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <h4 class="step-heading" style="font-size:22px;">Account created</h4>
        <p class="step-subheading mb-4">Your resident account was created and pending to review. We will sent an email/sms once your account is activated.</p>
        <p class="field-hint mb-3">Redirecting you to login...</p>
        <a href="/safebrgy/login" class="btn btn-primary px-4">Go to login</a>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/main.js"></script>
</body>
</html>
