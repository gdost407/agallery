import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { webcrypto } from 'node:crypto';
import vm from 'node:vm';

const source = readFileSync(new URL('../../public/assets/app/js/media-sync.js', import.meta.url), 'utf8');
const tick = () => new Promise(resolve => setTimeout(resolve, 5));
async function until(predicate) {
    for (let index = 0; index < 200; index++) {
        if (predicate()) return;
        await tick();
    }
    assert.fail('Sync did not reach the expected state');
}
function element() {
    return { hidden: true, disabled: false, textContent: '', style: {}, attributes: {}, listeners: {},
        addEventListener(name, callback) { this.listeners[name] = callback; },
        setAttribute(name, value) { this.attributes[name] = value; } };
}
function media(name, bytes) {
    const buffer = new TextEncoder().encode(bytes);
    return { kind: 'file', name, getFile: async () => ({ name, size: buffer.length, arrayBuffer: async () => buffer.slice().buffer }) };
}
function directory(entries, permission = 'granted') {
    return { name: 'Camera', kind: 'directory', permissionRequests: 0,
        queryPermission: async () => permission,
        async requestPermission() { this.permissionRequests++; return 'granted'; },
        async *values() { yield* entries; } };
}
function harness({ folder = null, supported = true, existing = [], failedWake = false, otherTab = false } = {}) {
    const nodes = Object.fromEntries(['select', 'start', 'stop', 'forget', 'status', 'progress', 'bar', 'wake', 'refresh'].map(name => [name, element()]));
    const panel = { dataset: { account: '7', statusUrl: '/status', uploadUrl: '/files', token: 'csrf' },
        querySelector(selector) { return nodes[selector.slice(11, -1)]; } };
    const document = { readyState: 'loading', visibilityState: 'visible', listeners: {},
        querySelector: () => panel, addEventListener(name, callback) { this.listeners[name] = callback; } };
    const saved = new Map(folder ? [['media-folder:7', folder]] : []);
    const indexedDB = { open() {
        const request = {};
        setImmediate(() => {
            request.result = { close() {}, transaction() {
                const transaction = { objectStore: () => ({
                    get: key => ({ result: saved.get(key) }),
                    put: (value, key) => { saved.set(key, value); return {}; },
                    delete: key => { saved.delete(key); return {}; },
                }) };
                setImmediate(() => transaction.oncomplete());
                return transaction;
            } };
            request.onsuccess();
        });
        return request;
    } };
    const requests = [], checks = [], locks = [];
    class XHR {
        constructor() { this.listeners = {}; this.upload = element(); this.headers = {}; requests.push(this); }
        open(method, url) { this.url = url; }
        setRequestHeader(key, value) { this.headers[key] = value; }
        addEventListener(name, callback) { this.listeners[name] = callback; }
        send(body) { this.body = body; }
        finish(code = 200, data = { storage: {} }) { this.status = code; this.responseText = JSON.stringify(data); this.listeners.load(); }
        abort() { this.listeners.abort(); }
    }
    class FormData {
        constructor() { this.values = {}; }
        append(key, value) { this.values[key] = value; }
    }
    const navigator = { wakeLock: { async request() {
        if (failedWake) throw new Error('denied');
        const lock = { released: false, addEventListener(name, callback) { this.callback = callback; },
            async release() { this.released = true; this.callback?.(); } };
        locks.push(lock);
        return lock;
    } } };
    if (otherTab) navigator.locks = { request: async (name, options, callback) => callback(null) };
    const window = { isSecureContext: true, indexedDB, crypto: webcrypto };
    if (supported) window.showDirectoryPicker = async () => folder || directory([]);
    vm.runInNewContext(source, { window, document, navigator, indexedDB, crypto: webcrypto, XMLHttpRequest: XHR, FormData, AbortController, DOMException,
        fetch: async (url, options) => { checks.push(JSON.parse(options.body)); return { ok: true, json: async () => ({ synced: existing }) }; } });
    document.listeners.DOMContentLoaded();
    return { nodes, requests, checks, locks, document, saved };
}

