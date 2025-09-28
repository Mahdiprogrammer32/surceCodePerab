// مدیریت آفلاین
window.addEventListener('online', () => {
    showToast('اتصال اینترنت برقرار شد!', 'success');
});

window.addEventListener('offline', () => {
    showToast('شما در حالت آفلاین هستید!', 'warning');
});

// نمایش نوتیفیکیشن
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => toast.remove(), 3000);
}

// نصب PWA
let deferredPrompt;
window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
    showInstallButton();
});

function showInstallButton() {
    const installBtn = document.createElement('button');
    installBtn.className = 'install-btn';
    installBtn.textContent = 'نصب برنامه';
    installBtn.onclick = installPWA;
    document.body.appendChild(installBtn);
}

function installPWA() {
    deferredPrompt.prompt();
    deferredPrompt.userChoice.then(() => {
        deferredPrompt = null;
    });
}