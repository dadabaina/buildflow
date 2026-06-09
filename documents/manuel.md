# Manuel d'utilisation — BuildFlow

**Version 1.0 — Mai 2026**

---

## Table des matières

1. [Présentation](#1-présentation)
2. [Connexion & Profil](#2-connexion--profil)
3. [Tableau de bord](#3-tableau-de-bord)
4. [Contacts](#4-contacts)
   - 4.1 [Clients](#41-clients)
   - 4.2 [Fournisseurs](#42-fournisseurs)
   - 4.3 [Employés](#43-employés)
5. [Chantiers](#5-chantiers)
6. [Dépenses](#6-dépenses)
7. [Bons de commande](#7-bons-de-commande)
8. [Tâches](#8-tâches)
9. [Pointage](#9-pointage)
10. [Commercial](#10-commercial)
    - 10.1 [Devis](#101-devis)
    - 10.2 [Factures](#102-factures)
    - 10.3 [Paiements](#103-paiements)
11. [Matériels & Stocks](#11-matériels--stocks)
   - 11.1 [Parc Matériel](#111-parc-matériel)
   - 11.2 [Gestion des Dépôts](#112-gestion-des-dépôts)
   - 11.3 [Mouvements de Stock](#113-mouvements-de-stock)
12. [Paramètres](#12-paramètres)
13. [Gestion des utilisateurs](#13-gestion-des-utilisateurs)
14. [Workflow global de gestion d'un chantier](#14-workflow-global-de-gestion-dun-chantier)
15. [Ordinogrammes](#15-ordinogrammes)

---

## 1. Présentation

**BuildFlow** est une application web de gestion de chantier BTP destinée aux PME, artisans et entreprises de construction. Elle permet de centraliser la gestion des chantiers, des dépenses, des revenus, des clients et des salariés.

**URL d'accès :** `http://localhost:8000`

**Technologies :** Laravel · MySQL · Bootstrap 5 · Alpine.js

---

## 2. Connexion & Profil

### Connexion

1. Accédez à l'URL de l'application.
2. Saisissez votre **adresse e-mail** et votre **mot de passe**.
3. Cliquez sur **Se connecter**.

> En cas d'oubli de mot de passe, cliquez sur **Mot de passe oublié ?** et suivez les instructions envoyées par e-mail.

### Modifier son profil

1. Cliquez sur votre nom en haut à droite.
2. Sélectionnez **Mon profil**.
3. Modifiez vos informations (nom, e-mail, mot de passe).
4. Cliquez sur **Enregistrer**.

---

## 3. Tableau de bord

Le tableau de bord affiche une synthèse en temps réel :

| Indicateur | Description |
|---|---|
| Chantiers actifs | Nombre de chantiers au statut « En cours » |
| Clients totaux | Nombre total de clients enregistrés |
| Dépenses (MGA) | Total des dépenses validées du mois courant |
| Factures impayées | Nombre de factures échues non réglées |

Des graphiques montrent la **répartition des chantiers par statut** et les **chantiers récents**.

---

## 4. Contacts

> Section du menu : **Contacts** — regroupe Clients, Fournisseurs et Employés.

### 4.1 Clients

**Accès :** Menu → Clients

| Action | Comment faire |
|---|---|
| Lister | Cliquez sur **Clients** dans le menu |
| Créer | Bouton **Nouveau client** → Remplir le formulaire → **Enregistrer** |
| Consulter | Cliquez sur le nom du client |
| Modifier | Page client → Bouton **Modifier** |
| Supprimer | Page client → Bouton **Supprimer** (confirmation requise) |

**Informations d'un client :** Nom, e-mail, téléphone, adresse, NIF/STAT, notes.

### 4.2 Fournisseurs

**Accès :** Menu → Fournisseurs

Même fonctionnement que les clients. Les fournisseurs sont utilisés dans les **bons de commande** et les **dépenses**.

### 4.3 Employés

**Accès :** Menu → Employés

| Action | Comment faire |
|---|---|
| Créer un employé | Bouton **Nouvel employé** → Remplir (nom, poste, taux journalier) → **Enregistrer** |
| Affecter à un chantier | Page employé → Section **Chantiers affectés** |
| Consulter les présences | Page employé → Section **Pointage** |

**Champs importants :** Nom, prénom, poste/fonction, taux journalier, taux horaire.

---

## 5. Chantiers

**Accès :** Menu → Chantiers *(section Chantiers)*

### Cycle de vie d'un chantier

```
Prospection → Devis en cours → Devis envoyé → En cours → En pause → Terminé → Clôturé
                                     ↓
                                  Annulé
```

### Créer un chantier

1. Cliquez sur **Nouveau chantier**.
2. Remplissez les informations :
   - **Nom** du chantier (obligatoire)
   - **Statut** initial (par défaut : Prospection)
   - **Client** (obligatoire)
   - **Région / Site** (optionnel)
   - **Adresse**, **Coordonnées GPS** (optionnel)
   - **Date de début**, **Date prévue de fin**
   - **Montant du contrat**, **TVA**, **Retenue de garantie**
   - **Notes**
3. Cliquez sur **Créer le chantier**.

> La **référence** est générée automatiquement au format `BF-ANNÉE-XXX`.

### Changer le statut

Sur la page d'un chantier, utilisez le bouton **Changer le statut** pour faire avancer le chantier dans son cycle de vie. Les transitions autorisées sont prédéfinies.

### Page détail d'un chantier

La page est organisée en **onglets** :

| Onglet | Contenu |
|---|---|
| Vue d'ensemble | Informations générales, budgets, marge brute, rentabilité |
| Équipe | Employés affectés au chantier |
| Dépenses | Liste des dépenses liées |
| Devis & Factures | Devis et factures associés |

En haut de la page : indicateurs synthétiques (Budget global, Total Dépenses, Marge brute, Rentabilité %).

---

## 6. Dépenses

**Accès :** Menu → Dépenses *(section Chantiers)*

### Créer une dépense

1. Cliquez sur **Nouvelle dépense**.
2. Remplissez :
   - **Chantier** associé
   - **Catégorie** de dépense
   - **Fournisseur** (optionnel)
   - **Montant HT** et **TVA**
   - **Date**, **Description**, **Pièce justificative**
3. Cliquez sur **Enregistrer**.

### Statuts d'une dépense

| Statut | Description |
|---|---|
| Saisie | En attente de validation |
| Validée | Approuvée par un responsable |
| Rejetée | Refusée |

### Valider / Rejeter

Sur la liste ou la page de la dépense, utilisez les boutons **Valider** ou **Rejeter**.

---

## 7. Bons de commande

**Accès :** Menu → Bons de commande *(section Chantiers)*

### Créer un bon de commande

1. Cliquez sur **Nouveau bon de commande**.
2. Sélectionnez le **Fournisseur** et le **Chantier**.
3. Ajoutez les lignes (désignation, quantité, prix unitaire).
4. Cliquez sur **Enregistrer**.

### Statuts

| Statut | Description |
|---|---|
| Brouillon | En cours de création |
| Envoyé | Transmis au fournisseur |
| Reçu | Marchandises reçues |
| Annulé | Bon annulé |

---

## 8. Tâches

**Accès :** Menu → Tâches *(section Chantiers)*

### Vue liste

Affiche toutes les tâches avec filtres par chantier, assigné et statut.

### Vue Kanban

Cliquez sur **Kanban** pour visualiser les tâches en 4 colonnes :

| Colonne | Description |
|---|---|
| À faire | Tâches non démarrées |
| En cours | Tâches en cours d'exécution |
| En pause | Tâches temporairement bloquées |
| Terminées | Tâches achevées |

### Créer une tâche

1. Cliquez sur **Nouvelle tâche**.
2. Remplissez : Titre, Chantier, Assigné à, Statut, Priorité, Date d'échéance, Description.
3. Cliquez sur **Enregistrer**.

**Statuts disponibles :** À faire · En cours · En pause · Terminée · Annulée

### Commentaires

Sur la page d'une tâche, saisissez un commentaire dans le champ **Ajouter un commentaire** et cliquez **Envoyer**.

---

## 9. Pointage

**Accès :** Menu → Pointage *(section Chantiers)*

### Saisir un pointage

1. Cliquez sur **Saisir pointage**.
2. Remplissez :
   - **Chantier** (obligatoire)
   - **Employé** (obligatoire)
   - **Date** (obligatoire)
   - **Statut** : Présent / Absent justifié / Absent non justifié
   - **Heure d'arrivée** et **Heure de départ** (calcul automatique des heures)
   - **Jours travaillés**
3. Cliquez sur **Enregistrer**.

**Récap mensuel :** Bouton **Récap mensuel** en haut de la liste pour voir les heures par employé/chantier sur une période.

---

## 10. Commercial

### 10.1 Devis

**Accès :** Menu → Devis

#### Cycle de vie d'un devis

```
Brouillon → Envoyé → Accepté → Facturé
                ↓
             Refusé / Expiré
```

#### Créer un devis

1. Cliquez sur **Nouveau devis**.
2. Remplissez : Chantier, Client, Titre, Date, Échéance de validité, TVA.
3. Cliquez sur **Créer**.

#### Ajouter des lignes

Sur la page du devis (statut **Brouillon**) :
1. Cliquez sur **Ajouter une ligne**.
2. Saisissez : Désignation, Quantité, **Unité** (sélectionnez dans la liste), Prix Unitaire HT.
3. Cliquez sur **Valider la ligne**.

#### Envoyer un devis

Cliquez sur le bouton **Envoyer** pour passer le devis en statut « Envoyé ».

Le client peut valider le devis via un lien public (sans connexion).

#### Convertir en facture

Quand le devis est **Accepté**, cliquez sur **Facturer** pour générer automatiquement une facture.

### 10.2 Factures

**Accès :** Menu → Factures

Les factures peuvent être créées manuellement ou générées depuis un devis accepté.

#### Statuts d'une facture

| Statut | Description |
|---|---|
| Brouillon | En cours de création |
| Envoyée | Transmise au client |
| Partiel | Paiement partiel reçu |
| Payée | Intégralement réglée |
| En retard | Échéance dépassée, non réglée |
| Annulée | Facture annulée |

#### Ajouter des paiements

Sur la page de la facture, cliquez sur **Enregistrer un paiement**, saisissez le montant et la date.

### 10.3 Paiements

**Accès :** Menu → Paiements

Visualisez tous les paiements reçus, filtrables par période ou client.

---

## 11. Matériels & Stocks

### 11.1 Parc Matériel

**Accès :** Menu → Matériels & Stock → Parc Matériel

Gérez vos équipements (véhicules, grues, outillage). Vous pouvez suivre leur état (disponible, maintenance, hors service) et leur affectation.

### 11.2 Gestion des Dépôts

**Accès :** Menu → Matériels & Stock → Dépôts / Magasins

Vous pouvez créer deux types de dépôts :
- **Dépôt Central/Régional** : Pour le stockage général hors chantiers.
- **Dépôt de Chantier** : En cochant un chantier lors de la création, le dépôt est lié à ce projet et son stock sera visible directement sur la fiche chantier.

### 11.3 Mouvements de Stock

**Accès :** Menu → Matériels & Stock → État des Stocks

#### Enregistrer un mouvement
1. Cliquez sur **Nouveau mouvement**.
2. **Type** : 
   - *Entrée* : Achat fournisseur ou retour de chantier.
   - *Sortie* : Consommation réelle sur les travaux.
   - *Transfert* : Déplacement d'un matériau d'un dépôt à un autre (ex: Central → Chantier A).
   - *Ajustement* : Pour corriger le stock après inventaire physique.
3. **Automatisation** : Si vous choisissez un article de votre bibliothèque (Matériau du catalogue), le nom, l'unité et le prix de référence se remplissent tout seuls.

#### Consulter le stock d'un chantier
Sur la page d'un chantier, l'onglet **Stock Site** affiche l'inventaire précis des matériaux présents sur place, calculé automatiquement selon les livraisons et consommations saisies.

---

## 12. Paramètres

**Accès :** Menu → Paramètres

La page paramètres est divisée en 5 sections accessibles via le menu de gauche :

### Entreprise

Informations affichées sur les documents officiels (devis, factures) :
- Nom, e-mail, téléphone, adresse
- Identifiants fiscaux (NIF, STAT, RCS)
- Taux de TVA par défaut
- Préfixes de numérotation (Devis, Facture, Chantier…)

### Régions

Gérez les régions géographiques utilisées dans les fiches clients et chantiers.
- **Ajouter :** Saisissez le nom et cliquez **Ajouter**.
- **Supprimer :** Icône corbeille à droite de la région.

### Postes & Fonctions

Définissez les postes professionnels utilisés pour les employés (ex : Chef de chantier, Maçon, Électricien…).

### Types d'unité

Gérez les unités de mesure utilisées dans les devis et matériaux.
- **Ajouter :** Saisissez le nom et le symbole (ex : `m³`, `ml`, `ens`) puis cliquez **Ajouter**.
- Ces unités apparaissent en **liste déroulante** lors de l'ajout de lignes dans un devis.

### Catégories de dépenses

Créez des catégories pour classer vos denses (ex : Matériaux, Main d'œuvre, Transport…).

---

## 13. Gestion des utilisateurs

**Accès :** Menu → Utilisateurs *(réservé aux administrateurs)*

| Action | Description |
|---|---|
| Créer un utilisateur | Nom, e-mail, rôle, mot de passe |
| Modifier | Changer les informations ou le rôle |
| Désactiver | Empêcher la connexion sans supprimer le compte |

**Rôles disponibles :** Administrateur, Manager, Employé.

---

## 14. Workflow global de gestion d'un chantier

Ce guide pas-à-pas décrit le parcours complet de la création d'un client jusqu'à la clôture du chantier.

| Étape | Action | Module concerné |
|-------|--------|------------------|
| 1 | Créer le **client** (nom, adresse, contact) | CRM > Clients |
| 2 | Créer le **devis** (lignes, K facteur, TVA) depuis la fiche client ou menu Ventes > Devis | Ventes > Devis |
| 3 | Envoyer le devis par email et attendre la validation client | Ventes > Devis > Envoyer |
| 4 | Créer le **chantier** lié au devis accepté (dates, chef de chantier) | Chantiers > Nouveau |
| 5 | Affecter **salariés**, **matériels** et **sous-traitants** au chantier | Chantier > Ressources |
| 6 | Enregistrer les **dépenses** au fil du chantier (matériaux, main-d'œuvre, etc.) | Dépenses |
| 7 | Émettre des **situations de travaux** ou **factures d'acompte** selon l'avancement | Ventes > Factures |
| 8 | **Pointer les salariés** quotidiennement (mobile ou back-office) | Pointage |
| 9 | Analyser la **rentabilité** (onglet Résumé de la fiche chantier) | Chantier > Résumé |
| 10 | Générer le **PV de réception** (document officiel fin de travaux) | Chantier > Documents |
| 11 | Émettre la **facture finale** solde | Ventes > Factures |
| 12 | Encaisser les **paiements** et vérifier le solde client | Ventes > Paiements |
| 13 | **Clôturer le chantier** et générer le rapport final | Chantier > Clôturer |

> **Conseil :** Chaque étape est traçable depuis le tableau de bord principal. Les alertes de dépassement de budget et de retard sont envoyées automatiquement dès que les seuils paramétrés sont atteints.

---

## 15. Ordinogrammes

### 13.1 Cycle de vie d'un Chantier *(transitions autorisées)*

```
[Nouveau chantier]
        |
        v
  [Prospection]
        |
        v
 [Devis en cours]
        |
        v
 [Devis envoyé]
     /     \
    v       v
[En cours] [Annulé]
    |
    v
 [En pause]
    |
    v
 [Terminé]
    |
    v
 [Clôturé]
```

---

### 13.2 Processus Devis → Facture

```
[Créer devis]
      |
      v
[Ajouter lignes]
      |
      v
  [Envoyer]
      |
    -----
    |   |
    v   v
[Refusé] [Accepté]
             |
             v
        [Facturer]
             |
             v
    [Facture brouillon]
             |
             v
      [Envoyer facture]
             |
             v
    [En attente paiement]
             |
             v
    [Enregistrer paiement]
             |
          -------
          |     |
          v     v
    [Part. payée] [Payée]
```

---

### 13.3 Flux de dépenses

```
[Nouvelle dépense]
        |
        v
   [Saisie / brouillon]
        |
        v
  [Soumise à validation]
       / \
      v   v
[Validée] [Rejetée]
```

---

### 13.4 Flux de pointage

```
[Saisir pointage]
         |
         v
[Choisir chantier + employé]
         |
         v
[Date + Statut présence]
    (Présent / Absent justifié / Absent non justifié)
         |
         v
[Heure arrivée → Heure départ]
  (calcul auto des heures travaillées)
         |
         v
    [Enregistrer]
         |
         v
[Visible dans Récap mensuel]
```

---

### 13.5 Processus Bon de commande

```
[Nouveau BC]
     |
     v
[Sélectionner fournisseur + chantier]
     |
     v
[Ajouter lignes]
     |
     v
[Brouillon → Envoyer]
     |
     v
[Envoyé → Marquer reçu]
     |
     v
[Reçu]
```

---

*Fin du manuel d'utilisation BuildFlow v1.0*
