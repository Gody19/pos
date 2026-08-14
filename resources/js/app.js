import './bootstrap';
import Chart from 'chart.js/auto';
import './helpers';

window.Chart = Chart;

document.addEventListener('DOMContentLoaded', () => {
    const sidebarToggle = document.querySelector('[data-toggle-sidebar]');
    const sidebar = document.querySelector('#sidebar');
    const sidebarOverlay = document.querySelector('[data-sidebar-overlay]');

    const closeSidebar = () => {
        sidebar?.classList.add('-translate-x-full');
        sidebarOverlay?.classList.add('hidden');
    };

    sidebarToggle?.addEventListener('click', () => {
        sidebar?.classList.toggle('-translate-x-full');
        sidebarOverlay?.classList.toggle('hidden');
    });

    sidebarOverlay?.addEventListener('click', closeSidebar);

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeSidebar();
        }
    });

    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            const { confirm } = window;
            confirm(form.dataset.confirm || 'Are you sure?').then((ok) => {
                if (ok) {
                    form.submit();
                }
            });
        });
    });

    const userMenu = document.querySelector('[data-user-menu]');
    const userDropdown = document.querySelector('[data-user-dropdown]');
    userMenu?.addEventListener('click', (e) => {
        e.stopPropagation();
        userDropdown?.classList.toggle('hidden');
    });
    document.addEventListener('click', () => userDropdown?.classList.add('hidden'));
});