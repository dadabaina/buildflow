import { driver } from 'driver.js';
import 'driver.js/dist/driver.css';

/**
 * Registre central des guides pas-à-pas.
 * Clé = nom de route Laravel (Route::currentRouteName()) affiché dans data-page sur <body>.
 * Chaque guide est une liste d'étapes Driver.js (element + popover).
 * Les étapes dont l'élément n'existe pas sur la page sont ignorées automatiquement.
 */
const tours = {
    'projects.index': [
        { element: '#tour-projects-new', popover: { title: 'Créer un chantier', description: "Clique ici pour créer un nouveau chantier. Le plus souvent, un chantier est créé automatiquement quand tu acceptes un devis — ce bouton sert pour les cas particuliers." } },
        { element: '#tour-projects-table', popover: { title: 'Liste des chantiers', description: "Chaque ligne est un chantier. La colonne « Montant Marché » montre ce que le client doit payer au total, « Facturé » ce qui a déjà été facturé." } },
    ],
    'projects.show': [
        { element: '#tour-project-header', popover: { title: 'Fiche du chantier', description: "Toutes les informations de ce chantier sont ici : montants, dépenses, équipe, tâches, documents..." } },
        { element: '#tour-project-budget', popover: { title: 'Budget Global', description: "C'est ta prévision de coût interne pour ce chantier (à ne pas confondre avec le montant payé par le client). Modifiable via le bouton « Modifier »." } },
        { element: '#tour-project-expenses-stat', popover: { title: 'Total Dépenses', description: "La somme de toutes les dépenses enregistrées sur ce chantier (matériaux, main d'œuvre...)." } },
        { element: '#tour-project-margin', popover: { title: 'Marge prév.', description: "Le pourcentage du Budget Global qu'il te reste avant de le dépasser. Négatif = budget dépassé." } },
        { element: '#tour-project-market-ratio', popover: { title: 'Dépenses / Marché', description: "La part du prix payé par le client déjà consommée par les dépenses réelles. Au-delà de 100%, le chantier coûte plus cher que ce que le client paie." } },
        { element: '#tour-project-tabs', popover: { title: 'Onglets du chantier', description: "Utilise ces onglets pour naviguer : Équipe, Dépenses, Bons de Commande, Tâches, Stock, Documents, Devis, Historique..." } },
    ],

    'quotes.index': [
        { element: '#tour-quotes-new', popover: { title: 'Créer un devis', description: "Commence ici pour proposer un prix à un client. Une fois le devis accepté, le chantier est créé automatiquement." } },
        { element: '#tour-quotes-table', popover: { title: 'Liste des devis', description: "Le statut indique où en est chaque devis : brouillon, envoyé, accepté ou refusé." } },
    ],
    'quotes.show': [
        { element: '#tour-quote-items', popover: { title: 'Lignes du devis', description: "Chaque ligne représente une prestation ou un matériau avec son prix. Le total se calcule automatiquement." } },
        { element: '#tour-quote-send', popover: { title: 'Envoyer au client', description: "Envoie le devis par email au client pour qu'il puisse l'accepter ou le refuser en ligne." } },
        { element: '#tour-quote-accept', popover: { title: 'Accepter le devis', description: "Quand le client donne son accord, clique ici : le chantier est créé automatiquement et les tâches générées." } },
    ],

    'expenses.index': [
        { element: '#tour-expenses-new', popover: { title: 'Enregistrer une dépense', description: "Ajoute une dépense réelle liée à un chantier : achat de matériaux, paiement d'un ouvrier, etc." } },
        { element: '#tour-expenses-table', popover: { title: 'Liste des dépenses', description: "Le statut « Validée » signifie que la dépense compte officiellement dans le coût réel du chantier. « Saisie » = en attente de validation." } },
    ],
    'expenses.show': [
        { element: '#tour-expense-details', popover: { title: 'Détail de la dépense', description: "Vérifie le montant, le chantier concerné et le fournisseur avant de valider." } },
        { element: '#tour-expense-validate', popover: { title: 'Valider la dépense', description: "Tant qu'une dépense n'est pas validée, elle n'est pas comptée dans le coût réel du chantier." } },
    ],

    'purchase-orders.index': [
        { element: '#tour-po-new', popover: { title: 'Créer un Bon de Commande', description: "Un BC est une commande passée à un fournisseur — un engagement d'achat, avant même la livraison." } },
        { element: '#tour-po-table', popover: { title: 'Liste des BCs', description: "Suis le statut de chaque commande : brouillon, validé, partiellement livré ou livré." } },
    ],
    'purchase-orders.show': [
        { element: '#tour-po-status', popover: { title: 'Statut du BC', description: "Fais avancer le statut au fur et à mesure : validé quand tu confirmes la commande, livré quand la marchandise arrive." } },
        { element: '#tour-po-convert', popover: { title: 'Convertir en dépense', description: "Une fois le BC livré, clique ici pour transformer chaque ligne en dépense réelle du chantier. Sans cette étape, le coût n'apparaît nulle part !" } },
    ],

    'invoices.index': [
        { element: '#tour-invoices-new', popover: { title: 'Créer une facture', description: "Facture le client pour les travaux réalisés ou à réaliser." } },
        { element: '#tour-invoices-table', popover: { title: 'Liste des factures', description: "Le statut « Soldée » signifie que le client a payé l'intégralité de cette facture." } },
    ],

    'amendments.index': [
        { element: '#tour-amendments-new', popover: { title: 'Créer un avenant', description: "Un avenant modifie le montant du marché initial (travaux supplémentaires, changement de périmètre...)." } },
        { element: '#tour-amendments-table', popover: { title: 'Liste des avenants', description: "Seuls les avenants « acceptés » viennent s'ajouter au Montant Total Marché du chantier." } },
    ],

    'payments.index': [
        { element: '#tour-payments-new', popover: { title: 'Enregistrer un paiement', description: "Note ici chaque somme réellement reçue d'un client, liée à une facture." } },
        { element: '#tour-payments-table', popover: { title: 'Liste des paiements', description: "C'est ce total qui représente l'argent réellement encaissé, à ne pas confondre avec le montant facturé." } },
    ],

    'progress-billings.index': [
        { element: '#tour-pb-new', popover: { title: 'Créer une situation de travaux', description: "Une situation de travaux facture l'avancement réel du chantier, souvent utilisée pour les gros marchés facturés par étapes." } },
        { element: '#tour-pb-table', popover: { title: 'Liste des situations', description: "Chaque situation correspond à un pourcentage d'avancement facturé au client." } },
    ],

    'tasks.index': [
        { element: '#tour-tasks-new', popover: { title: 'Créer une tâche', description: "Découpe le chantier en tâches pour suivre précisément l'avancement des travaux." } },
        { element: '#tour-tasks-kanban-link', popover: { title: 'Vue Kanban', description: "Bascule vers une vue en colonnes pour glisser-déposer les tâches selon leur statut." } },
    ],
    'tasks.kanban': [
        { element: '#tour-kanban-board', popover: { title: 'Tableau Kanban', description: "Fais glisser une tâche d'une colonne à l'autre pour changer son statut : à faire, en cours, terminée." } },
    ],

    'employees.index': [
        { element: '#tour-employees-new', popover: { title: 'Ajouter un employé', description: "Enregistre un nouvel employé pour pouvoir l'affecter ensuite à des chantiers." } },
        { element: '#tour-employees-table', popover: { title: 'Liste des employés', description: "Retrouve ici tout ton personnel, avec leur métier et leur statut." } },
    ],

    'attendances.index': [
        { element: '#tour-attendance-new', popover: { title: 'Enregistrer un pointage', description: "Note les jours et heures travaillées par chaque employé sur un chantier." } },
        { element: '#tour-attendance-recap', popover: { title: 'Récapitulatif', description: "Consulte le total d'heures ou de jours travaillés par employé sur une période, utile pour la paie." } },
    ],

    'stock-movements.index': [
        { element: '#tour-stock-new', popover: { title: 'Nouveau mouvement de stock', description: "Enregistre une entrée (livraison) ou une sortie (utilisation sur chantier) de matériau." } },
        { element: '#tour-stock-table', popover: { title: 'Historique des mouvements', description: "Chaque ligne est un mouvement de stock, avec la quantité et le chantier concerné." } },
    ],
    'materials.index': [
        { element: '#tour-materials-new', popover: { title: 'Ajouter un matériau', description: "Référence ici les matériaux que tu utilises pour pouvoir suivre leur stock." } },
        { element: '#tour-materials-table', popover: { title: 'Liste des matériaux', description: "Le stock actuel de chaque matériau est calculé automatiquement à partir des mouvements enregistrés." } },
    ],
    'dosage.index': [
        { element: '#tour-dosage-new', popover: { title: 'Créer un modèle de dosage', description: "Un dosage définit la quantité exacte de matériaux/main d'œuvre nécessaire pour une prestation type (ex: 1m³ de béton) — utile pour estimer automatiquement le coût réel dans un devis." } },
    ],

    'equipments.index': [
        { element: '#tour-equipments-new', popover: { title: 'Ajouter un équipement', description: "Enregistre les machines et engins de chantier pour pouvoir les affecter à des projets." } },
        { element: '#tour-equipments-table', popover: { title: 'Liste des équipements', description: "Vérifie ici quel équipement est disponible ou déjà affecté à un chantier." } },
    ],
    'warehouses.index': [
        { element: '#tour-warehouses-new', popover: { title: 'Créer un entrepôt', description: "Un entrepôt est un lieu de stockage, qui peut être rattaché à un chantier en particulier." } },
    ],

    'clients.index': [
        { element: '#tour-clients-new', popover: { title: 'Ajouter un client', description: "Enregistre un client avant de lui créer un devis ou un chantier." } },
        { element: '#tour-clients-table', popover: { title: 'Liste des clients', description: "Retrouve ici tous tes clients et accède à leurs chantiers en cours." } },
    ],
    'suppliers.index': [
        { element: '#tour-suppliers-new', popover: { title: 'Ajouter un fournisseur', description: "Enregistre un fournisseur avant de lui passer un Bon de Commande." } },
        { element: '#tour-suppliers-table', popover: { title: 'Liste des fournisseurs', description: "Retrouve ici tous tes fournisseurs et leur historique de commandes." } },
    ],

    'reports.index': [
        { element: '#tour-reports-list', popover: { title: 'Rapports disponibles', description: "Chaque carte t'amène vers une analyse différente : rentabilité par chantier, flux financier, dosage réel..." } },
    ],
    'reports.projects': [
        { element: '#tour-reports-projects-table', popover: { title: 'Suivi des chantiers', description: "Compare en un coup d'œil le Facturé, les Dépenses et la Marge de chaque chantier." } },
    ],

    'users.index': [
        { element: '#tour-users-new', popover: { title: 'Ajouter un utilisateur', description: "Crée un compte pour une nouvelle personne de ton équipe qui doit utiliser le logiciel." } },
        { element: '#tour-users-table', popover: { title: 'Liste des utilisateurs', description: "Gère ici qui a accès au logiciel et avec quel rôle (droits)." } },
    ],
    'roles.index': [
        { element: '#tour-roles-new', popover: { title: 'Créer un rôle', description: "Un rôle regroupe des permissions (ex: « Chef de chantier », « Comptable ») que tu attribues ensuite à un utilisateur." } },
    ],

    'settings.index': [
        { element: '#tour-settings-list', popover: { title: 'Paramètres', description: "Configure ici les listes utilisées partout dans l'application : catégories de dépenses, métiers, régions, unités..." } },
    ],
};

export function currentPageKey() {
    return document.body?.dataset?.page || null;
}

export function hasTourForCurrentPage() {
    const key = currentPageKey();
    return !!(key && tours[key] && tours[key].some(step => document.querySelector(step.element)));
}

export function startTour(key) {
    const steps = tours[key];
    if (!steps) return false;

    const availableSteps = steps.filter(step => document.querySelector(step.element));
    if (availableSteps.length === 0) return false;

    const d = driver({
        showProgress: true,
        nextBtnText: 'Suivant',
        prevBtnText: 'Précédent',
        doneBtnText: 'Terminé',
        steps: availableSteps,
    });
    d.drive();
    return true;
}

export function startCurrentPageTour() {
    const key = currentPageKey();
    if (!key) return false;
    return startTour(key);
}

export function availableTourKeys() {
    return Object.keys(tours);
}
