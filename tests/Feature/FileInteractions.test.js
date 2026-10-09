import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import vm from 'node:vm';

const source = readFileSync(new URL('../../public/assets/app/js/file-interactions.js', import.meta.url), 'utf8');

function element() {
    const classes = new Set();
    return {
        listeners: {}, dataset: {}, hidden: false, checked: false, disabled: false,
        classList: { toggle(name, enabled) { if (enabled) classes.add(name); else classes.delete(name); }, contains: name => classes.has(name) },
        addEventListener(name, callback) { this.listeners[name] = callback; },
        querySelector() { return null; },
        querySelectorAll() { return []; },
        closest() { return null; },
        click() { this.listeners.click?.({ target: this, preventDefault() {} }); },
    };
}

function selectionHarness() {
    const timers = new Map();
    let timerId = 0;
    const library = element();
    const toolbar = element();
    const count = element();
    const remove = element();
    const cancel = element();
    const start = element();
    const boxes = [element(), element()];
    const cards = boxes.map(box => {
        const card = element();
        card.querySelector = () => box;
        return card;
    });
    library.querySelectorAll = selector => selector === '[data-file]' ? cards : boxes;
    toolbar.querySelector = selector => ({ '[data-selection-count]': count, '[data-delete-selected]': remove, '[data-cancel-selection]': cancel }[selector]);
    const document = element();
    document.querySelector = selector => ({ '[data-library]': library, '[data-selection-toolbar]': toolbar, '[data-start-selection]': start }[selector] || null);
    vm.runInNewContext(source, {
        document,
        setTimeout(callback) { const id = ++timerId; timers.set(id, callback); return id; },
        clearTimeout(id) { timers.delete(id); },
    });
    const point = (x = 0, y = 0) => ({ isPrimary: true, button: 0, clientX: x, clientY: y, target: element() });
    const click = card => {
        let prevented = false;
        card.listeners.click({ target: element(), preventDefault() { prevented = true; } });
        return prevented;
    };
    return { library, toolbar, count, remove, cancel, start, boxes, cards, document, point, click, timers,
        fireTimers() { for (const callback of timers.values()) callback(); timers.clear(); },
    };
}

test('long press reveals checkboxes and delete action without opening the pressed file', () => {
    const h = selectionHarness();
    assert.equal(h.toolbar.hidden, true);
    h.cards[0].listeners.pointerdown(h.point());
    h.fireTimers();
    assert.equal(h.boxes[0].checked, true);
    assert.equal(h.toolbar.hidden, false);
    assert.equal(h.count.textContent, '1 selected');
    assert.equal(h.remove.disabled, false);
    assert.equal(h.click(h.cards[0]), true);
    assert.equal(h.boxes[0].checked, true);
    h.click(h.cards[1]);
    assert.equal(h.count.textContent, '2 selected');
    h.click(h.cards[0]);
    assert.equal(h.count.textContent, '1 selected');
});

test('short taps and scrolling never activate selection', () => {
    const h = selectionHarness();
    h.cards[0].listeners.pointerdown(h.point());
    h.cards[0].listeners.pointerup();
    h.fireTimers();
    assert.equal(h.click(h.cards[0]), false);
    h.cards[0].listeners.pointerdown(h.point());
    h.cards[0].listeners.pointermove(h.point(30, 30));
    h.fireTimers();
    assert.equal(h.toolbar.hidden, true);
    h.cards[0].listeners.pointerdown(h.point());
    h.document.listeners.scroll();
    h.fireTimers();
    assert.equal(h.toolbar.hidden, true);
});

test('desktop selection and cancellation clear checkboxes and disable deletion', () => {
    const h = selectionHarness();
    h.start.click();
    assert.equal(h.toolbar.hidden, false);
    assert.equal(h.remove.disabled, true);
    h.boxes[1].checked = true;
    h.boxes[1].listeners.change();
    assert.equal(h.count.textContent, '1 selected');
    h.cancel.click();
    assert.equal(h.boxes[1].checked, false);
    assert.equal(h.toolbar.hidden, true);
    assert.equal(h.remove.disabled, true);
});

function viewerHarness() {
    const viewer = element();
    viewer.dataset = { previous: '/previous', next: '/next' };
    const document = element();
    document.querySelector = selector => selector === '[data-media-viewer]' ? viewer : null;
    document.getElementById = () => element();
    const redirects = [];
    let detailsShown = 0;
    vm.runInNewContext(source, {
        document,
        window: { location: { assign: path => redirects.push(path) } },
        bootstrap: { Offcanvas: { getOrCreateInstance: () => ({ show() { detailsShown++; } }) } },
    });
    const touch = (x, y, target = element()) => ({ target, touches: [{ clientX: x, clientY: y }], changedTouches: [{ clientX: x, clientY: y }], preventDefault() {} });
    const swipe = (x, y) => {
        viewer.listeners.touchstart(touch(100, 100));
        viewer.listeners.touchmove(touch(100 + x, 100 + y));
        viewer.listeners.touchend(touch(100 + x, 100 + y));
    };
    return { viewer, document, redirects, touch, swipe, get detailsShown() { return detailsShown; } };
}

test('left and right swipes navigate while upward swipes open details', () => {
    const h = viewerHarness();
    h.swipe(-90, 0);
    h.swipe(90, 0);
    assert.deepEqual(h.redirects, ['/next', '/previous']);
    h.swipe(0, -90);
    assert.equal(h.detailsShown, 1);
    assert.equal(h.redirects.length, 2);
});

test('small gestures pinch zoom cancellations and list boundaries do not navigate', () => {
    const h = viewerHarness();
    h.swipe(15, 10);
    h.viewer.listeners.touchstart(h.touch(100, 100));
    h.viewer.listeners.touchmove({ touches: [{}, {}] });
    h.viewer.listeners.touchend(h.touch(0, 100));
    h.viewer.listeners.touchstart(h.touch(100, 100));
    h.viewer.listeners.touchcancel();
    h.viewer.listeners.touchend(h.touch(0, 100));
    h.viewer.dataset.next = '';
    h.swipe(-90, 0);
    assert.deepEqual(h.redirects, []);
    assert.equal(h.detailsShown, 0);
});

test('video control gestures and keyboard events inside form controls retain native behavior', () => {
    const h = viewerHarness();
    const video = { getBoundingClientRect: () => ({ bottom: 200 }) };
    const target = { closest: selector => selector === 'video' ? video : null };
    h.viewer.listeners.touchstart(h.touch(100, 180, target));
    h.viewer.listeners.touchend(h.touch(0, 180, target));
    assert.deepEqual(h.redirects, []);
    const input = { closest: () => ({}) };
    h.document.listeners.keydown({ key: 'ArrowRight', target: input, preventDefault() { throw new Error('Input should keep keyboard handling'); } });
    h.document.listeners.keydown({ key: 'ArrowRight', target: element(), preventDefault() {} });
    assert.deepEqual(h.redirects, ['/next']);
});
