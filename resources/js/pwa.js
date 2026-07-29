// Enregistrement du service worker + bandeau d'installation ("Add to Home Screen").

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js', { scope: '/' }).catch(() => {
            // Échec silencieux : l'app reste utilisable normalement sans PWA.
        });
    });
}

let deferredInstallPrompt = null;

window.addEventListener('beforeinstallprompt', (event) => {
    event.preventDefault();
    deferredInstallPrompt = event;
    showInstallBanner();
});

window.addEventListener('appinstalled', () => {
    deferredInstallPrompt = null;
    hideInstallBanner();
});

function showInstallBanner() {
    if (localStorage.getItem('pwa_install_dismissed') === '1') return;
    if (document.getElementById('pwa-install-banner')) return;

    const banner = document.createElement('div');
    banner.id = 'pwa-install-banner';
    banner.className = 'position-fixed bottom-0 start-0 end-0 d-flex align-items-center justify-content-between gap-3 p-3 shadow-lg';
    banner.style.cssText = 'z-index:1080;background:#696cff;color:#fff;';
    banner.innerHTML = `
        <span class="small">Installer BuildFlow sur cet appareil pour un accès rapide, comme une application.</span>
        <div class="d-flex gap-2 flex-shrink-0">
            <button type="button" id="pwa-install-btn" class="btn btn-light btn-sm">Installer</button>
            <button type="button" id="pwa-install-dismiss" class="btn btn-outline-light btn-sm">Plus tard</button>
        </div>
    `;
    document.body.appendChild(banner);

    document.getElementById('pwa-install-btn').addEventListener('click', async () => {
        hideInstallBanner();
        if (!deferredInstallPrompt) return;
        deferredInstallPrompt.prompt();
        await deferredInstallPrompt.userChoice;
        deferredInstallPrompt = null;
    });

    document.getElementById('pwa-install-dismiss').addEventListener('click', () => {
        localStorage.setItem('pwa_install_dismissed', '1');
        hideInstallBanner();
    });
}

function hideInstallBanner() {
    document.getElementById('pwa-install-banner')?.remove();
}
