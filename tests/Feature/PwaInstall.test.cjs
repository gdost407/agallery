const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');

function harness(dismissed = false) {
    const events = {};
    function element() {
        return { hidden: true, disabled: false, events: {}, addEventListener(name, callback) { this.events[name] = callback; } };
    }
    const banner = element(), button = element(), help = element(), close = element();
    const nodes = { pwaInstall: banner, pwaInstallButton: button, pwaInstallHelp: help, pwaInstallDismiss: close };
    const navigator = { userAgent: 'Chrome', platform: 'Linux', maxTouchPoints: 0 };
    const window = { navigator, matchMedia: () => ({ matches: false, addEventListener() {} }), addEventListener(name, callback) { events[name] = callback; } };
    const sessionStorage = { getItem: () => dismissed ? '1' : null, setItem() {} };
    vm.runInNewContext(fs.readFileSync('public/pwa.js', 'utf8'), {
        window, navigator, sessionStorage, document: { currentScript: null, getElementById: id => nodes[id] },
    });
    return { events, banner, button, close };
}

test('the visible custom Install button prompts once after the user clicks', async () => {
    const app = harness();
    let prevented = false, prompted = 0;
    app.events.beforeinstallprompt({ preventDefault() { prevented = true; }, async prompt() { prompted++; }, userChoice: Promise.resolve({ outcome: 'dismissed' }) });
    assert.equal(prevented, true);
    assert.equal(app.banner.hidden, false);
    assert.equal(app.button.hidden, false);
    assert.equal(prompted, 0);
    await app.button.events.click();
    await app.button.events.click();
    assert.equal(prompted, 1);
    assert.equal(app.banner.hidden, true);
});

test('a previously dismissed install banner does not suppress the browser prompt', () => {
    const app = harness(true);
    app.events.beforeinstallprompt({ preventDefault() { assert.fail('Hidden UI must not intercept installation'); } });
    assert.equal(app.banner.hidden, true);
});

test('dismissing the install banner also stops interception for later events', () => {
    const app = harness();
    app.close.events.click();
    app.events.beforeinstallprompt({ preventDefault() { assert.fail('Dismissed UI must not intercept installation'); } });
    assert.equal(app.banner.hidden, true);
});
