(() => {
    'use strict';

    const library = document.querySelector('[data-library]');
    const search = document.getElementById('librarySearch');
    const grid = document.getElementById('fileGrid');
    const files = Array.from(document.querySelectorAll('[data-file]'));
    const chips = Array.from(document.querySelectorAll('[data-filter]'));
    let activeFilter = 'all';

    function filterFiles() {
        const query = search.value.trim().toLocaleLowerCase();
        let visible = 0;
        files.forEach(file => {
            const matches = (activeFilter === 'all' || file.dataset.type === activeFilter)
                && file.dataset.name.toLocaleLowerCase().includes(query);
            file.hidden = !matches;
            if (matches) visible++;
        });
        document.getElementById('fileCount').textContent = visible;
        document.getElementById('searchEmpty').classList.toggle('d-none', visible !== 0);
    }

    if (library) {
        search.addEventListener('input', filterFiles);
        chips.forEach(chip => chip.addEventListener('click', () => {
            activeFilter = chip.dataset.filter;
            chips.forEach(item => {
                item.classList.toggle('active', item === chip);
                item.setAttribute('aria-pressed', String(item === chip));
            });
            filterFiles();
        }));
        document.getElementById('resetSearch').addEventListener('click', () => {
            search.value = '';
            chips[0].click();
            search.focus();
        });
        document.getElementById('fileSort').addEventListener('change', event => {
            const order = event.target.value;
            [...files].sort((a, b) => order === 'name'
                ? a.dataset.name.localeCompare(b.dataset.name)
                : order === 'oldest'
                    ? a.dataset.date.localeCompare(b.dataset.date)
                    : b.dataset.date.localeCompare(a.dataset.date))
                .forEach(file => grid.appendChild(file));
        });
        document.querySelectorAll('[data-view]').forEach(button => button.addEventListener('click', () => {
            grid.classList.toggle('list-view', button.dataset.view === 'list');
            document.querySelectorAll('[data-view]').forEach(item => {
                item.classList.toggle('active', item === button);
                item.setAttribute('aria-pressed', String(item === button));
            });
        }));
        document.querySelectorAll('[data-preview]').forEach(button => button.addEventListener('click', () => {
            const file = button.closest('[data-file]');
            document.getElementById('filePreviewTitle').textContent = file.dataset.name;
            const body = document.getElementById('filePreviewBody');
            body.replaceChildren();
            if (file.dataset.type === 'photos') {
                const image = document.createElement('img');
                image.className = 'preview-image';
                image.src = file.dataset.image;
                image.alt = file.dataset.name;
                body.appendChild(image);
            } else {
                const preview = document.createElement('div');
                preview.className = 'preview-document';
                const icon = document.createElement('i');
                icon.className = file.dataset.type === 'videos' ? 'bi bi-play-btn' : 'bi bi-file-earmark-text';
                icon.setAttribute('aria-hidden', 'true');
                const type = document.createElement('strong');
                type.textContent = file.dataset.extension + ' sample file';
                const note = document.createElement('p');
                note.className = 'mt-3 mb-0';
                note.textContent = 'This is a sample library item. A real file is needed to view its contents.';
                preview.append(icon, type, note);
                body.appendChild(preview);
            }
            const details = document.createElement('p');
            details.className = 'preview-details';
            details.textContent = file.querySelector('.file-meta').textContent.replace(/\s+/g, ' ').trim();
            body.appendChild(details);
            bootstrap.Modal.getOrCreateInstance(document.getElementById('filePreview')).show();
        }));
    }

    const input = document.getElementById('document');
    const dropzone = document.querySelector('.upload-dropzone');
    function showSelectedFiles(selected) {
        const container = document.getElementById('selectedFiles');
        container.replaceChildren();
        Array.from(selected).forEach(file => {
            const row = document.createElement('div');
            row.className = 'selected-file';
            const icon = document.createElement('i');
            icon.className = 'bi bi-file-earmark-check';
            icon.setAttribute('aria-hidden', 'true');
            const name = document.createElement('span');
            name.textContent = file.name;
            const size = document.createElement('small');
            size.textContent = file.size >= 1048576 ? (file.size / 1048576).toFixed(1) + ' MB' : Math.ceil(file.size / 1024) + ' KB';
            row.append(icon, name, size);
            container.appendChild(row);
        });
    }
    input.addEventListener('change', () => showSelectedFiles(input.files));
    ['dragover', 'dragenter'].forEach(type => dropzone.addEventListener(type, event => {
        event.preventDefault();
        dropzone.classList.add('dragging');
    }));
    ['dragleave', 'drop'].forEach(type => dropzone.addEventListener(type, event => {
        event.preventDefault();
        dropzone.classList.remove('dragging');
    }));
    dropzone.addEventListener('drop', event => {
        input.files = event.dataTransfer.files;
        showSelectedFiles(input.files);
    });
    const navigation = document.getElementById('libraryNavigation');
    document.querySelectorAll('[data-open-on-load]').forEach(modal => bootstrap.Modal.getOrCreateInstance(modal).show());
    navigation.addEventListener('show.bs.offcanvas', () => {
        navigation.querySelector('.sidebar-brand').scrollIntoView({ block: 'start' });
    });
    document.querySelectorAll('[data-bs-target="#offcanvasUploadDoc"]').forEach(button => button.addEventListener('click', () => {
        if (window.innerWidth < 992) bootstrap.Offcanvas.getInstance(navigation)?.hide();
    }));
})();
