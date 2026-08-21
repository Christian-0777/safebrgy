document.querySelectorAll('[data-tab]').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        
        document.querySelectorAll('[data-tab]').forEach(l => {
            l.classList.remove('active');
        });
        this.classList.add('active');
        
        const tab = this.getAttribute('data-tab');
        alert('Switched to ' + tab + ' tab');
    });
});

document.querySelectorAll('.post-action-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const icon = this.querySelector('i');
        if (this.innerHTML.includes('Like')) {
            this.style.color = this.style.color === 'rgb(10, 102, 194)' ? '#65676b' : '#0a66c2';
        } else {
            alert('Action clicked');
        }
    });
});

document.querySelectorAll('.btn-primary, .btn-outline-secondary').forEach(btn => {
    btn.addEventListener('click', function(e) {
        if (e.target.closest('.btn-primary')) {
            alert('Friend request sent');
        } else if (this.innerHTML.includes('Message')) {
            alert('Message dialog opened');
        }
    });
});