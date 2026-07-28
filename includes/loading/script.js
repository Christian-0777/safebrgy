function hideLoading() {
    const overlay = document.getElementById('loadingOverlay');
    overlay.classList.add('hidden');
    document.getElementById('content').classList.add('visible');
}

function showLoading() {
    const overlay = document.getElementById('loadingOverlay');
    overlay.classList.remove('hidden');
    document.getElementById('content').classList.remove('visible');
    
    setTimeout(hideLoading, 3000);
}

window.addEventListener('load', function() {
    setTimeout(hideLoading, 2000);
});