test('saved folder scans nested media, skips non media and server duplicates, and uploads sequentially', async () => {
    const duplicate = await webcrypto.subtle.digest('SHA-256', new TextEncoder().encode('saved'));
    const hash = Buffer.from(duplicate).toString('hex');
    const nested = directory([media('clip.mp4', 'video bytes')]);
    const h = harness({ folder: directory([media('old.jpg', 'saved'), media('new.jpg', 'photo bytes'), media('renamed.jpg', 'photo bytes'), media('notes.txt', 'ignore'), nested]), existing: [hash] });
    await until(() => h.requests.length === 1);
    assert.equal(h.checks[0].checksums.length, 3);
    assert.equal(h.requests[0].body.values['files[]'].name, 'new.jpg');
    assert.equal(h.requests[0].body.values.sync, '1');
    assert.equal(h.requests[0].headers['X-CSRF-TOKEN'], 'csrf');
    assert.equal(h.nodes.select.disabled, true);
    h.requests[0].upload.listeners.progress({ lengthComputable: true, loaded: 10, total: 11 });
    assert.ok(Number(h.nodes.progress.attributes['aria-valuenow']) > 0);
    h.requests[0].finish();
    await until(() => h.requests.length === 2);
    assert.equal(h.requests[1].body.values['files[]'].name, 'clip.mp4');
    h.requests[1].finish();
    await until(() => h.nodes.status.textContent.startsWith('Sync complete'));
    await until(() => h.locks[0].released);
    assert.equal(h.nodes.progress.attributes['aria-valuenow'], '100');
    assert.equal(h.nodes.refresh.hidden, false);
});

test('missing saved handle shows picker and persists the selected handle for this account', async () => {
    const h = harness();
    await until(() => h.nodes.status.textContent.includes('Choose your media folder'));
    assert.equal(h.requests.length, 0);
    assert.equal(h.nodes.select.hidden, false);
    await h.nodes.select.listeners.click();
    assert.equal(h.saved.get('media-folder:7').kind, 'directory');
    assert.equal(h.nodes.status.textContent, 'Your media is already synced.');
    await h.nodes.forget.listeners.click();
    assert.equal(h.saved.has('media-folder:7'), false);
});

test('permission renewal waits for a user gesture and then resumes', async () => {
    const folder = directory([media('photo.jpg', 'photo')], 'prompt');
    const h = harness({ folder });
    await until(() => h.nodes.status.textContent.includes('Click Sync now'));
    assert.equal(folder.permissionRequests, 0);
    assert.equal(h.requests.length, 0);
    const resumed = h.nodes.start.listeners.click();
    await until(() => h.requests.length === 1);
    h.requests[0].finish();
    await resumed;
    assert.equal(folder.permissionRequests, 1);
});

test('wake lock refusal permits sync and session errors stop the queue for retry', async () => {
    const h = harness({ folder: directory([media('first.jpg', 'first'), media('second.jpg', 'second')]), failedWake: true });
    await until(() => h.requests.length === 1);
    assert.match(h.nodes.wake.textContent, /could not keep/);
    h.requests[0].finish(419, {});
    await until(() => h.nodes.status.textContent.includes('session expired'));
    assert.equal(h.requests.length, 1);
    assert.equal(h.nodes.select.disabled, false);
});

test('wake lock is reacquired on visibility change and stop aborts an in flight upload', async () => {
    const h = harness({ folder: directory([media('first.jpg', 'first'), media('second.jpg', 'second')]) });
    await until(() => h.requests.length === 1);
    await h.locks[0].release();
    h.document.listeners.visibilitychange();
    await until(() => h.locks.length === 2);
    h.nodes.stop.listeners.click();
    await until(() => h.nodes.status.textContent.includes('Sync stopped'));
    await until(() => h.locks[1].released);
    assert.equal(h.requests.length, 1);
});

test('unsupported browsers and a busy tab do not scan or upload', async () => {
    const unavailable = harness({ supported: false });
    assert.match(unavailable.nodes.status.textContent, /unavailable/);
    assert.equal(unavailable.requests.length, 0);
    const busy = harness({ folder: directory([media('first.jpg', 'first')]), otherTab: true });
    await until(() => busy.nodes.status.textContent.includes('another tab'));
    assert.equal(busy.requests.length, 0);
    assert.equal(busy.locks.length, 0);
});
