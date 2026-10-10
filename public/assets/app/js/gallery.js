(() => {
    'use strict';

    const library = document.querySelector('[data-library]');
    const search = document.getElementById('librarySearch');
    const grid = document.getElementById('fileGrid');
    const files = Array.from(document.querySelectorAll('[data-file]'));
    const searchToggle = document.querySelector('[data-search-toggle]');
    const searchPanel = document.getElementById('navbarSearch');
    if (searchToggle && searchPanel && search) {
        function setSearchOpen(open) {
            searchPanel.hidden = !open;
            searchToggle.setAttribute('aria-expanded', String(open));
            searchToggle.setAttribute('aria-label', open ? 'Close search' : 'Open search');
            if (open) search.focus();
            else searchToggle.focus();
        }
        searchToggle.addEventListener('click', () => setSearchOpen(searchPanel.hidden));
        searchPanel.querySelector('[data-search-close]').addEventListener('click', () => setSearchOpen(false));
        searchPanel.addEventListener('keydown', event => {
            if (event.key === 'Escape') { event.preventDefault(); setSearchOpen(false); }
        });
        document.getElementById('resetSearch')?.addEventListener('click', () => setSearchOpen(true));
    }

    function filterFiles() {
        const query = search.value.trim().toLocaleLowerCase();
        let visible = 0;
        files.forEach(file => {
            const matches = file.dataset.name.toLocaleLowerCase().includes(query);
            file.hidden = !matches;
            if (matches) visible++;
        });
        document.getElementById('fileCount').textContent = visible;
        document.getElementById('searchEmpty').classList.toggle('d-none', visible !== 0);
    }

    if (library && search) {
        search.addEventListener('input', filterFiles);
        document.getElementById('resetSearch').addEventListener('click', () => {
            search.value = '';
            filterFiles();
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
    if (input && dropzone) {
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
    }
    const navigation = document.getElementById('libraryNavigation');
    document.querySelectorAll('[data-open-on-load]').forEach(modal => bootstrap.Modal.getOrCreateInstance(modal).show());
    navigation?.addEventListener('show.bs.offcanvas', () => {
        navigation.querySelector('.sidebar-brand').scrollIntoView({ block: 'start' });
    });
    document.querySelectorAll('[data-bs-target="#offcanvasUploadDoc"]').forEach(button => button.addEventListener('click', () => {
        if (window.innerWidth < 992) bootstrap.Offcanvas.getInstance(navigation)?.hide();
    }));
})();
