<?php

if (!function_exists('renderProfileAvatar')) {
    function renderProfileAvatar(string $name, PDO $pdo): string
    {
        $sessionUser = $_SESSION['user'] ?? null;
        $adminUser = $_SESSION['admin_user'] ?? null;
        $userId = is_array($sessionUser) && isset($sessionUser['id'])
            ? $sessionUser['id']
            : (is_array($adminUser) ? ($adminUser['id'] ?? null) : null);
        $profileImage = '';

        if ($userId) {
            $stmt = $pdo->prepare(
                'SELECT u.profile_image, r.profile_image_path
                   FROM users u
                   LEFT JOIN residents r ON r.user_id = u.id
                  WHERE u.id = ?
                  LIMIT 1'
            );
            $stmt->execute([$userId]);
            $profileData = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
            $profileImage = trim((string) ($profileData['profile_image'] ?: ($profileData['profile_image_path'] ?? '')));
        }

        if ($profileImage !== '') {
            if (filter_var($profileImage, FILTER_VALIDATE_URL)) {
                $imageUrl = $profileImage;
            } else {
                $normalizedPath = ltrim(str_replace('\\', '/', $profileImage), '/');
                $applicationRoot = rtrim(dirname(dirname(dirname($_SERVER['SCRIPT_NAME']))), '/');

                if (strpos($normalizedPath, 'uploads/profile/') === 0) {
                    $imageUrl = $applicationRoot . '/register/' . $normalizedPath;
                } else {
                    $imageUrl = $applicationRoot . '/' . $normalizedPath;
                }
            }

            return '<img src="' . htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8') . '" alt="Profile photo">';
        }

        return htmlspecialchars(strtoupper(substr($name, 0, 1)), ENT_QUOTES, 'UTF-8');
    }
}
