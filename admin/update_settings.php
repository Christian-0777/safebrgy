<?php
require_once __DIR__ . '/admin_protect.php';

$adminId = (int) ($_SESSION['admin_user']['id'] ?? 0);
$section = $_POST['section'] ?? '';
$pdo = safeBrgy_db_connect();

function settingsRedirect(string $message, bool $error = false): never
{
    $_SESSION[$error ? 'settings_error' : 'settings_success'] = $message;
    header('Location: /safebrgy/admin/account_settings#' . ($_POST['section'] ?? 'account'));
    exit;
}

try {
    if (!$adminId) {
        throw new RuntimeException('Administrator session expired.');
    }

    if ($section === 'account') {
        $username = trim($_POST['fullName'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        if ($username === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $phone === '') {
            throw new RuntimeException('Please provide a valid name, email address, and phone number.');
        }

        $profilePath = null;
        $coverPath = null;
        foreach (['profileImage' => 'Profile photo', 'coverPhoto' => 'Cover photo'] as $field => $label) {
            if (isset($_FILES[$field]) && $_FILES[$field]['error'] !== UPLOAD_ERR_OK && $_FILES[$field]['error'] !== UPLOAD_ERR_NO_FILE) {
                throw new RuntimeException($label . ' upload failed. The server may reject files above its PHP upload limit.');
            }
        }
        if (!empty($_FILES['profileImage']['tmp_name'])) {
            if ($_FILES['profileImage']['size'] > 20 * 1024 * 1024 || !in_array($_FILES['profileImage']['type'], ['image/jpeg', 'image/png', 'image/webp'], true)) {
                throw new RuntimeException('Profile photo must be a JPG, PNG, or WEBP under 20 MB.');
            }
            $directory = dirname(__DIR__) . '/uploads/profile_images';
            if (!is_dir($directory)) mkdir($directory, 0755, true);
            $profilePath = 'uploads/profile_images/admin_' . $adminId . '_' . bin2hex(random_bytes(5)) . '.' . pathinfo($_FILES['profileImage']['name'], PATHINFO_EXTENSION);
            if (!move_uploaded_file($_FILES['profileImage']['tmp_name'], dirname(__DIR__) . '/' . $profilePath)) throw new RuntimeException('Unable to save profile photo.');
        }

        if (!empty($_FILES['coverPhoto']['tmp_name'])) {
            if ($_FILES['coverPhoto']['size'] > 20 * 1024 * 1024 || !in_array($_FILES['coverPhoto']['type'], ['image/jpeg', 'image/png', 'image/webp'], true)) {
                throw new RuntimeException('Cover photo must be a JPG, PNG, or WEBP under 20 MB.');
            }
            $directory = dirname(__DIR__) . '/uploads/cover_photos';
            if (!is_dir($directory)) mkdir($directory, 0755, true);
            $coverPath = 'uploads/cover_photos/admin_' . $adminId . '_' . bin2hex(random_bytes(5)) . '.' . pathinfo($_FILES['coverPhoto']['name'], PATHINFO_EXTENSION);
            if (!move_uploaded_file($_FILES['coverPhoto']['tmp_name'], dirname(__DIR__) . '/' . $coverPath)) throw new RuntimeException('Unable to save cover photo.');
        }

        $sql = 'UPDATE users SET username = :username, email = :email, phone = :phone' . ($profilePath ? ', profile_image = :profile_image' : '') . ($coverPath ? ', cover_photo = :cover_photo' : '') . ' WHERE id = :id AND role = "admin"';
        $params = ['username' => $username, 'email' => $email, 'phone' => $phone, 'id' => $adminId];
        if ($profilePath) $params['profile_image'] = $profilePath;
        if ($coverPath) $params['cover_photo'] = $coverPath;
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $_SESSION['admin_user']['username'] = $username;
        $_SESSION['admin_user']['email'] = $email;
        $pdo->prepare('INSERT INTO admin_logs (admin_id, action, meta) VALUES (?, ?, ?)')->execute([$adminId, 'Updated administrator account', json_encode(['profile_photo' => (bool) $profilePath])]);
        settingsRedirect('Account details saved successfully.');
    }

    if ($section === 'barangay') {
        $websiteUrl = trim($_POST['websiteUrl'] ?? '');
        if ($websiteUrl !== '' && !preg_match('#^https?://#i', $websiteUrl)) {
            $websiteUrl = 'https://' . $websiteUrl;
        }
        if ($websiteUrl !== '' && filter_var($websiteUrl, FILTER_VALIDATE_URL) === false) {
            throw new RuntimeException('Please provide a valid website URL.');
        }
        $logoPath = null;
        if (!empty($_FILES['barangayLogo']['tmp_name'])) {
            if ($_FILES['barangayLogo']['size'] > 2 * 1024 * 1024 || !in_array($_FILES['barangayLogo']['type'], ['image/jpeg', 'image/png', 'image/webp'], true)) throw new RuntimeException('Barangay logo must be a JPG, PNG, or WEBP under 2 MB.');
            $directory = dirname(__DIR__) . '/uploads/announcements';
            if (!is_dir($directory)) mkdir($directory, 0755, true);
            $logoPath = 'uploads/announcements/barangay_logo_' . bin2hex(random_bytes(5)) . '.' . pathinfo($_FILES['barangayLogo']['name'], PATHINFO_EXTENSION);
            if (!move_uploaded_file($_FILES['barangayLogo']['tmp_name'], dirname(__DIR__) . '/' . $logoPath)) throw new RuntimeException('Unable to save barangay logo.');
        }
        $sql = 'UPDATE barangay_settings SET name = ?, address = ?, contact_number = ?, official_email = ?, website_url = ?, description = ?, updated_by = ?' . ($logoPath ? ', logo_path = ?' : '') . ' WHERE id = 1';
        $params = [trim($_POST['barangayName'] ?? ''), trim($_POST['barangayAddress'] ?? ''), trim($_POST['barangayContact'] ?? ''), trim($_POST['officialEmail'] ?? ''), $websiteUrl, trim($_POST['systemDescription'] ?? ''), $adminId];
        if ($logoPath) $params[] = $logoPath;
        $pdo->prepare($sql)->execute($params);
        $pdo->prepare('INSERT INTO admin_logs (admin_id, action, meta) VALUES (?, ?, ?)')->execute([$adminId, 'Updated barangay information', json_encode(['logo' => (bool) $logoPath])]);
        settingsRedirect('Barangay information saved successfully.');
    }

    if ($section === 'security' && ($_POST['action'] ?? '') === 'two_factor') {
        $enabled = (int) ($_POST['enabled'] ?? 0) === 1 ? 1 : 0;
        $pdo->prepare('UPDATE users SET two_factor_enabled = ? WHERE id = ? AND role = "admin"')->execute([$enabled, $adminId]);
        $pdo->prepare('INSERT INTO admin_logs (admin_id, action, meta) VALUES (?, ?, ?)')->execute([$adminId, $enabled ? 'Enabled two-factor authentication' : 'Disabled two-factor authentication', json_encode(['enabled' => $enabled])]);
        http_response_code(204);
        exit;
    }

    throw new RuntimeException('Unsupported settings section.');
} catch (Throwable $exception) {
    settingsRedirect($exception->getMessage(), true);
}