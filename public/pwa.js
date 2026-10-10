(() => {
    'use strict';

    const script = document.currentScript;
    const banner = document.getElementById('pwaInstall');
    const button = document.getElementById('pwaInstallButton');
    const help = document.getElementById('pwaInstallHelp');
    const close = document.getElementById('pwaInstallDismiss');
    const standalone = window.matchMedia('(display-mode: standalone)');
    let deferredPrompt = null;
    let dismissed = false;

    try {
        dismissed = sessionStorage.getItem('agallery-install-dismissed') === '1';
    } catch {}

    const installed = () => standalone.matches || window.navigator.standalone === true;
    const hide = () => { if (banner) banner.hidden = true; };
    const show = () => { if (banner && !installed() && !dismissed) banner.hidden = false; };

    if ('serviceWorker' in navigator && window.isSecureContext && script?.dataset.serviceWorker) {
        const workerUrl = new URL(script.dataset.serviceWorker, location.href);
        navigator.serviceWorker.register(workerUrl.href, {
            scope: new URL('./', workerUrl).pathname,
            updateViaCache: 'none',
        }).catch(error => console.warn('AGallery service worker registration failed:', error));
    }

    window.addEventListener('beforeinstallprompt', event => {
        if (!banner || !button || installed() || dismissed) return;
        event.preventDefault();
        deferredPrompt = event;
        button.hidden = false;
        if (help) help.hidden = true;
        show();
    });

    button?.addEventListener('click', async () => {
        if (!deferredPrompt) return;
        const prompt = deferredPrompt;
        deferredPrompt = null;
        button.disabled = true;
        try {
            await prompt.prompt();
            await prompt.userChoice;
        } finally {
            button.disabled = false;
            hide();
        }
    });

    close?.addEventListener('click', () => {
        dismissed = true;
        hide();
        try {
            sessionStorage.setItem('agallery-install-dismissed', '1');
        } catch {}
    });

    window.addEventListener('appinstalled', () => {
        deferredPrompt = null;
        hide();
    });
    standalone.addEventListener('change', () => { if (installed()) hide(); });

    const ios = /iPad|iPhone|iPod/.test(navigator.userAgent)
        || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
    if (ios && window.isSecureContext && !installed() && help) {
        help.hidden = false;
        show();
    }
})();
