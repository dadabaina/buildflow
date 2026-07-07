<?php

namespace App\Http\Controllers;

class HelpController extends Controller
{
    public function index()
    {
        $sections = [
            [
                'title' => 'Chantiers & Devis',
                'items' => [
                    ['label' => 'Chantiers', 'description' => "Suivre l'avancement, le budget et les dépenses d'un chantier.", 'route' => 'projects.index', 'tour' => 'projects.index'],
                    ['label' => 'Devis', 'description' => "Proposer un prix à un client. Une fois accepté, le chantier est créé automatiquement.", 'route' => 'quotes.index', 'tour' => 'quotes.index'],
                    ['label' => 'Avenants', 'description' => "Modifier le montant du marché initial (travaux supplémentaires...).", 'route' => 'amendments.index', 'tour' => 'amendments.index'],
                ],
            ],
            [
                'title' => 'Argent : dépenses, commandes, factures',
                'items' => [
                    ['label' => 'Dépenses', 'description' => "Enregistrer les coûts réels d'un chantier (matériaux, main d'œuvre...).", 'route' => 'expenses.index', 'tour' => 'expenses.index'],
                    ['label' => 'Bons de Commande', 'description' => "Commander auprès d'un fournisseur, puis convertir en dépense une fois livré.", 'route' => 'purchase-orders.index', 'tour' => 'purchase-orders.index'],
                    ['label' => 'Factures', 'description' => "Facturer le client pour les travaux réalisés.", 'route' => 'invoices.index', 'tour' => 'invoices.index'],
                    ['label' => 'Situations de travaux', 'description' => "Facturer l'avancement réel du chantier, étape par étape.", 'route' => 'progress-billings.index', 'tour' => 'progress-billings.index'],
                    ['label' => 'Paiements', 'description' => "Noter l'argent réellement reçu d'un client.", 'route' => 'payments.index', 'tour' => 'payments.index'],
                ],
            ],
            [
                'title' => 'Terrain : tâches, équipe, pointage',
                'items' => [
                    ['label' => 'Tâches', 'description' => "Découper le chantier en tâches pour suivre l'avancement.", 'route' => 'tasks.index', 'tour' => 'tasks.index'],
                    ['label' => 'Employés', 'description' => "Gérer le personnel disponible pour les chantiers.", 'route' => 'employees.index', 'tour' => 'employees.index'],
                    ['label' => 'Pointage', 'description' => "Enregistrer les jours/heures travaillées par employé.", 'route' => 'attendances.index', 'tour' => 'attendances.index'],
                ],
            ],
            [
                'title' => 'Stock & matériel',
                'items' => [
                    ['label' => 'Mouvements de stock', 'description' => "Enregistrer les entrées et sorties de matériaux.", 'route' => 'stock-movements.index', 'tour' => 'stock-movements.index'],
                    ['label' => 'Matériaux', 'description' => "Référencer les matériaux et suivre leur stock.", 'route' => 'materials.index', 'tour' => 'materials.index'],
                    ['label' => 'Modèles de dosage', 'description' => "Définir la quantité de matériaux/main d'œuvre nécessaire pour une prestation type.", 'route' => 'dosage.index', 'tour' => 'dosage.index'],
                    ['label' => 'Équipements', 'description' => "Gérer les machines et engins de chantier.", 'route' => 'equipments.index', 'tour' => 'equipments.index'],
                    ['label' => 'Entrepôts', 'description' => "Gérer les lieux de stockage.", 'route' => 'warehouses.index', 'tour' => 'warehouses.index'],
                ],
            ],
            [
                'title' => 'Clients & fournisseurs',
                'items' => [
                    ['label' => 'Clients', 'description' => "Gérer les clients pour qui tu réalises des chantiers.", 'route' => 'clients.index', 'tour' => 'clients.index'],
                    ['label' => 'Fournisseurs', 'description' => "Gérer les fournisseurs à qui tu passes des commandes.", 'route' => 'suppliers.index', 'tour' => 'suppliers.index'],
                ],
            ],
            [
                'title' => 'Rapports',
                'items' => [
                    ['label' => 'Tous les rapports', 'description' => "Accéder aux différentes analyses : rentabilité, flux financier, dosage réel...", 'route' => 'reports.index', 'tour' => 'reports.index'],
                    ['label' => 'Suivi des chantiers', 'description' => "Comparer Facturé, Dépenses et Marge de chaque chantier.", 'route' => 'reports.projects', 'tour' => 'reports.projects'],
                ],
            ],
            [
                'title' => 'Administration',
                'items' => [
                    ['label' => 'Utilisateurs', 'description' => "Créer des comptes pour les membres de ton équipe.", 'route' => 'users.index', 'tour' => 'users.index'],
                    ['label' => 'Rôles', 'description' => "Définir les droits d'accès par type de poste.", 'route' => 'roles.index', 'tour' => 'roles.index'],
                    ['label' => 'Paramètres', 'description' => "Configurer les listes utilisées dans toute l'application.", 'route' => 'settings.index', 'tour' => 'settings.index'],
                ],
            ],
        ];

        return view('help.index', compact('sections'));
    }
}
