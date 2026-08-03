import './bootstrap';

function initializeSidebar() {
    const sidebar = document.querySelector('[data-sidebar]');
    const backdrop = document.querySelector('[data-sidebar-backdrop]');
    const openButtons = [...document.querySelectorAll('[data-sidebar-open]')];
    const closeButtons = [...document.querySelectorAll('[data-sidebar-close]')];

    if (!sidebar || !backdrop || openButtons.length === 0) {
        return;
    }

    const desktopMedia = window.matchMedia('(min-width: 1024px)');
    let isOpen = false;
    let previouslyFocused = null;

    const focusableSelector = [
        'a[href]',
        'button:not([disabled])',
        'input:not([disabled])',
        'select:not([disabled])',
        'textarea:not([disabled])',
        '[tabindex]:not([tabindex="-1"])',
    ].join(',');

    function synchronizeSidebar(open, restoreFocus = false) {
        const isMobile = !desktopMedia.matches;
        isOpen = isMobile && open;

        sidebar.classList.toggle('-translate-x-full', !isOpen);
        backdrop.classList.toggle('hidden', !isOpen);
        backdrop.setAttribute('aria-hidden', String(!isOpen));
        openButtons.forEach((button) => button.setAttribute('aria-expanded', String(isOpen)));
        document.body.classList.toggle('overflow-hidden', isOpen);

        sidebar.inert = isMobile && !isOpen;
        sidebar.setAttribute('aria-hidden', String(isMobile && !isOpen));

        if (isOpen) {
            const firstFocusable = sidebar.querySelector(focusableSelector);
            firstFocusable?.focus();
        } else if (restoreFocus && previouslyFocused instanceof HTMLElement) {
            previouslyFocused.focus();
        }
    }

    openButtons.forEach((button) => {
        button.addEventListener('click', () => {
            previouslyFocused = button;
            synchronizeSidebar(true);
        });
    });

    closeButtons.forEach((button) => {
        button.addEventListener('click', () => synchronizeSidebar(false, true));
    });

    backdrop.addEventListener('click', () => synchronizeSidebar(false, true));

    document.addEventListener('keydown', (event) => {
        if (!isOpen) {
            return;
        }

        if (event.key === 'Escape') {
            event.preventDefault();
            synchronizeSidebar(false, true);
            return;
        }

        if (event.key !== 'Tab') {
            return;
        }

        const focusableElements = [...sidebar.querySelectorAll(focusableSelector)]
            .filter((element) => element instanceof HTMLElement && !element.hidden);

        if (focusableElements.length === 0) {
            event.preventDefault();
            return;
        }

        const firstElement = focusableElements[0];
        const lastElement = focusableElements[focusableElements.length - 1];

        if (event.shiftKey && document.activeElement === firstElement) {
            event.preventDefault();
            lastElement.focus();
        } else if (!event.shiftKey && document.activeElement === lastElement) {
            event.preventDefault();
            firstElement.focus();
        }
    });

    desktopMedia.addEventListener('change', () => synchronizeSidebar(false));
    synchronizeSidebar(false);
}

function initializeDashboard() {
    const dashboard = document.querySelector('[data-dashboard-charts]');

    if (!dashboard) {
        return;
    }

    import('./dashboard')
        .then(({ initializeDashboardCharts }) => initializeDashboardCharts())
        .catch((error) => {
            console.error('Visualisasi dashboard gagal dimuat.', error);
            dashboard.setAttribute('aria-busy', 'false');
            dashboard.querySelector('[data-dashboard-loading]')?.classList.add('hidden');
            dashboard.querySelector('[data-dashboard-error]')?.classList.remove('hidden');
        });
}

function initializeApplication() {
    initializeSidebar();
    initializeDashboard();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeApplication, { once: true });
} else {
    initializeApplication();
}
