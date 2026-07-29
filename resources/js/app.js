import './bootstrap';
import './pwa';

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
        // L'icône reste toujours cliquable : elle mène au centre d'aide (/aide) par défaut.
        // Si un guide interactif existe pour la page en cours, il se lance à la place.
        if (hasTourForCurrentPage()) {
            helpBtn.title = "Lancer le guide de cette page";
            helpBtn.addEventListener('click', (e) => {
                e.preventDefault();
                startCurrentPageTour();
            });
        } else {
            helpBtn.title = "Centre d'aide";
        }
    }

    // Lancement automatique depuis la page hub /aide (?tour=1)
    if (new URLSearchParams(window.location.search).get('tour') === '1') {
        startCurrentPageTour();
    }
});

