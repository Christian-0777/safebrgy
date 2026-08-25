<?php
require_once __DIR__ . '/../admin_protect.php';
require_once __DIR__ . '/../../config/db.php';

function adminUserAssetUrl($path): string
{
  $path = trim((string) $path);
  if ($path === '') {
    return '';
  }

  if (filter_var($path, FILTER_VALIDATE_URL)) {
    return $path;
  }

  $filename = basename(str_replace('\\', '/', $path));
  return '/safebrgy/uploads/id/' . rawurlencode($filename);
}

function adminUserMediaUrl($path, string $folder): string
{
  $path = trim((string) $path);
  if ($path === '') {
    return '';
  }

  if (filter_var($path, FILTER_VALIDATE_URL)) {
    return $path;
  }

  $normalizedPath = str_replace('\\', '/', $path);
  $filename = basename($normalizedPath);

  if (strpos($normalizedPath, 'uploads/' . $folder . '/') === 0) {
    return '/safebrgy/register/uploads/' . $folder . '/' . rawurlencode($filename);
  }

  return '/safebrgy/' . ltrim($normalizedPath, '/');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $userId = $data['user_id'] ?? null;

    if (!$userId) {
        echo json_encode(['success' => false, 'message' => 'User ID required']);
        exit;
    }

    $pdo = safeBrgy_db_connect();
    $stmt = $pdo->prepare('
        SELECT u.*, r.*
        FROM users u
        LEFT JOIN residents r ON u.id = r.user_id
        WHERE u.id = ?
    ');
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }

    $profileImagePath = $user['profile_image_path'] ?: ($user['profile_image'] ?? '');
    $coverPhotoPath = $user['cover_photo_path'] ?: ($user['cover_photo'] ?? '');
    $profileImageUrl = adminUserMediaUrl($profileImagePath, 'profile');
    $coverPhotoUrl = adminUserMediaUrl($coverPhotoPath, 'cover');
    $validIdPath = adminUserAssetUrl($user['valid_id_path'] ?? '');
    $validIdBackPath = adminUserAssetUrl($user['valid_id_back_path'] ?? '');

    $html = '
    <div class="row">
      <div class="col-md-6">
        <h6>Personal Information</h6>
        <p><strong>Resident ID:</strong> ' . htmlspecialchars($user['resident_id'] ?? 'N/A') . '</p>
        <p><strong>Name:</strong> ' . htmlspecialchars($user['first_name'] . ' ' . $user['middle_name'] . ' ' . $user['last_name']) . '</p>
        <p><strong>Email:</strong> ' . htmlspecialchars($user['email']) . '</p>
        <p><strong>Phone:</strong> ' . htmlspecialchars($user['phone'] ?? 'N/A') . '</p>
        <p><strong>Birthdate:</strong> ' . htmlspecialchars($user['birthdate'] ?? 'N/A') . '</p>
        <p><strong>Age:</strong> ' . htmlspecialchars($user['age'] ?? 'N/A') . '</p>
        <p><strong>Gender:</strong> ' . htmlspecialchars($user['gender'] ?? 'N/A') . '</p>
        <p><strong>Civil Status:</strong> ' . htmlspecialchars($user['civil_status'] ?? 'N/A') . '</p>
        <p><strong>Nationality:</strong> ' . htmlspecialchars($user['nationality'] ?? 'N/A') . '</p>
        <p><strong>Religion:</strong> ' . htmlspecialchars($user['religion'] ?? 'N/A') . '</p>
      </div>
      <div class="col-md-6">
        <h6>Address & Contact</h6>
        <p><strong>Address:</strong> ' . htmlspecialchars($user['complete_address'] ?? 'N/A') . '</p>
        <p><strong>Purok:</strong> ' . htmlspecialchars($user['purok'] ?? 'N/A') . '</p>
        <p><strong>Years of Residency:</strong> ' . htmlspecialchars($user['years_of_residency'] ?? 'N/A') . '</p>
        <p><strong>Mobile:</strong> ' . htmlspecialchars($user['mobile_number'] ?? 'N/A') . '</p>
        <p><strong>Voter Status:</strong> ' . htmlspecialchars($user['voter_status'] ?? 'N/A') . '</p>
        <p><strong>Employment Status:</strong> ' . htmlspecialchars($user['employment_status'] ?? 'N/A') . '</p>
        <p><strong>Occupation:</strong> ' . htmlspecialchars($user['occupation'] ?? 'N/A') . '</p>
      </div>
    </div>
    <div class="row mt-3">
      <div class="col-md-6">
        <h6>Family & Health</h6>
        <p><strong>Household Head:</strong> ' . htmlspecialchars($user['household_head'] ?? 'N/A') . '</p>
        <p><strong>Emergency Contact:</strong> ' . htmlspecialchars($user['emergency_contact_name'] ?? 'N/A') . '</p>
        <p><strong>Emergency Contact Number:</strong> ' . htmlspecialchars($user['emergency_contact_number'] ?? 'N/A') . '</p>
        <p><strong>Family Members:</strong> ' . htmlspecialchars($user['number_of_family_member'] ?? 'N/A') . '</p>
        <p><strong>Educational Attainment:</strong> ' . htmlspecialchars($user['educational_attainment'] ?? 'N/A') . '</p>
        <p><strong>Blood Type:</strong> ' . htmlspecialchars($user['blood_type'] ?? 'N/A') . '</p>
        <p><strong>Disabilities:</strong> ' . htmlspecialchars($user['disabilities'] ?? 'None') . '</p>
      </div>
      <div class="col-md-6">
        <h6>Documents</h6>
        <p class="mb-2"><strong style="text-transform: uppercase; font-size: 11px; color: #666; letter-spacing: 0.5px;">Front of Valid ID</strong></p>
        ' . ($validIdPath ? '<div class="image-preview-container"><a href="' . htmlspecialchars($validIdPath) . '" target="_blank" title="Click to view full size"><img src="' . htmlspecialchars($validIdPath) . '" alt="Valid ID" class="image-preview"></a></div>' : '<p class="text-muted fst-italic" style="font-size: 12px;">Not uploaded</p>') . '
        <p class="mb-2 mt-4"><strong style="text-transform: uppercase; font-size: 11px; color: #666; letter-spacing: 0.5px;">Back of Valid ID</strong></p>
        ' . ($validIdBackPath ? '<div class="image-preview-container"><a href="' . htmlspecialchars($validIdBackPath) . '" target="_blank" title="Click to view full size"><img src="' . htmlspecialchars($validIdBackPath) . '" alt="Back of Valid ID" class="image-preview"></a></div>' : '<p class="text-muted fst-italic" style="font-size: 12px;">Not uploaded</p>') . '
        <p class="mb-2 mt-4"><strong style="text-transform: uppercase; font-size: 11px; color: #666; letter-spacing: 0.5px;">Profile Picture</strong></p>
        ' . ($profileImageUrl ? '<div class="image-preview-container"><a href="' . htmlspecialchars($profileImageUrl, ENT_QUOTES, 'UTF-8') . '" target="_blank" title="Click to view full size"><img src="' . htmlspecialchars($profileImageUrl, ENT_QUOTES, 'UTF-8') . '" alt="Profile Picture" class="image-preview" style="border-radius: 50%;"></a></div>' : '<p class="text-muted fst-italic" style="font-size: 12px;">Not uploaded</p>') . '
        <p class="mb-2 mt-4"><strong style="text-transform: uppercase; font-size: 11px; color: #666; letter-spacing: 0.5px;">Cover Photo</strong></p>
        ' . ($coverPhotoUrl ? '<div class="image-preview-container"><a href="' . htmlspecialchars($coverPhotoUrl, ENT_QUOTES, 'UTF-8') . '" target="_blank" title="Click to view full size"><img src="' . htmlspecialchars($coverPhotoUrl, ENT_QUOTES, 'UTF-8') . '" alt="Cover Photo" class="image-preview"></a></div>' : '<p class="text-muted fst-italic" style="font-size: 12px;">Not uploaded</p>') . '
        <p class="mt-4 pt-2 border-top"><small><strong>Registered:</strong></small><br>' . htmlspecialchars(date('M d, Y \\a\\t H:i', strtotime($user['created_at']))) . '</p>
      </div>
    </div>';

    echo json_encode(['success' => true, 'html' => $html]);
}
?>