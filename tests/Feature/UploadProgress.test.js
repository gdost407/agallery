import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import vm from 'node:vm';

const source = readFileSync(new URL('../../public/assets/app/js/upload.js', import.meta.url), 'utf8');

function element() {
    return {
        hidden: true, disabled: false, textContent: '', style: {}, attributes: {}, listeners: {},
        addEventListener(name, callback) { this.listeners[name] = callback; },
        setAttribute(name, value) { this.attributes[name] = value; },
    };
}

function harness() {
    const progress = element();
    const bar = element();
    const container = element();
    container.querySelector = selector => selector === '.progress-bar' ? bar : progress;
    const status = element();
    const error = element();
    const button = element();
    const input = element();
    const dropzone = element();
    const panel = element();
    panel.querySelector = () => dropzone;
    const form = element();
    form.action = 'https://example.com/app/files';
    form.reportValidity = () => true;
    const nodes = {
        '[data-upload-progress]': container, '[data-upload-status]': status,
        '[data-upload-error]': error, '[type="submit"]': button, '[type="file"]': input,
    };
    form.querySelector = selector => nodes[selector];
    const requests = [];
    const redirects = [];
    class FakeXHR {
        constructor() {
            this.listeners = {};
            this.upload = element();
            this.headers = {};
            requests.push(this);
        }
        open(method, url) { this.method = method; this.url = url; }
        setRequestHeader(name, value) { this.headers[name] = value; }
        addEventListener(name, callback) { this.listeners[name] = callback; }
        send(body) { this.body = body; }
    }
    class FakeFormData {
        constructor(target) { this.form = target; this.fileWasEnabled = !input.disabled; }
    }
    const context = {
        URL, XMLHttpRequest: FakeXHR, FormData: FakeFormData,
        document: { querySelector: () => form, getElementById: () => panel },
        window: {
            XMLHttpRequest: FakeXHR, FormData: FakeFormData,
            location: { href: 'https://example.com/app/dashboard', origin: 'https://example.com', assign: url => redirects.push(url) },
        },
    };
    vm.runInNewContext(source, context);
    const submit = () => form.listeners.submit({ preventDefault() {} });
    const complete = (code, data) => {
        const xhr = requests.at(-1);
        xhr.status = code;
        xhr.responseText = JSON.stringify(data);
        xhr.listeners.load();
    };
    return { form, panel, progress, bar, container, status, error, button, input, requests, redirects, submit, complete };
}

test('upload progress reports actual bytes and waits for saving before redirecting', () => {
    const h = harness();
    h.submit();
    assert.equal(h.container.hidden, false);
    assert.equal(h.input.disabled, true);
    assert.equal(h.requests[0].body.fileWasEnabled, true);
    assert.equal(h.requests[0].headers.Accept, 'application/json');
    h.requests[0].upload.listeners.progress({ lengthComputable: true, loaded: 25, total: 100 });
    assert.equal(h.bar.style.width, '25%');
    assert.equal(h.progress.attributes['aria-valuenow'], '25');
    h.requests[0].upload.listeners.progress({ lengthComputable: true, loaded: 100, total: 100 });
    assert.match(h.status.textContent, /Saving files/);
    assert.deepEqual(h.redirects, []);
    h.complete(200, { redirect: 'https://example.com/app/photos' });
    assert.deepEqual(h.redirects, ['https://example.com/app/photos']);
});

test('duplicate submissions and closing the panel are blocked while uploading', () => {
    const h = harness();
    h.submit();
    h.submit();
    assert.equal(h.requests.length, 1);
    let prevented = false;
    h.panel.listeners['hide.bs.offcanvas']({ preventDefault() { prevented = true; } });
    assert.equal(prevented, true);
});

test('validation errors preserve inputs and permit another upload attempt', () => {
    const h = harness();
    h.submit();
    h.complete(422, { errors: { files: ['Not enough storage.'] } });
    assert.equal(h.error.textContent, 'Not enough storage.');
    assert.equal(h.error.hidden, false);
    assert.equal(h.input.disabled, false);
    assert.equal(h.button.disabled, false);
    assert.deepEqual(h.redirects, []);
    h.submit();
    assert.equal(h.requests.length, 2);
    assert.equal(h.error.hidden, true);
    assert.equal(h.bar.style.width, '0%');
});

test('network errors restore controls and do not report successful uploads', () => {
    const h = harness();
    h.submit();
    h.requests[0].listeners.error();
    assert.match(h.error.textContent, /Connection lost/);
    assert.equal(h.button.disabled, false);
    assert.deepEqual(h.redirects, []);
});

test('expired sessions and server upload limits display useful errors', () => {
    for (const [code, expected] of [[419, /session expired/], [413, /upload limit/]]) {
        const h = harness();
        h.submit();
        h.complete(code, null);
        assert.match(h.error.textContent, expected);
        assert.equal(h.input.disabled, false);
    }
});

test('upload completion rejects redirects to another website', () => {
    const h = harness();
    h.submit();
    h.complete(200, { redirect: 'https://other.example.com/app/photos' });
    assert.deepEqual(h.redirects, []);
    assert.equal(h.button.disabled, false);
});
