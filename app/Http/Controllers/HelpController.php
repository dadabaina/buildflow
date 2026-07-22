<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class HelpController extends Controller
{
    public function index()
    {
        $company = Auth::user()->company;
        $guides = $this->guides();

        $sections = [
            [
                'title' => 'Chantiers & Devis',
                'items' => [
                    ['label' => 'Chantiers', 'description' => "Suivre l'avancement, le budget et les dépenses d'un chantier.", 'route' => 'projects.index', 'tour' => 'projects.index', 'guide' => $guides['projects'], 'permission' => 'projects.view', 'phase' => 'daily'],
                    ['label' => 'Devis', 'description' => "Proposer un prix à un client. Une fois accepté, le chantier est créé automatiquement.", 'route' => 'quotes.index', 'tour' => 'quotes.index', 'guide' => $guides['quotes'], 'permission' => 'quotes.view', 'phase' => 'daily'],
                    ['label' => 'Avenants', 'description' => "Modifier le montant du marché initial (travaux supplémentaires...).", 'route' => 'amendments.index', 'tour' => 'amendments.index', 'permission' => 'amendments.view', 'phase' => 'daily'],
                ],
            ],
            [
                'title' => 'Argent : dépenses, commandes, factures',
                'items' => [
                    ['label' => 'Dépenses', 'description' => "Enregistrer les coûts réels d'un chantier (matériaux, main d'œuvre...).", 'route' => 'expenses.index', 'tour' => 'expenses.index', 'guide' => $guides['expenses'], 'permission' => 'expenses.view', 'phase' => 'daily'],
                    ['label' => 'Bons de Commande', 'description' => "Commander auprès d'un fournisseur, puis convertir en dépense une fois livré.", 'route' => 'purchase-orders.index', 'tour' => 'purchase-orders.index', 'permission' => 'purchase_orders.view', 'phase' => 'daily'],
                    ['label' => 'Factures', 'description' => "Facturer le client pour les travaux réalisés.", 'route' => 'invoices.index', 'tour' => 'invoices.index', 'guide' => $guides['invoices'], 'permission' => 'invoices.view', 'phase' => 'daily'],
                    ['label' => 'Situations de travaux', 'description' => "Facturer l'avancement réel du chantier, étape par étape.", 'route' => 'progress-billings.index', 'tour' => 'progress-billings.index', 'permission' => 'progress_billings.view', 'phase' => 'daily'],
                    ['label' => 'Paiements', 'description' => "Noter l'argent réellement reçu d'un client.", 'route' => 'payments.index', 'tour' => 'payments.index', 'permission' => 'payments.view', 'phase' => 'daily'],
                ],
            ],
            [
                'title' => 'Terrain : tâches, équipe, pointage',
                'items' => [
                    ['label' => 'Tâches', 'description' => "Découper le chantier en tâches pour suivre l'avancement.", 'route' => 'tasks.index', 'tour' => 'tasks.index', 'guide' => $guides['tasks'], 'permission' => 'tasks.view', 'phase' => 'daily'],
                    ['label' => 'Employés', 'description' => "Gérer le personnel disponible pour les chantiers.", 'route' => 'employees.index', 'tour' => 'employees.index', 'permission' => 'employees.view', 'phase' => 'setup'],
                    ['label' => 'Pointage', 'description' => "Enregistrer les jours/heures travaillées par employé.", 'route' => 'attendances.index', 'tour' => 'attendances.index', 'permission' => 'attendances.view', 'phase' => 'daily'],
                ],
            ],
            [
                'title' => 'Stock & matériel',
                'items' => [
                    ['label' => 'Mouvements de stock', 'description' => "Enregistrer les entrées et sorties de matériaux.", 'route' => 'stock-movements.index', 'tour' => 'stock-movements.index', 'permission' => 'stock.view', 'phase' => 'daily'],
                    ['label' => 'Matériaux', 'description' => "Référencer les matériaux et suivre leur stock.", 'route' => 'materials.index', 'tour' => 'materials.index', 'permission' => 'materials.view', 'phase' => 'setup'],
                    ['label' => 'Modèles de dosage', 'description' => "Définir la quantité de matériaux/main d'œuvre nécessaire pour une prestation type.", 'route' => 'dosage.index', 'tour' => 'dosage.index', 'permission' => 'dosage.view', 'phase' => 'setup'],
                    ['label' => 'Équipements', 'description' => "Gérer les machines et engins de chantier.", 'route' => 'equipments.index', 'tour' => 'equipments.index', 'permission' => 'equipments.view', 'phase' => 'setup'],
                    ['label' => 'Entrepôts', 'description' => "Gérer les lieux de stockage.", 'route' => 'warehouses.index', 'tour' => 'warehouses.index', 'permission' => 'warehouses.view', 'phase' => 'setup'],
                ],
            ],
            [
                'title' => 'Clients & fournisseurs',
                'items' => [
                    ['label' => 'Clients', 'description' => "Gérer les clients pour qui tu réalises des chantiers.", 'route' => 'clients.index', 'tour' => 'clients.index', 'permission' => 'clients.view', 'phase' => 'setup'],
                    ['label' => 'Fournisseurs', 'description' => "Gérer les fournisseurs à qui tu passes des commandes.", 'route' => 'suppliers.index', 'tour' => 'suppliers.index', 'permission' => 'suppliers.view', 'phase' => 'setup'],
                ],
            ],
            [
                'title' => 'Rapports',
                'items' => [
                    ['label' => 'Tous les rapports', 'description' => "Accéder aux différentes analyses : rentabilité, flux financier, dosage réel...", 'route' => 'reports.index', 'tour' => 'reports.index', 'permission' => 'reports.view', 'phase' => 'daily'],
                    ['label' => 'Suivi des chantiers', 'description' => "Comparer Facturé, Dépenses et Marge de chaque chantier.", 'route' => 'reports.projects', 'tour' => 'reports.projects', 'permission' => 'reports.view', 'phase' => 'daily'],
                ],
            ],
            [
                'title' => 'Administration',
                'items' => [
                    ['label' => 'Utilisateurs', 'description' => "Créer des comptes pour les membres de ton équipe.", 'route' => 'users.index', 'tour' => 'users.index', 'permission' => 'users.view', 'phase' => 'setup'],
                    ['label' => 'Rôles', 'description' => "Définir les droits d'accès par type de poste.", 'route' => 'roles.index', 'tour' => 'roles.index', 'permission' => 'roles.view', 'phase' => 'setup'],
                    ['label' => 'Paramètres', 'description' => "Configurer les listes utilisées dans toute l'application.", 'route' => 'settings.index', 'tour' => 'settings.index', 'permission' => 'settings.view', 'phase' => 'setup'],
                ],
            ],
        ];

        // Ne montrer que les modules auxquels l'utilisateur connecté a effectivement accès.
        $sections = collect($sections)
            ->map(function ($section) {
                $section['items'] = array_values(array_filter(
                    $section['items'],
                    fn ($item) => Auth::user()->can($item['permission'])
                ));
                return $section;
            })
            ->filter(fn ($section) => !empty($section['items']))
            ->values()
            ->all();

        $setupSections = $this->filterByPhase($sections, 'setup');
        $dailySections = $this->filterByPhase($sections, 'daily');

        $onboarding = $this->onboardingSteps($company);
        $onboardingDone = collect($onboarding)->where('done', true)->count();

        $cycle = $this->cycleSteps();
        $faq = $this->faq();

        return view('help.index', compact(
            'sections', 'setupSections', 'dailySections',
            'onboarding', 'onboardingDone', 'cycle', 'faq'
        ));
    }

    private function filterByPhase(array $sections, string $phase): array
    {
        return collect($sections)
            ->map(function ($section) use ($phase) {
                $section['items'] = array_values(array_filter(
                    $section['items'],
                    fn ($item) => ($item['phase'] ?? 'daily') === $phase
                ));
                return $section;
            })
            ->filter(fn ($section) => !empty($section['items']))
            ->values()
            ->all();
    }

    /**
     * Parcours de démarrage : les toutes premières actions à faire dans l'application,
     * dans l'ordre. L'état "fait" est déduit des vraies données de la société — pas
     * une simple checklist statique.
     */
    private function onboardingSteps($company): array
    {
        $hasReferentials = $company->regions()->exists()
            || $company->jobTypes()->exists()
            || $company->expenseCategories()->exists();
        $hasClient = $company->clients()->exists();
        $hasQuote = $company->quotes()->exists();
        $hasAcceptedQuote = $company->quotes()->where('status', 'accepte')->exists();
        $hasProject = $company->projects()->exists();
        $hasExpense = $company->expenses()->exists();
        $hasInvoice = $company->invoices()->exists();

        return [
            [
                'title' => 'Configurer votre société',
                'text' => "Régions, postes, unités, catégories de dépenses, taux de TVA... les listes que vous retrouverez partout ailleurs dans l'application.",
                'route' => 'settings.index',
                'cta' => 'Ouvrir les paramètres',
                'done' => $hasReferentials,
            ],
            [
                'title' => 'Ajouter votre premier client',
                'text' => "Un client est nécessaire avant de pouvoir créer un devis ou un chantier.",
                'route' => 'clients.index',
                'cta' => 'Ajouter un client',
                'done' => $hasClient,
            ],
            [
                'title' => 'Créer votre premier devis',
                'text' => "Le devis est le point de départ de tout chantier dans BuildFlow.",
                'route' => 'quotes.index',
                'cta' => 'Créer un devis',
                'done' => $hasQuote,
            ],
            [
                'title' => 'Faire accepter le devis',
                'text' => "Envoyez-le au client (validation en ligne) ou acceptez-le directement depuis l'interface.",
                'route' => 'quotes.index',
                'cta' => 'Voir mes devis',
                'done' => $hasAcceptedQuote,
            ],
            [
                'title' => 'Voir le chantier créé automatiquement',
                'text' => "Dès l'acceptation, le chantier apparaît ici, avec une tâche déjà générée pour chaque ligne du devis.",
                'route' => 'projects.index',
                'cta' => 'Voir mes chantiers',
                'done' => $hasProject,
            ],
            [
                'title' => 'Enregistrer une dépense',
                'text' => "Notez un premier coût réel sur le chantier pour commencer à suivre le budget.",
                'route' => 'expenses.index',
                'cta' => 'Enregistrer une dépense',
                'done' => $hasExpense,
            ],
            [
                'title' => 'Facturer et encaisser',
                'text' => "Convertissez le devis accepté en facture, puis enregistrez le paiement du client.",
                'route' => 'invoices.index',
                'cta' => 'Voir mes factures',
                'done' => $hasInvoice,
            ],
        ];
    }

    /**
     * Étapes du cycle central de l'application, pour le schéma visuel en haut de /aide.
     */
    private function cycleSteps(): array
    {
        return [
            ['icon' => 'bi-file-earmark-text', 'label' => 'Devis', 'text' => "Vous proposez un prix au client."],
            ['icon' => 'bi-building', 'label' => 'Chantier', 'text' => "Créé automatiquement à l'acceptation."],
            ['icon' => 'bi-check2-square', 'label' => 'Tâches', 'text' => "Une par ligne du devis, générées automatiquement."],
            ['icon' => 'bi-receipt', 'label' => 'Dépenses', 'text' => "Le coût réel du chantier, une fois validé."],
            ['icon' => 'bi-file-earmark-ruled', 'label' => 'Facture', 'text' => "Générée depuis le devis ou une situation."],
            ['icon' => 'bi-cash-coin', 'label' => 'Paiement', 'text' => "L'argent réellement encaissé du client."],
        ];
    }

    /**
     * Questions fréquentes — les distinctions qui reviennent le plus souvent en pratique.
     */
    private function faq(): array
    {
        return [
            [
                'q' => "Quelle est la différence entre le « Budget initial » et le « Montant du marché » d'un chantier ?",
                'a' => "Le Budget initial est votre estimation interne du coût de revient (ce que le chantier va vous coûter). Le Montant du marché est ce que le client doit payer au total. Les deux sont indépendants : un chantier rentable a un montant du marché supérieur au budget initial.",
            ],
            [
                'q' => "Quelle est la différence entre une Facture standard et une Situation de travaux ?",
                'a' => "Une facture standard facture généralement la totalité (ou une partie fixe) d'un devis en une fois. Une situation de travaux facture l'avancement réel, étape par étape (ex : 30% à la fondation, 30% à l'élévation...) — plus adaptée aux gros chantiers facturés progressivement.",
            ],
            [
                'q' => "Pourquoi une dépense que je viens de saisir n'apparaît pas dans le coût réel du chantier ?",
                'a' => "Une dépense fraîchement créée est au statut « Saisie » : elle n'est prise en compte qu'une fois « Validée » par un responsable. C'est volontaire, pour éviter qu'une erreur de saisie fausse les chiffres avant vérification.",
            ],
            [
                'q' => "Dois-je créer le chantier moi-même après avoir accepté un devis ?",
                'a' => "Non. Dès qu'un devis est accepté, le chantier est créé automatiquement (montant du marché = total TTC du devis), et une tâche est générée pour chaque ligne. La création manuelle d'un chantier ne sert que pour les cas particuliers (ex : prospection sans devis formel).",
            ],
            [
                'q' => "J'ai supprimé une tâche : ses dépenses ont-elles disparu ?",
                'a' => "Non, les dépenses restent sur le chantier — elles sont simplement détachées de la tâche supprimée, toujours visibles et comptées dans le coût réel du chantier.",
            ],
            [
                'q' => "Pourquoi je ne peux pas facturer plus que le montant du marché du chantier ?",
                'a' => "C'est une garde volontaire : le total facturé (factures + situations de travaux confondues) ne peut pas dépasser le montant du marché, pour éviter une double facturation involontaire.",
            ],
            [
                'q' => "Un avenant accepté modifie-t-il le montant du marché du chantier ?",
                'a' => "Oui : comme un devis, le montant de l'avenant (positif ou négatif s'il s'agit d'une déduction) s'ajoute automatiquement au montant du marché.",
            ],
            [
                'q' => "Comment enregistrer plusieurs dépenses d'un coup pour une tâche (ex : tout le sous-détail d'un béton) ?",
                'a' => "Utilisez un Modèle de dépense (Stock & matériel) : depuis la tâche, cliquez sur « Appliquer un modèle », indiquez la quantité réelle réalisée, et une dépense est générée automatiquement pour chaque ligne du modèle (matériaux, main d'œuvre, matériel, sous-traitance).",
            ],
            [
                'q' => "Pourquoi certains modules ne sont pas dans cette liste ?",
                'a' => "Le centre d'aide n'affiche que les modules auxquels votre rôle donne accès. Si un module vous manque, demandez à un administrateur de vérifier vos permissions dans Paramètres → Rôles.",
            ],
        ];
    }

    /**
     * Guides détaillés (étapes + explication de chaque champ) pour le cycle central
     * de l'application. Le contenu des champs reflète exactement les règles de
     * validation des contrôleurs correspondants — à tenir à jour si elles évoluent.
     */
    private function guides(): array
    {
        return [
            'quotes' => [
                'intro' => "Le devis est le point de départ du cycle : une fois accepté, il crée automatiquement le chantier et génère une tâche par ligne.",
                'steps' => [
                    ['title' => '1. Créer le devis', 'text' => "Cliquez sur « Nouveau devis », renseignez au minimum le client et le titre, puis validez : le devis est créé au statut Brouillon."],
                    ['title' => '2. Ajouter les lignes', 'text' => "Depuis la fiche du devis, ajoutez une ligne par prestation ou matériau (description, quantité, prix unitaire). Le total se recalcule automatiquement à chaque ajout."],
                    ['title' => '3. Envoyer ou accepter', 'text' => "Envoyez le devis au client par email (il pourra l'accepter ou le refuser en ligne), ou acceptez-le directement depuis l'interface si l'accord est déjà obtenu autrement."],
                    ['title' => '4. Le chantier se crée automatiquement', 'text' => "Dès l'acceptation, un chantier est créé (montant du marché = total TTC du devis) et une tâche est générée pour chaque ligne. Si le devis était déjà lié à un chantier existant, son montant s'ajoute au marché de ce chantier."],
                ],
                'fields' => [
                    ['label' => 'Client', 'required' => true, 'text' => "Le client pour qui ce devis est établi."],
                    ['label' => 'Chantier', 'required' => false, 'text' => "À renseigner uniquement si ce devis concerne un chantier déjà existant (ex. travaux supplémentaires) : son montant s'ajoutera au marché de ce chantier au lieu d'en créer un nouveau."],
                    ['label' => 'Titre', 'required' => true, 'text' => "Nom du devis, affiché sur le PDF et dans les listes."],
                    ['label' => 'Date du devis', 'required' => true, 'text' => "Date d'émission."],
                    ['label' => 'Valide jusqu\'au', 'required' => false, 'text' => "Date limite de validité de l'offre, affichée sur le PDF envoyé au client."],
                    ['label' => 'Taux de TVA', 'required' => false, 'text' => "En %. Reprend le taux par défaut de la société si laissé vide (20% typiquement)."],
                    ['label' => 'Remise globale + Type', 'required' => false, 'text' => "Remise appliquée sur l'ensemble du devis, en montant fixe ou en pourcentage."],
                    ['label' => 'Notes / Conditions', 'required' => false, 'text' => "Texte libre affiché sur le devis (conditions de paiement, remarques...)."],
                ],
            ],
            'projects' => [
                'intro' => "La majorité des chantiers naissent automatiquement de l'acceptation d'un devis — la création manuelle sert surtout aux cas particuliers (prospection sans devis formel, etc.).",
                'steps' => [
                    ['title' => '1. Création automatique (cas normal)', 'text' => "Rien à faire : dès qu'un devis est accepté, le chantier est créé avec le montant du marché déjà rempli."],
                    ['title' => '2. Création manuelle (cas particulier)', 'text' => "Cliquez sur « Nouveau chantier », renseignez au minimum le nom et le client."],
                    ['title' => '3. Compléter les informations', 'text' => "Ajoutez budget, dates, région si besoin — modifiable à tout moment depuis « Modifier »."],
                    ['title' => '4. Piloter le chantier au quotidien', 'text' => "Utilisez les onglets de la fiche chantier : Équipe, Tâches, Dépenses, Bons de Commande, Stock, Documents, Historique..."],
                ],
                'fields' => [
                    ['label' => 'Nom', 'required' => true, 'text' => "Nom du chantier."],
                    ['label' => 'Statut', 'required' => false, 'text' => "Prospection, Devis en cours, En cours, Terminé, Clôturé... Les changements de statut suivent un ordre logique : impossible par exemple de passer directement de « Prospection » à « Clôturé »."],
                    ['label' => 'Client', 'required' => true, 'text' => "Le client concerné par ce chantier."],
                    ['label' => 'Région', 'required' => false, 'text' => "Utilisée pour appliquer les prix de matériaux et grilles salariales spécifiques à cette région, si configurés."],
                    ['label' => 'Description', 'required' => false, 'text' => "Texte libre décrivant le chantier."],
                    ['label' => 'Budget initial', 'required' => false, 'text' => "Votre estimation interne du coût de revient — à ne pas confondre avec le montant du marché (ce que paie le client)."],
                    ['label' => 'Montant du marché', 'required' => false, 'text' => "Montant total TTC dû par le client. Rempli automatiquement si le chantier vient d'un devis accepté."],
                    ['label' => 'Date de début / Date de fin prévue / Date de clôture', 'required' => false, 'text' => "Jalons du chantier, utilisés notamment pour détecter les retards."],
                    ['label' => 'Notes internes', 'required' => false, 'text' => "Texte libre, visible uniquement en interne."],
                ],
            ],
            'tasks' => [
                'intro' => "Les tâches découpent un chantier en étapes suivables. La plupart sont générées automatiquement depuis les lignes d'un devis accepté.",
                'steps' => [
                    ['title' => '1. Génération automatique (cas normal)', 'text' => "Quand un devis est accepté, une tâche est créée pour chaque ligne — rien à faire manuellement."],
                    ['title' => '2. Création manuelle', 'text' => "Cliquez sur « Nouvelle tâche », choisissez le chantier et renseignez un titre."],
                    ['title' => '3. Suivre l\'avancement', 'text' => "Faites évoluer le statut de la tâche, ou utilisez la vue Kanban pour glisser-déposer les tâches entre colonnes (à faire / en cours / terminée)."],
                    ['title' => '4. Détailler avec une checklist', 'text' => "Ajoutez des sous-étapes à cocher : le pourcentage d'avancement de la tâche se met à jour automatiquement selon les cases cochées."],
                    ['title' => '5. Rattacher les dépenses réelles', 'text' => "Depuis la tâche, ajoutez les dépenses au fur et à mesure (ou appliquez un modèle de dépense pour en générer plusieurs d'un coup) afin de comparer le prévu (déboursé du devis) au réel."],
                ],
                'fields' => [
                    ['label' => 'Chantier', 'required' => true, 'text' => "Le chantier auquel cette tâche appartient."],
                    ['label' => 'Titre', 'required' => true, 'text' => "Nom de la tâche."],
                    ['label' => 'Description', 'required' => false, 'text' => "Détail optionnel de ce qu'il faut faire."],
                    ['label' => 'Statut', 'required' => true, 'text' => "À faire, En cours, En pause, Terminée ou Annulée."],
                    ['label' => 'Priorité', 'required' => true, 'text' => "Basse, Normale, Haute ou Urgente — sert à trier/filtrer les tâches."],
                    ['label' => 'Poids', 'required' => true, 'text' => "Nombre entier ≥ 1. Pondère cette tâche dans le calcul de l'avancement global du chantier : une tâche à poids 3 compte 3 fois plus qu'une tâche à poids 1."],
                    ['label' => 'Échéance', 'required' => false, 'text' => "Date limite. Une tâche non terminée après cette date est signalée « en retard » et peut déclencher une notification."],
                    ['label' => 'Équipe assignée', 'required' => false, 'text' => "Employé(s) responsables de cette tâche — ils reçoivent une notification lors de l'assignation."],
                    ['label' => 'Checklist', 'required' => false, 'text' => "Liste de sous-étapes à cocher, chacune avec un libellé."],
                ],
            ],
            'expenses' => [
                'intro' => "Une dépense enregistre un coût réel sur un chantier. Elle ne compte dans le coût réel qu'une fois validée par un responsable.",
                'steps' => [
                    ['title' => '1. Créer la dépense', 'text' => "Cliquez sur « Nouvelle dépense », choisissez le chantier (obligatoire) et, si possible, la tâche concernée pour affiner le suivi prévu/réel."],
                    ['title' => '2. Décrire et chiffrer', 'text' => "Renseignez la description, la date, le prix unitaire et la quantité — le montant total se calcule automatiquement."],
                    ['title' => '3. Validation', 'text' => "La dépense est créée au statut « Saisie » : elle n'est pas encore comptée dans le coût réel. Un responsable doit la « Valider » (ou la « Rejeter » avec un motif obligatoire) pour qu'elle compte officiellement."],
                    ['title' => 'Alternative : générer plusieurs dépenses d\'un coup', 'text' => "Depuis une tâche, utilisez « Appliquer un modèle » et indiquez la quantité réelle réalisée : une dépense est créée automatiquement pour chaque ligne du modèle (matériaux, main d'œuvre...)."],
                ],
                'fields' => [
                    ['label' => 'Chantier', 'required' => true, 'text' => "Le chantier concerné par cette dépense."],
                    ['label' => 'Tâche', 'required' => false, 'text' => "La tâche précise concernée, si applicable — permet de comparer prévu et réel sur cette tâche."],
                    ['label' => 'Description', 'required' => true, 'text' => "Nature de la dépense (ex : « Ciment CPA 42.5, 50 sacs »)."],
                    ['label' => 'Catégorie', 'required' => false, 'text' => "Classement (matériaux, main d'œuvre, location...), utile pour les rapports par catégorie."],
                    ['label' => 'Fournisseur', 'required' => false, 'text' => "D'où vient cet achat, si applicable."],
                    ['label' => 'Date de la dépense', 'required' => true, 'text' => "Date à laquelle la dépense a été engagée."],
                    ['label' => 'Prix unitaire', 'required' => true, 'text' => "Prix d'une unité. Le montant total = prix unitaire × quantité, calculé automatiquement."],
                    ['label' => 'Quantité', 'required' => false, 'text' => "Nombre d'unités. Laissé vide, elle vaut 0 (montant total nul) : à ne pas oublier de remplir."],
                    ['label' => 'Unité', 'required' => false, 'text' => "Ex : sac, m3, h, forfait..."],
                    ['label' => 'Mode / Référence de paiement', 'required' => false, 'text' => "Traçabilité du règlement (espèces, virement, chèque n°...)."],
                    ['label' => 'Justificatif', 'required' => false, 'text' => "Photo ou PDF de la facture/du reçu."],
                    ['label' => 'Notes', 'required' => false, 'text' => "Texte libre."],
                ],
            ],
            'invoices' => [
                'intro' => "Une facture n'est le plus souvent PAS créée à la main : elle vient de la conversion d'un devis accepté, ou de la génération depuis une situation de travaux validée.",
                'steps' => [
                    ['title' => '1. Conversion depuis un devis (cas normal)', 'text' => "Sur un devis accepté, cliquez sur « Facturer » : une facture est créée en brouillon avec les mêmes lignes, prête à être envoyée."],
                    ['title' => '2. Génération depuis une situation de travaux', 'text' => "Pour une facturation par étapes d'avancement, validez la situation de travaux puis générez la facture correspondante."],
                    ['title' => '3. Création manuelle (cas particulier)', 'text' => "Cliquez sur « Nouvelle facture », choisissez chantier, client et type, puis ajoutez les lignes."],
                    ['title' => '4. Envoyer et suivre le paiement', 'text' => "Marquez la facture « Envoyée » une fois transmise au client, puis enregistrez chaque paiement reçu (bouton « Enregistrer un paiement ») : le statut passe automatiquement à « Partiellement payée » puis « Soldée »."],
                ],
                'fields' => [
                    ['label' => 'Chantier', 'required' => true, 'text' => "Le chantier facturé."],
                    ['label' => 'Client', 'required' => true, 'text' => "Rempli automatiquement selon le chantier sélectionné."],
                    ['label' => 'Titre', 'required' => true, 'text' => "Objet de la facture."],
                    ['label' => 'Type', 'required' => true, 'text' => "Standard, Acompte, Situation ou Avoir."],
                    ['label' => 'Date de facture', 'required' => true, 'text' => "Date d'émission."],
                    ['label' => 'Date d\'échéance', 'required' => false, 'text' => "Date limite de paiement — une facture non soldée après cette date est signalée en retard."],
                    ['label' => 'Taux de TVA', 'required' => false, 'text' => "En %, repris du taux par défaut de la société si laissé vide."],
                    ['label' => 'Taux de retenue de garantie (RG)', 'required' => false, 'text' => "En %, montant retenu par le client jusqu'à la réception des travaux."],
                    ['label' => 'Notes', 'required' => false, 'text' => "Texte libre."],
                ],
            ],
        ];
    }
}
