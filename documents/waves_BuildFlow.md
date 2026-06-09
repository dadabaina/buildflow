# 🌊 WAVES DE DÉVELOPPEMENT — BuildFlow

## Plan de livraison itératif

---

| Champ              | Valeur                          |
|--------------------|---------------------------------|
| **Projet**         | BuildFlow                       |
| **Version**        | 1.0                             |
| **Date**           | 10 Mai 2026                     |
| **Basé sur**       | CDC v2.0 + Specs v1.0           |
| **Méthodologie**   | Itératif par waves (2–3 semaines) |
| **Stack**          | Laravel 11 / Bootstrap 5 / PWA  |

---

# VISION GLOBALE DES WAVES

```
PHASE 1 — MVP (Mois 1–4)
├── Wave 0 — Setup & Infrastructure        [2 sem]
├── Wave 1 — Auth + Socle Multi-tenant     [2 sem]
├── Wave 2 — Clients + Salariés + Chantiers [3 sem]
├── Wave 3 — ACHATS (Dépenses)             [2 sem]
├── Wave 4 — VENTES (Devis + Facturation)  [3 sem]
└── Wave 5 — Paiements + Dashboard MVP     [2 sem]

PHASE 2 — V1.1 (Mois 5–7)
├── Wave 6 — Fournisseurs + Bons de commande [2 sem]
├── Wave 7 — Tâches + Planning             [2 sem]
├── Wave 8 — Pointage + Paie               [2 sem]
├── Wave 9 — Documents + Photos            [2 sem]
└── Wave 10 — Situations + Avenants + Bibliothèque prix [3 sem]

PHASE 3 — V2.0 (Mois 8–12)
├── Wave 11 — Compte-rendus + PV Réception + Clôture [2 sem]
├── Wave 12 — Matériels + Stocks           [3 sem]
├── Wave 13 — Notifications complètes      [2 sem]
├── Wave 14 — Dashboard avancé + Rapports  [3 sem]
└── Wave 15 — PWA hors-ligne + SaaS/Abonnements [3 sem]

PHASE 4 — V3.0 (Vision long terme)
└── Wave 16+ — IA, Marketplace, Intégrations...
```

---

# LÉGENDE

| Symbole | Signification                   |
|---------|---------------------------------|
| 🏗️      | Infrastructure / Setup          |
| 🔐      | Sécurité / Auth                 |
| 👤      | Fonctionnalité utilisateur      |
| 💰      | Financier (ACHATS/VENTES)       |
| 📊      | Reporting / Dashboard           |
| 📱      | Mobile / PWA                    |
| 🔔      | Notifications                   |
| ✅      | Livrable de la wave             |
| 🧪      | Tests requis                    |

---

# PHASE 1 — MVP

---

## 🌊 WAVE 0 — Setup & Infrastructure
**Durée :** 2 semaines | **Priorité :** Fondation

### Objectif
Mettre en place l'environnement de développement, la structure du projet, le CI/CD et les fondations techniques avant tout développement fonctionnel.

### Tâches

#### 🏗️ Environnement & Tooling
- [x] Initialisation projet Laravel 11
- [x] Configuration `.env` (dev / staging / prod)
- [ ] Setup Docker Compose local (PHP 8.3, MySQL 8, Redis, Nginx)
- [x] Setup Vite (CSS/JS bundling, Bootstrap 5.3, Alpine.js)
- [ ] Configuration Debugbar (dev uniquement)
- [ ] Configuration Laravel Telescope (dev uniquement)

#### 🏗️ Base de données — Migrations fondatrices
- [x] Migration `companies` (tenants)
- [x] Migration `users` (avec `company_id`)
- [x] Migration `regions`
- [x] Migration `job_types`
- [x] Migration `unit_types`
- [x] Migration `expense_categories`
- [x] Migration `material_categories`
- [x] Seeders : données de test (1 entreprise, 2 users, 5 régions, métiers BTP courants)

#### 🏗️ Architecture multi-tenant
- [x] Middleware `EnsureTenant` (injection `company_id`)
- [x] Scope global Eloquent `CompanyScope` sur tous les modèles métier
- [x] Helper `currentCompany()` disponible globalement
- [ ] Tests unitaires : isolation tenant (test cross-company → 404)

#### 🏗️ CI/CD & Qualité
- [ ] GitHub Actions (ou GitLab CI) : lint PHP (Laravel Pint), tests PHPUnit
- [ ] Pipeline : test → build assets → deploy staging
- [ ] Configuration PHPUnit (tests feature + unit)
- [ ] `.editorconfig` + conventions de code documentées

#### 🏗️ Layout de base
- [x] Layout Blade principal (sidebar, topbar, breadcrumb, flash messages)
- [x] Composants Blade réutilisables : table, card, badge, modal, form-group
- [ ] Page 403, 404, 500 personnalisées au branding BuildFlow
- [ ] Favicon, manifest PWA basique

#### ✅ Livrables Wave 0
- Projet Laravel installé et démarrable en `docker compose up`
- Base de données créée avec migrations et seeders
- Layout de base fonctionnel sur une page vide
- CI pipeline vert
- README développeur complet (setup, commandes, conventions)

---

## 🌊 WAVE 1 — Authentification & Multi-tenant
**Durée :** 2 semaines | **Specs :** SPEC-01, SPEC-02, SPEC-28

