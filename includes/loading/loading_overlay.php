<?php
// PHP can handle the page logic here
$page_loading = true;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loading Overlay</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-content">
            <div class="spinner"></div>
            <div class="loading-text">
                Loading
                <span class="dot"></span>
                <span class="dot"></span>
                <span class="dot"></span>
            </div>
        </div>
    </div>

    <div class="content" id="content">
        <h1>Welcome Back</h1>
        <p>Your content has loaded successfully. The loading overlay will disappear automatically or you can trigger it again with the button below.</p>
        <button class="test-button" onclick="showLoading()">Show Loading Overlay</button>
    </div>

    <script src="script.js"></script>

</body>
</html>
