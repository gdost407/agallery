(() => {
    'use strict';

    const library = document.querySelector('[data-library]');
    const toolbar = document.querySelector('[data-selection-toolbar]');
    if (library && toolbar) {
        const cards = Array.from(library.querySelectorAll('[data-file]'));
        const boxes = Array.from(library.querySelectorAll('[data-file-select]'));
        const count = toolbar.querySelector('[data-selection-count]');
        const remove = toolbar.querySelector('[data-delete-selected]');
        let selecting = false;
        let hold = null;
        let suppressedCard = null;
        function refresh() {
            const total = boxes.filter(box => box.checked).length;
            count.textContent = total + ' selected';
            remove.disabled = total === 0;
            toolbar.hidden = !selecting;
            library.classList.toggle('selection-mode', selecting);
            cards.forEach(card => card.classList.toggle('file-selected', Boolean(card.querySelector('[data-file-select]')?.checked)));
        }
        function cancelHold() {
            if (hold) clearTimeout(hold.timer);
            hold = null;
        }
        document.querySelector('[data-start-selection]').addEventListener('click', () => { selecting = true; refresh(); });
        toolbar.querySelector('[data-cancel-selection]').addEventListener('click', () => {
            selecting = false;
            boxes.forEach(box => { box.checked = false; });
            suppressedCard = null;
            cancelHold();
            refresh();
        });
        boxes.forEach(box => box.addEventListener('change', () => { selecting = true; refresh(); }));
        cards.forEach(card => {
            const box = card.querySelector('[data-file-select]');
            if (!box) return;
            card.addEventListener('pointerdown', event => {
                if (!event.isPrimary || event.button !== 0 || event.target.closest('input, button, form, label')) return;
                cancelHold();
                suppressedCard = null;
                hold = { x: event.clientX, y: event.clientY, timer: setTimeout(() => {
                    selecting = true;
                    box.checked = true;
                    suppressedCard = card;
                    hold = null;
                    refresh();
                }, 450) };
            });
            card.addEventListener('pointermove', event => {
                if (hold && Math.hypot(event.clientX - hold.x, event.clientY - hold.y) > 10) cancelHold();
            });
            ['pointerup', 'pointercancel', 'pointerleave'].forEach(name => card.addEventListener(name, cancelHold));
            card.addEventListener('contextmenu', event => { if (selecting || hold) event.preventDefault(); });
            card.addEventListener('click', event => {
                if (suppressedCard === card) {
                    event.preventDefault();
                    suppressedCard = null;
                    return;
                }
                if (event.target.closest('input, label')) return;
                if (!selecting) return;
                event.preventDefault();
                box.checked = !box.checked;
                refresh();
            }, true);
            card.addEventListener('dragstart', event => event.preventDefault());
        });
        document.addEventListener('keydown', event => {
            if (event.key === 'Escape' && selecting) toolbar.querySelector('[data-cancel-selection]').click();
        });
        document.addEventListener('scroll', cancelHold, true);
        refresh();
    }

    const viewer = document.querySelector('[data-media-viewer]');
    if (!viewer) return;
    let start = null;
    const showDetails = () => bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('fileDetailsPanel')).show();
    const navigate = direction => {
        const path = viewer.dataset[direction];
        if (path) window.location.assign(path);
    };
    viewer.addEventListener('touchstart', event => {
        start = null;
        if (event.touches.length !== 1 || event.target.closest('a, button, input, select, textarea')) return;
        const touch = event.touches[0];
        const video = event.target.closest('video');
        if (video && touch.clientY > video.getBoundingClientRect().bottom - 64) return;
        start = { x: touch.clientX, y: touch.clientY };
    }, { passive: true });
    viewer.addEventListener('touchmove', event => {
        if (!start || event.touches.length !== 1) { start = null; return; }
        const dx = event.touches[0].clientX - start.x;
        const dy = event.touches[0].clientY - start.y;
        if (Math.abs(dx) > 12 && Math.abs(dx) > Math.abs(dy) * 1.3 || dy < -12 && Math.abs(dy) > Math.abs(dx) * 1.3) {
            event.preventDefault();
        }
    }, { passive: false });
    viewer.addEventListener('touchend', event => {
        if (!start || event.changedTouches.length !== 1) { start = null; return; }
        const dx = event.changedTouches[0].clientX - start.x;
        const dy = event.changedTouches[0].clientY - start.y;
        start = null;
        if (Math.abs(dx) > 60 && Math.abs(dx) > Math.abs(dy) * 1.3) navigate(dx < 0 ? 'next' : 'previous');
        else if (dy < -60 && Math.abs(dy) > Math.abs(dx) * 1.3) showDetails();
    }, { passive: true });
    viewer.addEventListener('touchcancel', () => { start = null; });
    document.addEventListener('keydown', event => {
        if (event.target.closest('input, select, textarea, button, video, audio') || document.querySelector('.offcanvas.show, .modal.show')) return;
        if (event.key === 'ArrowLeft') { event.preventDefault(); navigate('previous'); }
        if (event.key === 'ArrowRight') { event.preventDefault(); navigate('next'); }
        if (event.key === 'ArrowUp') { event.preventDefault(); showDetails(); }
    });
})();
