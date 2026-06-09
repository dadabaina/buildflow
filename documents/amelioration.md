# 🔍 ANALYSE COMPARATIVE — BuildFlow
## Ajustements & Améliorations identifiés

---

| Champ              | Valeur                          |
|--------------------|---------------------------------|
| **Basé sur**       | `additif.md` (pièce jointe)     |
| **Comparé avec**   | `cdc_BuildFlow_v2.md`, `specs_BuildFlow.md`, `waves_BuildFlow.md` |
| **Date d'analyse** | 20 Mai 2026                     |
| **Statut**         | À intégrer dans CDC v2.1        |

---

## Méthode d'analyse

Chaque fonctionnalité de l'`additif.md` a été recherchée dans les documents existants (CDC v2, Specs, Waves). Les résultats sont classés en trois catégories :

| Icône | Statut |
|-------|--------|
| ✅ | Déjà couvert — rien à faire |
| ⚠️ | Partiellement couvert — à compléter |
| ❌ | Absent — à ajouter |

---

# AJUSTEMENTS IDENTIFIÉS

---

## ❌ AJT-01 — Versioning des devis

**Origine additif :** Module Devis → "Versioning des devis"

**État actuel :** Le CDC v2 et les specs mentionnent uniquement la **duplication** d'un devis existant (US-09-01). Il n'existe aucun mécanisme de versioning réel (V1, V2, V3 d'un même devis avec historique des modifications).

**Ajustement à intégrer :**
- Ajouter une colonne `version` (integer, default 1) sur la table `quotes`
- Chaque modification significative d'un devis `Envoyé` crée une nouvelle version (incrémentation automatique)
- L'historique des versions est consultable depuis la fiche devis
- Seule la dernière version est active ; les versions précédentes sont archivées (lecture seule)
- Affichage dans le PDF : "Devis DEV-2026-001 — Version 3"

**Priorité suggérée :** S (Should have)
**Wave suggérée :** Wave 4 (VENTES)

---

## ❌ AJT-02 — Diagramme de Gantt

**Origine additif :** Module Planning → "Gantt simple"

**État actuel :** Le CDC v2 (MODULE 12) mentionne "Vue Gantt simplifiée par chantier *(optionnel V2)*" mais ce point n'est pas spécifié dans les specs (`specs_BuildFlow.md`) et n'apparaît dans aucune wave. Il reste donc sans spec ni implémentation.

**Ajustement à intégrer :**
- Promouvoir le Gantt de "optionnel V2" à une fonctionnalité planifiée concrète (Wave 10 ou 12)
- Créer une spec dédiée `SPEC-17-bis — Vue Gantt` :
  - Axe horizontal = timeline (semaines / mois)
  - Axe vertical = tâches du chantier ou chantiers actifs
  - Barre colorée par statut de tâche/chantier
  - Drag & drop pour modifier les dates (option)
  - Affichage des jalons (devis envoyé, situation émise, réception)
  - Export PDF

**Priorité suggérée :** C (Could have)
**Wave suggérée :** Wave 12

---

## ❌ AJT-03 — Alertes de dépassement de budget

**Origine additif :** Module Rentabilité → "Alertes de dépassement"

**État actuel :** Le CDC v2 (MODULE 18 — Notifications) et la SPEC-23 ne listent pas d'alerte spécifique au dépassement du budget prévisionnel d'un chantier. L'indicateur "Écart budget" existe dans la fiche chantier mais il est passif (lecture seule).

**Ajustement à intégrer :**
- Ajouter dans le tableau des notifications (MODULE 18 + SPEC-23) :

| Événement                              | Canal             | Seuil paramétrable |
|----------------------------------------|-------------------|--------------------|
| ACHATS > 80% du budget prévisionnel    | In-app + Email    | Oui (80% par défaut) |
| ACHATS > 100% du budget prévisionnel   | In-app + Email    | Fixe               |

- Le seuil d'alerte (ex : 80%) doit être paramétrable par chantier ou globalement dans les Paramètres entreprise
- Ajout d'un badge visuel rouge sur la fiche chantier quand le budget est dépassé

**Priorité suggérée :** S (Should have)
**Wave suggérée :** Wave 5

---

## ❌ AJT-04 — Alertes de retard de chantier

**Origine additif :** Module Planning → "Notifications de retard" & Module Notifications → "Retards chantier"

**État actuel :** La SPEC-23 couvre les alertes de tâches en retard (`Tâche en retard → In-app + Email`) mais il n'existe pas d'alerte de retard au niveau du **chantier global** (date de fin prévisionnelle dépassée).

**Ajustement à intégrer :**
- Ajouter dans le tableau des notifications :

| Événement                                     | Canal          | Déclenchement           |
|-----------------------------------------------|----------------|-------------------------|
| Date de fin prévisionnelle dépassée (chantier)| In-app + Email | J+0 puis relance J+7    |
| Chantier toujours `En cours` J+30 après fin prév.| In-app     | Relance mensuelle       |

- Job planifié quotidien (Laravel Scheduler) vérifiant les chantiers dépassant leur `date_fin_prevue`

**Priorité suggérée :** S
**Wave suggérée :** Wave 5

---

## ❌ AJT-05 — Relance automatique des impayés

**Origine additif :** Module Facturation → "Relance automatique des impayés"

**État actuel :** La SPEC-13 (US-13-02) prévoit un **bouton manuel** "Envoyer relance email". L'`additif.md` spécifie une relance **automatique**, non déclenchée par l'utilisateur.

**Ajustement à intégrer :**
- Ajouter un module de relance automatique dans les Paramètres entreprise :
  - Séquence de relance configurable :
    - Relance 1 : J+7 après échéance (email doux)
    - Relance 2 : J+14 (email ferme)
    - Relance 3 : J+30 (email mise en demeure)
  - Template d'email modifiable par étape
  - Option d'activation/désactivation globale et par client
  - Log des relances envoyées (date, type, facture concernée)
- Conserver le bouton manuel en complément

**Priorité suggérée :** S
**Wave suggérée :** Wave 6

---

## ❌ AJT-06 — Suivi des dettes fournisseurs

**Origine additif :** Module Finance & Comptabilité → "Dettes fournisseurs"

**État actuel :** Le CDC v2 couvre les bons de commande (MODULE 7.5) et la fiche fournisseur affiche l'historique des achats. Cependant, il n'existe pas de module explicite de **comptes fournisseurs (dettes)** avec solde en attente de règlement.

**Ajustement à intégrer :**
- Ajouter dans la fiche fournisseur :
  - Indicateur "Total factures fournisseurs non réglées"
  - Indicateur "Total réglé sur la période"
  - Solde dû au fournisseur
- Ajouter un module ou onglet global "Dettes fournisseurs" :
  - Liste de toutes les dépenses/BC avec mode de règlement non encore enregistré
  - Enregistrement des paiements fournisseurs (date, montant, mode)
  - Vue "Balance âgée fournisseurs" analogue à la balance âgée clients (SPEC-13)

**Priorité suggérée :** S
**Wave suggérée :** Wave 6

---

## ❌ AJT-07 — Export comptable

**Origine additif :** Module Finance & Comptabilité → "Export comptable"

**État actuel :** Les specs prévoient des exports PDF/Excel pour rapports financiers et un export CSV pour la paie. Il n'existe aucun export au format comptable standard (Grand Livre, écritures journalisées, FEC).

**Ajustement à intégrer :**
- Ajouter dans les Rapports exportables (SPEC-24 / MODULE 19) :

| Rapport                    | Format         | Description                                      |
|----------------------------|----------------|--------------------------------------------------|
| Journal des ventes         | CSV / Excel    | Toutes les factures émises avec TVA ventilée     |
| Journal des achats         | CSV / Excel    | Toutes les dépenses par catégorie comptable      |
| Grand Livre simplifié      | CSV / Excel    | Mouvements par tiers (client/fournisseur)        |
| Export FEC (optionnel)     | TXT (norme DGI)| Compatible logiciels comptables (Sage, EBP…)     |

- Paramétrer dans les Paramètres entreprise un plan de comptes simplifié (catégorie dépense ↔ numéro de compte)

**Priorité suggérée :** C (Could have)
**Wave suggérée :** Wave 14

---

## ❌ AJT-08 — Bons de livraison dans les documents

**Origine additif :** Module Documents → "Bons de livraison"

**État actuel :** Le MODULE 14 (Documents) liste des catégories de documents incluant "Factures fournisseurs" et "Contrats & devis" mais ne mentionne pas explicitement les **bons de livraison** comme catégorie.

**Ajustement à intégrer :**
- Ajouter "Bons de livraison" à la liste des catégories de documents (MODULE 14.2 / SPEC-19) :

```diff
Catégories de documents :
  - Plans & dessins techniques
  - Contrats & devis
  - Autorisation de construire / permis
  - Rapports de chantier
  - Photos & vidéos
  - Certificats & procès-verbaux
  - Factures fournisseurs
+ - Bons de livraison
  - Divers
```

- Lier optionnellement un bon de livraison à un bon de commande ou une dépense

**Priorité suggérée :** M (Must have)
**Wave suggérée :** Wave 2

---

## ❌ AJT-09 — Rapport de performance des équipes

**Origine additif :** Module Reporting → "Performance équipes"

**État actuel :** La fiche salarié (SPEC-04, SPEC-18) contient un "taux de présence" individuel. Le dashboard global (SPEC-24) n'inclut pas de rapport agrégé sur la performance des équipes.

**Ajustement à intégrer :**
- Ajouter dans les rapports exportables (SPEC-24-02) :

| Rapport                     | Contenu                                                            |
|-----------------------------|--------------------------------------------------------------------|
| Performance équipes         | Par période : heures pointées, taux présence, chantiers couverts, coût main d'œuvre |

- Ajouter dans le Dashboard (SPEC-24-01) un bloc "Équipe" :
  - Top 5 salariés les plus actifs (heures)
  - Taux de présence moyen global
  - Coût main d'œuvre total du mois

**Priorité suggérée :** C
**Wave suggérée :** Wave 14

---

## ❌ AJT-10 — Catégorisation automatique des dépenses

**Origine additif :** Module Suivi des dépenses → "Catégorisation automatique"

**État actuel :** La saisie de dépense (SPEC-07) requiert une sélection manuelle de la catégorie. Il n'existe aucune logique de suggestion automatique.

**Ajustement à intégrer :**
- Implémenter une suggestion de catégorie basée sur les mots-clés de la description :
  - Règles simples configurables (ex : "ciment" → Matériaux, "transport" → Transport)
  - Apprentissage par fréquence : si l'utilisateur catégorise souvent "béton" en "Matériaux", le système mémorise et suggère
  - La suggestion est affichée mais reste modifiable
- Ajouter la gestion des règles de catégorisation dans les Paramètres entreprise

**Priorité suggérée :** C
**Wave suggérée :** Wave 7

---

## ❌ Exclu — AJT-11 — Signature électronique (non applicable)

**Origine additif :** Module Devis → "Signature électronique"

**État actuel :** La SPEC-09-02 prévoit un "lien de validation client" (URL unique) où le client clique "J'accepte" ou "Je refuse". Ceci est fonctionnel mais minimaliste. La SPEC-14 (PV Réception) se limite à "case à cocher + nom imprimé".

**Ajustement proposé dans l'additif :**
- Étendre le module de signature numérique pour couvrir Devis, Avenants et PV de Réception avec zone de signature tactile (canvas), horodatage/IP et SMS OTP.

**Motif d'exclusion :** Aucun espace client dans l'application. La validation de devis se fait via un lien public sans authentification. Aucune extension de ce mécanisme n'est prévue dans le périmètre actuel.

**Priorité :** — | **Wave :** —

---

## ⚠️ AJT-12 — Workflow de vie du chantier documenté

**Origine additif :** Section 16 → "Logique globale du système"

**État actuel :** Le cycle de vie est décrit dans le CDC v2 (section 3.2) sous forme de diagramme ASCII. Cependant, il n'est pas formalisé comme guide utilisateur dans le manuel ni comme workflow cliquable dans l'interface.

**Ajustement à intégrer :**
- Ajouter dans le `manuel.md` une section dédiée au workflow global :

```
1. Créer le client
2. Créer le devis (depuis fiche client ou menu VENTES)
3. Envoyer le devis → attendre validation client
4. Créer le chantier (lié au devis accepté)
5. Affecter salariés, matériels, sous-traitants
6. Enregistrer les dépenses au fil du chantier
7. Émettre des situations de travaux / factures d'acompte
8. Pointer les salariés (quotidien)
9. Analyser la rentabilité (fiche chantier → onglet Résumé)
10. Générer le PV de Réception
11. Émettre la facture finale
12. Encaisser les paiements
13. Clôturer le chantier → rapport final
```

- Envisager un **assistant de démarrage rapide** dans l'UI : bandeau "Étapes suivantes" visible sur la fiche chantier selon le statut actuel.

**Priorité suggérée :** S
**Wave suggérée :** Wave 2 (manuel) / Wave 5 (UI bandeau)

---

## ⚠️ AJT-13 — Module Stock : promouvoir la priorité

**Origine additif :** Module Stock & Matériaux (complet, présenté comme core)

**État actuel :** Le MODULE 17 (Stocks) et la SPEC-22 existent dans les documents, mais avec une **priorité C** (Could have) et placé en **Wave 3** (Phase 3 — Mois 8-12). L'`additif.md` présente la gestion des stocks comme une fonctionnalité centrale, pas accessoire.

**Ajustement suggéré :**
- Réévaluer la priorité de SPEC-22 de **C → S**
- Avancer la wave de **Wave 12 → Wave 8** (Phase 2)
- Minimum viable du module stocks à livrer en Wave 8 :
  - CRUD articles stock
  - Entrées / sorties manuelles liées à un chantier
  - Stock minimum + alerte
- Fonctionnalités avancées (valorisation PAMP, transferts, inventaire physique) restent en Wave 12

**Priorité suggérée :** S → déplacer vers Phase 2
**Wave suggérée :** Avancer à Wave 8

---

## ⚠️ AJT-14 — Rentabilité en temps réel : indicateur visible

**Origine additif :** Module Rentabilité → "Suivi en temps réel" & Objectif final : "Savoir en temps réel combien tu gagnes / combien tu dépenses"

**État actuel :** Les indicateurs financiers de la fiche chantier (CDC v2 §6.3) sont calculés dynamiquement. Cependant, il n'existe pas de **widget de rentabilité en temps réel** clairement visible sur le dashboard principal.

**Ajustement à intégrer :**
- Ajouter au dashboard (SPEC-24-01) une section "Rentabilité en direct" :
  - Liste des 5 chantiers actifs avec leur marge actuelle (%)
  - Code couleur : vert (marge > 20%), orange (0–20%), rouge (marge < 0%)
  - Barre de progression "Budget consommé"
- Ajouter sur la fiche chantier (en-tête toujours visible) un indicateur de santé financière :

```
💰 Résultat = +2 450 000 MGA  |  📊 Marge = 18%  |  ⚠️ Budget : 87% consommé
```

**Priorité suggérée :** S
**Wave suggérée :** Wave 5

---

# RÉCAPITULATIF

| ID     | Titre                                    | État | Priorité | Wave cible |
|--------|------------------------------------------|------|----------|------------|
| AJT-01 | Versioning des devis                     | ❌   | S        | Wave 4     |
| AJT-02 | Diagramme de Gantt                       | ❌   | C        | Wave 12    |
| AJT-03 | Alertes de dépassement de budget         | ❌   | S        | Wave 5     |
| AJT-04 | Alertes de retard de chantier            | ❌   | S        | Wave 5     |
| AJT-05 | Relance automatique des impayés          | ❌   | S        | Wave 6     |
| AJT-06 | Suivi des dettes fournisseurs            | ❌   | S        | Wave 6     |
| AJT-07 | Export comptable                         | ❌   | C        | Wave 14    |
| AJT-08 | Bons de livraison (catégorie document)   | ❌   | M        | Wave 2     |
| AJT-09 | Rapport de performance des équipes       | ❌   | C        | Wave 14    |
| AJT-10 | Catégorisation automatique des dépenses  | ❌   | C        | Wave 7     |
| AJT-11 | Renforcer la signature électronique      | ❌ Exclu | —        | —          |
| AJT-12 | Workflow de vie du chantier documenté    | ⚠️   | S        | Wave 2/5   |
| AJT-13 | Module Stock : promouvoir la priorité    | ⚠️   | S→       | Wave 8     |
| AJT-14 | Rentabilité en temps réel (widget)       | ⚠️   | S        | Wave 5     |

---

## Fonctionnalités déjà couvertes ✅

Les éléments suivants de l'`additif.md` sont déjà bien couverts dans les documents existants et ne nécessitent pas d'ajustement :

| Fonctionnalité additif                        | Document de référence               |
|-----------------------------------------------|-------------------------------------|
| Création et gestion des clients               | CDC v2 MODULE 3 + SPEC-03           |
| Historique des chantiers par client           | SPEC-03 US-03-02                    |
| Statut client (actif/inactif)                 | CDC v2 MODULE 3.2                   |
| Devis détaillés avec bibliothèque de prix     | CDC v2 MODULE 8.1 + SPEC-09, SPEC-25|
| Calcul automatique HT/TVA/TTC                 | CDC v2 MODULE 8.1                   |
| Export PDF devis                              | SPEC-09-03                          |
| Transformation devis → facture                | SPEC-12-01                          |
| Factures d'acompte                            | SPEC-12-01                          |
| Facturation progressive (situations)          | CDC v2 MODULE 8.3 + SPEC-11         |
| Gestion TVA                                   | CDC v2 MODULE 8.4                   |
| Création chantier + suivi avancement          | CDC v2 MODULE 6 + SPEC-06           |
| Photos et documents chantier                  | SPEC-19 + SPEC-20                   |
| Statut chantier (en cours, terminé, suspendu) | SPEC-06-04                          |
| Achat matériaux + dépenses fournisseurs       | CDC v2 MODULE 7 + SPEC-07           |
| Dépenses main d'œuvre                         | CDC v2 MODULE 7.3 (catégorie)       |
| Gestion des ouvriers / salariés               | CDC v2 MODULE 4 + SPEC-04           |
| Pointage journalier                           | CDC v2 MODULE 13 + SPEC-18          |
| Salaire par jour ou heure                     | SPEC-18-03                          |
| Planning des équipes et chantiers             | CDC v2 MODULE 12 + SPEC-17          |
| Calcul marge brute/nette                      | CDC v2 §8.6 + §8.7                  |
| Comparaison devis vs réel (rapport clôture)   | CDC v2 MODULE 9.2 + SPEC-14-02      |
| Suivi des paiements clients                   | SPEC-13                             |
| Notifications factures impayées               | CDC v2 MODULE 18 + SPEC-23          |
| Notifications tâches en retard                | CDC v2 MODULE 18                    |
| Dashboard CA global                           | CDC v2 MODULE 19 + SPEC-24          |
| Graphiques CA et répartition dépenses         | CDC v2 MODULE 19.1                  |
| Stockage PDF, contrats, photos                | SPEC-19 + SPEC-20                   |
| Mobile Money (modes de paiement indicatifs)   | CDC v2 MODULE 8.5 + SPEC-13-01      |

---

## Notes de mise à jour recommandées

1. **CDC v2 → v2.1** : intégrer AJT-01, AJT-03, AJT-04, AJT-05, AJT-06, AJT-08, AJT-14 dans les modules concernés (AJT-11 exclu).
2. **specs_BuildFlow.md** : créer les specs manquantes pour AJT-01, AJT-03 à AJT-06, AJT-08.
3. **waves_BuildFlow.md** : repositionner AJT-13 (Stocks) en Wave 8 ; planifier AJT-02 (Gantt) en Wave 12.
4. **manuel.md** : ajouter la section workflow global (AJT-12).
