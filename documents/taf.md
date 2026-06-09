# 📋 TRAVAIL À FAIRE (TAF) — Suggestions d'Améliorations BuildFlow

Ce document regroupe les suggestions pour faciliter la gestion et le suivi des chantiers, organisées par priorité d'implémentation.

---

## 🔥 PRIORITÉ 1 : CRITIQUE & FONDATIONS
*Objectif : Garantir la fiabilité des données terrain et la visibilité sur la rentabilité.*

- [ ] **UX Terrain — Offline First Renforcé**
    - Assurer que la saisie des dépenses, le pointage et les photos fonctionnent sans connexion internet.
    - Synchronisation automatique intelligente lors du retour du réseau avec gestion des conflits.
- [ ] **Widget "Santé Financière" en temps réel**
    - Affichage persistant sur la fiche chantier : `[Marge réelle %] | [Budget consommé %] | [Reste à encaisser]`.
    - Alertes visuelles (code couleur) dès que la marge descend sous le seuil défini.
- [ ] **Indicateur de Dérive (Physique vs Financier)**
    - Comparer l'avancement des tâches (physique) avec l'avancement du budget (financier).
    - Alerter si le budget est consommé à 80% alors que les tâches ne sont terminées qu'à 40%.
- [ ] **Validation du cycle d'Achat (Bons de Livraison)**
    - Permettre de lier une photo du Bon de Livraison (BL) à un Bon de Commande (BC) avant de valider la dépense.
    - Assurer que l'entreprise ne paie que ce qui a été réellement reçu sur site.

---

## ⚡ PRIORITÉ 2 : EFFICACITÉ OPÉRATIONNELLE
*Objectif : Automatiser les tâches répétitives et éviter les erreurs de planification.*

- [ ] **Gestion des Conflits d'Affectation**
    - Alerte lors de l'affectation d'un salarié ou d'un équipement s'il est déjà prévu sur un autre chantier aux mêmes dates.
- [ ] **Relances Automatiques des Impayés**
    - Système de relance mail automatique (J+7, J+15) pour les factures non soldées.
- [ ] **Alertes de Stock Critique**
    - Notification automatique si la consommation réelle sur chantier dépasse les prévisions du devis ou si le stock minimal est atteint.
- [ ] **Pointage Photo "Preuve de présence"**
    - Option de prendre une photo de l'équipe au moment du pointage (check-in) pour renforcer la traçabilité.

---

## 🌱 PRIORITÉ 3 : VALEUR AJOUTÉE & CONFORT
*Objectif : Améliorer l'expérience utilisateur et la communication client.*

- [ ] **Journal de Chantier Vocal**
    - Intégrer l'enregistrement vocal sur la PWA pour les comptes-rendus quotidiens (plus rapide que la saisie texte).
- [ ] **Météo Automatique**
    - Récupérer et enregistrer automatiquement les conditions météo lors de la création d'un rapport de chantier (impact sur les retards).
- [ ] **Catégorisation Automatique des Dépenses**
    - Suggestion de catégorie basée sur les mots-clés de la description (ex: "Peinture" -> Finitions).
- [ ] **Galerie "Avant / Après"**
    - Outil de comparaison visuelle des étapes du chantier pour valoriser le travail auprès des clients.

---

## 🛠 LOGIQUE GLOBALE DE SUIVI (Rappel)
Pour un suivi efficace, le flux suivant doit être respecté dans l'outil :
1. **Équipe** : Qui est où ? (Planning & Pointage)
2. **Achats** : Qu'est-ce qui est commandé/reçu ? (BC -> BL -> Dépense)
3. **Ventes** : Qu'est-ce qui est facturé/encaissé ? (Devis -> Situation -> Facture)
4. **État** : Où en est-on ? (Tâches -> Avancement Physique -> Rentabilité)
