# Travaux réalisés le 7 juin 2026

## 1. Amélioration de l'Interface (UX/UI)
*   **Gestion des Employés :** Modernisation du champ "Postes / Fonctions (Polyvalence)" avec l'intégration de **Tom Select**. Ajout de la recherche en temps réel et de la gestion par badges (tags) dans le formulaire et le modal.
*   **Refonte du formulaire Chantier :** Simplification des formulaires de création et d'édition en retirant la gestion complexe des équipes pour la centraliser dans la fiche détaillée.

## 2. Automatisation des Processus
*   **Génération des Tâches :** Automatisation complète de la création des tâches dès qu'un devis est accepté (soit par l'administrateur, soit par le client via le lien public).
*   **Workflow Commercial :** Suppression du bouton manuel "Générer Tâches" devenu redondant.

## 3. Pilotage et Traçabilité (Nouveau module)
*   **Fil d'actualité (Project Logs) :** Mise en place d'un système de journalisation automatique pour chaque chantier.
    *   Enregistrement des actions clés : Validation de devis, création/statut de tâches, mouvements de stock, affectation de matériel, changements d'équipe.
    *   Affichage d'un bloc "Activité Récente" sur la vue d'ensemble.
    *   Ajout d'un onglet "Historique" complet avec icônes et auteurs des actions.

## 4. Gestion de l'Équipe (RH Chantier)
*   **Centralisation :** Déplacement de toute la gestion humaine vers l'onglet "Équipe" de la fiche chantier.
*   **Outils de gestion :** Ajout d'un modal de synchronisation d'équipe, possibilité de définir des besoins par poste et de retirer des collaborateurs individuellement avec suivi de couverture en temps réel.

## 5. Matériels & Logistique
*   **Alertes Stock Site :** Implémentation de seuils d'alerte personnalisables par chantier pour les consommables (sable, ciment, etc.). Affichage d'alertes visuelles rouges en cas de stock critique.
*   **Suivi des Locations :**
    *   Distinction entre matériel interne et matériel loué (avec lien fournisseur).
    *   Calcul automatique du décompte des jours restants avant restitution.
*   **Nouvel onglet "Équipements" :** Espace dédié pour affecter/libérer le matériel lourd du chantier séparément des consommables.

## 6. Bons de Commande (BC)
*   **Refonte du Workflow :** Renommage du statut "Envoyé" en "Validé" pour refléter le processus de validation interne par l'admin.
*   **Intégration Log :** Traçabilité complète des BC (création, validation, livraison, conversion en dépense) dans le fil d'actualité du chantier.

## 7. Maintenance et Corrections (Bugfixes)
*   Correction d'une erreur de relation manquante (`projectLogs`) sur le modèle Project.
*   Correction d'une erreur de syntaxe (`Unmatched '}'`) dans le modèle PurchaseOrder.
*   Ajout du log manquant lors de la création initiale d'une tâche.
