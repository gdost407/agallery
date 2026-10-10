(() => {
    'use strict';

    function init() {
        const panel = document.querySelector('[data-media-sync]');
        if (!panel) return;
        const node = name => panel.querySelector(`[data-sync-${name}]`);
        const select = node('select'), start = node('start'), stop = node('stop'), forget = node('forget');
        const status = node('status'), progress = node('progress'), bar = node('bar'), wakeNote = node('wake');
        let handle = null, running = false, cancelled = false, wake = null, requestingWake = false, xhr = null, controller = null;
        const accountKey = `media-folder:${panel.dataset.account}`;
        const extensions = /\.(jpe?g|png|gif|webp|bmp|avif|heic|heif|mp4|mov|avi|mkv|webm|mpeg|mpg|m4v|3gp)$/i;

        function savedFolder(mode, value) {
            return new Promise((resolve, reject) => {
                const open = indexedDB.open('agallery-media-sync', 1);
                open.onupgradeneeded = () => open.result.createObjectStore('folders');
                open.onerror = () => reject(new Error('Your browser could not open saved folder settings.'));
                open.onblocked = () => reject(new Error('Close other AGallery tabs and try again.'));
                open.onsuccess = () => {
                    const db = open.result;
                    const transaction = db.transaction('folders', mode === 'read' ? 'readonly' : 'readwrite');
                    const store = transaction.objectStore('folders');
                    const request = mode === 'read' ? store.get(accountKey) : mode === 'forget' ? store.delete(accountKey) : store.put(value, accountKey);
                    transaction.oncomplete = () => { db.close(); resolve(request.result); };
                    transaction.onabort = transaction.onerror = () => { db.close(); reject(new Error('Your browser could not save folder settings.')); };
                };
            });
        }

        function controls() {
            select.hidden = false;
            select.textContent = handle ? 'Change folder' : 'Choose media folder';
            select.disabled = running;
            start.hidden = !handle;
            start.disabled = running;
            forget.hidden = !handle;
            forget.disabled = running;
            stop.hidden = !running;
        }

        function update(percent, message) {
            const value = Math.max(0, Math.min(100, Math.round(percent)));
            progress.hidden = false;
            progress.setAttribute('aria-valuenow', String(value));
            bar.style.width = `${value}%`;
            bar.textContent = `${value}%`;
            status.textContent = message;
        }

        function checkCancelled() {
            if (cancelled) throw new DOMException('Sync stopped.', 'AbortError');
        }

        async function acquireWake() {
            if (!running || requestingWake || wake || document.visibilityState !== 'visible') return;
            if (!navigator.wakeLock) {
                wakeNote.hidden = false;
                wakeNote.textContent = 'Keep the app open while syncing. Screen wake lock is unavailable in this browser.';
                return;
            }
            requestingWake = true;
            try {
                const lock = await navigator.wakeLock.request('screen');
                if (!running) { await lock.release(); return; }
                wake = lock;
                lock.addEventListener('release', () => { if (wake === lock) wake = null; });
                wakeNote.hidden = false;
                wakeNote.textContent = 'Keeping the screen awake during sync.';
            } catch {
                wakeNote.hidden = false;
                wakeNote.textContent = 'Keep the app open while syncing. The browser could not keep the screen awake.';
            } finally { requestingWake = false; }
        }

        async function scan(directory, files) {
            for await (const entry of directory.values()) {
                checkCancelled();
                if (entry.kind === 'directory') await scan(entry, files);
                else if (entry.kind === 'file' && extensions.test(entry.name)) {
                    const file = await entry.getFile();
                    files.push(file);
                    status.textContent = `Scanning ${handle.name}: ${files.length} media files found…`;
                }
            }
        }

        async function checksum(file) {
            const digest = await crypto.subtle.digest('SHA-256', await file.arrayBuffer());
            return Array.from(new Uint8Array(digest), byte => byte.toString(16).padStart(2, '0')).join('');
        }

        function errorMessage(code, data) {
            if (code === 401 || code === 419) return 'Your session expired. Sign in again to resume sync.';
            if (code === 413) return 'A file exceeds the server upload limit. Choose a smaller file or increase the server limit.';
            return Object.values(data?.errors || {}).flat()[0] || data?.message || 'Sync failed. Try again when your connection is available.';
        }

        async function synced(checksums) {
            const response = await fetch(panel.dataset.statusUrl, {
                method: 'POST', credentials: 'same-origin', signal: controller.signal,
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': panel.dataset.token },
                body: JSON.stringify({ checksums }),
            });
            const data = await response.json().catch(() => null);
            if (!response.ok || !Array.isArray(data?.synced)) throw new Error(errorMessage(response.status, data));
            return data.synced;
        }

        function upload(file, onProgress) {
            return new Promise((resolve, reject) => {
                const request = new XMLHttpRequest();
                xhr = request;
                request.open('POST', panel.dataset.uploadUrl);
                request.setRequestHeader('Accept', 'application/json');
                request.setRequestHeader('X-CSRF-TOKEN', panel.dataset.token);
                request.timeout = 15 * 60 * 1000;
                request.upload.addEventListener('progress', event => {
                    if (event.lengthComputable) onProgress(Math.min(0.99, event.loaded / event.total));
                });
                request.addEventListener('load', () => {
                    let data;
                    try { data = JSON.parse(request.responseText); } catch { data = null; }
                    if (request.status >= 200 && request.status < 300 && data?.storage) resolve(data);
                    else reject(new Error(errorMessage(request.status, data)));
                });
                request.addEventListener('error', () => reject(new Error('Connection lost. Sync will resume when you retry or reopen the app.')));
                request.addEventListener('timeout', () => reject(new Error('Upload timed out. Retry sync to continue.')));
                request.addEventListener('abort', () => reject(new DOMException('Sync stopped.', 'AbortError')));
                const body = new FormData();
                body.append('files[]', file, file.name);
                body.append('sync', '1');
                request.send(body);
            });
        }

        async function performSync() {
            checkCancelled();
            await acquireWake();
            const files = [];
            update(0, `Scanning ${handle.name}…`);
            await scan(handle, files);
            const candidates = [], unique = new Set();
            let skipped = 0;
            for (let index = 0; index < files.length; index++) {
                checkCancelled();
                const file = files[index];
                if (file.size > 512 * 1024 * 1024) { skipped++; continue; }
                update(index / files.length * 100, `Checking ${index + 1} of ${files.length}: ${file.name}`);
                const hash = await checksum(file);
                if (!unique.has(hash)) { unique.add(hash); candidates.push({ file, hash }); }
            }
            const existing = new Set();
            for (let index = 0; index < candidates.length; index += 100) {
                checkCancelled();
                status.textContent = 'Comparing media with your library…';
                (await synced(candidates.slice(index, index + 100).map(item => item.hash))).forEach(hash => existing.add(hash));
            }
            const pending = candidates.filter(item => !existing.has(item.hash));
            const totalBytes = pending.reduce((total, item) => total + item.file.size, 0);
            let completedBytes = 0;
            update(0, `${pending.length} new files to sync.`);
            for (let index = 0; index < pending.length; index++) {
                checkCancelled();
                const { file } = pending[index];
                const label = `Syncing ${index + 1} of ${pending.length}: ${file.name}`;
                update(totalBytes ? completedBytes / totalBytes * 100 : index / pending.length * 100, label);
                await upload(file, fraction => update(totalBytes ? (completedBytes + file.size * fraction) / totalBytes * 100 : 0, label));
                completedBytes += file.size;
                node('refresh').hidden = false;
            }
            update(100, pending.length ? `Sync complete: ${pending.length} files saved. Refresh the library to see them.` : 'Your media is already synced.');
            if (skipped) status.textContent += ` ${skipped} files over 512 MB were skipped.`;
        }

        async function run() {
            if (running || !handle) return;
            running = true;
            cancelled = false;
            controller = new AbortController();
            controls();
            try {
                if (navigator.locks) {
                    await navigator.locks.request(`agallery-media-sync:${panel.dataset.account}`, { ifAvailable: true }, async lock => {
                        if (!lock) { status.textContent = 'Media sync is already running in another tab.'; return; }
                        await performSync();
                    });
                } else await performSync();
            } catch (error) {
                status.textContent = error.name === 'AbortError' ? 'Sync stopped. Saved files stay in your library. Use Sync now to continue.' : error.name === 'NotAllowedError' ? 'Folder access was denied. Use Sync now to grant access again.' : error.message;
            } finally {
                running = false;
                xhr = null;
                if (wake) { const lock = wake; wake = null; await lock.release().catch(() => {}); }
                wakeNote.hidden = true;
                controls();
            }
        }

        document.addEventListener('visibilitychange', () => { if (running) void acquireWake(); });
        stop.addEventListener('click', () => { cancelled = true; controller?.abort(); xhr?.abort(); });
        start.addEventListener('click', async () => {
            try {
                if (await handle.requestPermission({ mode: 'read' }) !== 'granted') { status.textContent = 'Allow read access to your media folder to sync.'; return; }
                await run();
            } catch (error) { status.textContent = error.message; }
        });
        select.addEventListener('click', async () => {
            try {
                const selected = await window.showDirectoryPicker({ mode: 'read', id: 'agallery-media' });
                await savedFolder('write', selected);
                handle = selected;
                controls();
                await run();
            } catch (error) { if (error.name !== 'AbortError') status.textContent = error.message; }
        });
        forget.addEventListener('click', async () => {
            try {
                await savedFolder('forget');
                handle = null;
                progress.hidden = true;
                node('refresh').hidden = true;
                status.textContent = 'Folder disconnected. Files already uploaded stay in your library.';
                controls();
            } catch (error) { status.textContent = error.message; }
        });

        if (!window.isSecureContext || !window.showDirectoryPicker || !window.indexedDB || !window.crypto?.subtle) {
            status.textContent = 'Automatic folder sync is unavailable in this browser. Use Add files to upload photos and videos.';
            return;
        }
        (async () => {
            try {
                handle = await savedFolder('read');
                controls();
                if (!handle) { status.textContent = 'Choose your media folder once to enable sync on app load.'; return; }
                if (await handle.queryPermission({ mode: 'read' }) === 'granted') await run();
                else status.textContent = `Saved folder: ${handle.name}. Click Sync now to allow folder access.`;
            } catch (error) { status.textContent = error.message; select.hidden = false; }
        })();
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init, { once: true });
    else init();
})();
