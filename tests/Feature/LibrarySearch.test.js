import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import vm from 'node:vm';

const source = readFileSync(new URL('../../public/assets/app/js/gallery.js', import.meta.url), 'utf8');

function element() {
    return {
        value: '', hidden: false, textContent: '', listeners: {},
        classList: { toggle() {}, add() {}, remove() {} },
        addEventListener(name, callback) { this.listeners[name] = callback; },
        focus() {},
    };
}

function harness() {
    const names = ['librarySearch', 'fileGrid', 'fileCount', 'searchEmpty', 'resetSearch', 'fileSort', 'document', 'selectedFiles', 'libraryNavigation'];
    const nodes = Object.fromEntries(names.map(name => [name, element()]));
    const cards = ['Holiday.jpg', 'Report.pdf'].map(name => ({ hidden: false, dataset: { name } }));
    const dropzone = element();
    const document = {
        getElementById: name => nodes[name],
        querySelector: selector => selector === '[data-library]' ? element() : dropzone,
        querySelectorAll: selector => selector === '[data-file]' ? cards : [],
    };
    vm.runInNewContext(source, { document });
    return { nodes, cards };
}

test('library search works with no file type filter buttons and clear search restores every card', () => {
    const h = harness();
    h.nodes.librarySearch.value = 'HOLIDAY';
    h.nodes.librarySearch.listeners.input();
    assert.equal(h.cards[0].hidden, false);
    assert.equal(h.cards[1].hidden, true);
    assert.equal(h.nodes.fileCount.textContent, 1);
    h.nodes.resetSearch.listeners.click();
    assert.equal(h.nodes.librarySearch.value, '');
    assert.equal(h.cards[1].hidden, false);
    assert.equal(h.nodes.fileCount.textContent, 2);
});
