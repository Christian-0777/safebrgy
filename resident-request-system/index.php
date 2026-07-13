<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';

$requests = getAllRequests($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Barangay Resident Portal — Document Requests</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Zilla+Slab:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<header class="masthead">
  <div class="masthead-inner">
    <div class="seal">B</div>
    <div class="masthead-text">
      <p class="eyebrow">Office of the Barangay Secretary</p>
      <h1>Resident Document Request Portal</h1>
    </div>
  </div>
</header>

<section class="hero">
  <div>
    <p class="hero-eyebrow">Records &amp; Certification Desk</p>
    <h2>Request barangay documents without lining up at the hall.</h2>
    <p>Fill out a short form for the certificate you need, attach any supporting documents, and our office will review it and email you updates — from submission to release.</p>
    <a href="#services" class="btn-link">Browse document types ↓</a>
  </div>
  <div class="hero-stamp">
    <span>Est. Records Desk</span>
    <strong>Official<br>Request Portal</strong>
  </div>
</section>

<section class="section" id="services">
  <div class="section-head">
    <div>
      <p class="label">Services</p>
      <h3>Available Document Requests</h3>
      <p>Choose the document you need and click Request Now to begin.</p>
    </div>
  </div>

  <div class="cards-grid">

    <!-- 1. Barangay Clearance -->
    <div class="doc-card">
      <p class="doc-index">Doc. 01</p>
      <div class="doc-icon">BC</div>
      <h4>Barangay Clearance</h4>
      <p>A general certification of good standing, commonly required for employment, school, or transaction requirements.</p>
      <button type="button" class="btn-request" data-modal="modal-clearance">Request Now</button>
    </div>

    <!-- 2. Barangay Residency -->
    <div class="doc-card">
      <p class="doc-index">Doc. 02</p>
      <div class="doc-icon">BR</div>
      <h4>Barangay Residency</h4>
      <p>Certifies how long you have lived within the barangay. Often needed for scholarships, IDs, or legal proof of address.</p>
      <button type="button" class="btn-request" data-modal="modal-residency">Request Now</button>
    </div>

    <!-- 3. Barangay Indigency -->
    <div class="doc-card">
      <p class="doc-index">Doc. 03</p>
      <div class="doc-icon">BI</div>
      <h4>Barangay Indigency</h4>
      <p>Certifies financial status for residents applying for medical, educational, financial, or burial assistance.</p>
      <button type="button" class="btn-request" data-modal="modal-indigency">Request Now</button>
    </div>

    <!-- 4. Barangay Business Clearance -->
    <div class="doc-card">
      <p class="doc-index">Doc. 04</p>
      <div class="doc-icon">BX</div>
      <h4>Barangay Business Clearance</h4>
      <p>Required permit clearance for operating a business within the barangay's jurisdiction.</p>
      <button type="button" class="btn-request" data-modal="modal-business">Request Now</button>
    </div>

  </div>
</section>

<section class="section" id="my-requests">
  <div class="section-head">
    <div>
      <p class="label">Tracking</p>
      <h3>My Requests</h3>
      <p>Status of documents you have submitted so far.</p>
    </div>
  </div>

  <div class="table-wrap">
    <table class="requests-table">
      <thead>
        <tr>
          <th>Reference No.</th>
          <th>Document Type</th>
          <th>Submitted On</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody id="requestsTableBody">
        <?php if (empty($requests)): ?>
          <tr class="empty-row">
            <td colspan="5">No requests submitted yet. Use the Services section above to make your first request.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($requests as $r): ?>
            <?php $statusClass = 'status-' . strtolower(str_replace(' ', '-', $r['status'])); ?>
            <tr>
              <td class="ref-no"><?= h($r['reference_no']) ?></td>
              <td><?= h($r['document_type']) ?></td>
              <td><?= h(date('M d, Y g:i A', strtotime($r['submitted_at']))) ?></td>
              <td><span class="status-pill <?= h($statusClass) ?>"><?= h($r['status']) ?></span></td>
              <td><a href="#" class="action-link" onclick="alert('Viewing details for <?= h($r['reference_no']) ?> is not part of this demo.'); return false;">View</a></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>

<footer class="site-footer">
  Barangay Resident Portal &mdash; School Project Demo. All requests are for demonstration purposes only.
</footer>

<!-- =========================================================
     MODAL 1: Barangay Clearance
========================================================= -->
<div class="modal-overlay" id="modal-clearance">
  <div class="modal-box">
    <div class="modal-head">
      <div>
        <p class="eyebrow">Doc. 01</p>
        <h3>Request Barangay Clearance</h3>
      </div>
      <button type="button" class="modal-close" data-close>&times;</button>
    </div>
    <div class="modal-body">
      <form id="form-clearance" class="request-form" data-doctype="Barangay Clearance" data-table="barangay_clearance" enctype="multipart/form-data">
        <div class="form-alert"></div>
        <div class="form-row">
          <div class="form-group">
            <label for="clearance-name">Full Name *</label>
            <input type="text" id="clearance-name" name="resident_name" required>
          </div>
          <div class="form-group">
            <label for="clearance-email">Email Address *</label>
            <input type="email" id="clearance-email" name="resident_email" required>
          </div>
        </div>
        <div class="form-group">
          <label for="clearance-purpose">Purpose of Request *</label>
          <textarea id="clearance-purpose" name="purpose" required placeholder="e.g. Employment requirement, school requirement..."></textarea>
        </div>
        <div class="form-group">
          <label for="clearance-file">Supporting Document / Image (optional)</label>
          <input type="file" id="clearance-file" name="supporting_file" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
          <span class="hint">Accepted: JPG, PNG, PDF, DOC/DOCX — max 5MB.</span>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn-secondary" data-close>Cancel</button>
          <button type="submit" class="btn-primary">Submit Request</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- =========================================================
     MODAL 2: Barangay Residency
========================================================= -->
<div class="modal-overlay" id="modal-residency">
  <div class="modal-box">
    <div class="modal-head">
      <div>
        <p class="eyebrow">Doc. 02</p>
        <h3>Request Barangay Residency</h3>
      </div>
      <button type="button" class="modal-close" data-close>&times;</button>
    </div>
    <div class="modal-body">
      <form id="form-residency" class="request-form" data-doctype="Barangay Residency" data-table="barangay_residency" enctype="multipart/form-data">
        <div class="form-alert"></div>
        <div class="form-row">
          <div class="form-group">
            <label for="residency-name">Full Name *</label>
            <input type="text" id="residency-name" name="resident_name" required>
          </div>
          <div class="form-group">
            <label for="residency-email">Email Address *</label>
            <input type="email" id="residency-email" name="resident_email" required>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label for="residency-years">Years of Residency *</label>
            <input type="number" id="residency-years" name="years_of_residency" min="0" required>
          </div>
          <div class="form-group">
            <label for="residency-date">Date Started Living in Barangay *</label>
            <input type="date" id="residency-date" name="date_started" required>
          </div>
        </div>
        <div class="form-group">
          <label for="residency-purpose">Purpose of Request *</label>
          <textarea id="residency-purpose" name="purpose" required placeholder="e.g. Scholarship application, ID requirement..."></textarea>
        </div>
        <div class="form-group">
          <label for="residency-file">Supporting Document / Image (optional)</label>
          <input type="file" id="residency-file" name="supporting_file" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
          <span class="hint">Accepted: JPG, PNG, PDF, DOC/DOCX — max 5MB.</span>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn-secondary" data-close>Cancel</button>
          <button type="submit" class="btn-primary">Submit Request</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- =========================================================
     MODAL 3: Barangay Indigency
========================================================= -->
<div class="modal-overlay" id="modal-indigency">
  <div class="modal-box">
    <div class="modal-head">
      <div>
        <p class="eyebrow">Doc. 03</p>
        <h3>Request Barangay Indigency</h3>
      </div>
      <button type="button" class="modal-close" data-close>&times;</button>
    </div>
    <div class="modal-body">
      <form id="form-indigency" class="request-form" data-doctype="Barangay Indigency" data-table="barangay_indigency" enctype="multipart/form-data">
        <div class="form-alert"></div>
        <div class="form-row">
          <div class="form-group">
            <label for="indigency-name">Full Name *</label>
            <input type="text" id="indigency-name" name="resident_name" required>
          </div>
          <div class="form-group">
            <label for="indigency-email">Email Address *</label>
            <input type="email" id="indigency-email" name="resident_email" required>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label for="indigency-income">Monthly Income (₱) *</label>
            <input type="number" id="indigency-income" name="monthly_income" min="0" step="0.01" required>
          </div>
          <div class="form-group">
            <label for="indigency-members">Number of Household Members *</label>
            <input type="number" id="indigency-members" name="household_members" min="1" required>
          </div>
        </div>
        <div class="form-group">
          <label for="indigency-purpose">Purpose of Request *</label>
          <select id="indigency-purpose" name="purpose" required>
            <option value="" disabled selected>Select purpose...</option>
            <option value="Medical Assistance">Medical Assistance</option>
            <option value="Educational Assistance">Educational Assistance</option>
            <option value="Financial Assistance">Financial Assistance</option>
            <option value="Burial Assistance">Burial Assistance</option>
            <option value="Other">Other</option>
          </select>
        </div>
        <div class="form-group" id="indigency-other-wrap" style="display:none;">
          <label for="indigency-other">Please specify</label>
          <input type="text" id="indigency-other" name="purpose_other" placeholder="Specify other purpose">
        </div>
        <div class="form-group">
          <label for="indigency-file">Supporting Document / Image (optional)</label>
          <input type="file" id="indigency-file" name="supporting_file" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
          <span class="hint">Accepted: JPG, PNG, PDF, DOC/DOCX — max 5MB.</span>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn-secondary" data-close>Cancel</button>
          <button type="submit" class="btn-primary">Submit Request</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- =========================================================
     MODAL 4: Barangay Business Clearance
========================================================= -->
<div class="modal-overlay" id="modal-business">
  <div class="modal-box">
    <div class="modal-head">
      <div>
        <p class="eyebrow">Doc. 04</p>
        <h3>Request Barangay Business Clearance</h3>
      </div>
      <button type="button" class="modal-close" data-close>&times;</button>
    </div>
    <div class="modal-body">
      <form id="form-business" class="request-form" data-doctype="Barangay Business Clearance" data-table="barangay_business_clearance" enctype="multipart/form-data">
        <div class="form-alert"></div>
        <div class="form-row">
          <div class="form-group">
            <label for="business-name-owner">Full Name (Owner) *</label>
            <input type="text" id="business-name-owner" name="resident_name" required>
          </div>
          <div class="form-group">
            <label for="business-email">Email Address *</label>
            <input type="email" id="business-email" name="resident_email" required>
          </div>
        </div>
        <div class="form-group">
          <label for="business-name">Business Name *</label>
          <input type="text" id="business-name" name="business_name" required>
        </div>
        <div class="form-group">
          <label for="business-description">Business Description *</label>
          <textarea id="business-description" name="business_description" required placeholder="Briefly describe the nature of the business"></textarea>
        </div>
        <div class="form-group">
          <label for="business-logo">Business Logo (optional)</label>
          <input type="file" id="business-logo" name="business_logo" accept=".jpg,.jpeg,.png">
          <span class="hint">JPG or PNG — max 5MB.</span>
        </div>
        <div class="form-group">
          <label for="business-address">Business Full Address *</label>
          <input type="text" id="business-address" name="business_address" required>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label for="business-contact">Contact Number *</label>
            <input type="tel" id="business-contact" name="contact_number" required>
          </div>
          <div class="form-group">
            <label for="business-tin">TIN <span class="hint" style="display:inline">(if applicable, optional but recommended)</span></label>
            <input type="text" id="business-tin" name="tin_number">
          </div>
        </div>
        <div class="form-group">
          <label for="business-started">Business Started *</label>
          <input type="date" id="business-started" name="business_started" required>
        </div>
        <div class="form-group">
          <label for="business-purpose">Purpose of Request *</label>
          <textarea id="business-purpose" name="purpose" required placeholder="e.g. Renewal of permit, new application..."></textarea>
        </div>
        <div class="form-group">
          <label for="business-file">Supporting Document / Image (optional)</label>
          <input type="file" id="business-file" name="supporting_file" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
          <span class="hint">Accepted: JPG, PNG, PDF, DOC/DOCX — max 5MB.</span>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn-secondary" data-close>Cancel</button>
          <button type="submit" class="btn-primary">Submit Request</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- =========================================================
     CONFIRMATION MODAL (shown after successful submit)
========================================================= -->
<div class="modal-overlay" id="modal-confirm">
  <div class="modal-box confirm-box">
    <div class="confirm-icon">✓</div>
    <h3 id="confirm-title">Request Submitted</h3>
    <p id="confirm-message"></p>
    <p class="confirm-ref" id="confirm-ref"></p>
    <div class="modal-footer" style="justify-content:center;">
      <button type="button" class="btn-primary" id="confirm-ok">Okay, got it</button>
    </div>
  </div>
</div>

<script src="js/script.js"></script>
</body>
</html>