# 🚀 AUTOMATISATION & GESTION DES RETARDS — BuildFlow

## Compléments d'analyse et propositions avancées

Ce document propose des mécanismes d'automatisation et de gestion proactive des retards en s'inspirant des meilleures pratiques des outils SaaS BTP (Procore, Fieldwire) et de gestion de projet (Monday.com, ClickUp).

---

### 1. ANALYSE DE L'EXISTANT

| Type | Mécanisme Actuel | Limite Identifiée |
|------|------------------|-------------------|
| **Automatisation** | Transitions simples (PV → Clôture, Paiement → Facture Soldée) | Linéaire et manuel pour la majorité du cycle de vie. |
| **Retards** | Alertes basiques J+0 (SPEC-23) | Réactif plutôt que prédictif ; ne mesure pas l'impact sur la fin du chantier. |

---

### 2. PROPOSITIONS : AUTOMATISATION DES MÉCANISMES

#### AUT-01 — Génération automatique du "Plan de Travail"
**Concept :** Lorsqu'un devis est passé au statut `Accepté`, le système propose de créer automatiquement les tâches du chantier basées sur les lignes du devis.
- **Mécanisme :** Chaque section de devis devient un "Lot de tâches". Chaque ligne de devis devient une tâche parente.
- **Bénéfice :** Gain de temps majeur à l'ouverture du chantier et suppression de la double saisie.

#### AUT-02 — Cascading de Statuts (Automatisation Hiérarchique)
**Concept :** Les statuts des entités parentes réagissent aux entités enfants.
- **Règles proposées :**
    - **Tâches → Chantier :** Si 100% des tâches sont `Terminées`, envoyer une notification au Chef : *"Toutes les tâches sont finies. Souhaitez-vous passer le chantier en 'Terminé' et générer le PV de réception ?"*
    - **Bons de Commande → Dépenses :** Si un BC est marqué `Livré`, créer automatiquement une dépense en statut `Saisie` (déjà partiellement couvert par AJT-06).

#### AUT-03 — Check-in/out par "Geofencing"
**Concept :** Automatiser le pointage via la PWA (SPEC-27).
- **Mécanisme :** Si le salarié a sa PWA ouverte et entre dans le rayon GPS du chantier (défini dans SPEC-06), une notification push lui propose : *"Vous êtes sur le chantier [Nom]. Pointer l'entrée ?"*.
- **Bénéfice :** Réduit les oublis de pointage et fiabilise les données RH.

---

### 3. PROPOSITIONS : GESTION AVANCÉE DES RETARDS

#### RET-01 — Analyse d'Impact sur le Chemin Critique
**Concept :** Dans la vue Gantt (AJT-02), si une tâche avec dépendance (ex: "Électricité" dépend de "Murs") prend du retard, le système recalcule automatiquement la date de fin prévisionnelle du chantier.
- **Indicateur visuel :** Un "Décalage estimé" s'affiche sur la fiche chantier (ex: `⚠️ Fin prévue décalée de +4 jours`).

#### RET-02 — Matrice d'Escalade des Retards
**Concept :** Ne pas se contenter d'une notification J+0, mais intensifier les alertes.
- **Niveau 1 (J+1) :** Notification au Salarié assigné + Chef de chantier.
- **Niveau 2 (J+3) :** Notification à l'Admin + Marquage de la tâche en "Bloquante".
- **Niveau 3 (J+7) :** Ajout automatique d'une note dans le rapport de clôture pour analyse post-mortem.

#### RET-03 — Blocage par Dépendance de Stock
**Concept :** Alerte préventive si une tâche est planifiée mais que les matériaux nécessaires ne sont pas en stock (SPEC-22).
- **Mécanisme :** Si une tâche `Maçonnerie` est prévue dans 2 jours et que le stock de `Ciment` est inférieur à la quantité prévue au devis/dosage.
- **Notification :** *"Risque de retard : Stock insuffisant pour la tâche de demain. Commander via BC ?"*

---

### 4. RÉSUMÉ DES NOUVEAUX INDICATEURS (Dashboard)

Pour piloter ces automatisations, ajout de deux widgets "Smart" sur le Dashboard :

1. **Le "Poulpe" de l'Efficacité :** Score de respect des délais par équipe (nb tâches finies à temps / total).
2. **Le "Radar des Risques" :** Liste des chantiers où la date de fin dérive de plus de 10% par rapport au planning initial.

---

### 5. IMPACT SUR LA STRUCTURE DE DONNÉES

- **Table `tasks` :** Ajouter `dependency_id` (auto-référence) et `original_due_date` (pour mesurer la dérive).
- **Table `projects` :** Ajouter `estimated_end_date_dynamic` (calculée par le système).