### Objectif
Permettre l'inscription d'une entreprise, la gestion des comptes utilisateurs et le contrôle d'accès par rôles.

### Tâches

#### 🔐 Authentification
- [x] Page Login (email + mot de passe)
- [x] Logout (invalidation session serveur)
- [x] Page "Mot de passe oublié" + email de réinitialisation
- [x] Page "Nouveau mot de passe" (lien à usage unique, expire 60 min)
- [ ] Verrouillage compte (5 tentatives / 15 min)
- [ ] Rate limiting login (5/min/IP)
- [x] Journal de connexions (table `login_logs`)
- [x] "Se souvenir de moi" (cookie 30 jours)
- [ ] Middleware `RedirectIfAuthenticated`

#### 🔐 Inscription SaaS
- [ ] Page d'inscription publique (nom entreprise, nom, email, MDP)
- [ ] Vérification email obligatoire (`MustVerifyEmail`)
- [ ] Création automatique du tenant (`companies`) + Admin Entreprise
- [ ] Wizard onboarding 3 étapes : infos entreprise, logo, régions (skip possible)
- [ ] Plan Gratuit assigné par défaut

#### 👤 Gestion des rôles & permissions
- [x] Installation Spatie Laravel Permission
- [x] Seeder : rôles système (Super Admin, Admin Entreprise, Chef Chantier, Comptable, Terrain, Lecture seule)
- [x] Seeder : permissions granulaires par module (voir, créer, modifier, supprimer)
- [ ] Interface Admin : CRUD rôles personnalisés
- [ ] Interface de configuration permissions (grille module × action, checkboxes)

#### 👤 Gestion des utilisateurs
- [x] CRUD utilisateurs (US-02-01, US-02-02)
- [ ] Invitation par email (lien activation 7 jours)
- [ ] Activation compte via lien → saisie mot de passe
- [ ] Activation / désactivation compte
- [x] Page profil utilisateur (US-02-03)
- [ ] Réinitialisation MDP par admin

#### 🏗️ Paramètres entreprise (fondation)
- [x] Page paramètres — onglet Informations entreprise
- [ ] Upload logo (stockage sécurisé)
- [ ] Devise, Taux TVA, Taux RG par défaut
- [x] Numérotation des documents (préfixes configurables)
- [x] CRUD Régions

#### 🧪 Tests
- [ ] Test : login réussi / login échoué / verrouillage
- [ ] Test : isolation multi-tenant (user A ne voit pas les données de company B)
- [ ] Test : permissions (rôle sans droit → 403)
- [ ] Test : inscription → email vérification → accès

#### ✅ Livrables Wave 1
- Login / Logout fonctionnel
- Inscription entreprise + onboarding
- Gestion utilisateurs + rôles + permissions
- Paramètres entreprise (basiques)
- Isolation multi-tenant validée par tests

---

## 🌊 WAVE 2 — Clients, Salariés & Chantiers
**Durée :** 3 semaines | **Specs :** SPEC-03, SPEC-04, SPEC-06

### Objectif
Permettre la création et gestion des entités métier fondamentales : clients, salariés et chantiers.

### Tâches

