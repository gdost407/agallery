(() => {
    'use strict';

    const key = 'agallery-theme';
    const root = document.documentElement;
    const media = window.matchMedia('(prefers-color-scheme: dark)');
    const validPreference = value => ['light', 'dark', 'system'].includes(value) ? value : 'system';
    let preference = 'system';
    try {
        preference = validPreference(window.localStorage.getItem(key));
    } catch {}

    function apply() {
        const theme = preference === 'system' ? (media.matches ? 'dark' : 'light') : preference;
        root.dataset.theme = theme;
        root.dataset.bsTheme = theme;
        root.classList.toggle('dark', theme === 'dark');
        root.style.colorScheme = theme;
        const meta = document.querySelector('meta[name="theme-color"]');
        if (meta) meta.content = theme === 'dark' ? '#111827' : '#3267e3';
        document.querySelectorAll('[data-theme-toggle]').forEach(button => {
            const label = theme === 'dark' ? 'Light mode' : 'Dark mode';
            button.setAttribute('aria-label', `Switch to ${label.toLowerCase()}`);
            button.querySelector('[data-theme-label]').textContent = label;
            button.querySelector('[data-theme-icon]').textContent = theme === 'dark' ? '☀' : '☾';
        });
        document.querySelectorAll('[data-theme-preference]').forEach(select => { select.value = preference; });
    }

    function choose(value) {
        preference = validPreference(value);
        try { window.localStorage.setItem(key, preference); } catch {}
        apply();
    }

    apply();
    document.addEventListener('DOMContentLoaded', () => {
        apply();
        document.querySelectorAll('[data-theme-toggle]').forEach(button => {
            button.addEventListener('click', () => choose(root.dataset.theme === 'dark' ? 'light' : 'dark'));
        });
        document.querySelectorAll('[data-theme-preference]').forEach(select => {
            select.addEventListener('change', () => choose(select.value));
        });
    });
    media.addEventListener('change', () => { if (preference === 'system') apply(); });
    window.addEventListener('storage', event => {
        if (event.key === key || event.key === null) {
            try { preference = validPreference(window.localStorage.getItem(key)); } catch { preference = 'system'; }
            apply();
        }
    });
})();
