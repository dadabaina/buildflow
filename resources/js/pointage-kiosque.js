// Kiosque pointage — file d'attente hors-ligne (IndexedDB).
//
// Le pointage (Entrée/Sortie) doit fonctionner même sans réseau sur le chantier :
// la photo + l'horodatage (capturé ici, côté appareil, au moment du clic) sont
// mis en file localement si l'envoi échoue, puis synchronisés automatiquement
// dès que la connexion revient. Le service worker (public/sw.js) ne gère pas
// cette file : elle est portée entièrement par cette page pour rester fiable
// avec ou sans support de Background Sync selon le navigateur.

const DB_NAME = 'buildflow-pointage';
const STORE = 'pending';

function openDb() {
    return new Promise((resolve, reject) => {
        const req = indexedDB.open(DB_NAME, 1);
        req.onupgradeneeded = () => {
            req.result.createObjectStore(STORE, { keyPath: 'id', autoIncrement: true });
        };
        req.onsuccess = () => resolve(req.result);
        req.onerror = () => reject(req.error);
    });
}

async function withStore(mode, fn) {
    const db = await openDb();
    return new Promise((resolve, reject) => {
        const tx = db.transaction(STORE, mode);
        const result = fn(tx.objectStore(STORE));
        tx.oncomplete = () => resolve(result?.result ?? result);
        tx.onerror = () => reject(tx.error);
    });
}

function queueSubmission(item) {
    return withStore('readwrite', (store) => store.add(item));
}

async function getQueue() {
    const db = await openDb();
    return new Promise((resolve, reject) => {
        const req = db.transaction(STORE, 'readonly').objectStore(STORE).getAll();
        req.onsuccess = () => resolve(req.result);
        req.onerror = () => reject(req.error);
    });
}

function removeFromQueue(id) {
    return withStore('readwrite', (store) => store.delete(id));
}

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]').content;
}

async function submitPointage(url, photo, capturedAt) {
    const fd = new FormData();
    fd.append('_token', csrfToken());
    fd.append('photo', photo, 'photo.jpg');
    fd.append('captured_at', capturedAt);
    return fetch(url, { method: 'POST', body: fd, credentials: 'same-origin' });
}

function setStatus(employeeId, action, state) {
    const row = document.querySelector(`[data-row="${employeeId}"]`);
    if (!row) return;
    const badge = row.querySelector('.pointage-status');
    const button = row.querySelector('.pointage-form button');
    if (state === 'queued') {
        if (badge) {
            badge.textContent = '⏳ En attente de synchronisation';
            badge.className = 'badge bg-label-warning pointage-status';
        }
        if (button) {
            button.disabled = true;
            button.className = 'btn btn-outline-secondary w-100';
            button.innerHTML = '<i class="bx bx-time-five me-1"></i>En attente de synchro';
        }
    } else if (state === 'error') {
        if (badge) {
            badge.textContent = '⚠ Échec de synchronisation';
            badge.className = 'badge bg-label-danger pointage-status';
        }
        if (button) {
            button.disabled = false;
            button.className = 'btn btn-outline-danger w-100';
            button.innerHTML = '<i class="bx bx-error me-1"></i>Échec — réessayer';
        }
    }
}

async function trySyncQueue() {
    let items;
    try {
        items = await getQueue();
    } catch {
        return;
    }
    // Affiche l'état "en attente" dès le chargement pour toute soumission déjà
    // en file (ex. page rechargée hors-ligne) : sans ça, la ligne retombe à
    // l'état par défaut et le bouton "Entrée" reste vert comme si de rien n'était.
    items.forEach((item) => setStatus(item.employeeId, item.action, 'queued'));
    for (const item of items) {
        try {
            const resp = await submitPointage(item.url, item.photo, item.capturedAt);
            if (resp.ok || resp.redirected || resp.status === 302) {
                await removeFromQueue(item.id);
            } else {
                setStatus(item.employeeId, item.action, 'error');
            }
        } catch {
            // Toujours hors-ligne : on s'arrête, on réessaiera au prochain événement "online".
            return;
        }
    }
    if (items.length > 0) {
        // Au moins une synchro a réussi : la page est obsolète (statuts changés côté serveur).
        window.location.reload();
    }
}

function setButtonSending(form) {
    const button = form.querySelector('button');
    if (!button) return;
    button.disabled = true;
    button.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span>Envoi en cours…';
}

// Compresse la photo côté téléphone avant l'envoi : les photos brutes d'un
// smartphone (plusieurs Mo) rendent l'upload très lent sur un réseau de
// chantier. On la redimensionne et on la réencode en JPEG ici — le serveur
// la retraite de toute façon (scaleDown + WebP), donc seule la taille du
// transfert réseau compte ici.
function compressPhoto(file, maxDim = 1000, quality = 0.7) {
    return new Promise((resolve) => {
        const img = new window.Image();
        const url = URL.createObjectURL(file);
        img.onload = () => {
            URL.revokeObjectURL(url);
            let { width, height } = img;
            if (width > maxDim || height > maxDim) {
                if (width > height) {
                    height = Math.round(height * (maxDim / width));
                    width = maxDim;
                } else {
                    width = Math.round(width * (maxDim / height));
                    height = maxDim;
                }
            }
            const canvas = document.createElement('canvas');
            canvas.width = width;
            canvas.height = height;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0, width, height);
            canvas.toBlob((blob) => resolve(blob || file), 'image/jpeg', quality);
        };
        img.onerror = () => { URL.revokeObjectURL(url); resolve(file); };
        img.src = url;
    });
}

function updateOfflineBanner() {
    const banner = document.getElementById('pointage-offline-banner');
    if (!banner) return;
    banner.classList.toggle('d-none', navigator.onLine);
}

function initKiosque() {
    const forms = document.querySelectorAll('.pointage-form');
    if (forms.length === 0) return;

    updateOfflineBanner();
    window.addEventListener('online', () => { updateOfflineBanner(); trySyncQueue(); });
    window.addEventListener('offline', updateOfflineBanner);
    trySyncQueue();

    forms.forEach((form) => {
        const fileInput = form.querySelector('input[type="file"]');
        fileInput.addEventListener('change', async (e) => {
            const rawPhoto = e.target.files[0];
            if (!rawPhoto) return;

            // Retour visuel immédiat : l'envoi + la compression peuvent prendre
            // quelques secondes sur un réseau de chantier, le bouton doit le
            // montrer tout de suite plutôt que de sembler figé.
            setButtonSending(form);

            const capturedAt = new Date().toISOString();
            const employeeId = form.dataset.employeeId;
            const action = form.dataset.action;
            const photo = await compressPhoto(rawPhoto);

            if (navigator.onLine) {
                try {
                    const resp = await submitPointage(form.action, photo, capturedAt);
                    if (resp.redirected) {
                        window.location.href = resp.url;
                    } else {
                        window.location.reload();
                    }
                    return;
                } catch {
                    // Le navigateur se croyait en ligne mais la requête a échoué : on met en file.
                }
            }

            await queueSubmission({ url: form.action, photo, capturedAt, employeeId, action });
            setStatus(employeeId, action, 'queued');
        });
    });
}

document.addEventListener('DOMContentLoaded', initKiosque);
