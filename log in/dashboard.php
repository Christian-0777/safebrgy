<?php
session_start();

if (!isset($_SESSION['user_email'])) {
    header('Location: login.html');
    exit;
}

$user_email = $_SESSION['user_email'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: #f5f7fa;
            color: #333;
        }

        header {
            background: white;
            padding: 20px 40px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-title {
            font-size: 24px;
            font-weight: 700;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-email {
            font-size: 14px;
            color: #666;
        }

        .logout-btn {
            padding: 10px 20px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: background 0.3s;
        }

        .logout-btn:hover {
            background: #c82333;
        }

        .container {
            padding: 40px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .welcome-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
        }

        .welcome-card h2 {
            color: #667eea;
            margin-bottom: 10px;
        }

        .welcome-card p {
            color: #666;
            line-height: 1.6;
        }

        @media (max-width: 768px) {
            header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
                padding: 20px;
            }

            .user-info {
                width: 100%;
                justify-content: center;
            }

            .container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-title">Dashboard</div>
        <div class="user-info">
            <span class="user-email"><?php echo htmlspecialchars($user_email); ?></span>
            <form method="POST" action="logout.php" style="margin: 0;">
                <button type="submit" class="logout-btn">Logout</button>
            </form>
        </div>
    </header>

    <div class="container">
        <div class="welcome-card">
            <h2>Welcome back</h2>
            <p>You are successfully logged in as <?php echo htmlspecialchars($user_email); ?>. You can now access all features of your account.</p>
        </div>
    </div>
</body>
</html>
