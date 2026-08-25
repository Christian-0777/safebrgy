document.addEventListener('DOMContentLoaded', function() {
    const emailForm = document.getElementById('emailForm');
    const codeForm = document.getElementById('codeForm');
    const passwordForm = document.getElementById('passwordForm');
    const emailInput = document.getElementById('email');
    const codeInput = document.getElementById('code');
    const passwordInput = document.getElementById('password');
    const confirmationInput = document.getElementById('confirmation');
    const resetMessage = document.getElementById('resetMessage');
    const confirmButton = document.getElementById('confirmButton');
    const sendCodeButton = document.getElementById('sendCodeButton');
    const resendButton = document.getElementById('resendButton');
    const resendTimer = document.getElementById('resendTimer');
    const togglePassword = document.getElementById('togglePassword');
    const passwordMatchMessage = document.getElementById('passwordMatchMsg');
    let resendInterval;

    function showMessage(message, type) {
        resetMessage.textContent = message;
        resetMessage.className = 'reset-message visible ' + type;
    }

    function clearMessage() {
        resetMessage.textContent = '';
        resetMessage.className = 'reset-message';
    }

    function moveToStep(step) {
        document.querySelectorAll('[data-content]').forEach(function(content) {
            content.classList.toggle('active', Number(content.dataset.content) === step);
        });
        document.querySelectorAll('[data-step]').forEach(function(item) {
            const itemStep = Number(item.dataset.step);
            item.classList.toggle('active', itemStep === step);
            item.classList.toggle('completed', itemStep < step);
        });
        document.querySelectorAll('.reset-connector').forEach(function(connector, index) {
            connector.classList.toggle('completed', index + 1 < step);
        });
        clearMessage();
    }

    async function submitForm(form) {
        const response = await fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: { Accept: 'application/json' }
        });
        let data;
        try {
            data = await response.json();
        } catch (error) {
            throw new Error('The server returned an invalid response.');
        }
        if (!response.ok || !data.success) {
            throw new Error(data.message || 'Something went wrong.');
        }
        return data;
    }

    function maskEmail(email) {
        const parts = email.split('@');
        const localPart = parts[0];
        return localPart.slice(0, 2) + '*'.repeat(Math.max(1, localPart.length - 2)) + '@' + parts[1];
    }

    function startResendTimer() {
        let seconds = 60;
        clearInterval(resendInterval);
        resendButton.disabled = true;
        resendTimer.textContent = seconds;
        resendButton.firstChild.textContent = 'Resend code in ';
        resendInterval = setInterval(function() {
            seconds -= 1;
            resendTimer.textContent = seconds;
            if (seconds === 0) {
                clearInterval(resendInterval);
                resendButton.disabled = false;
                resendButton.firstChild.textContent = 'Resend code';
                resendTimer.textContent = '';
            }
        }, 1000);
    }

    function validPassword(password) {
        return password.length >= 8 && /[A-Z]/.test(password) && /[0-9]/.test(password) && /[^A-Za-z0-9]/.test(password);
    }

    function updatePasswordState() {
        const matches = passwordInput.value === confirmationInput.value;
        passwordMatchMessage.classList.toggle('hidden', !confirmationInput.value || matches);
        confirmButton.disabled = !validPassword(passwordInput.value) || !matches;
    }

    emailForm.addEventListener('submit', async function(event) {
        event.preventDefault();
        sendCodeButton.disabled = true;
        clearMessage();
        try {
            const data = await submitForm(emailForm);
            document.getElementById('maskedEmail').textContent = maskEmail(emailInput.value.trim().toLowerCase());
            moveToStep(2);
            startResendTimer();
            showMessage(data.message, 'success');
        } catch (error) {
            showMessage(error.message, 'error');
        } finally {
            sendCodeButton.disabled = false;
        }
    });

    codeInput.addEventListener('input', function() {
        codeInput.value = codeInput.value.replace(/\D/g, '').slice(0, 6);
    });

    codeForm.addEventListener('submit', async function(event) {
        event.preventDefault();
        try {
            const data = await submitForm(codeForm);
            moveToStep(3);
            showMessage(data.message, 'success');
            passwordInput.focus();
        } catch (error) {
            showMessage(error.message, 'error');
        }
    });

    resendButton.addEventListener('click', async function() {
        resendButton.disabled = true;
        try {
            const data = await submitForm(emailForm);
            startResendTimer();
            showMessage(data.message, 'success');
        } catch (error) {
            resendButton.disabled = false;
            showMessage(error.message, 'error');
        }
    });

    document.getElementById('backButton').addEventListener('click', function() {
        moveToStep(1);
        codeInput.value = '';
    });

    passwordInput.addEventListener('input', updatePasswordState);
    confirmationInput.addEventListener('input', updatePasswordState);

    togglePassword.addEventListener('click', function() {
        const showing = passwordInput.type === 'text';
        passwordInput.type = showing ? 'password' : 'text';
        togglePassword.textContent = showing ? 'Show' : 'Hide';
        togglePassword.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
    });

    passwordForm.addEventListener('submit', async function(event) {
        event.preventDefault();
        confirmButton.disabled = true;
        try {
            const data = await submitForm(passwordForm);
            showMessage('Password reset successful. Redirecting to the dashboard...', 'success');
            window.setTimeout(function() {
                window.location.href = data.redirect;
            }, 700);
        } catch (error) {
            showMessage(error.message, 'error');
            updatePasswordState();
        }
    });
});
