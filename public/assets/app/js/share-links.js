(() => {
    'use strict';
    const input = document.getElementById('selectedShareLinks');
    if (!input) return;
    const status = document.querySelector('[data-share-status]');
    document.querySelector('[data-copy-share-links]').addEventListener('click', async () => {
        try {
            await navigator.clipboard.writeText(input.value);
            status.textContent = 'Links copied.';
        } catch {
            input.focus();
            input.select();
            status.textContent = 'Select and copy the links above.';
        }
    });
    const share = document.querySelector('[data-native-share-links]');
    if (navigator.share) {
        share.hidden = false;
        share.addEventListener('click', async () => {
            try {
                await navigator.share({ title: 'AGallery files', text: input.value });
            } catch (error) {
                if (error.name !== 'AbortError') status.textContent = 'Use Copy links to share these files.';
            }
        });
    }
})();
