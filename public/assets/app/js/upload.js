(() => {
    'use strict';

    const form = document.querySelector('[data-upload-form]');
    if (!form || !window.XMLHttpRequest || !window.FormData) return;
    const panel = document.getElementById('offcanvasUploadDoc');
    const container = form.querySelector('[data-upload-progress]');
    const progress = container.querySelector('[role="progressbar"]');
    const bar = container.querySelector('.progress-bar');
    const status = form.querySelector('[data-upload-status]');
    const error = form.querySelector('[data-upload-error]');
    const button = form.querySelector('[type="submit"]');
    const input = form.querySelector('[type="file"]');
    let uploading = false;

    panel.addEventListener('hide.bs.offcanvas', event => {
        if (uploading) event.preventDefault();
    });
    panel.querySelector('.upload-dropzone').addEventListener('drop', event => {
        if (uploading) {
            event.preventDefault();
            event.stopImmediatePropagation();
        }
    }, true);

    function fail(message) {
        uploading = false;
        button.disabled = false;
        input.disabled = false;
        button.textContent = 'Upload files';
        error.textContent = message;
        error.hidden = false;
        status.textContent = 'Upload failed. Please try again.';
    }

    form.addEventListener('submit', event => {
        event.preventDefault();
        if (uploading || !form.reportValidity()) return;
        const body = new FormData(form);
        const xhr = new XMLHttpRequest();
        uploading = true;
        container.hidden = false;
        error.hidden = true;
        error.textContent = '';
        button.disabled = true;
        input.disabled = true;
        button.textContent = 'Uploading…';
        bar.style.width = '0%';
        bar.textContent = '0%';
        progress.setAttribute('aria-valuenow', '0');
        status.textContent = 'Uploading files…';
        xhr.open('POST', form.action);
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.upload.addEventListener('progress', event => {
            if (!event.lengthComputable) {
                status.textContent = 'Uploading files…';
                return;
            }
            const percent = Math.min(100, Math.round(event.loaded / event.total * 100));
            bar.style.width = percent + '%';
            bar.textContent = percent + '%';
            progress.setAttribute('aria-valuenow', String(percent));
            status.textContent = percent === 100 ? 'Upload sent. Saving files…' : 'Uploading files: ' + percent + '%';
        });
        xhr.addEventListener('load', () => {
            let data;
            try { data = JSON.parse(xhr.responseText); } catch { data = {}; }
            if (!data || typeof data !== 'object') data = {};
            if (xhr.status >= 200 && xhr.status < 300 && data.redirect) {
                const destination = new URL(data.redirect, window.location.href);
                if (destination.origin !== window.location.origin) {
                    fail('Unable to open the library. Please reload the page.');
                    return;
                }
                status.textContent = 'Files saved. Opening your library…';
                window.location.assign(destination.href);
                return;
            }
            const messages = Object.values(data.errors || {}).flat();
            fail(messages.length ? messages.join(' ') : data.message || (xhr.status === 413
                ? 'These files exceed the server upload limit. Choose smaller files.'
                : xhr.status === 401 || xhr.status === 419
                    ? 'Your session expired. Reload the page and sign in again.'
                    : 'The upload could not be saved. Please try again.'));
        });
        xhr.addEventListener('error', () => fail('Connection lost. Check your connection and try again.'));
        xhr.addEventListener('abort', () => fail('Upload cancelled. You can try again.'));
        try {
            xhr.send(body);
        } catch {
            fail('The upload could not start. Reload the page and try again.');
        }
    });
})();
