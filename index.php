<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/shared/remember_me.php';
session_start();
$rememberedRole = restoreRememberedLogin(safeBrgy_db_connect());
if ($rememberedRole === 'resident') {
  header('Location: /safebrgy/dashboard');
  exit;
}
if ($rememberedRole === 'admin') {
  header('Location: /safebrgy/admin/dashboard');
  exit;
}
// index.php - Landing page for SafeBrgy (simple PHP template)
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>SafeBrgy — Barangay San Jose</title>
  <link rel="icon" type="image/png" href="assets/img/seal.png">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="assets/css/shared/colors.css">
  <link rel="stylesheet" href="assets/css/shared/layout.css">
  <link rel="stylesheet" href="assets/css/public/modals/login.css">
  <link rel="stylesheet" href="assets/css/shared/cookie-consent.css?v=2">
  <link rel="stylesheet" href="assets/style.css">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
  <header class="site-header">
    <div class="container header-inner">
      <a href="#home" class="header-logo">
        <img src="assets/img/seal.png" alt="Barangay San Jose Logo" class="logo-image">
        <span>Barangay San Jose</span>
      </a>
      <nav class="main-nav">
        <a href="#home">Home</a>
        <a href="#about">About</a>
        <a href="#services">Services</a>
        <a href="#officials">Officials</a>
        <a href="#contact">Contact</a>
      </nav>
      <div class="header-actions">
        <a href="register/" class="btn-outline">Register</a>
        <a href="login" class="btn-primary">Login</a>
      </div>
      <button class="nav-toggle" data-nav-toggle aria-label="Toggle navigation"><i class="fas fa-bars"></i></button>
    </div>
    <nav class="mobile-nav" id="mobileNav">
      <a href="#home">Home</a>
      <a href="#about">About</a>
      <a href="#services">Services</a>
      <a href="#officials">Officials</a>
      <a href="#contact">Contact</a>
      <a href="login">Login</a>
    </nav>
  </header>

  <main>
    <!-- Hero -->
    <section id="home" class="hero">
      <div class="hero-overlay">
        <div class="container hero-content">
          <div class="hero-text">
            <h2>SafeBrgy</h2>
            <p class="lead">Welcome to the SafeBrgy — a modern solution built to bring essential barangay services right to your fingertips.</p>
          </div>
          <div class="hero-seal">
            <img src="assets/img/seal.png" alt="Barangay Seal">
          </div>
        </div>
      </div>
    </section>

    <!-- About -->
    <section id="about" class="section about">
      <div class="container">
        <h3>About Us</h3>
        <p>Our mission is to empower our community by delivering fast and reliable barangay services through digital innovation.</p>

        <div class="register-guide">
          <h4>SafeBrgy Portal Account Registration Guide</h4>
          <ol>
            <li><strong>Step 1:</strong> Click the <em>Register</em> button at the top-right of the homepage.</li>
            <li><strong>Step 2:</strong> Complete the resident account creation form and submit. Wait for admin verification.</li>
            <li><strong>Step 3:</strong> After verification, you may log in directly.</li>
          </ol>
        </div>
      </div>
    </section>

    <!-- Services -->
    <section id="services" class="section services">
      <div class="container">
        <h3>Our Services</h3>
        <p class="sub">Efficient Document Processing: Quick and reliable issuance of barangay documents and certifications.</p>

        <div class="services-grid">
          <?php
          $services = [
            ['title'=>'Barangay Clearance','desc'=>'Proof that a person has no bad record in the barangay.','icon'=>'verified_user'],
            ['title'=>'Barangay Residency','desc'=>'Confirms that a person is a resident of the barangay.','icon'=>'home'],
            ['title'=>'Barangay Indigency','desc'=>'Issued to low-income individuals for aid, scholarships, or medical.','icon'=>'help_outline'],
            ['title'=>'Business Clearance','desc'=>'Permission for a business to operate within the barangay.','icon'=>'business'],
            ['title'=>'Incident Report','desc'=>'Record of complaints or incidents filed at the barangay.','icon'=>'report','action'=>'Report'],
            ['title'=>'Lost Property','desc'=>'Assistance for residents who have misplaced or lost belongings.','icon'=>'search','action'=>'Report'],
          ];
          foreach($services as $s): ?>
            <article class="service-card">
              <i class="material-icons service-icon"><?php echo $s['icon']; ?></i>
              <h4><?php echo $s['title']; ?></h4>
              <p><?php echo $s['desc']; ?></p>
              <button class="btn-request" type="button" data-service="<?php echo htmlspecialchars($s['title']); ?>"><?php echo $s['action'] ?? 'Request now'; ?></button>
            </article>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <!-- Officials -->
    <section id="officials" class="section officials">
      <div class="container">
        <h3>Barangay Officials</h3>
        <p class="sub">Our officials of Barangay San Jose</p>

        <div class="officials-grid">
          <div class="column">
            <ul>
              <li><strong>Reynaldo C Taruc</strong><span>Brgy Captain</span></li>
              <li><strong>Marlon Manalili</strong><span>Kagawad</span></li>
              <li><strong>Reynaldo Muga</strong><span>Kagawad</span></li>
              <li><strong>Paulino Batac</strong><span>Kagawad</span></li>
              <li><strong>Amang De Jesus</strong><span>Kagawad</span></li>
              <li><strong>Ricardo Suarez</strong><span>Kagawad</span></li>
              <li><strong>Pablo Valerio</strong><span>Kagawad</span></li>
              <li><strong>Ponciano Calma</strong><span>Kagawad</span></li>
              <li><strong>Tante Sulit</strong><span>Secretary</span></li>
              <li><strong>Bernabe Muga</strong><span>Treasurer</span></li>
            </ul>
          </div>
          <div class="column">
            <ul>
              <li><strong>Chelsie Anne D.J. Galang</strong><span>SK Chairperson</span></li>
              <li><strong>Andrea Reasele Suarez</strong><span>Kagawad</span></li>
              <li><strong>Ladylyn Cao</strong><span>Kagawad</span></li>
              <li><strong>Rochelle Dianne Batac</strong><span>Kagawad</span></li>
              <li><strong>Ivan Paolo Miranda</strong><span>Kagawad</span></li>
              <li><strong>Emmanual Rivera</strong><span>Kagawad</span></li>
              <li><strong>Jimuel Dela Cruz</strong><span>Kagawad</span></li>
              <li><strong>Catherine Gonzales</strong><span>Kagawad</span></li>
              <li><strong>Mark Jayson Manalili</strong><span>Secretary</span></li>
              <li><strong>Alexi Nicole O. Cao</strong><span>Treasurer</span></li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    <section id="register" class="section register">
      <div class="container register-notice">
        <h3>Resident account registration has moved</h3>
        <p>The resident account creation process is now available on our dedicated registration page.</p>
        <a class="btn-primary register-notice-button" href="register/">Create account now</a>
      </div>
      <div class="container legacy-register-form" aria-hidden="true">
        <h3>Create Resident Account</h3>
        <p><strong>Note:</strong> Your account will require admin approval before you can log in.</p>

        <?php
        if (isset($_SESSION['registration_errors'])) {
            echo '<div class="alert alert-danger"><ul>';
            foreach ($_SESSION['registration_errors'] as $error) {
                echo '<li>' . htmlspecialchars($error) . '</li>';
            }
            echo '</ul></div>';
            unset($_SESSION['registration_errors']);
        }
        ?>

        <form action="register.php" method="post" enctype="multipart/form-data" class="register-form">
          <div style="display:flex;gap:12px;flex-wrap:wrap">
            <input name="first_name" placeholder="First Name" required style="flex:1;padding:10px;border-radius:8px;border:1px solid #d1d5db">
            <input name="middle_name" placeholder="Middle Name" style="flex:1;padding:10px;border-radius:8px;border:1px solid #d1d5db">
            <input name="last_name" placeholder="Last Name" required style="flex:1;padding:10px;border-radius:8px;border:1px solid #d1d5db">
          </div>

          <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:12px;">
            <div style="flex:1;">
              <label>Birthdate</label><br>
              <input type="date" name="birthdate" required style="width:100%;padding:10px;border-radius:8px;border:1px solid #d1d5db">
            </div>
            <div style="flex:1;">
              <label>Age</label><br>
              <input type="number" name="age" placeholder="Age" required style="width:100%;padding:10px;border-radius:8px;border:1px solid #d1d5db">
            </div>
            <div style="flex:1;">
              <label>Place of Birth</label><br>
              <input name="place_of_birth" placeholder="Place of Birth" required style="width:100%;padding:10px;border-radius:8px;border:1px solid #d1d5db">
            </div>
          </div>

          <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:12px;">
            <div style="flex:1;">
              <label>Gender</label><br>
              <select name="gender" required style="width:100%;padding:10px;border-radius:8px;border:1px solid #d1d5db">
                <option value="">Select Gender</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
              </select>
            </div>
            <div style="flex:1;">
              <label>Civil Status</label><br>
              <select name="civil_status" required style="width:100%;padding:10px;border-radius:8px;border:1px solid #d1d5db">
                <option value="">Select Civil Status</option>
                <option value="Single">Single</option>
                <option value="Married">Married</option>
                <option value="Divorced">Divorced</option>
                <option value="Widowed">Widowed</option>
                <option value="Separated">Separated</option>
              </select>
            </div>
          </div>

          <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:12px;">
            <div style="flex:1;">
              <label>Nationality</label><br>
              <input name="nationality" placeholder="Nationality" required style="width:100%;padding:10px;border-radius:8px;border:1px solid #d1d5db">
            </div>
            <div style="flex:1;">
              <label>Religion</label><br>
              <input name="religion" placeholder="Religion" required style="width:100%;padding:10px;border-radius:8px;border:1px solid #d1d5db">
            </div>
          </div>

          <div style="margin-top:12px;">
            <label>Complete Address</label><br>
            <input name="address" placeholder="Complete Address" required style="width:100%;padding:10px;border-radius:8px;border:1px solid #d1d5db">
          </div>

          <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:12px;">
            <div style="flex:1;">
              <label>Purok</label><br>
              <select name="purok" required style="width:100%;padding:10px;border-radius:8px;border:1px solid #d1d5db">
                <option value="">Select Purok</option>
                <option value="Purok 1">Purok 1</option>
                <option value="Purok 2">Purok 2</option>
                <option value="Purok 3">Purok 3</option>
                <option value="Purok 4">Purok 4</option>
                <option value="Purok 5">Purok 5</option>
              </select>
            </div>
            <div style="flex:1;">
              <label>Years of Residency</label><br>
              <input type="number" name="years_residency" placeholder="Years of Residency" required style="width:100%;padding:10px;border-radius:8px;border:1px solid #d1d5db">
            </div>
          </div>

          <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:12px;">
            <div style="flex:1;">
              <label>Mobile Number</label><br>
              <input name="mobile" placeholder="09XXXXXXXXX" required style="width:100%;padding:10px;border-radius:8px;border:1px solid #d1d5db">
            </div>
            <div style="flex:1;">
              <label>Email</label><br>
              <input type="email" name="email" placeholder="you@example.com" required style="width:100%;padding:10px;border-radius:8px;border:1px solid #d1d5db">
            </div>
          </div>

          <div style="margin-top:12px;">
            <label>Voter Status</label><br>
            <select name="voter_status" required style="width:100%;padding:10px;border-radius:8px;border:1px solid #d1d5db">
              <option value="">Select Voter Status</option>
              <option value="Registered Voter">Registered Voter</option>
              <option value="Non-Voter">Non-Voter</option>
            </select>
          </div>

          <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:12px;">
            <div style="flex:1;">
              <label>Employment Status</label><br>
              <input name="employment_status" placeholder="Employment Status" required style="width:100%;padding:10px;border-radius:8px;border:1px solid #d1d5db">
            </div>
            <div style="flex:1;">
              <label>Occupation</label><br>
              <input name="occupation" placeholder="Occupation" required style="width:100%;padding:10px;border-radius:8px;border:1px solid #d1d5db">
            </div>
          </div>

          <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:12px;">
            <div style="flex:1;">
              <label>Household Head</label><br>
              <input name="household_head" placeholder="Household Head" required style="width:100%;padding:10px;border-radius:8px;border:1px solid #d1d5db">
            </div>
            <div style="flex:1;">
              <label>Emergency Contact Person</label><br>
              <input name="emergency_contact" placeholder="Emergency Contact Person" required style="width:100%;padding:10px;border-radius:8px;border:1px solid #d1d5db">
            </div>
          </div>

          <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:12px;">
            <div style="flex:1;">
              <label>Number of Family Members</label><br>
              <input type="number" name="family_members" placeholder="Number of Family Members" required style="width:100%;padding:10px;border-radius:8px;border:1px solid #d1d5db">
            </div>
            <div style="flex:1;">
              <label>Educational Attainment</label><br>
              <select name="educational_attainment" required style="width:100%;padding:10px;border-radius:8px;border:1px solid #d1d5db">
                <option value="">Select Educational Attainment</option>
                <option value="No Formal Education">No Formal Education</option>
                <option value="Elementary">Elementary</option>
                <option value="High School">High School</option>
                <option value="Vocational">Vocational</option>
                <option value="College">College</option>
                <option value="Postgraduate">Postgraduate</option>
              </select>
            </div>
          </div>

          <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:12px;">
            <div style="flex:1;">
              <label>Blood Type</label><br>
              <select name="blood_type" required style="width:100%;padding:10px;border-radius:8px;border:1px solid #d1d5db">
                <option value="">Select Blood Type</option>
                <option value="A+">A+</option>
                <option value="A-">A-</option>
                <option value="B+">B+</option>
                <option value="B-">B-</option>
                <option value="AB+">AB+</option>
                <option value="AB-">AB-</option>
                <option value="O+">O+</option>
                <option value="O-">O-</option>
              </select>
            </div>
            <div style="flex:1;">
              <label>Disabilities (Optional)</label><br>
              <input name="disabilities" placeholder="Disabilities" style="width:100%;padding:10px;border-radius:8px;border:1px solid #d1d5db">
            </div>
          </div>

          <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:12px;">
            <div style="flex:1;">
              <label>Valid ID</label><br>
              <input type="file" name="valid_id" accept="image/*,.pdf" required style="width:100%;padding:10px;border-radius:8px;border:1px solid #d1d5db">
            </div>
            <div style="flex:1;">
              <label>Profile Image</label><br>
              <input type="file" name="profile_image" accept="image/*" required style="width:100%;padding:10px;border-radius:8px;border:1px solid #d1d5db">
            </div>
          </div>

          <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:12px;">
            <div style="flex:1;">
              <label>Password</label><br>
              <input type="password" name="password" required style="width:100%;padding:10px;border-radius:8px;border:1px solid #d1d5db">
            </div>
            <div style="flex:1;">
              <label>Confirm Password</label><br>
              <input type="password" name="confirm_password" required style="width:100%;padding:10px;border-radius:8px;border:1px solid #d1d5db">
            </div>
          </div>

          <div style="margin-top:12px;">
            <label><input type="checkbox" name="terms" required> I agree to the Terms and Condition</label>
          </div>

          <div style="margin-top:16px;">
            <button type="submit" class="btn-request">Create Account</button>
          </div>
        </form>
      </div>
    </section>

    <!-- Contact / Footer anchor -->
    <section id="contact" class="section contact">
      <div class="container">
        <h3>Contact us</h3>
        <p>Sitio Manena, Barangay San Jose, San Luis, Pampanga, Philippines</p>
      </div>
    </section>
  </main>

  <footer class="site-footer">
    <div class="container footer-grid">
      <div>
        <img src="assets/img/seal.png" alt="seal" class="footer-seal" style ="border-radius:50%;">
        <h4>Barangay San Jose</h4>
      </div>
      <div>
        <h5>Useful Links</h5>
        <ul>
          <li><a href="#home">Home</a></li>
          <li><a href="#about">About us</a></li>
          <li><a href="#services">Services</a></li>
          <li><a href="#officials">Officials</a></li>
        </ul>
      </div>
      <div>
        <h5>Our Services</h5>
        <ul>
          <li>SafeBrgy Services</li>
        </ul>
      </div>
      <div>
        <h5>Contact us</h5>
        <address>
          Sitio Manena,<br>
          Barangay San Jose,<br>
          San Luis, Pampanga,<br>
          Philippines
        </address>
      </div>
    </div>
    <div class="copyright">
      &copy; <?php echo date('Y'); ?> Barangay San Jose — All rights reserved.
    </div>
  </footer>

  <!-- Login Modal -->
  <div id="loginModal" class="modal">
    <div class="modal-content">
      <span class="close">&times;</span>
      <div class="modal-kicker">SafeBrgy Services</div>
      <h2>You must log in to access our services</h2>
      <p>Please log in to continue with your request.</p>
      <div class="modal-actions">
        <a href="login" class="btn-primary">Log In</a>
      </div>
    </div>
  </div>

  <!-- Report access modal -->
  <div id="reportModal" class="modal">
    <div class="modal-content">
      <span class="close" aria-label="Close">&times;</span>
      <div class="modal-kicker">SafeBrgy Reports</div>
      <h2>Submit a Report</h2>
      <p id="reportModalMessage">You can click Report Now to submit your immediate report.</p>
      <div class="modal-actions">
        <button type="button" class="btn-primary" id="submitReportNow">Submit Report Now</button>
        <a href="login" class="btn-secondary">Log In</a>
      </div>
    </div>
  </div>

  <div id="cookieConsentModal" class="cookie-consent-backdrop" role="dialog" aria-modal="true" aria-labelledby="cookieConsentTitle" hidden>
    <div class="cookie-consent-modal">
      <h2 id="cookieConsentTitle">Do you want to allow cookies?</h2>
      <p>Cookies help SafeBrgy remember your preferences and keep your signed-in session available.</p>
      <div class="cookie-consent-actions">
        <button type="button" class="cookie-consent-deny" id="cookieConsentDeny">Deny</button>
        <button type="button" class="cookie-consent-allow" id="cookieConsentAllow">Allow</button>
      </div>
    </div>
  </div>

  <script src="assets/js/public/modals/login.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/shared/logo_functions.js"></script>
  <script src="assets/js/shared/layout_functions.js"></script>
  <script src="assets/js/shared/cookie-consent.js?v=2"></script>
</body>
</html>
