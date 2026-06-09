# 📘 CAHIER DES CHARGES COMPLET — BuildFlow

## SaaS de Gestion de Chantier BTP

---

| Champ            | Valeur                          |
|------------------|---------------------------------|
| **Projet**       | BuildFlow                       |
| **Version**      | 2.0                             |
| **Date**         | 10 Mai 2026                     |
| **Type**         | Application SaaS Web + PWA      |
| **Statut**       | En cours de définition          |
| **Confidentialité** | Confidentiel                 |

---

# SOMMAIRE

1. [Présentation du projet](#1-présentation-du-projet)
2. [Objectifs & Valeur métier](#2-objectifs--valeur-métier)
3. [Concept clé](#3-concept-clé)
4. [Architecture technique](#4-architecture-technique)
5. [Modèle SaaS & Multi-tenant](#5-modèle-saas--multi-tenant)
6. [Modules fonctionnels](#6-modules-fonctionnels)
7. [Modèles de prix & Bibliothèques](#7-modèles-de-prix--bibliothèques)
8. [Calculs financiers chantier](#8-calculs-financiers-chantier)
9. [Formules paramétrables](#9-formules-paramétrables)
10. [Base de données](#10-base-de-données)
11. [UX/UI & Design System](#11-uxui--design-system)
12. [Sécurité](#12-sécurité)
13. [Performance & Scalabilité](#13-performance--scalabilité)
14. [PWA & Fonctionnement Hors-ligne](#14-pwa--fonctionnement-hors-ligne)
15. [Roadmap & Phases de déploiement](#15-roadmap--phases-de-déploiement)
16. [Glossaire](#16-glossaire)

---

# 1. Présentation du projet

## 1.1 Nom

**BuildFlow**

## 1.2 Type

Application **SaaS Web + PWA** (Progressive Web App) — accessible navigateur et mobile, installable sur Android/iOS sans store.

## 1.3 Cible

| Segment            | Description                                                   |
|--------------------|---------------------------------------------------------------|
| PME BTP            | Entreprises de 5 à 100 employés, plusieurs chantiers actifs   |
| Artisans           | Indépendants gérant 1 à 5 chantiers simultanément             |
| Entrepreneurs BTP  | Sociétés de construction de bâtiments & infrastructure        |
| Sociétés techniques | Électricité, plomberie, climatisation, menuiserie            |
| Maîtres d'œuvre    | Pilotage de chantiers pour compte de tiers                    |

## 1.4 Zone géographique prioritaire

- **Madagascar** (marché primaire — Ariary MGA)
- **Afrique francophone** : Côte d'Ivoire, Sénégal, Cameroun, RDC, etc.
- Adaptable à d'autres marchés francophones

## 1.5 Contexte & problématique

Le secteur BTP en Afrique francophone souffre d'une gestion encore très manuelle :
- Suivi des dépenses sur Excel ou papier
- Facturation sans traçabilité des paiements
- Pertes financières dues à l'absence de pilotage en temps réel
- Difficultés à mesurer la rentabilité réelle d'un chantier
- Communication terrain/bureau déficiente

**BuildFlow** répond à ces défis en proposant un outil digital, simple et adapté aux réalités locales (connectivité limitée, Mobile Money, multi-régions).

---

# 2. Objectifs & Valeur métier

## 2.1 Objectifs fonctionnels

- Centraliser intégralement la gestion de chantier (de l'offre à la clôture)
- Suivre toutes les dépenses (**ACHATS**) en temps réel
- Suivre tous les revenus (**VENTES** : devis, factures, encaissements)
- Calculer la rentabilité réelle par chantier et globalement
- Gérer les ressources humaines (salariés, sous-traitants)
- Gérer les équipements et stocks
- Digitaliser le terrain (pointage, photos, rapports, documents)
- Gérer la relation client (CRM léger)

## 2.2 Valeur ajoutée différenciante

| Fonctionnalité               | Valeur                                               |
|------------------------------|------------------------------------------------------|
| Tableau de bord financier    | Vision rentabilité en temps réel                     |
| Bibliothèque de prix locale  | Chiffrage rapide adapté au marché malgache           |
| Mobile Money intégré         | Réalité des paiements locaux (MVola, Orange Money)   |
| PWA hors-ligne               | Utilisable sur chantier sans internet                |
| Multi-rôles granulaires      | Sécurité et délégation adaptées aux PME              |
| Export PDF professionnel     | Devis, factures, rapports présentables aux clients   |

---

# 3. Concept clé

## 3.1 Équation financière d'un chantier

```
Un chantier = ACHATS (coûts) + VENTES (revenus)
              ↓                   ↑
         Ce qu'on dépense    Ce qu'on encaisse

Rentabilité = VENTES − ACHATS
```

## 3.2 Cycle de vie d'un chantier

```
PROSPECTION → DEVIS → COMMANDE → EXÉCUTION → RÉCEPTION → CLÔTURE
     ↓            ↓        ↓           ↓            ↓         ↓
  Prospect     Devis    Contrat    Suivi coûts   PV Récep.  Bilan
   Client      PDF      Signé     + Pointage    + Solde    Financier
```

---

# 4. Architecture technique

## 4.1 Stack technologique

| Couche           | Technologie          | Justification                                    |
|------------------|----------------------|--------------------------------------------------|
| **Backend**      | Laravel 11 (PHP 8.3) | Robuste, ecosystème riche, ORM Eloquent          |
| **API**          | Laravel API REST + Sanctum | Auth token sécurisée pour PWA/API        |
| **Frontend**     | Blade + Bootstrap 5  | Rendu serveur rapide, faible bande passante      |
| **Interactivité**| Alpine.js / Livewire | Réactivité légère sans SPA complète              |
| **Base de données** | MySQL 8.0         | Fiabilité, relations complexes, JSON columns     |
| **Cache**        | Redis                | Sessions, queues, cache requêtes lourdes         |
| **Files d'attente** | Laravel Queues (Redis) | Export PDF, emails, notifications async    |
| **Stockage**     | Local + S3-compatible | Documents, photos, PDF générés                 |
| **PDF**          | DomPDF / Snappy      | Génération devis, factures, rapports             |
| **Mobile**       | PWA (Service Worker) | Offline, installable, camera access             |
| **Emails**       | SMTP / Mailgun       | Notifications, envoi devis/factures              |
| **Serveur**      | Nginx + PHP-FPM      | Production Ubuntu/Debian                         |

## 4.2 Architecture applicative

```
┌─────────────────────────────────────────────────────────┐
│                    CLIENT (Browser/PWA)                  │
│         Bootstrap 5 + Blade + Alpine.js                  │
└──────────────────────┬──────────────────────────────────┘
                       │ HTTPS
┌──────────────────────▼──────────────────────────────────┐
│                  NGINX (Reverse Proxy)                   │
└──────────────────────┬──────────────────────────────────┘
                       │
┌──────────────────────▼──────────────────────────────────┐
│              Laravel Application (PHP-FPM)               │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌───────────┐  │
│  │ Auth     │ │ API REST │ │ Modules  │ │  Queue    │  │
│  │ Sanctum  │ │ (Mobile) │ │ BTP      │ │  Worker   │  │
│  └──────────┘ └──────────┘ └──────────┘ └───────────┘  │
└────────┬─────────────────────────────────┬──────────────┘
         │                                 │
┌────────▼──────────┐          ┌───────────▼──────────────┐
│   MySQL 8.0       │          │   Redis                  │
│   (Données)       │          │   (Cache + Queues)       │
└───────────────────┘          └──────────────────────────┘
                                           │
                              ┌────────────▼─────────────┐
                              │   Stockage Fichiers       │
                              │   (Local/S3 compatible)   │
                              └──────────────────────────┘
```

## 4.3 Sécurité de l'architecture

- **HTTPS obligatoire** — TLS 1.2+
- **Authentification** — Laravel Sanctum (tokens) + sessions web
- **Autorisation** — Spatie Laravel Permission (RBAC)
- **Séparation des tenants** — par `company_id` sur chaque table
- **Validation** — Form Requests Laravel, côté serveur systématiquement
- **Rate limiting** — protection API contre brute force

---

# 5. Modèle SaaS & Multi-tenant

## 5.1 Architecture Multi-tenant

BuildFlow est **multi-tenant** : plusieurs entreprises (tenants) utilisent la même instance, avec isolation complète des données.

- Stratégie : **Single Database + company_id** (pragmatique pour le marché cible)
- Chaque table métier contient `company_id` (clé étrangère vers `companies`)
- Middleware Laravel vérifie automatiquement le tenant sur chaque requête

## 5.2 Plans d'abonnement (SaaS)

| Plan         | Cible              | Chantiers | Utilisateurs | Prix/mois (indicatif) |
|--------------|--------------------|-----------|--------------|-----------------------|
| **Gratuit**  | Test / Artisan solo | 2         | 1            | 0 MGA                 |
| **Starter**  | Artisan PME        | 10        | 5            | 50 000 MGA            |
| **Pro**      | PME BTP            | Illimité  | 20           | 150 000 MGA           |
| **Enterprise**| Grande entreprise | Illimité  | Illimité     | Sur devis             |

## 5.3 Gestion des entreprises (Tenants)

- Inscription de l'entreprise (nom, SIRET/NIF, adresse, région, secteur)
- Paramétrage initial (devise, TVA, logo, informations légales)
- Gestion de la licence et date d'expiration
- Tableau de bord Super-Admin (gestion globale des tenants)

---

# 6. Modules fonctionnels

---

## MODULE 1 — Authentification & Sécurité

### 1.1 Fonctionnalités

| Fonctionnalité              | Description                                                  |
|-----------------------------|--------------------------------------------------------------|
| Login / Logout              | Email + mot de passe, session sécurisée                      |
| Réinitialisation mot de passe | Email avec lien temporaire (expire en 60 min)             |
| Authentification 2FA        | Code OTP par email ou SMS (optionnel, activable par admin)   |
| Remember Me                 | Token persistant 30 jours (optionnel)                        |
| Verrouillage compte         | Après 5 tentatives échouées (déverrouillage auto 15 min)     |
| Journal de connexion        | IP, date, navigateur, succès/échec                           |

### 1.2 Gestion des rôles et permissions

**Rôles système (prédéfinis) :**

| Rôle             | Accès                                                    |
|------------------|----------------------------------------------------------|
| Super Admin      | Gestion globale de la plateforme (multi-tenant)          |
| Admin Entreprise | Accès complet à son entreprise                           |
| Chef de chantier | Accès défini par l'Admin sur les modules autorisés       |
| Comptable        | Accès finances (devis, factures, paiements, rapports)    |
| Ouvrier/Terrain  | Pointage, photos, consultation chantiers assignés        |
| Lecture seule    | Consultation uniquement (client externe par exemple)     |

**Rôles personnalisés (créés par l'Admin) :**
- Nom du rôle libre
- Sélection module par module (checkbox) des droits : **Voir / Créer / Modifier / Supprimer**
- Affectation à un ou plusieurs utilisateurs
- Possibilité de restreindre à certains chantiers uniquement

---

## MODULE 2 — Gestion des utilisateurs

### 2.1 Fonctionnalités

- CRUD complet des utilisateurs de l'entreprise
- Invitation par email (lien d'activation)
- Assignation de rôle(s)
- Activation / désactivation d'un compte
- Réinitialisation du mot de passe par l'admin
- Profil utilisateur (nom, photo, contact, métier)
- Historique d'activité par utilisateur (audit trail)

### 2.2 Champs utilisateur

- Nom & prénom
- Email (identifiant unique)
- Téléphone
- Métier / poste
- Région d'intervention
- Rôle(s) assigné(s)
- Statut (actif / inactif)
- Date de création et dernière connexion

---

## MODULE 3 — Gestion des CLIENTS (CRM Léger)

### 3.1 Fonctionnalités

- CRUD clients
- Recherche et filtrage (nom, région, statut)
- Import en masse (CSV)
- Historique complet des interactions

### 3.2 Champs client

| Champ            | Type        | Obligatoire |
|------------------|-------------|-------------|
| Nom / Raison sociale | Texte   | ✅          |
| Type             | Particulier / Entreprise | ✅  |
| NIF/CIN          | Texte       | Non         |
| Téléphone        | Texte       | ✅          |
| Email            | Email       | Non         |
| Adresse          | Texte long  | Non         |
| Région           | Liste       | ✅          |
| Notes internes   | Texte long  | Non         |
| Statut           | Actif / Inactif | ✅      |

### 3.3 Fiche client (tableau de bord client)

- Liste des chantiers associés (avec statut et montant)
- Liste des devis (statut : brouillon, envoyé, accepté, refusé, expiré)
- Liste des factures (statut : brouillon, émise, partiellement payée, soldée)
- Historique des paiements reçus
- **Indicateurs financiers** :
  - Total devisé (Σ devis acceptés)
  - Total facturé
  - Total encaissé
  - Solde dû (total facturé − total encaissé)
- Export PDF de la fiche client

---

## MODULE 4 — Gestion des SALARIÉS

### 4.1 Fonctionnalités

- CRUD salariés
- Recherche et filtrage (métier, région, statut)
- Affectation à un ou plusieurs chantiers (many-to-many)
- Suivi des heures travaillées par chantier

### 4.2 Champs salarié

| Champ              | Type          | Obligatoire |
|--------------------|---------------|-------------|
| Nom & prénom       | Texte         | ✅          |
| CIN / identifiant  | Texte         | Non         |
| Téléphone          | Texte         | ✅          |
| Email              | Email         | Non         |
| Métier             | Liste (CRUD)  | ✅          |
| Tarif horaire      | Décimal (MGA) | Non         |
| Tarif journalier   | Décimal (MGA) | Non         |
| Région de base     | Liste         | ✅          |
| Type de contrat    | CDI/CDD/Journalier/Sous-traitant | ✅ |
| Date d'entrée      | Date          | Non         |
| Statut             | Actif / Inactif | ✅        |
| Notes              | Texte long    | Non         |

### 4.3 Règles métier

- Un salarié peut être affecté à **plusieurs chantiers simultanément**
- Un salarié peut travailler dans **toutes les régions**
- Le tarif peut être défini au niveau du salarié **ou** hérité du modèle de salaire de la région/métier
- Type **Sous-traitant** → référencé aussi dans la gestion des fournisseurs

### 4.4 Fiche salarié

- Chantiers actifs et historiques
- Total heures pointées par chantier
- Total salaire brut calculé
- Journal de pointage détaillé
- Documents (contrat, pièces d'identité)
- Indicateur de performance (taux de présence)

---

## MODULE 5 — Gestion des FOURNISSEURS *(nouveau)*

### 5.1 Objectif

Référencer tous les prestataires externes : fournisseurs de matériaux, loueurs de matériel, sous-traitants.

### 5.2 Fonctionnalités

- CRUD fournisseurs
- Catégorisation (matériaux, location matériel, sous-traitance, transport, divers)
- Lien avec les bons de commande
- Historique des achats par fournisseur

### 5.3 Champs fournisseur

| Champ              | Type        |
|--------------------|-------------|
| Raison sociale     | Texte       |
| NIF/STAT           | Texte       |
| Catégorie          | Liste       |
| Téléphone          | Texte       |
| Email              | Email       |
| Adresse            | Texte       |
| Région             | Liste       |
| Conditions paiement | Texte      |
| Notes              | Texte long  |

### 5.4 Fiche fournisseur

- Bons de commande émis
- Total achats par période
- Historique des paiements effectués
- **Indicateurs dettes fournisseurs** :
  - Total dépenses / BC non encore réglés (dettes en cours)
  - Total réglé sur la période
  - Solde dû au fournisseur

### 5.5 Module Dettes fournisseurs *(nouveau)*

- Vue globale « Comptes fournisseurs » : liste de toutes les dépenses / BC avec mode de règlement non encore enregistré
- Enregistrement des paiements fournisseurs (date, montant, mode, référence)
- Balance âgée fournisseurs : regroupement par tranche de retard (0-30j, 30-60j, +60j)

---

## MODULE 6 — Gestion des CHANTIERS

### 6.1 Création d'un chantier

| Champ              | Type               | Obligatoire |
|--------------------|--------------------|-------------|
| Référence auto     | AUTO (BF-2026-001) | ✅          |
| Nom                | Texte              | ✅          |
| Client             | Relation           | ✅          |
| Description        | Texte long         | Non         |
| Adresse chantier   | Texte              | Non         |
| Région             | Liste              | ✅          |
| Coordonnées GPS    | Lat/Long           | Non         |
| Budget prévisionnel| Décimal (MGA)      | Non         |
| Date de début      | Date               | ✅          |
| Date de fin prév.  | Date               | Non         |
| Statut             | Enum               | ✅          |
| Photo de couverture| Image              | Non         |
| Notes internes     | Texte long         | Non         |

**Statuts chantier :** `Prospection` → `Devis envoyé` → `En cours` → `En pause` → `Terminé` → `Clôturé` → `Annulé`

### 6.2 Affectations

- Salariés affectés (avec rôle sur le chantier : chef, ouvrier, technicien…)
- Matériels affectés (avec dates d'affectation)
- Sous-traitants affectés
- Modèles de prix appliqués (matériaux, salaires)

### 6.3 Fiche chantier (tableau de bord chantier)

**Section Informations générales :**
- Données de base + client + équipe

**Section Financière (synthèse) :**

| Indicateur              | Calcul                                     |
|-------------------------|--------------------------------------------|
| Budget prévisionnel     | Saisi à la création                        |
| Total ACHATS réels      | Σ dépenses validées                        |
| Total VENTES facturées  | Σ factures émises                          |
| Total encaissé          | Σ paiements reçus                          |
| Reste à encaisser       | Total facturé − Total encaissé             |
| Bénéfice brut           | Total VENTES − Total ACHATS                |
| Taux de marge           | (Bénéfice / Total VENTES) × 100            |
| Écart budget            | Budget prév. − Total ACHATS réels          |
| Avancement financier    | Total encaissé / Total facturé (%)         |

**Onglets de la fiche :**
1. Résumé & indicateurs
2. ACHATS (dépenses)
3. Bons de commande
4. VENTES (devis / factures / paiements)
5. Situations de travaux
6. Avenants
7. Salariés & Pointage
8. Tâches & Planning
9. Documents
10. Photos
11. Compte-rendus de chantier
12. Historique & Journal d'activité

**Export :**
- Export PDF fiche chantier complète
- Export Excel synthèse financière
- Rapport de clôture chantier (PDF)

---

## MODULE 7 — ACHATS (Dépenses chantier)

### 7.1 Objectif

Enregistrer et catégoriser toutes les dépenses liées à un chantier pour connaître le coût réel.

### 7.2 Fonctionnalités

- Ajout de dépense (manuelle ou depuis bon de commande)
- Modification et suppression (avec justification)
- Filtrage par catégorie, période, statut
- Validation des dépenses par le chef ou l'admin (workflow optionnel)
- Import CSV de dépenses multiples
- Statistiques par catégorie (graphique)

### 7.3 Catégories de dépenses

| Catégorie         | Exemples                                            |
|-------------------|-----------------------------------------------------|
| Matériaux         | Ciment, sable, fer, briques, peinture…              |
| Main d'œuvre      | Salaires ouvriers, primes                           |
| Location matériel | Grue, bétonnière, échafaudage                       |
| Transport         | Livraison matériaux, déplacement équipe             |
| Sous-traitance    | Électricité, plomberie, carrelage                   |
| Carburant         | Groupes électrogènes, engins                        |
| Frais divers      | Repas, hébergement, imprévus                        |
| Sécurité          | EPI, signalisation                                  |

### 7.4 Champs d'une dépense

| Champ             | Type          | Obligatoire |
|-------------------|---------------|-------------|
| Chantier          | Relation      | ✅          |
| Date              | Date          | ✅          |
| Catégorie         | Liste         | ✅          |
| Description       | Texte         | ✅          |
| Fournisseur       | Relation      | Non         |
| Quantité          | Décimal       | Non         |
| Unité             | Texte         | Non         |
| Prix unitaire     | Décimal (MGA) | Non         |
| Montant total     | Décimal (MGA) | ✅          |
| Mode de paiement  | Enum          | Non         |
| Justificatif      | Fichier/Photo | Non         |
| Statut validation | Enum          | ✅          |
| Notes             | Texte long    | Non         |

### 7.5 Bons de commande *(nouveau)*

Avant de déclencher une dépense, possibilité d'émettre un **Bon de Commande (BC)** vers un fournisseur :

- Numérotation automatique (BC-2026-001)
- Lignes de commande (article, quantité, prix unitaire, total)
- Statut : `Brouillon` → `Envoyé` → `Partiellement livré` → `Livré` → `Annulé`
- Conversion BC → dépense à la réception
- Export PDF bon de commande

---

## MODULE 8 — VENTES (Devis, Factures, Paiements)

### 8.1 Devis

**Fonctionnalités :**
- Création devis depuis la fiche chantier ou la fiche client
- Numérotation automatique (DEV-2026-001)
- Utilisation des modèles de prix (bibliothèque interne)
- Lignes de devis : matériaux, main d'œuvre, sous-traitance, frais divers
- Regroupement en sections/lots (ex : Gros œuvre, Second œuvre, Finitions)
- Calcul automatique HT, TVA (paramétrable), TTC
- Application d'une remise globale ou par ligne
- Mentions légales personnalisables
- **Statuts :** `Brouillon` → `Envoyé` → `Accepté` → `Refusé` → `Expiré` → `Transformé en facture`
- Date d'expiration du devis
- Export PDF professionnel (logo, couleurs entreprise)
- Envoi par email directement depuis l'application
- Signature numérique client (lien de validation)
- Duplication de devis
- **Versioning des devis** : chaque modification significative d'un devis `Envoyé` crée une nouvelle version (V1, V2, V3…) ; les versions précédentes sont archivées en lecture seule ; numéro de version affiché dans le PDF (DEV-2026-001 — V3)

**Champs ligne devis :**

| Champ            | Type           |
|------------------|----------------|
| Désignation      | Texte          |
| Section/Lot      | Texte (optionnel) |
| Quantité         | Décimal        |
| Unité (m², ml, u, h) | Liste     |
| Prix unitaire    | Décimal (MGA)  |
| Remise ligne (%) | Décimal        |
| TVA ligne (%)    | Décimal        |
| Montant ligne    | Calculé auto   |

### 8.2 Avenants / Ordres de Modification *(nouveau)*

Les chantiers évoluent souvent par rapport au devis initial :

- Création d'un avenant lié à un devis/contrat existant
- Numérotation automatique (AVN-2026-001)
- Description des modifications (ajouts, suppressions, modifications)
- Montant supplémentaire ou déduction
- Validation client (même workflow que le devis)
- Intégration au total facturable du chantier
- Export PDF avenant

### 8.3 Situations de travaux (Facturation progressive) *(nouveau)*

Pour les chantiers de longue durée, facturation par tranches d'avancement :

- Création de situation liée à un chantier et ses devis
- Numérotation (SIT-2026-001-S01, S02…)
- Avancement par ligne du devis (% réalisé)
- Calcul automatique du montant à facturer (% × montant devis)
- Déduction des situations précédentes
- Gestion de la **retenue de garantie** (RG — pourcentage paramétrable, libérable à la réception)
- Génération de la facture de situation
- Export PDF situation de travaux

### 8.4 Facturation

**Génération de facture :**
- Depuis un devis accepté (transformation en 1 clic)
- Depuis une situation de travaux
- Facture manuelle (hors devis)
- Numérotation automatique (FAC-2026-001)
- Lignes identiques au devis + possibilité de modification
- **Types :** Facture simple, Facture d'acompte, Facture de situation, Facture finale, Avoir
- Calcul HT / TVA / TTC
- Retenue de garantie affichée si applicable
- Mentions légales et conditions de paiement
- **Statuts :** `Brouillon` → `Émise` → `Partiellement payée` → `Soldée` → `Annulée`
- Export PDF
- Envoi par email

**Avoir :**
- Création d'avoir lié à une facture
- Imputation sur solde client

### 8.5 Paiements

**Enregistrement paiement :**
- Un paiement est lié à une ou plusieurs factures (ventilation)
- Date, montant, référence, mode de paiement

> ⚠️ **Note importante :** Les modes de paiement ci-dessous sont **purement indicatifs et informatifs**. Ils sont enregistrés en texte libre à titre de traçabilité uniquement. BuildFlow **n'intègre aucune passerelle de paiement** et ne traite aucune transaction financière. Le règlement s'effectue entièrement hors de l'application entre les parties concernées.

**Modes de paiement (champ indicatif — liste non exhaustive) :**

| Mode              | Description                    |
|-------------------|--------------------------------|
| Espèces           | Cash sur place                 |
| Chèque            | Chèque bancaire                |
| Virement bancaire | Virement compte à compte       |
| MVola             | Mobile Money Telma             |
| Orange Money      | Mobile Money Orange Madagascar |
| Airtel Money      | Mobile Money Airtel            |
| Autre             | Valeur libre saisie par l'utilisateur |

- Référence de transaction (numéro chèque, référence virement/MM) — champ texte libre, informatif
- Justificatif (photo, scan) — optionnel, à titre de preuve interne
- Multi-paiements sur une même facture
- Remboursements

**Suivi financier client/chantier :**
- Total facturé
- Total encaissé
- Reste à payer
- Retenue de garantie bloquée / libérée

---

## MODULE 9 — Bilan & Clôture de chantier *(nouveau)*

### 9.1 PV de réception

À la fin des travaux, génération d'un **Procès-Verbal de Réception** :
- Date de réception
- Réserves éventuelles (liste des points à corriger)
- Délai de levée des réserves
- Signature du client (numérique ou manuelle)
- Libération de la retenue de garantie après levée des réserves
- Export PDF

### 9.2 Rapport de clôture financière

Généré automatiquement à la clôture :
- Récapitulatif budget prévu vs réel
- Décomposition des achats par catégorie
- Chiffre d'affaires facturé vs encaissé
- Bénéfice final et taux de marge
- Comparaison devis initial vs coût réel
- Écarts et commentaires

---

## MODULE 10 — Compte-rendus de chantier *(nouveau)*

### 10.1 Objectif

Formaliser les réunions et visites de chantier.

### 10.2 Fonctionnalités

- Création de compte-rendu lié à un chantier
- Numérotation automatique (CR-2026-001)
- Date, lieu, participants
- Ordre du jour
- Points abordés (texte structuré)
- Décisions prises
- Actions à mener (responsable + délai)
- Prochaine réunion
- Photos jointes
- Export PDF
- Envoi par email aux participants

---

## MODULE 11 — Tâches & Gestion de projet

### 11.1 Fonctionnalités

- Création de tâches liées à un chantier
- Assignation à un ou plusieurs salariés
- Priorité (haute, normale, basse)
- Date d'échéance
- Statut : `À faire` → `En cours` → `Bloquée` → `Terminée` → `Annulée`
- Sous-tâches (checklist)
- Commentaires sur la tâche
- Pièces jointes
- Notifications à l'assigné
- Vue par chantier (liste + kanban)

### 11.2 Suivi d'avancement global

- Pourcentage d'avancement du chantier (basé sur les tâches terminées)
- Alertes tâches en retard

---

## MODULE 12 — Planning & Calendrier

### 12.1 Fonctionnalités

- Calendrier global (tous chantiers)
- Calendrier par chantier
- Affectation salariés / tâches à des plages horaires
- Vue semaine / mois
- Détection des conflits de planning (salarié affecté deux fois)
- Export PDF planning hebdomadaire
- Vue Gantt simplifiée par chantier *(optionnel V2)*
- Synchronisation avec calendrier externe (Google Calendar) *(V2)*

---

## MODULE 13 — Pointage (Présences)

### 13.1 Objectif

Suivre les heures de présence des salariés sur chantier.

### 13.2 Fonctionnalités

- Pointage d'entrée (check-in) et sortie (check-out) par le salarié ou le chef
- Via application mobile (PWA)
- **Géolocalisation GPS** à la prise en compte (enregistrée, non bloquante)
- Saisie manuelle par le chef de chantier
- Calcul automatique des heures travaillées
- Validation / correction des pointages par chef ou admin
- Absence justifiée / non justifiée
- Récapitulatif mensuel par salarié

### 13.3 Intégration paie

- Calcul salaire brut automatique (heures × tarif horaire ou jours × tarif journalier)
- Génération bulletin de salaire simplifié (PDF)
- Export CSV pour logiciel de paie externe

---

## MODULE 14 — Documents chantier

### 14.1 Fonctionnalités

- Upload fichiers (PDF, DOCX, XLSX, images, plans DWG…)
- Organisation en dossiers/catégories
- Taille max configurable par fichier
- Prévisualisation en ligne (PDF, images)
- Partage par lien sécurisé (lien avec expiration)
- Versionning (possibilité de remplacer un document en gardant l'historique)

### 14.2 Catégories de documents

- Plans & dessins techniques
- Contrats & devis
- Autorisation de construire / permis
- Rapports de chantier
- Photos & vidéos
- Certificats & procès-verbaux
- Factures fournisseurs
- **Bons de livraison** *(lié optionnellement à un BC ou une dépense)*
- Divers

---

## MODULE 15 — Photos & Galerie

### 15.1 Fonctionnalités

- Capture photo directe depuis PWA (caméra mobile)
- Upload depuis galerie mobile ou ordinateur
- Tagging (catégorie, phase du chantier, localisation)
- Commentaire sur chaque photo
- Galerie par chantier avec vue grille
- Comparaison avant/après *(V2)*
- Compression automatique (optimisation bande passante)
- Téléchargement ZIP de la galerie
- Intégration dans les rapports et PV

---

## MODULE 16 — Matériels & Équipements

### 16.1 Inventaire

- CRUD matériels/équipements (nom, catégorie, immatriculation, valeur)
- État : `Disponible` / `En service` / `En maintenance` / `Hors service`
- Historique des affectations par chantier

### 16.2 Affectation chantier

- Affectation d'un matériel à un chantier (dates début/fin)
- Coût de location/amortissement par jour (imputable aux ACHATS)
- Alerte de fin d'affectation

### 16.3 Maintenance

- Enregistrement des interventions de maintenance
- Prochain entretien (kilométrage ou date)
- Alerte maintenance préventive

---

## MODULE 17 — Gestion des STOCKS *(matériaux)*

### 17.1 Typologie des dépôts
- **Dépôts Fixes :** Magasins centraux ou régionaux (non liés à un chantier spécifique).
- **Dépôts de Chantier :** Espaces de stockage temporaires rattachés à un projet (`project_id`). Possibilité de création automatique d'un dépôt à l'ouverture du chantier.

### 17.2 Flux et Approvisionnement
- **Entrées de stock :**
    - **Achat Direct :** Réception d'une livraison fournisseur directement sur le chantier ou au dépôt central.
    - **Transfert Inter-dépôts :** Approvisionnement d'un chantier depuis le dépôt central ou un autre chantier.
- **Sorties de stock :** Consommation réelle des matériaux sur le terrain (imputable aux tâches/lots).
- **Inventaire :** Suivi en temps réel de la balance par dépôt et globalement.
- **Stock minimum :** Alertes de rupture paramétrables par dépôt.
- **Valorisation :** Prix d'Achat Moyen Pondéré (PAMP) calculé par dépôt ou par entreprise.

---

## MODULE 18 — Notifications

### 18.1 Types de notifications

| Événement                                       | Canal             | Seuil / délai             |
|-------------------------------------------------|-------------------|---------------------------|
| Devis accepté / refusé                          | In-app + Email    | —                         |
| Facture échéance dépassée                       | In-app + Email    | J+0, relance J+7, J+14    |
| Paiement reçu                                   | In-app            | —                         |
| Tâche assignée                                  | In-app + Email    | —                         |
| Tâche en retard                                 | In-app + Email    | J+0 puis quotidien        |
| Stock sous seuil minimum                        | In-app            | Seuil paramétrable        |
| Pointage oublié (fin de journée)                | In-app            | —                         |
| Chantier terminé                                | In-app + Email    | —                         |
| Retenue de garantie libérable                   | In-app + Email    | —                         |
| Maintenance matériel due                        | In-app            | J-7                       |
| **ACHATS > seuil % du budget prévisionnel**     | In-app + Email    | 80% par défaut (paramétrable par chantier) |
| **ACHATS > 100% du budget prévisionnel**        | In-app + Email    | Fixe                      |
| **Date de fin prévisionnelle dépassée (chantier)** | In-app + Email | J+0 puis relance J+7      |

### 18.2 Paramétrage

- Activation/désactivation par type de notification
- Paramétrage des délais (ex : rappel facture J-7, J-3, J+0)
- Notification push PWA (service worker)

---

## MODULE 19 — Dashboard & Rapports

### 19.1 Dashboard principal (vue entreprise)

**Indicateurs financiers globaux :**
- Chiffre d'affaires total (mois en cours / cumul annuel)
- Total dépenses (mois en cours / cumul annuel)
- Bénéfice brut global
- Taux de marge moyen
- Factures impayées (total + liste)
- Devis en attente de validation

**Indicateurs chantiers :**
- Nombre de chantiers actifs / terminés / en pause
- Top 5 chantiers les plus rentables
- Top 5 chantiers les plus déficitaires
- Avancement moyen des chantiers actifs

**Section Rentabilité en direct :** *(nouveau)*
- Liste des chantiers actifs avec leur marge actuelle (%)
- Code couleur : vert (marge > 20%), orange (0–20%), rouge (marge < 0%)
- Barre de progression « Budget consommé » par chantier
- Indicateur santé financière affiché dans l'en-tête de chaque fiche chantier :
  `Résultat = +X MGA | Marge = Y% | Budget : Z% consommé`

**Graphiques :**
- Évolution CA mensuelle (courbe)
- Répartition dépenses par catégorie (camembert)
- Chantiers par statut (barres)

### 19.2 Rapports exportables

| Rapport                         | Format       |
|---------------------------------|--------------|
| Bilan financier entreprise      | PDF + Excel  |
| Suivi financier par chantier    | PDF + Excel  |
| État des factures (balance âgée)| PDF + Excel  |
| Journal des dépenses            | PDF + Excel  |
| Récapitulatif des pointages     | PDF + Excel  |
| Inventaire stock                | PDF + Excel  |
| Rapport de clôture chantier     | PDF          |

---

## MODULE 20 — PWA & Mobile

### 20.1 Fonctionnalités PWA

- Installable sur Android et iOS (via navigateur, sans store)
- Interface responsive mobile-first
- Icône sur écran d'accueil
- Écran de démarrage (splash screen)

### 20.2 Mode hors-ligne

**Fonctionnalités disponibles hors-ligne :**
- Consultation chantiers assignés (données mises en cache)
- Saisie de pointage (synchronisé à la reconnexion)
- Ajout de photos (stockage local, envoi différé)
- Saisie de dépenses (synchronisation différée)
- Consultation des tâches assignées

**Stratégie de synchronisation :**
- Service Worker (Cache API) pour les assets statiques
- IndexedDB pour les données offline
- Synchronisation automatique à la reconnexion (Background Sync API)
- Indication visuelle du mode offline (bandeau)
- Gestion des conflits de synchronisation (last-write-wins avec alerte)

---

## MODULE 21 — Paramètres entreprise

- Informations de l'entreprise (nom, logo, NIF, adresse, contacts)
- Informations légales pour les documents (mentions légales, RIB…)
- Devise (MGA par défaut, paramétrable)
- Taux de TVA par défaut
- Numérotation des documents (préfixe, numéro de départ)
- Modèles d'emails (devis, facture…)
- Formules de calcul (marges, coefficients, frais généraux)
- Gestion des régions
- **Relance automatique des impayés** *(nouveau)* :
  - Activation/désactivation globale
  - Séquence configurable : Relance 1 (J+7, email doux), Relance 2 (J+14, email ferme), Relance 3 (J+30, mise en demeure)
  - Template d'email modifiable par étape de relance
  - Option de désactivation par client
  - Log des relances envoyées (date, facture, étape)
- **Seuil d'alerte budget chantier** : pourcentage de déclenchement de l'alerte dépassement (défaut : 80%)
- **Règles de catégorisation automatique des dépenses** : mots-clés → catégorie suggérée (liste configurable)

---

# 7. Modèles de prix & Bibliothèques

## 7.1 Bibliothèque de matériaux

**Objectif :** Pré-remplir les devis rapidement avec des prix de référence du marché.

- CRUD articles (nom, catégorie, unité, prix de référence)
- Organisation par catégorie (gros œuvre, second œuvre, finitions…)
- Prix différenciés par **région** (les prix varient selon la zone géographique)
- Import/Export CSV de la bibliothèque

## 7.2 Bibliothèque de salaires

- Grille salariale par métier et par région
- Tarif horaire et journalier de référence

## 7.3 Règles clés

- Les prix sont **suggérés** lors de la saisie d'un devis ou d'une dépense
- Ils sont **modifiables en temps réel** sans contrainte
- Le prix réel saisi n'écrase pas le prix de référence

## 7.4 Historique des prix

Chaque modification de prix de référence est tracée :

| Champ          | Valeur                         |
|----------------|-------------------------------|
| Article        | Ciment Portland 50kg           |
| Ancien prix    | 18 000 MGA                    |
| Nouveau prix   | 20 000 MGA                    |
| Date           | 2026-04-01                    |
| Modifié par    | admin@buildflow.mg            |
| Région         | Antananarivo                  |

---

# 8. Calculs financiers chantier

## 8.1 Déboursé sec (coût direct)

$$DS = \sum Matériaux + \sum Main\,d'œuvre + \sum Matériel + \sum Sous\text{-}traitance + \sum Transport$$

## 8.2 Total ACHATS

$$ACHATS = DS + \sum Frais\,divers$$

## 8.3 Total VENTES facturées

$$VENTES = \sum Factures\,émises\,(HT)$$

## 8.4 Total encaissé

$$Encaissé = \sum Paiements\,reçus$$

## 8.5 Reste à encaisser

$$RAE = VENTES - Encaissé$$

## 8.6 Bénéfice brut chantier

$$Bénéfice = VENTES - ACHATS$$

## 8.7 Taux de marge

$$Taux\,marge = \frac{Bénéfice}{VENTES} \times 100$$

## 8.8 Taux de marque (mark-up)

$$Taux\,marque = \frac{Bénéfice}{ACHATS} \times 100$$

## 8.9 Retenue de garantie

$$RG = VENTES \times taux\,RG\,(\%)$$

$$Net\,à\,payer = Montant\,facture - RG$$

## 8.10 Écart budgétaire

$$Écart = Budget\,prévisionnel - ACHATS\,réels$$

---

# 9. Formules paramétrables

## 9.1 Paramètres configurables par l'Admin

| Paramètre         | Description                              | Exemple     |
|-------------------|------------------------------------------|-------------|
| Frais généraux    | % charges structure                      | 15%         |
| Marge souhaitée   | % bénéfice cible                         | 20%         |
| Aléas             | % risques imprévus                       | 5%          |
| TVA               | Taux TVA applicable                      | 20% (ou 0%) |
| Retenue garantie  | % RG sur chaque facture                  | 5%          |
| Coefficient K     | Multiplicateur prix de vente             | Calculé auto |

## 9.2 Coefficient de vente

$$K = \frac{1}{1 - (FG\% + Marge\% + Aléas\%)}$$

**Exemple :** FG=15%, Marge=20%, Aléas=5% → K = 1 / (1 - 0.40) = **1.667**

**Prix de vente suggéré :**

$$PV = DS \times K$$

## 9.3 Utilisation dans les devis

Lors de la création d'un devis, le système peut :
- Suggérer le prix de vente à partir du coût de revient × K
- Afficher en temps réel la marge générée en fonction du prix saisi

---

# 10. Base de données

## 10.1 Tables principales

### Tenancy & Auth

| Table             | Description                            |
|-------------------|----------------------------------------|
| `companies`       | Tenants (entreprises)                  |
| `plans`           | Plans d'abonnement                     |
| `subscriptions`   | Abonnements actifs                     |
| `users`           | Utilisateurs                           |
| `roles`           | Rôles (Spatie)                         |
| `permissions`     | Permissions granulaires                |
| `activity_logs`   | Audit trail complet                    |

### Référentiels

| Table              | Description                           |
|--------------------|---------------------------------------|
| `regions`          | Régions géographiques                 |
| `job_types`        | Métiers BTP                           |
| `expense_categories` | Catégories de dépenses              |
| `material_categories`| Catégories matériaux               |
| `unit_types`       | Unités (m², ml, u, h, j, kg, t…)     |

### CRM & RH

| Table              | Description                           |
|--------------------|---------------------------------------|
| `clients`          | Clients                               |
| `employees`        | Salariés                              |
| `suppliers`        | Fournisseurs                          |

### Chantiers

| Table              | Description                           |
|--------------------|---------------------------------------|
| `projects`         | Chantiers                             |
| `project_employees`| Pivot salariés ↔ chantiers            |
| `project_equipments`| Pivot matériels ↔ chantiers          |

### ACHATS

| Table              | Description                           |
|--------------------|---------------------------------------|
| `purchase_orders`  | Bons de commande                      |
| `purchase_order_items` | Lignes des bons de commande       |
| `expenses`         | Dépenses / achats réels               |

### VENTES

| Table              | Description                           |
|--------------------|---------------------------------------|
| `quotes`           | Devis                                 |
| `quote_sections`   | Sections/lots d'un devis              |
| `quote_items`      | Lignes de devis                       |
| `amendments`       | Avenants                              |
| `amendment_items`  | Lignes d'avenants                     |
| `progress_billings`| Situations de travaux                 |
| `invoices`         | Factures (et avoirs)                  |
| `invoice_items`    | Lignes de factures                    |
| `payments`         | Paiements reçus                       |
| `payment_allocations`| Ventilation paiement ↔ factures    |

### Production

| Table              | Description                           |
|--------------------|---------------------------------------|
| `tasks`            | Tâches                                |
| `task_comments`    | Commentaires sur tâches               |
| `attendances`      | Pointages (check-in/out + GPS)        |
| `site_reports`     | Compte-rendus de chantier             |
| `site_report_items`| Points/décisions du compte-rendu      |
| `documents`        | Documents chantier                    |
| `photos`           | Photos chantier                       |

### Équipements & Stocks

| Table              | Description                           |
|--------------------|---------------------------------------|
| `equipments`       | Matériels inventaire                  |
| `equipment_maintenances` | Historique maintenance          |
| `warehouses`       | Dépôts de stock                       |
| `materials`        | Articles en stock                     |
| `stock_movements`  | Mouvements entrées/sorties            |

### Pricing

| Table              | Description                           |
|--------------------|---------------------------------------|
| `material_models`  | Bibliothèque de prix matériaux        |
| `salary_models`    | Grilles de salaires                   |
| `price_history`    | Historique des modifications de prix  |
| `formula_settings` | Paramètres de calcul (K, TVA, RG…)   |

## 10.2 Champs communs à toutes les tables

```sql
id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
company_id          BIGINT UNSIGNED NOT NULL  -- Multi-tenant
created_by          BIGINT UNSIGNED           -- Traçabilité
updated_by          BIGINT UNSIGNED           -- Traçabilité
created_at          TIMESTAMP
updated_at          TIMESTAMP
deleted_at          TIMESTAMP NULL            -- Soft delete
```

---

# 11. UX/UI & Design System

## 11.1 Principes de design

| Principe          | Application                                          |
|-------------------|------------------------------------------------------|
| Mobile-first      | Interface conçue pour smartphone avant desktop       |
| Clarté            | Actions primaires immédiatement visibles             |
| Rapidité          | Formulaires courts, saisie rapide sur terrain        |
| Accessibilité     | Contraste suffisant, boutons larges (tactile)        |
| Cohérence         | Design system unifié sur tous les modules            |

## 11.2 Stack UI

- **Bootstrap 5.3** — grille, composants de base
- **Bootstrap Icons** — iconographie cohérente
- **Alpine.js** — interactions légères sans rechargement
- **Chart.js** — graphiques dashboard
- **Flatpickr** — sélecteurs de dates
- **Tom Select** — selects avancés avec recherche
- **Dropzone.js** — upload de fichiers drag & drop

## 11.3 Navigation

- Menu latéral (sidebar) sur desktop — rétractable
- Menu hamburger sur mobile
- Barre de navigation rapide (actions fréquentes)
- Fil d'Ariane (breadcrumb) sur toutes les pages
- Recherche globale (chantier, client, facture)

## 11.4 Couleurs & Thème

- **Primaire :** Bleu professionnel (#2563EB)
- **Succès :** Vert (#16A34A)
- **Danger :** Rouge (#DC2626)
- **Alerte :** Orange (#D97706)
- **Neutres :** Gris (#6B7280, #F3F4F6)
- **Fond :** Blanc / Gris très clair
- Logo et couleurs personnalisables par entreprise (white-label)

---

# 12. Sécurité

## 12.1 Authentification & Autorisation

- Mots de passe hashés avec **bcrypt** (coût 12)
- Tokens API signés (Laravel Sanctum)
- Expiration automatique des sessions inactives (configurable, défaut 8h)
- **CSRF protection** sur tous les formulaires
- **XSS protection** — échappement automatique Blade + Content-Security-Policy
- **SQL Injection** — utilisation exclusive de l'ORM Eloquent (requêtes préparées)
- **Rate limiting** : login (5 tentatives/min), API (60 req/min)
- **2FA optionnel** (TOTP — Google Authenticator, email OTP)

## 12.2 Multi-tenant & Isolation des données

- Middleware `EnsureTenant` : injecte automatiquement le `company_id` dans chaque requête
- Vérification systématique `company_id` dans les Policies Laravel
- Impossibilité pour un tenant d'accéder aux données d'un autre

## 12.3 Audit Trail

- Table `activity_logs` : enregistre toutes les actions critiques
  - Qui, Quoi, Quand, Depuis quelle IP
  - Création, modification, suppression, connexion/déconnexion
- Conservation 12 mois (configurable)
- Accessible par l'Admin dans les paramètres

## 12.4 Sécurité des fichiers

- Fichiers uploadés stockés hors webroot (non accessibles directement par URL)
- Accès via contrôleur Laravel avec vérification des droits
- Validation type MIME côté serveur (pas seulement l'extension)
- Taille maximale configurable
- Scan antivirus *(V2 — intégration ClamAV ou service tiers)*

## 12.5 Sauvegardes

- Backup base de données quotidien automatique (Spatie Laravel Backup)
- Backup stockage fichiers (sync S3 ou stockage distant)
- Rétention : 7 jours quotidiens + 4 semaines hebdomadaires + 12 mois mensuels
- Test de restauration trimestriel recommandé

## 12.6 HTTPS & Infrastructure

- HTTPS obligatoire (Let's Encrypt ou certificat commercial)
- En-têtes de sécurité HTTP (HSTS, X-Frame-Options, X-Content-Type-Options)
- Serveur à jour (patches de sécurité)
- Pare-feu applicatif (WAF) — recommandé en production

---

# 13. Performance & Scalabilité

## 13.1 Optimisations backend

- **Cache Redis** : requêtes lourdes (dashboard, statistiques) avec invalidation intelligente
- **Queues asynchrones** : génération PDF, envoi emails, notifications, synchro PWA
- **Eager loading** : prévention des requêtes N+1 (Laravel Debugbar en dev)
- **Pagination** : toutes les listes paginées (25 éléments par défaut)
- **Index SQL** : sur `company_id`, `project_id`, dates, statuts
- **Soft deletes** : conservation des données supprimées pour audit

## 13.2 Optimisations frontend

- **Compression images** : redimensionnement automatique à l'upload (max 1920px, WebP)
- **Assets compilés** : Vite (CSS + JS minifiés + versionnés)
- **Lazy loading** : images chargées à la demande
- **Service Worker** : mise en cache statiques (PWA)

## 13.3 Scalabilité

- Architecture stateless (sessions Redis) — prête pour multi-serveur
- Queues Redis — workers séparables
- Stockage S3-compatible — séparable du serveur applicatif
- Monitoring recommandé : Sentry (erreurs), New Relic ou Prometheus/Grafana (métriques)

---

# 14. PWA & Fonctionnement Hors-ligne

## 14.1 Stratégie cache Service Worker

| Type de ressource    | Stratégie              |
|----------------------|------------------------|
| Assets CSS/JS/polices | Cache First            |
| Pages HTML           | Network First          |
| API données chantier | Stale-While-Revalidate |
| Photos               | Cache First (limitée)  |

## 14.2 Données synchronisables hors-ligne

- Pointages (IndexedDB → sync au retour réseau)
- Photos capturées (stockage local → upload différé)
- Dépenses saisies (queue locale → sync)
- Tâches cochées (state local → sync)

## 14.3 Adaptations réseau faible

- Images compressées avant upload
- Pagination agressive (moins d'éléments)
- Feedback utilisateur clair (spinner, états de chargement)
- Retry automatique en cas d'échec réseau

---

# 15. Roadmap & Phases de déploiement

## Phase 1 — MVP (Mois 1–4)

**Objectif :** Produit utilisable en production sur les modules cœur

| Module                              | Priorité |
|-------------------------------------|----------|
| Authentification + Rôles            | ✅ P1    |
| Gestion utilisateurs                | ✅ P1    |
| Gestion clients                     | ✅ P1    |
| Gestion salariés                    | ✅ P1    |
| Gestion chantiers (CRUD + fiche)    | ✅ P1    |
| ACHATS (dépenses basiques)          | ✅ P1    |
| VENTES — Devis + Facturation        | ✅ P1    |
| Paiements                           | ✅ P1    |
| Dashboard basique                   | ✅ P1    |
| Export PDF devis & factures         | ✅ P1    |
| PWA (responsive + installable)      | ✅ P1    |
| Notifications in-app                | ✅ P1    |

## Phase 2 — V1.1 (Mois 5–7)

| Module                              | Priorité |
|-------------------------------------|----------|
| Tâches & Planning                   | ✅ P2    |
| Pointage GPS                        | ✅ P2    |
| Documents & Photos                  | ✅ P2    |
| Gestion fournisseurs                | ✅ P2    |
| Bons de commande                    | ✅ P2    |
| Situations de travaux               | ✅ P2    |
| Avenants                            | ✅ P2    |
| Bibliothèque de prix                | ✅ P2    |
| PWA hors-ligne (sync pointage/dépenses) | ✅ P2 |
| Compte-rendus de chantier           | ✅ P2    |
| Rapports & exports Excel            | ✅ P2    |

## Phase 3 — V2.0 (Mois 8–12)

| Module                              | Priorité |
|-------------------------------------|----------|
| Gestion stocks                      | P3       |
| Gestion matériels + maintenance     | P3       |
| PV de réception + clôture chantier  | P3       |
| Vue Gantt planning                  | P3       |
| Dashboard analytique avancé         | P3       |
| Synchronisation Google Calendar     | P3       |
| Module abonnements & facturation SaaS | P3     |
| API publique (intégrations tierces) | P3       |
| Application mobile native (React Native) | P3  |

## Phase 4 — V3.0 *(vision long terme)*

| Module                              |
|-------------------------------------|
| Intelligence artificielle (prévision coûts, alertes anomalies) |
| Marketplace sous-traitants          |
| Module appels d'offres              |
| Intégration comptabilité (Sage, etc.) |
| Application desktop (Electron)      |

---

# 16. Glossaire

| Terme                  | Définition                                                                 |
|------------------------|----------------------------------------------------------------------------|
| **Chantier**           | Projet de construction suivi dans BuildFlow                                |
| **ACHATS**             | Ensemble des dépenses d'un chantier (coûts)                                |
| **VENTES**             | Ensemble des revenus d'un chantier (devis, factures, encaissements)        |
| **Déboursé sec (DS)**  | Coût direct des travaux hors charges de structure                          |
| **Avenant**            | Modification au contrat/devis initial (travaux supplémentaires ou déduits) |
| **Situation de travaux** | Facture intermédiaire basée sur l'avancement du chantier (% réalisé)     |
| **Retenue de garantie (RG)** | % retenu sur chaque facture, libéré après réception sans réserves   |
| **PV de réception**    | Procès-Verbal constatant la fin des travaux et leur acceptation            |
| **Bon de commande**    | Document d'engagement d'achat envoyé à un fournisseur                     |
| **Compte-rendu**       | Rapport de réunion de chantier                                             |
| **MVola**              | Service Mobile Money de Telma (Madagascar)                                 |
| **Multi-tenant**       | Architecture où plusieurs entreprises partagent une même instance logicielle |
| **PWA**                | Progressive Web App — application web installable avec capacités offline   |
| **K (coefficient)**    | Multiplicateur appliqué au coût pour calculer le prix de vente             |
| **Taux de marge**      | (Bénéfice / CA) × 100 — mesure la rentabilité sur le chiffre d'affaires   |
| **PAMP**               | Prix d'Achat Moyen Pondéré — méthode de valorisation des stocks            |
| **MGA**                | Ariary Malgache — devise nationale de Madagascar                           |
