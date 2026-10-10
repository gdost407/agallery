const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');

function browser(saved, dark = false, blocked = false) {
    const events = {};
    const root = { dataset: {}, style: {}, classList: { toggle() {} } };
    const media = { matches: dark, addEventListener: (name, callback) => { events.system = callback; } };
    const select = { value: '', addEventListener: (name, callback) => { events.select = callback; } };
    const label = {};
    const icon = {};
    const button = {
        setAttribute(name, value) { this[name] = value; },
        querySelector: selector => selector.includes('label') ? label : icon,
        addEventListener: (name, callback) => { events.click = callback; },
    };
    const document = {
        documentElement: root,
        querySelector: () => null,
        querySelectorAll: selector => selector.includes('toggle') ? [button] : [select],
        addEventListener: (name, callback) => { events.ready = callback; },
    };
    const window = {
        matchMedia: () => media,
        localStorage: {
            getItem() { if (blocked) throw Error('blocked'); return saved; },
            setItem(key, value) { if (blocked) throw Error('blocked'); saved = value; },
        },
        addEventListener: (name, callback) => { events[name] = callback; },
    };
    vm.runInNewContext(fs.readFileSync('public/theme.js', 'utf8'), { window, document });
    events.ready();
    return { root, media, events, select, button, saved: () => saved, setSaved: value => { saved = value; } };
}

test('defaults to device theme and follows device changes', () => {
    const app = browser(null, true);
    assert.equal(app.root.dataset.theme, 'dark');
    assert.equal(app.select.value, 'system');
    app.media.matches = false;
    app.events.system();
    assert.equal(app.root.dataset.theme, 'light');
});

test('explicit theme persists and toggle announces its next action', () => {
    const app = browser('light', true);
    assert.equal(app.root.dataset.theme, 'light');
    app.events.click();
    assert.equal(app.saved(), 'dark');
    assert.equal(app.root.dataset.bsTheme, 'dark');
    assert.equal(app.button['aria-label'], 'Switch to light mode');
    app.media.matches = false;
    app.events.system();
    assert.equal(app.root.dataset.theme, 'dark');
    app.select.value = 'system';
    app.events.select();
    assert.equal(app.root.dataset.theme, 'light');
});

test('works when storage is blocked or contains an invalid value', () => {
    for (const app of [browser('invalid', true), browser(null, true, true)]) {
        assert.equal(app.root.dataset.theme, 'dark');
        app.events.click();
        assert.equal(app.root.dataset.theme, 'light');
    }
});

test('updates when another tab changes or clears the preference', () => {
    const app = browser('light', true);
    app.setSaved('dark');
    app.events.storage({ key: 'agallery-theme' });
    assert.equal(app.root.dataset.theme, 'dark');
    app.setSaved(null);
    app.events.storage({ key: null });
    assert.equal(app.select.value, 'system');
});
