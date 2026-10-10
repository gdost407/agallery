(() => {
    'use strict';

    // Keep only the last three decoded photos in memory, never in persistent storage.
    const photos = new Map();
    function remember(path, image) {
        photos.delete(path);
        photos.set(path, image);
        while (photos.size > 3) photos.delete(photos.keys().next().value);
    }

    window.galleryMediaNavigate = async ({ viewer, stage, details, direction, reducedMotion }) => {
        const path = viewer.dataset[direction];
        const status = viewer.querySelector('[data-viewer-status]');
        const currentImage = stage.querySelector('img');
        if (currentImage) remember(viewer.dataset.current, currentImage);
        const controller = new AbortController();
        const timeout = window.setTimeout(() => controller.abort(), 30000);
        viewer.setAttribute('aria-busy', 'true');
        stage.classList.remove('is-dragging');
        stage.style.transform = '';
        if (status) { status.hidden = false; status.textContent = 'Loading…'; }
        let incoming;
        try {
            const response = await fetch(path, { credentials: 'same-origin', cache: 'no-store', signal: controller.signal });
            if (!response.ok || response.redirected) throw new Error('Preview unavailable');
            const page = new DOMParser().parseFromString(await response.text(), 'text/html');
            const nextViewer = page.querySelector('[data-media-viewer][data-immersive]');
            const nextStage = nextViewer?.querySelector('[data-viewer-stage]');
            const nextDetails = page.getElementById('fileDetailsPanel');
            if (!nextStage || !nextDetails) throw new Error('Preview unavailable');
            incoming = document.createElement('div');
            incoming.className = 'viewer-stage viewer-stage-incoming';
            const cached = photos.get(path);
            incoming.replaceChildren(cached || document.importNode(nextStage.firstElementChild, true));
            const image = incoming.querySelector('img');
            if (image) {
                await image.decode();
                remember(path, image);
            }
            const video = incoming.querySelector('video');
            if (video) video.load();
            stage.querySelector('video')?.pause();
            viewer.appendChild(incoming);
            if (status) status.hidden = true;
            if (!reducedMotion && typeof stage.animate === 'function') {
                const offset = direction === 'next' ? 100 : -100;
                const drag = stage.getBoundingClientRect().left - viewer.getBoundingClientRect().left;
                const options = { duration: 260, easing: 'cubic-bezier(.22,.61,.36,1)', fill: 'forwards' };
                const outgoingAnimation = stage.animate([{ transform: `translateX(${drag}px)` }, { transform: `translateX(${-offset}%)` }], options);
                const incomingAnimation = incoming.animate([{ transform: `translateX(calc(${offset}% + ${drag}px))` }, { transform: 'translateX(0)' }], options);
                await Promise.allSettled([outgoingAnimation.finished, incomingAnimation.finished]);
                stage.replaceChildren(...incoming.childNodes);
                outgoingAnimation.cancel();
                incomingAnimation.cancel();
            } else {
                stage.replaceChildren(...incoming.childNodes);
            }
            viewer.dataset.current = path;
            viewer.dataset.previous = nextViewer.dataset.previous;
            viewer.dataset.next = nextViewer.dataset.next;
            viewer.classList.toggle('immersive-video', nextViewer.classList.contains('immersive-video'));
            // Keep the panel instance and its gesture listeners while refreshing all file actions.
            details.querySelector('.offcanvas-body').replaceChildren(...nextDetails.querySelector('.offcanvas-body').childNodes);
            for (const selector of ['.viewer-overlay-top', '.viewer-overlay-bottom']) {
                viewer.querySelector(selector).innerHTML = nextViewer.querySelector(selector).innerHTML;
            }
            viewer.querySelector('[data-bs-target="#fileDetailsPanel"]').setAttribute('aria-expanded', String(viewer.classList.contains('details-open')));
            document.title = page.title;
            // Update the current file without a page navigation; forms return to the correct preview.
            window.history.replaceState(null, '', path);
        } catch {
            if (status) { status.hidden = false; status.textContent = 'Could not load this file. Swipe or tap an arrow to retry.'; }
        } finally {
            incoming?.remove();
            window.clearTimeout(timeout);
            viewer.setAttribute('aria-busy', 'false');
            stage.style.transform = '';
        }
    };
})();