#### 👤 Gestion des clients (SPEC-03)
- [x] Migration `clients`
- [x] CRUD complet + validation (US-03-01)
- [ ] Recherche temps réel (debounce 300ms)
- [x] Filtres : région, statut, type
- [ ] Import CSV (avec rapport d'import)
- [x] Archivage (soft delete)
- [x] Fiche client : onglets + indicateurs financiers (US-03-02)
- [ ] Export PDF fiche client

#### 👤 Gestion des salariés (SPEC-04)
- [x] Migration `employees`
- [x] CRUD référentiel métiers (`job_types`)
- [x] CRUD salariés + validation (US-04-01)
- [ ] Recherche + filtres (métier, région, type contrat, statut)
- [x] Archivage
- [x] Fiche salarié (onglets — contenu rempli progressivement par les waves suivantes)

#### 🏗️ Gestion des chantiers (SPEC-06)
- [x] Migrations : `projects`, `project_employees`
- [x] CRUD chantiers (US-06-01)
- [x] Numérotation automatique : BF-AAAA-NNN
- [ ] Bouton "Ma position GPS" (Geolocation API)
- [x] Vue liste (tableau + cartes) avec filtres et tri (US-06-02)
- [x] Fiche chantier : structure onglets (US-06-03)
- [x] Gestion des statuts avec transitions validées (US-06-04)
- [ ] Export PDF fiche chantier
- [ ] Recherche globale chantiers

#### 🧪 Tests
- [ ] CRUD clients (validation, archivage, unicité)
- [ ] CRUD salariés (validation, règles métier)
- [ ] CRUD chantiers (numérotation, transitions statut)
- [ ] Affectation salarié ↔ chantier
- [ ] Import CSV clients (succès + erreurs)

#### ✅ Livrables Wave 2
- Module Clients opérationnel (CRUD + fiche + import)
- Module Salariés opérationnel (CRUD + fiche)
- Module Chantiers opérationnel (CRUD + fiche + statuts)
- Affectation équipe sur chantier

---

## 🌊 WAVE 3 — ACHATS (Dépenses)
**Durée :** 2 semaines | **Specs :** SPEC-07

### Objectif
Permettre l'enregistrement et le suivi complet des dépenses sur un chantier.

### Tâches

#### 💰 Module ACHATS (SPEC-07)
- [x] Migration `expenses` + `expense_categories` (seeder catégories par défaut)
- [x] CRUD dépenses (US-07-01)
- [x] Liste dépenses chantier avec filtres (US-07-02)
- [x] CRUD catégories de dépense (avec couleur, icône, statut actif)
- [ ] Récapitulatif par catégorie (tableau + graphique camembert Chart.js)
- [ ] Total général mis à jour en temps réel sur la fiche chantier
- [ ] Export PDF + Excel liste dépenses

#### 💰 Workflow validation dépenses (SPEC-07 US-07-03)
- [x] Statuts validation : Saisie / Validée / Rejetée
- [x] Interface de validation pour chef/admin
- [x] Motif de rejet obligatoire
- [ ] Paramètre entreprise : workflow actif/inactif
- [ ] Seules les dépenses `Validées` entrent dans le calcul (si workflow actif)

#### 🏗️ Mise à jour fiche chantier
- [x] Onglet ACHATS : liste dépenses + récapitulatif
- [ ] Bloc indicateurs : Total ACHATS réels se met à jour

#### 🧪 Tests
- [ ] CRUD dépenses (champs obligatoires, calcul automatique)
- [ ] Upload justificatif (stockage sécurisé, types acceptés)
- [ ] Workflow validation (états, notifications)
- [ ] Isolation tenant sur les dépenses

#### ✅ Livrables Wave 3
- Module ACHATS opérationnel avec workflow de validation
- Fiche chantier : Total ACHATS en temps réel
- Export dépenses PDF + Excel

---

## 🌊 WAVE 4 — VENTES (Devis & Facturation)
**Durée :** 3 semaines | **Specs :** SPEC-09, SPEC-12

### Objectif
Permettre la création de devis professionnels et leur transformation en factures.

### Tâches

#### 💰 Module Devis (SPEC-09)
- [x] Migrations : `quotes`, `quote_sections`, `quote_items`
- [x] CRUD devis avec toutes les fonctionnalités (US-09-01)
- [x] Gestion des statuts et transitions (US-09-02)
- [ ] Expiration automatique (Laravel Scheduler → job quotidien)
- [ ] Export PDF devis professionnel (DomPDF)
- [x] Envoi email devis (PDF en pièce jointe)
- [x] Lien de validation client (token unique, page publique "Accepter / Refuser")
- [ ] Notification admin à l'acceptation/refus client
- [ ] Duplication devis

#### 💰 Module Facturation (SPEC-12)
- [x] Migrations : `invoices`, `invoice_items`
- [x] Génération facture depuis devis accepté (1 clic) (US-12-01)
- [x] Facture manuelle (lignes libres)
- [ ] Facture d'acompte (% du devis)
- [x] Numérotation auto : FAC-AAAA-NNN
- [x] Calculs : HT / TVA / TTC / Retenue de garantie / Net à payer
- [x] Gestion des statuts facture
- [ ] Export PDF facture
- [x] Envoi email facture
- [ ] Module Avoir (US-12-02)

#### 🏗️ Mise à jour fiche chantier & client
- [x] Onglet VENTES de la fiche chantier : liste devis + factures
- [ ] Bloc indicateurs chantier : Total VENTES facturées
- [x] Fiche client : onglets devis + factures remplis

#### 🧪 Tests
- [ ] CRUD devis (lignes, sections, calculs, statuts)
- [ ] Calcul automatique HT/TVA/TTC (cas TVA 0%, 20%)
- [ ] Transformation devis → facture
- [ ] Export PDF (non-null, contient les bonnes données)
- [ ] Lien validation client (token valide, expiration, usage unique)
- [ ] Avoir (imputation solde)

#### ✅ Livrables Wave 4
- Module Devis complet avec validation client en ligne
- Module Facturation (simple, acompte, avoir)
- PDF professionnels devis et factures
- Envoi email depuis l'application

---

## 🌊 WAVE 5 — Paiements & Dashboard MVP
**Durée :** 2 semaines | **Specs :** SPEC-13, SPEC-23 (partiel), SPEC-24 (partiel)

### Objectif
Compléter le cycle financier avec les encaissements, et livrer le premier dashboard utilisable en production.

### Tâches

#### 💰 Module Paiements (SPEC-13)
- [x] Migrations : `payments`, `payment_allocations`
- [x] Enregistrement paiement (US-13-01)
- [ ] Balance âgée (US-13-02)
- [ ] Bloc indicateurs chantier : Total encaissé, Reste à payer, RG
- [ ] Fiche client : Total encaissé + Solde dû mis à jour

#### 🔔 Notifications basiques (SPEC-23 partiel)
- [ ] Migration `notifications` (polymorphique Laravel)
- [ ] Cloche notification dans topbar (badge compteur)
- [ ] Notifications in-app pour :
  - Devis accepté / refusé
  - Paiement reçu
  - Facture en retard (job planifié quotidien)
- [ ] Marquage lu/non lu

#### 📊 Dashboard MVP (SPEC-24 partiel)
- [x] Page dashboard (route `/dashboard`)
- [x] KPI cards : CA mois, Total dépenses mois, Bénéfice brut, Taux de marge
- [x] Alertes : factures en retard (nombre + montant total)
- [x] Liste 5 chantiers actifs récents
- [ ] Graphique CA mensuel (12 mois) — Chart.js ligne
- [ ] Graphique répartition dépenses par catégorie — Chart.js camembert
- [ ] Cache Redis sur les KPIs (invalidé à chaque nouvelle dépense/paiement)

#### 📱 PWA installable (SPEC-27 partiel)
- [ ] `manifest.json` complet (nom, icônes 192/512px, couleurs, orientation portrait)
- [ ] Service Worker : Cache First pour assets CSS/JS/polices
- [ ] Bannière installation (beforeinstallprompt)
- [ ] Splash screen et icône maison

#### 🧪 Tests
- [ ] Paiement simple → statut facture Soldée
- [ ] Paiement partiel → statut Partiellement payée
- [ ] Ventilation paiement sur 2 factures
- [ ] Dashboard KPIs (données de référence, cache invalidé)
- [ ] Job factures en retard (statut + notification)

#### ✅ Livrables Wave 5 — FIN MVP
- **MVP complet livré en staging**
- Cycle financier complet : Dépense → Devis → Facture → Paiement
- Dashboard opérationnel
- PWA installable sur mobile
- Notifications de base
- **Déploiement production possible**

---

# PHASE 2 — V1.1

---

## 🌊 WAVE 6 — Fournisseurs & Bons de commande
**Durée :** 2 semaines | **Specs :** SPEC-05, SPEC-08

### Objectif
Formaliser la chaîne d'achat avec les fournisseurs et les bons de commande.

### Tâches

#### 👤 Module Fournisseurs (SPEC-05)
- [x] Migration `suppliers`
- [x] CRUD fournisseurs (US-05-01) — index, create, store, show, edit, update, destroy
- [x] Fiche fournisseur avec historique achats (US-05-02) — vue `show.blade.php` + dépenses liées
- [x] Lien salarié Sous-traitant ↔ fiche fournisseur

#### 💰 Module Bons de commande (SPEC-08)
- [x] Migrations : `purchase_orders`, `purchase_order_items`
- [x] CRUD BC avec lignes dynamiques (US-08-01)
- [x] Numérotation : BC-AAAA-NNN
- [x] Gestion des statuts BC
- [ ] Export PDF BC
- [ ] Envoi email fournisseur
- [x] Conversion BC → dépense (US-08-02)

#### 🏗️ Mise à jour fiche chantier
- [x] Onglet Bons de commande opérationnel
- [x] Total commandes en attente affiché

#### ✅ Livrables Wave 6
- Module Fournisseurs complet
- Module Bons de commande complet avec conversion BC → dépense

---

## 🌊 WAVE 7 — Tâches & Planning
**Durée :** 2 semaines | **Specs :** SPEC-16, SPEC-17

### Objectif
Permettre la coordination de l'équipe via la gestion des tâches et un calendrier de planning.

### Tâches

#### 👤 Module Tâches (SPEC-16)
- [x] Migrations : `tasks`, `task_comments`, pivot `task_employees`
- [x] CRUD tâches (US-16-01)
  - Sous-tâches (JSON ou table `task_checklists`)
  - Commentaires (fil de discussion)
  - Pièces jointes
  - Assignation multi-salariés
- [x] Vue liste par chantier avec filtres
- [x] Vue Kanban (colonnes statuts) — Drag & Drop (SortableJS)
- [x] Indicateur d'avancement chantier (% tâches terminées)
- [x] Alertes visuelles tâches en retard (rouge)
- [ ] Notification : tâche assignée → notification in-app

#### 📅 Module Planning (SPEC-17)
- [ ] Calendrier global (FullCalendar.js ou custom)
- [ ] Vue mensuelle + hebdomadaire
- [ ] Barre chantiers (date début → date fin)
- [ ] Overlay tâches (optionnel, toggle)
- [ ] Filtres : région, statut, salarié
- [ ] Détection conflits salarié (doublon de plage horaire)
- [ ] Export PDF planning hebdomadaire

#### ✅ Livrables Wave 7
- Module Tâches (liste + kanban + commentaires)
- Planning calendrier global et par chantier

---

## 🌊 WAVE 8 — Pointage & Paie
**Durée :** 2 semaines | **Specs :** SPEC-18

### Objectif
Digitaliser le pointage terrain et automatiser le calcul des rémunérations.

### Tâches

#### 📱 Pointage mobile (SPEC-18)
- [x] Migration `attendances` (chantier, salarié, check_in, check_out, lat, lng, statut)
- [ ] Interface PWA pointage simplifié (US-18-01)
  - Sélection chantier (liste chantiers assignés)
  - Bouton "Pointer l'entrée" → heure serveur + GPS
  - Bouton "Pointer la sortie"
  - Feedback visuel (heures travaillées affichées)
- [x] Grille saisie manuelle chef de chantier (US-18-02)
  - Vue semaine : salariés × jours
  - Statuts : Présent / Absent justifié / Absent non justifié
  - Modification avec motif obligatoire
- [ ] Validation pointage par admin (optionnel, selon paramètre)
- [ ] Fiche salarié : onglet Pointage rempli

#### 💰 Calcul rémunération (SPEC-18 US-18-03)
- [x] Récapitulatif mensuel salarié (heures × tarif, jours × tarif journalier)
- [ ] Génération bulletin de salaire simplifié PDF
- [x] Export CSV pour logiciel paie externe

#### 📱 Hors-ligne pointage (SPEC-27 US-27-02 partiel)
- [ ] IndexedDB : stockage pointage offline
- [ ] Background Sync API : envoi différé à la reconnexion
- [ ] Gestion conflits (signalement à l'utilisateur)

#### 🏗️ Mise à jour fiche chantier & salarié
- [x] Onglet Salariés & Pointage complet sur fiche chantier
- [ ] Fiche salarié : onglet Pointage + Rémunération

#### ✅ Livrables Wave 8
- Pointage mobile fonctionnel (avec GPS)
- Saisie manuelle équipe par chef de chantier
- Calcul rémunérations + bulletin PDF
- Pointage hors-ligne fonctionnel

---

## 🌊 WAVE 8b — Stocks MVP *(nouveau)*
**Durée :** 1 semaine | **Specs :** SPEC-22 (partiel) | **Priorité :** S *(promue depuis Wave 12)*

### Objectif
Délivrer le minimum viable de gestion des stocks pour permettre le suivi des entrées/sorties de matériaux dès la Phase 2.

### Tâches

#### 🏭 Module Stocks MVP (SPEC-22)
- [ ] CRUD articles stock (nom, catégorie, unité, stock initial, seuil minimum)
- [ ] Entrées de stock manuelles liées à un chantier ou bon de commande
- [ ] Sorties de stock liées à un chantier (consommation)
- [ ] Alerte stock sous seuil minimum (notification in-app)
- [ ] Vue stock courant par article + dépôt

> Les fonctionnalités avancées (valorisation PAMP, transferts inter-dépôts, inventaire physique, rapport consommation) restent en **Wave 12**.

#### ✅ Livrables Wave 8b
- Gestion stocks basique opérationnelle (entrées/sorties + alertes seuil)

---

## 🌊 WAVE 9 — Documents & Photos
**Durée :** 2 semaines | **Specs :** SPEC-19, SPEC-20

### Objectif
Centraliser la documentation et la photographie de chantier.

### Tâches

#### 📁 Module Documents (SPEC-19)
- [x] Migration `documents`
- [x] Upload fichiers (Dropzone.js) — types acceptés : PDF, DOCX, XLSX, JPG, PNG, DWG
- [x] Stockage hors webroot (accès via contrôleur + vérification droits)
- [x] Validation MIME côté serveur
- [x] Organisation par catégories
- [ ] Prévisualisation in-app (PDF avec pdf.js, images)
- [ ] Partage lien sécurisé (token + expiration 7/30j)
- [ ] Versionning (upload nouvelle version, ancienne conservée)
- [ ] Téléchargement ZIP multi-fichiers (job asynchrone)

#### 📸 Module Photos (SPEC-20)
- [ ] Migration `photos`
- [ ] Capture depuis caméra PWA (MediaDevices API)
- [ ] Upload depuis galerie (multi-sélection)
- [ ] Compression automatique côté serveur : max 1920px, WebP (Intervention Image)
- [ ] Métadonnées (date, auteur, catégorie/phase, commentaire)
- [ ] Galerie vue grille (miniatures) + Lightbox
- [ ] Filtres : catégorie, date, auteur
- [ ] Téléchargement ZIP sélection
- [ ] Hors-ligne : photos stockées localement → upload différé (IndexedDB + Background Sync)

#### ✅ Livrables Wave 9
- Module Documents complet (upload, classement, partage, versionning)
- Module Photos complet (capture mobile, galerie, compression)
- Hors-ligne photos fonctionnel

---

## 🌊 WAVE 10 — Situations, Avenants & Bibliothèque de prix
**Durée :** 3 semaines | **Specs :** SPEC-10, SPEC-11, SPEC-25

### Objectif
Compléter la gestion contractuelle avec les situations de travaux, les avenants et la bibliothèque de prix pour accélérer le chiffrage.

### Tâches

#### 💰 Module Avenants (SPEC-10)
- [x] Migrations : `amendments`, `amendment_items`
- [x] CRUD avenants liés à devis/chantier (US-10-01)
- [x] Numérotation : AVN-AAAA-NNN
- [x] Lignes positives et négatives
- [x] Workflow validation client (même que devis)
- [x] Recalcul total facturable chantier = Devis + Σ Avenants acceptés
- [ ] Export PDF avenant

#### 💰 Module Situations de travaux (SPEC-11)
- [x] Migrations : `progress_billings`
- [x] Création situation avec tableau d'avancement par ligne devis (US-11-01)
- [x] Numérotation : SIT-AAAA-NNN-Sxx
- [x] Calcul automatique : % × montant devis − situations précédentes
- [x] Gestion retenue de garantie par situation
- [ ] Génération facture de situation associée
- [ ] Export PDF situation de travaux
- [ ] Fiche chantier : onglet Situations de travaux

#### 🏗️ Bibliothèque de prix (SPEC-25)
- [x] Migrations : `materials`, `material_prices`, `salary_rates`, `dosage_models`, `dosage_items`
- [x] CRUD bibliothèque matériaux par région (US-25-01)
- [x] Import/Export CSV bibliothèque
- [x] Historique automatique des changements de prix
- [x] Grille salariale métier × région (US-25-02) — CRUD UI ajouté dans Paramètres
- [ ] Intégration dans le formulaire devis : recherche article → auto-remplissage prix
- [x] Calcul coefficient K dans les paramètres (US-26-01 formules) — via QuoteCalculationService
- [ ] Affichage marge en temps réel lors de la saisie du prix de vente

#### 🧮 Calcul DBE & Modèles de dosage (SPEC-25 — US-25-03/04)
- [x] CRUD modèles de dosage (recettes techniques par ouvrage) — `DosageController`
- [x] Items de dosage : type matériaux / main d'œuvre / matériel / sous-traitance + `waste_rate`
- [x] `QuoteCalculationService::calculateFromDosage()` — calcul DBE ventilé par type
- [x] `QuoteCalculationService::applyCoefficients()` — FG% + Marge% + Aléas% → coefficient K → prix unitaire
- [x] Endpoint AJAX `POST /dosage/{dosage}/calculate` avec breakdown détaillé
- [x] Intégration devis : bouton "Calculer depuis dosage" → modal + auto-remplissage ligne
- [ ] Affichage coefficient K calculé en temps réel dans le formulaire devis

#### ✅ Livrables Wave 10 — FIN V1.1
- **V1.1 complète livrée**
- Avenants + Situations de travaux opérationnels
- Bibliothèque de prix avec historique
- Coefficient K et calcul marge temps réel

---

# PHASE 3 — V2.0

---

## 🌊 WAVE 11 — Compte-rendus, PV Réception & Clôture
**Durée :** 2 semaines | **Specs :** SPEC-14, SPEC-15

### Objectif
Formaliser la fin de chantier avec les compte-rendus de réunion, le PV de réception et le rapport de clôture.

### Tâches

#### 📋 Module Compte-rendus (SPEC-15)
- [x] Migrations : `site_reports`, `site_report_items`
- [x] CRUD compte-rendus (US-15-01)
- [x] Numérotation : CR-AAAA-NNN
- [x] Participants (multi-select + saisie libre)
- [x] Gestion actions (responsable + délai)
- [ ] Attach photos depuis galerie chantier
- [x] Export PDF
- [ ] Envoi email aux participants

#### 🏗️ PV de Réception & Clôture (SPEC-14)
- [x] Migration `reception_reports`
- [x] Génération PV depuis fiche chantier statut `Terminé` (US-14-01)
- [x] Gestion réserves (liste, délai, responsable)
- [x] Libération retenue de garantie (bouton + confirmation)
- [ ] Passage automatique chantier à `Clôturé`
- [ ] Rapport de clôture financière auto-généré (US-14-02)
  - Budget prévu vs réel
  - Décomposition achats
  - CA facturé vs encaissé
  - Bénéfice final + taux de marge
- [ ] PDF rapport de clôture stocké dans documents chantier

#### ✅ Livrables Wave 11
- Module Compte-rendus complet
- PV Réception + libération RG
- Rapport de clôture financière automatique

---

## 🌊 WAVE 12 — Matériels & Stocks
**Durée :** 3 semaines | **Specs :** SPEC-21, SPEC-22

### Objectif
Gérer l'inventaire des équipements, leur affectation et le stock de matériaux.

### Tâches

#### 🏗️ Module Matériels (SPEC-21)
- [x] Migrations : `equipments`, `equipment_maintenances`, pivot `project_equipments`
- [x] CRUD matériels (US-21-01)
- [ ] Affectation chantier (dates + coût journalier)
- [ ] Intégration coût affectation → ACHATS automatiquement (catégorie "Location matériel")
- [ ] Alerte fin affectation J-3
- [x] Module maintenance : enregistrement interventions (US-21-02)
- [ ] Fiche chantier : matériels affectés opérationnel

#### 🏗️ Module Stocks avancé (SPEC-22 complète)
- [x] Migrations : `warehouses`, `stock_movements`
- [x] CRUD articles + dépôts (US-22-01)
- [x] Entrées stock (liées à BC/livraison)
- [x] Sorties stock (liées à chantier)
- [ ] Valorisation PAMP (calculée à chaque mouvement)
- [x] Seuil minimum + alertes (notification in-app)
- [x] Transferts entre dépôts
- [x] Ajustement inventaire physique (avec motif)
- [x] Rapport consommation par chantier (dashboard)

#### 📅 Vue Gantt — SPEC-17-bis *(promu depuis V3)*
- [ ] Axe horizontal = timeline (semaines / mois) ; axe vertical = tâches ou chantiers
- [ ] Barre colorée par statut (tâche / chantier)
- [ ] Affichage des jalons (devis envoyé, situation émise, réception)
- [ ] Navigation « précédent / suivant » par période
- [ ] Drag & drop des dates (optionnel)
- [ ] Export PDF du Gantt

#### ✅ Livrables Wave 12
- Module Matériels complet (inventaire + affectation + maintenance)
- Module Stocks avancé complet (mouvements + PAMP + alertes)
- Vue Gantt opérationnelle (planning chantier + tâches)

---

## 🌊 WAVE 13 — Notifications complètes
**Durée :** 2 semaines | **Specs :** SPEC-23

### Objectif
Compléter le système de notifications in-app et email sur tous les événements définis dans le CDC.

### Tâches

#### 🔔 Notifications complètes
- [ ] Tous les événements du tableau CDC §18.1 implémentés
- [ ] Notifications email (queue, template Blade HTML)
- [ ] Push notifications PWA (Service Worker Push API) — optionnel selon support navigateur
- [ ] Page paramètres notifications (activation/désactivation par type)
- [ ] Paramétrage délais de rappel (J-7, J-3, J+0 pour factures)
- [ ] Jobs planifiés : rappels factures, tâches en retard, maintenance due, retenue libérable
- [x] Migration `notifications` (table polymorphique Laravel)
- [x] Cloche notifications topbar avec badge + dropdown (5 dernières)
- [x] Classes notifications : TaskAssigned, InvoiceOverdue, PaymentReceived
- [x] NotificationController (index, markRead, markAllRead)
- [x] Page toutes les notifications (avec pagination + marque-tout-lu)

#### ✅ Livrables Wave 13
- Toutes les notifications du CDC implémentées
- Emails et in-app fonctionnels
- Paramétrage utilisateur des notifications

---

## 🌊 WAVE 14 — Dashboard avancé & Rapports
**Durée :** 3 semaines | **Specs :** SPEC-24

### Objectif
Livrer un dashboard analytique complet et tous les rapports exportables définis dans le CDC.

### Tâches

#### 📊 Dashboard avancé (SPEC-24 US-24-01)
- [ ] Top 5 chantiers les plus rentables (triable)
- [ ] Top 5 chantiers les plus déficitaires
- [ ] Avancement moyen des chantiers actifs (barre de progression)
- [ ] Graphique chantiers par statut (barres Chart.js)
- [ ] Filtres temporels dashboard (mois en cours, trimestre, année)
- [ ] Vue chef de chantier : dashboard restreint à ses chantiers
- [ ] Optimisation cache Redis (invalidation granulaire)

#### 📊 Rapports exportables (SPEC-24 US-24-02)
- [x] Bilan financier entreprise (PDF)
- [x] Suivi financier par chantier (PDF)
- [ ] Balance âgée complète (PDF + Excel)
- [x] Journal des dépenses (PDF)
- [x] Récapitulatif des pointages (PDF)
- [ ] Inventaire stock (PDF + Excel)
- [ ] Rapport de clôture chantier (PDF)
- [x] ReportController (financial, projects, expenses, attendance) + vues + templates PDF
- [ ] Export Excel (xlsx)
- [ ] Génération asynchrone (queue) + notification "votre rapport est prêt"

#### ✅ Livrables Wave 14
- Dashboard complet avec tous les KPIs et graphiques
- 7 rapports exportables en PDF et Excel

---

## 🌊 WAVE 15 — PWA hors-ligne complet & SaaS Abonnements
**Durée :** 3 semaines | **Specs :** SPEC-27, SPEC-28

### Objectif
Finaliser les capacités hors-ligne de la PWA et mettre en place le module d'abonnement SaaS.

### Tâches

#### 📱 PWA hors-ligne complet (SPEC-27 US-27-02)
- [ ] Service Worker : stratégie cache complète (voir tableau CDC §14.1)
- [ ] IndexedDB : pointages, dépenses, tâches
- [ ] Background Sync API : sync automatique au retour réseau
- [ ] Bandeau "Mode hors-ligne" visible
- [ ] Gestion conflits de synchronisation (notification utilisateur)
- [ ] Pagination agressive en mode offline
- [ ] Tests offline (Lighthouse PWA score cible ≥ 90)

#### 🏗️ Module Abonnements SaaS
- [ ] Migrations : `plans`, `subscriptions`
- [ ] Gestion des plans (Gratuit, Starter, Pro, Enterprise)
- [ ] Limitations par plan (chantiers max, utilisateurs max)
- [ ] Middleware `CheckSubscription` : blocage si quota dépassé
- [ ] Page "Gérer mon abonnement" (upgrade/downgrade)
- [ ] Page Super Admin : liste tous les tenants + plans + statuts
- [ ] Date d'expiration + notifications renouvellement (J-30, J-7, J+0)

#### 🏗️ Paramètres entreprise (finalisation)
- [ ] Modèles d'emails (devis, facture, relance) — éditeur WYSIWYG
- [ ] Mentions légales par défaut
- [ ] Toutes les formules paramétrables (FG, Marge, Aléas, TVA, RG, K)
- [ ] White-label : couleurs personnalisées (primaire, secondaire) appliquées aux PDFs

#### ✅ Livrables Wave 15 — FIN V2.0
- **V2.0 complète livrée**
- PWA hors-ligne robuste (Lighthouse ≥ 90)
- Module SaaS abonnements opérationnel
- White-label entreprise complet

---

# PHASE 4 — V3.0 (Vision long terme)

---

## 🌊 WAVE 16+ — Fonctionnalités avancées

Ces waves seront détaillées ultérieurement selon les retours utilisateurs post-V2.0.

| Wave    | Sujet                                    | Description                                           |
|---------|------------------------------------------|-------------------------------------------------------|
| Wave 16 | **API publique**                         | API REST documentée (Swagger/OpenAPI) pour intégrations tierces |
| Wave 17 | **Application mobile native**            | React Native (iOS + Android) — pour distribution store |
| Wave 18 | **Intelligence artificielle**            | Prévision de coûts, détection anomalies              |
| Wave 19 | **Intégration comptabilité**             | Export vers Sage, QuickBooks, EBP (FEC)               |
| Wave 20 | **Marketplace sous-traitants**           | Mise en relation entreprises BTP ↔ sous-traitants     |
| Wave 21 | **Module appels d'offres**               | Création AO, réception offres, comparaison            |
| Wave 22 | **Synchronisation Google Calendar**      | Bidirectionnelle (planning chantier ↔ agenda)         |
| Wave 23 | **Application desktop Electron**         | Version desktop Windows/macOS                         |

---

# RÉCAPITULATIF GLOBAL

## Planning synthétique

| Wave | Sujet                              | Durée  | Fin estimée  | Phase     |
|------|------------------------------------|--------|--------------|-----------|
| W0   | Setup & Infrastructure             | 2 sem  | Mois 1 S2   | MVP       |
| W1   | Auth + Multi-tenant                | 2 sem  | Mois 2 S2   | MVP       |
| W2   | Clients + Salariés + Chantiers     | 3 sem  | Mois 3 S3   | MVP       |
| W3   | ACHATS (Dépenses)                  | 2 sem  | Mois 4 S1   | MVP       |
| W4   | VENTES (Devis + Factures)          | 3 sem  | Mois 4 S4 ✅ | MVP      |
| W5   | Paiements + Dashboard + PWA        | 2 sem  | Mois 5 S2 🚀 | MVP      |
| W6   | Fournisseurs + Bons de commande    | 2 sem  | Mois 6 S2   | V1.1      |
| W7   | Tâches + Planning                  | 2 sem  | Mois 7 S2   | V1.1      |
| W8   | Pointage + Paie                    | 2 sem  | Mois 8 S2   | V1.1      |
| W9   | Documents + Photos                 | 2 sem  | Mois 9 S2   | V1.1      |
| W10  | Situations + Avenants + Prix       | 3 sem  | Mois 10 S3 🚀 | V1.1   |
| W11  | Compte-rendus + PV + Clôture       | 2 sem  | Mois 11 S3  | V2.0      |
| W12  | Matériels + Stocks                 | 3 sem  | Mois 12 S4  | V2.0      |
| W13  | Notifications complètes            | 2 sem  | Mois 13 S2  | V2.0      |
| W14  | Dashboard avancé + Rapports        | 3 sem  | Mois 14 S3  | V2.0      |
| W15  | PWA hors-ligne + SaaS              | 3 sem  | Mois 15 S4 🚀 | V2.0   |

> 🚀 = Jalon de livraison majeur

## Dépendances critiques

```
W0 → W1 → W2 → W3 → W4 → W5 (MVP 🚀)
                          ↓
                    W6 → W7 → W8 → W9 → W10 (V1.1 🚀)
                                             ↓
                                       W11 → W12 → W13 → W14 → W15 (V2.0 🚀)
```

**Dépendances spécifiques :**
- W4 (Devis) doit être terminé avant W10 (Situations — basées sur les devis)
- W3 (ACHATS) doit être terminé avant W6 (BC → conversion dépense)
- W2 (Salariés) doit être terminé avant W8 (Pointage)
- W4 (Factures) + W5 (Paiements) doivent être terminés avant W12 (Stocks → lien BC)

---

## Critères de définition de "Done" par wave

Une wave est **Done** quand :
- [ ] Toutes les tâches de la wave sont implémentées
- [ ] Tests unitaires et features passent (CI vert)
- [ ] Aucun bug bloquant ou critique ouvert
- [ ] Interface validée sur desktop (Chrome) et mobile (Chrome Android + Safari iOS)
- [ ] Performance acceptable : < 3s sur connexion 3G (pages principales)
- [ ] Code reviewé et mergé sur la branche principale
- [ ] Déployé sur environnement staging
- [ ] Démo interne réalisée
- [ ] Documentation mise à jour (si applicable)

---

## Stack technique — Rappel pour chaque wave

| Outil              | Usage                                    |
|--------------------|------------------------------------------|
| Laravel 11         | Backend, API, queues, jobs planifiés     |
| Spatie Permission  | RBAC (rôles et permissions)              |
| Spatie Backup      | Sauvegardes automatiques                 |
| DomPDF             | Génération PDF                           |
| Intervention Image | Compression et redimensionnement photos  |
| Laravel Excel      | Export XLSX                              |
| Bootstrap 5.3      | UI, grille, composants                   |
| Alpine.js          | Réactivité légère (calculs temps réel)   |
| Chart.js           | Graphiques dashboard                     |
| FullCalendar.js    | Module planning (W7)                     |
| SortableJS         | Drag & drop Kanban (W7)                  |
| Dropzone.js        | Upload fichiers (W9)                     |
| pdf.js             | Prévisualisation PDF in-app (W9)         |
| Redis              | Cache + queues                           |
| PHPUnit            | Tests unitaires et d'intégration         |
| GitHub Actions     | CI/CD                                    |
