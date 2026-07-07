import './bootstrap';

// Bootstrap 5
import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

// Alpine.js
import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

// Bootstrap tooltips
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el) => {
        new bootstrap.Tooltip(el);
    });
});

// Guide d'aide pas-à-pas
import { startCurrentPageTour, hasTourForCurrentPage } from './help-tours';

document.addEventListener('DOMContentLoaded', () => {
    const helpBtn = document.getElementById('help-launch-btn');
    if (helpBtn) {
        if (!hasTourForCurrentPage()) {
            helpBtn.classList.add('disabled');
            helpBtn.title = "Aucun guide disponible sur cette page";
        }
        helpBtn.addEventListener('click', (e) => {
            e.preventDefault();
            if (!startCurrentPageTour()) {
                window.location.href = helpBtn.dataset.hubUrl;
            }
        });
    }

    // Lancement automatique depuis la page hub /aide (?tour=1)
    if (new URLSearchParams(window.location.search).get('tour') === '1') {
        startCurrentPageTour();
    }
});

