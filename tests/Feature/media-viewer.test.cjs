const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');

function harness() {
    const events = [];
    const nodes = {};
    let fail = false;
    let finishDecode;
    let finishAnimation;
    const decode = new Promise(resolve => { finishDecode = resolve; });
    const animation = new Promise(resolve => { finishAnimation = resolve; });
    function stage(image) {
        return {
            childNodes: image ? [image] : [], style: {}, classList: { remove() {} },
            getBoundingClientRect: () => ({ left: 0 }),
            querySelector(selector) { return selector === 'img' ? this.childNodes[0] : null; },
            replaceChildren(...children) { this.childNodes = children; },
            remove() { events.push('remove'); },
            animate(frames) { events.push(frames); return { finished: animation, cancel() {} }; },
        };
    }
    const first = { id: 'first', decode: async () => {} };
    const current = stage(first);
    const body = stage({ id: 'old-actions' });
    const details = { querySelector: () => body };
    const status = {};
    const overlays = { '.viewer-overlay-top': {}, '.viewer-overlay-bottom': {}, '[data-bs-target="#fileDetailsPanel"]': { setAttribute() {} } };
    const viewer = {
        dataset: { current: '/first', next: '/second' },
        getBoundingClientRect: () => ({ left: 0 }),
        classList: { toggle() {}, contains: () => false },
        querySelector: selector => selector === '[data-viewer-status]' ? status : overlays[selector],
        setAttribute(name, value) { this[name] = value; }, appendChild() { events.push('append'); },
    };
    const window = { setTimeout: () => 1, clearTimeout() {}, history: { replaceState(state, title, path) { events.push(path); } } };
    const document = { createElement: () => stage(), importNode: node => node };
    const fetch = async path => {
        events.push('fetch:' + path);
        return { ok: !fail, redirected: false, text: async () => path };
    };
    class DOMParser {
        parseFromString(path) {
            nodes[path] = { id: path, decode: () => decode };
            const nextStage = stage(nodes[path]);
            nextStage.firstElementChild = nodes[path];
            const nextViewer = { dataset: { previous: '/first', next: '/third' }, classList: { contains: () => false }, querySelector: selector => selector === '[data-viewer-stage]' ? nextStage : { innerHTML: path } };
            return { title: path, querySelector: () => nextViewer, getElementById: () => ({ querySelector: () => stage({ id: 'new-actions' }) }) };
        }
    }
    vm.runInNewContext(fs.readFileSync('public/assets/app/js/media-viewer.js', 'utf8'), { window, document, fetch, DOMParser, AbortController });
    return { events, current, viewer, body, status, first, finishDecode, finishAnimation, fail: () => { fail = true; }, navigate: reducedMotion => window.galleryMediaNavigate({ viewer, stage: current, details, direction: 'next', reducedMotion }) };
}

test('keeps current photo until decoded and synchronizes both slides before updating actions', async () => {
    const app = harness();
    const loading = app.navigate(false);
    await new Promise(resolve => setImmediate(resolve));
    assert.equal(app.current.childNodes[0], app.first);
    assert.equal(app.events.includes('append'), false);
    app.finishDecode();
    await new Promise(resolve => setImmediate(resolve));
    assert.equal(app.events.filter(Array.isArray).length, 2);
    assert.equal(app.body.childNodes[0].id, 'old-actions');
    app.finishAnimation();
    await loading;
    assert.equal(app.current.childNodes[0].id, '/second');
    assert.equal(app.body.childNodes[0].id, 'new-actions');
    assert.equal(app.viewer.dataset.current, '/second');
    assert.equal(app.viewer['aria-busy'], 'false');
});

test('failed AJAX leaves the original photo and actions available for retry', async () => {
    const app = harness();
    app.fail();
    await app.navigate(false);
    assert.equal(app.current.childNodes[0], app.first);
    assert.equal(app.body.childNodes[0].id, 'old-actions');
    assert.match(app.status.textContent, /retry/);
    assert.equal(app.viewer['aria-busy'], 'false');
});

test('reduced motion skips animations and revisiting a photo reuses its decoded image', async () => {
    const app = harness();
    app.finishDecode();
    await app.navigate(true);
    app.viewer.dataset.next = '/first';
    await app.navigate(true);
    assert.equal(app.current.childNodes[0], app.first);
    assert.equal(app.events.filter(Array.isArray).length, 0);
    assert.equal(app.events.includes('fetch:/first'), true);
});
