import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import vm from 'node:vm';

const workerSource = readFileSync(new URL('../../public/service-worker.js', import.meta.url), 'utf8');
const installSource = readFileSync(new URL('../../public/pwa.js', import.meta.url), 'utf8');

function workerHarness(fetcher = async () => new Response('live')) {
    const listeners = {};
    const scope = 'https://example.com/gallery/';
    const cached = [];
    const deleted = [];
    const cache = {
        addAll: async urls => cached.push(...urls),
        match: async url => String(url).endsWith('offline.html')
            ? new Response('<html><head></head><body>Offline</body></html>') : undefined,
    };
    const prefix = 'agallery-pwa-' + encodeURIComponent('/gallery/') + '-';
    const context = {
        URL, Response,
        fetch: fetcher,
        caches: {
            open: async () => cache,
            keys: async () => [prefix + 'v0', prefix + 'v1', 'another-app-cache'],
            delete: async name => { deleted.push(name); return true; },
        },
        self: {
            registration: { scope },
            location: { origin: 'https://example.com' },
            skipWaiting: async () => {},
            clients: { claim: async () => {} },
            addEventListener: (name, listener) => { listeners[name] = listener; },
        },
    };
    vm.runInNewContext(workerSource, context);
    return { listeners, scope, cached, deleted, prefix };
}

test('installation caches only public offline assets', async () => {
    const harness = workerHarness();
    let work;
    harness.listeners.install({ waitUntil: promise => { work = promise; } });
    await work;
    assert.equal(harness.cached.length, 5);
    assert.ok(harness.cached.every(url => /\/(offline\.html|pwa-icon[^/]+\.png)$/.test(url)));
});

test('navigation always uses the network and never caches authenticated responses', async () => {
    const calls = [];
    const harness = workerHarness(async (request, options) => {
        calls.push({ url: request.url, options });
        return new Response('private dashboard');
    });
    let work;
    harness.listeners.fetch({
        request: { url: harness.scope + 'app/dashboard', method: 'GET', mode: 'navigate' },
        respondWith: promise => { work = promise; },
    });
    assert.equal(await (await work).text(), 'private dashboard');
    assert.equal(calls[0].options.cache, 'no-store');
    assert.equal(harness.cached.length, 0);
});

test('offline navigation uses the public fallback with the correct base path', async () => {
    const harness = workerHarness(async () => { throw new Error('Offline'); });
    let work;
    harness.listeners.fetch({
        request: { url: harness.scope + 'app/files/example', method: 'GET', mode: 'navigate' },
        respondWith: promise => { work = promise; },
    });
    const response = await work;
    assert.match(await response.text(), /<base href="https:\/\/example.com\/gallery\/">/);
    assert.equal(response.headers.get('Cache-Control'), 'no-store');
});

test('uploads file contents and cross origin requests do not use worker caching', () => {
    const harness = workerHarness();
    for (const request of [
        { url: harness.scope + 'app/files', method: 'POST', mode: 'navigate' },
        { url: harness.scope + 'app/files/example/content', method: 'GET', mode: 'cors' },
        { url: 'https://other.example/app/dashboard', method: 'GET', mode: 'navigate' },
    ]) {
        harness.listeners.fetch({ request, respondWith: () => assert.fail('Private request intercepted') });
    }
});

test('worker updates delete only old caches belonging to this app scope', async () => {
    const harness = workerHarness();
    let work;
    harness.listeners.activate({ waitUntil: promise => { work = promise; } });
    await work;
    assert.deepEqual(harness.deleted, [harness.prefix + 'v0']);
});

function installHarness({ ios = false, installed = false } = {}) {
    const elements = Object.fromEntries(['pwaInstall', 'pwaInstallButton', 'pwaInstallHelp', 'pwaInstallDismiss'].map(id => [id, {
        hidden: true, disabled: false, listeners: {},
        addEventListener(name, listener) { this.listeners[name] = listener; },
    }]));
    const listeners = {};
    const registrations = [];
    const navigator = {
        userAgent: ios ? 'iPhone' : 'Android',
        platform: ios ? 'iPhone' : 'Linux',
        maxTouchPoints: 1,
        serviceWorker: { register: async (...args) => { registrations.push(args); } },
    };
    const window = {
        navigator, isSecureContext: true,
        matchMedia: () => ({ matches: installed, addEventListener: () => {} }),
        addEventListener: (name, listener) => { listeners[name] = listener; },
    };
    vm.runInNewContext(installSource, {
        URL, window, navigator, console,
        location: { href: 'https://example.com/gallery/' },
        document: {
            currentScript: { dataset: { serviceWorker: 'https://example.com/gallery/service-worker.js' } },
            getElementById: id => elements[id],
        },
        sessionStorage: { getItem: () => null, setItem: () => {} },
    });
    return { elements, listeners, registrations };
}

test('native install prompts are shown on request and dismissed after installation', async () => {
    const harness = installHarness();
    let prompted = false;
    harness.listeners.beforeinstallprompt({
        preventDefault() {},
        prompt: async () => { prompted = true; },
        userChoice: Promise.resolve({ outcome: 'accepted' }),
    });
    assert.equal(harness.elements.pwaInstall.hidden, false);
    assert.equal(harness.elements.pwaInstallButton.hidden, false);
    await harness.elements.pwaInstallButton.listeners.click();
    assert.equal(prompted, true);
    assert.equal(harness.elements.pwaInstall.hidden, true);
    assert.equal(harness.registrations[0][1].scope, '/gallery/');
});

test('iPhone shows home screen instructions and installed apps hide promotion', () => {
    const ios = installHarness({ ios: true });
    assert.equal(ios.elements.pwaInstall.hidden, false);
    assert.equal(ios.elements.pwaInstallHelp.hidden, false);
    assert.equal(ios.elements.pwaInstallButton.hidden, true);
    const installed = installHarness({ ios: true, installed: true });
    assert.equal(installed.elements.pwaInstall.hidden, true);
});
