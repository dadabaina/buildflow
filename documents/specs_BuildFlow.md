# 📋 SPÉCIFICATIONS FONCTIONNELLES — BuildFlow

## Document de référence développement

---

| Champ              | Valeur                          |
|--------------------|---------------------------------|
| **Projet**         | BuildFlow                       |
| **Version**        | 1.0                             |
| **Date**           | 10 Mai 2026                     |
| **Basé sur**       | CDC v2.0                        |
| **Destinataires**  | Équipe développement, Lead tech |
| **Statut**         | Validé pour développement       |

---

# SOMMAIRE

- [Conventions](#conventions)
- [SPEC-01 — Authentification](#spec-01--authentification--sécurité)
- [SPEC-02 — Utilisateurs](#spec-02--gestion-des-utilisateurs)
- [SPEC-03 — Clients](#spec-03--gestion-des-clients)
- [SPEC-04 — Salariés](#spec-04--gestion-des-salariés)
- [SPEC-05 — Fournisseurs](#spec-05--gestion-des-fournisseurs)
- [SPEC-06 — Chantiers](#spec-06--gestion-des-chantiers)
- [SPEC-07 — Achats & Dépenses](#spec-07--achats--dépenses)
- [SPEC-08 — Bons de commande](#spec-08--bons-de-commande)
- [SPEC-09 — Devis](#spec-09--devis)
- [SPEC-10 — Avenants](#spec-10--avenants)
- [SPEC-11 — Situations de travaux](#spec-11--situations-de-travaux)
- [SPEC-12 — Facturation](#spec-12--facturation)
- [SPEC-13 — Paiements](#spec-13--paiements)
- [SPEC-14 — Clôture & PV Réception](#spec-14--clôture--pv-réception)
- [SPEC-15 — Compte-rendus](#spec-15--compte-rendus-de-chantier)
- [SPEC-16 — Tâches](#spec-16--tâches)
- [SPEC-17 — Planning](#spec-17--planning--calendrier)
- [SPEC-18 — Pointage](#spec-18--pointage)
- [SPEC-19 — Documents](#spec-19--documents-chantier)
- [SPEC-20 — Photos](#spec-20--photos--galerie)
- [SPEC-21 — Matériels](#spec-21--matériels--équipements)
- [SPEC-22 — Stocks](#spec-22--gestion-des-stocks)
- [SPEC-23 — Notifications](#spec-23--notifications)
- [SPEC-24 — Dashboard & Rapports](#spec-24--dashboard--rapports)
- [SPEC-25 — Bibliothèque de prix](#spec-25--bibliothèque-de-prix)
- [SPEC-26 — Paramètres entreprise](#spec-26--paramètres-entreprise)
- [SPEC-27 — PWA & Hors-ligne](#spec-27--pwa--hors-ligne)
- [SPEC-28 — Multi-tenant & SaaS](#spec-28--multi-tenant--saas)

---

# Conventions

## Format des user stories

```
En tant que [RÔLE]
Je veux [ACTION]
Afin de [BÉNÉFICE]
```

## Statut des specs

| Statut       | Signification                    |
|--------------|----------------------------------|
| ✅ Validé    | Spec approuvée, prête à coder    |
| 🔄 En revue  | En attente de validation métier  |
| 📌 V2        | Planifié pour version ultérieure |
| ❌ Exclu     | Hors périmètre                   |

## Priorités MoSCoW

- **M** — Must have (obligatoire MVP)
- **S** — Should have (important, pas bloquant)
- **C** — Could have (confort, si temps)
- **W** — Won't have (exclu version actuelle)

## Conventions de nommage

| Document          | Format              | Exemple           |
|-------------------|---------------------|-------------------|
| Devis             | DEV-AAAA-NNN        | DEV-2026-001      |
| Facture           | FAC-AAAA-NNN        | FAC-2026-001      |
| Avoir             | AVO-AAAA-NNN        | AVO-2026-001      |
| Bon de commande   | BC-AAAA-NNN         | BC-2026-001       |
| Avenant           | AVN-AAAA-NNN        | AVN-2026-001      |
| Situation         | SIT-AAAA-NNN-Sxx    | SIT-2026-001-S01  |
| Compte-rendu      | CR-AAAA-NNN         | CR-2026-001       |
| Chantier          | BF-AAAA-NNN         | BF-2026-001       |

---

# SPEC-01 — Authentification & Sécurité

**Priorité Wave :** Wave 1 | **Statut :** ✅ Validé

## User Stories

### US-01-01 — Login
```
En tant qu'utilisateur enregistré
Je veux me connecter avec mon email et mot de passe
Afin d'accéder à mon espace BuildFlow
```
**Priorité :** M

**Critères d'acceptation :**
- [ ] Formulaire email + mot de passe
- [ ] Validation côté serveur (email valide, mot de passe non vide)
- [ ] Message d'erreur générique (ne pas indiquer si c'est l'email ou le MDP qui est faux)
- [ ] Redirection vers le dashboard après connexion réussie
- [ ] Mémoriser la dernière URL tentée → rediriger après login
- [ ] Case "Se souvenir de moi" (cookie 30 jours)
- [ ] Verrouillage après 5 tentatives échouées (message : "Compte temporairement bloqué, réessayez dans 15 minutes")

**Règles techniques :**
- Rate limiting : 5 tentatives / minute / IP
- Session expiration : 8h d'inactivité par défaut (configurable)
- Log : enregistrer IP, user-agent, résultat (succès/échec)

---

### US-01-02 — Logout
```
En tant qu'utilisateur connecté
Je veux me déconnecter
Afin de sécuriser mon accès
```
**Priorité :** M

**Critères d'acceptation :**
- [ ] Bouton déconnexion accessible depuis le menu utilisateur
- [ ] Invalidation de la session côté serveur (pas seulement cookie)
- [ ] Redirection vers la page de login
- [ ] Message de confirmation : "Vous avez été déconnecté"

---

### US-01-03 — Réinitialisation mot de passe
```
En tant qu'utilisateur ayant oublié son mot de passe
Je veux recevoir un lien de réinitialisation par email
Afin de récupérer l'accès à mon compte
```
**Priorité :** M

**Critères d'acceptation :**
- [ ] Page "Mot de passe oublié" avec champ email
- [ ] Si email inconnu : réponse identique (ne pas révéler l'existence du compte)
- [ ] Lien de réinitialisation valide 60 minutes
- [ ] Lien à usage unique (invalidé après utilisation)
- [ ] Formulaire nouveau mot de passe : confirmation requise, min 8 caractères
- [ ] Invalidation de toutes les sessions actives après changement

---

### US-01-04 — Gestion des rôles
```
En tant qu'Admin Entreprise
Je veux créer des rôles personnalisés avec des permissions par module
Afin de contrôler finement les accès de mon équipe
```
**Priorité :** M

**Critères d'acceptation :**
- [ ] Interface de création de rôle (nom libre)
- [ ] Tableau de permissions : une ligne par module, colonnes : Voir / Créer / Modifier / Supprimer
- [ ] Rôles système non modifiables (Super Admin, Admin Entreprise)
- [ ] Un utilisateur peut avoir un seul rôle (ou plusieurs si multi-rôle activé)
- [ ] Changement de rôle prend effet à la prochaine connexion ou immédiatement
- [ ] Impossible de supprimer un rôle assigné à des utilisateurs actifs

---

# SPEC-02 — Gestion des utilisateurs

**Priorité Wave :** Wave 1 | **Statut :** ✅ Validé

## User Stories

### US-02-01 — Inviter un utilisateur
```
En tant qu'Admin Entreprise
Je veux inviter un collaborateur par email
Afin qu'il puisse rejoindre mon espace BuildFlow
```
**Priorité :** M

**Critères d'acceptation :**
- [ ] Formulaire : email + rôle à assigner
- [ ] Email d'invitation avec lien d'activation (expire 7 jours)
- [ ] L'invité doit créer son mot de passe lors de la première connexion
- [ ] Si email déjà existant dans le tenant → message d'erreur
- [ ] Renvoi de l'invitation possible (bouton "Renvoyer l'invitation")
- [ ] Statut "En attente" jusqu'à activation

---

### US-02-02 — Gérer les utilisateurs
```
En tant qu'Admin Entreprise
Je veux voir, modifier et désactiver les comptes de mes collaborateurs
Afin de maintenir la liste à jour
```
**Priorité :** M

**Critères d'acceptation :**
- [ ] Liste des utilisateurs avec : nom, email, rôle, statut, dernière connexion
- [ ] Filtres : statut (actif/inactif/en attente), rôle
- [ ] Modifier le rôle d'un utilisateur
- [ ] Activer / désactiver un compte (utilisateur désactivé ne peut plus se connecter)
- [ ] Réinitialiser le mot de passe (envoi email de réinitialisation)
- [ ] Un admin ne peut pas se désactiver lui-même
- [ ] Pagination : 25 par page

---

### US-02-03 — Profil utilisateur
```
En tant qu'utilisateur connecté
Je veux modifier mon profil
Afin de maintenir mes informations à jour
```
**Priorité :** S

**Critères d'acceptation :**
- [ ] Modifier nom, prénom, téléphone
- [ ] Changer son mot de passe (doit saisir l'ancien)
- [ ] Uploader une photo de profil (JPG/PNG, max 2 Mo, recadrage automatique 200×200)
- [ ] Ne peut pas modifier son propre rôle ni son email (contacter l'admin)

---

# SPEC-03 — Gestion des clients

**Priorité Wave :** Wave 1 | **Statut :** ✅ Validé

## User Stories

### US-03-01 — CRUD Client
```
En tant qu'utilisateur avec permission Clients
Je veux créer, modifier et archiver des clients
Afin de gérer mon portefeuille client
```
**Priorité :** M

**Critères d'acceptation :**
- [ ] Formulaire de création avec tous les champs définis dans le CDC
- [ ] Validation : Nom et Téléphone obligatoires
- [ ] Recherche en temps réel (debounce 300ms) sur nom, téléphone, email
- [ ] Filtre par région et statut
- [ ] Archivage (soft delete) — un client archivé n'apparaît plus dans les listes mais ses données sont conservées
- [ ] Impossibilité de supprimer un client ayant des chantiers ou factures
- [ ] Import CSV : colonnes nom, type, téléphone, email, adresse, région
  - Rapport d'import : lignes importées / erreurs / ignorées

---

### US-03-02 — Fiche client
```
En tant qu'utilisateur avec permission Clients
Je veux consulter la fiche complète d'un client
Afin d'avoir une vue 360° de la relation client
```
**Priorité :** M

**Critères d'acceptation :**
- [ ] En-tête : nom, type, contact, région, statut
- [ ] Onglet **Chantiers** : liste avec statut, dates, montant facturé
- [ ] Onglet **Devis** : liste avec statut et montant, accès direct
- [ ] Onglet **Factures** : liste avec statut (émise/payée/en retard)
- [ ] Onglet **Paiements** : historique chronologique
- [ ] Bloc **Indicateurs financiers** toujours visible :
  - Total devisé, Total facturé, Total encaissé, Solde dû
- [ ] Export PDF de la fiche client (résumé + indicateurs)

---

# SPEC-04 — Gestion des salariés

**Priorité Wave :** Wave 1 | **Statut :** ✅ Validé

## User Stories

### US-04-01 — CRUD Salarié
```
En tant qu'utilisateur avec permission RH
Je veux gérer le registre du personnel
Afin d'avoir une base RH centralisée
```
**Priorité :** M

**Critères d'acceptation :**
- [ ] Tous les champs du CDC présents
- [ ] Métier : liste déroulante gérée par l'admin (CRUD référentiel métiers)
- [ ] Type de contrat : CDI / CDD / Journalier / Sous-traitant
- [ ] Si type = Sous-traitant : lien automatique avec fiche fournisseur (optionnel)
- [ ] Tarif horaire ET journalier peuvent coexister (l'un ou l'autre utilisé selon le mode de pointage)
- [ ] Recherche + filtres (métier, région, type contrat, statut)
- [ ] Archivage salarié (données conservées)

---

### US-04-02 — Affectation salarié ↔ chantier
```
En tant que Chef de chantier ou Admin
Je veux affecter un ou plusieurs salariés à un chantier
Afin de constituer l'équipe du projet
```
**Priorité :** M

**Critères d'acceptation :**
- [ ] Interface d'affectation depuis la fiche chantier (onglet Salariés)
- [ ] Sélection multi-salarié avec leur rôle sur ce chantier (chef, ouvrier, technicien…)
- [ ] Un salarié peut être affecté à plusieurs chantiers simultanément sans blocage
- [ ] Date de début/fin d'affectation sur le chantier (optionnel)
- [ ] Retrait d'un salarié du chantier (historique conservé)

---

### US-04-03 — Fiche salarié
```
En tant qu'utilisateur RH
Je veux consulter la fiche complète d'un salarié
Afin de suivre son activité et ses performances
```
**Priorité :** S

**Critères d'acceptation :**
- [ ] Onglet **Chantiers** : chantiers actifs + historiques
- [ ] Onglet **Pointage** : journal détaillé (date, chantier, entrée, sortie, heures, statut)
- [ ] Onglet **Rémunération** : total heures × tarif par chantier, total brut calculé
- [ ] Onglet **Documents** : contrat, CIN (upload sécurisé)
- [ ] Indicateur : taux de présence (jours pointés / jours affectés × 100)

---

### US-05-03 — Suivi des dettes fournisseurs
```
En tant qu'utilisateur Achats ou Comptable
Je veux visualiser les montants non encore réglés à mes fournisseurs
Afin de piloter ma trésorerie fournisseurs
```
**Priorité :** S

**Critères d'acceptation :**
- [ ] Fiche fournisseur : bloc indicateurs (total non réglé, total réglé sur la période, solde dû)
- [ ] Vue globale « Comptes fournisseurs » (menu Finance) : liste de toutes les dépenses/BC sans règlement enregistré
- [ ] Enregistrement d'un paiement fournisseur : date, montant, mode, référence, lien à la dépense/BC
- [ ] Balance âgée fournisseurs : regroupement par tranche (0-30j, 30-60j, +60j)
- [ ] Filtres : fournisseur, période, chantier
- [ ] Export Excel

---

# SPEC-05 — Gestion des fournisseurs

**Priorité Wave :** Wave 2 | **Statut :** ✅ Validé

## User Stories

### US-05-01 — CRUD Fournisseur
```
En tant qu'utilisateur avec permission Achats
Je veux référencer mes fournisseurs
Afin de les associer à mes bons de commande et dépenses
```
**Priorité :** S

**Critères d'acceptation :**
- [ ] Tous les champs du CDC présents
- [ ] Catégories : Matériaux / Location matériel / Sous-traitance / Transport / Divers
- [ ] Recherche par nom, catégorie, région
- [ ] Archivage (données conservées)
- [ ] Catégorie de document « Bons de livraison » disponible et liée optionnellement à un BC ou une dépense

---

### US-05-02 — Fiche fournisseur
```
En tant qu'utilisateur Achats
Je veux consulter l'historique d'un fournisseur
Afin d'évaluer notre relation commerciale
```
**Priorité :** S

**Critères d'acceptation :**
- [ ] Liste des bons de commande émis avec statut
- [ ] Total achats par période (filtre mois/trimestre/année)
- [ ] Dernier prix pratiqué par article commandé

---

# SPEC-06 — Gestion des chantiers

**Priorité Wave :** Wave 1 | **Statut :** ✅ Validé

## User Stories

### US-06-01 — Créer un chantier
```
En tant qu'Admin ou Chef de chantier
Je veux créer un nouveau chantier
Afin de commencer à suivre un projet
```
**Priorité :** M

**Critères d'acceptation :**
- [ ] Référence auto-générée (BF-AAAA-NNN, séquentielle par entreprise)
- [ ] Champs obligatoires : Nom, Client, Région, Date de début, Statut
- [ ] Statut initial par défaut : `Prospection`
- [ ] Coordonnées GPS : saisie manuelle ou depuis le navigateur (bouton "Ma position")
- [ ] Photo de couverture : upload optionnel (max 5 Mo)
- [ ] Affectation immédiate de salariés, matériels, modèles de prix (optionnel à la création)

---

### US-06-02 — Liste des chantiers
```
En tant qu'utilisateur
Je veux voir la liste de tous les chantiers
Afin d'avoir une vue d'ensemble
```
**Priorité :** M

**Critères d'acceptation :**
- [ ] Vue liste (tableau) et vue cartes (cards) — bascule
- [ ] Colonnes liste : Référence, Nom, Client, Région, Statut, Dates, Budget, Total ACHATS, Total VENTES, Bénéfice
- [ ] Filtres : Statut, Région, Client, Période (date début)
- [ ] Tri sur toutes les colonnes
- [ ] Barre de recherche globale (nom, ref, client)
- [ ] Pagination 25 éléments
- [ ] Export Excel de la liste filtrée

---

### US-06-03 — Fiche chantier
```
En tant qu'utilisateur autorisé
Je veux accéder à la fiche complète d'un chantier
Afin de piloter toutes ses dimensions
```
**Priorité :** M

**Critères d'acceptation :**
- [ ] En-tête : référence, nom, client, région, statut (modifiable inline), dates
- [ ] Bloc indicateurs financiers toujours visible (voir CDC §6.3)
- [ ] 12 onglets définis dans le CDC §6.3
- [ ] Changement de statut : dropdown avec transitions valides uniquement
  - Ex : on ne peut pas passer de `Clôturé` à `En cours`
- [ ] Bouton "Export PDF fiche complète"
- [ ] Bouton "Export Excel synthèse"

---

### US-06-04 — Changer le statut d'un chantier
```
En tant que Chef ou Admin
Je veux faire évoluer le statut d'un chantier
Afin de refléter l'avancement réel du projet
```
**Priorité :** M

**Transitions autorisées :**

```
Prospection → Devis envoyé → En cours → En pause → Terminé → Clôturé
     ↓                          ↓                      ↓
  Annulé                    Annulé                  (final)
```

**Critères d'acceptation :**
- [ ] Modal de confirmation pour passage à `Clôturé` ou `Annulé`
- [ ] Passage à `Clôturé` déclenche la génération du rapport de clôture
- [ ] Journal d'activité enregistre chaque changement de statut (qui, quand, ancien → nouveau)

---

# SPEC-07 — Achats & Dépenses

**Priorité Wave :** Wave 1 | **Statut :** ✅ Validé

## User Stories

### US-07-01 — Ajouter une dépense
```
En tant qu'utilisateur avec permission Achats
Je veux enregistrer une dépense sur un chantier
Afin de suivre les coûts réels
```
**Priorité :** M

**Critères d'acceptation :**
- [ ] Champs obligatoires : Chantier, Date, Catégorie, Description, Montant total
- [ ] Si Quantité ET Prix unitaire sont renseignés → Montant calculé automatiquement
- [ ] Fournisseur : sélection depuis la liste (optionnel)
- [ ] Justificatif : upload photo/PDF (optionnel, max 10 Mo)
- [ ] Mode de paiement : champ indicatif texte libre (non obligatoire)
- [ ] Statut validation : `Saisie` par défaut (peut être `Validée` ou `Rejetée` par chef/admin)
- [ ] Accessible depuis la fiche chantier (onglet ACHATS) et depuis le menu global

---

### US-07-02 — Liste des dépenses d'un chantier
```
En tant qu'utilisateur
Je veux consulter toutes les dépenses d'un chantier
Afin de comprendre où va l'argent
```
**Priorité :** M

**Critères d'acceptation :**
- [ ] Tableau : Date, Catégorie, Description, Fournisseur, Montant, Statut, Justificatif
- [ ] Filtres : Catégorie, Période, Statut validation
- [ ] Total par catégorie visible (tableau récapitulatif + graphique camembert)
- [ ] Total général en bas de tableau
- [ ] Export PDF et Excel

---

### US-07-03 — Workflow de validation (optionnel)
```
En tant qu'Admin ou Chef de chantier
Je veux valider ou rejeter les dépenses saisies par mon équipe
Afin de contrôler les coûts
```
**Priorité :** C

**Critères d'acceptation :**
- [ ] Boutons Valider / Rejeter (avec motif de rejet obligatoire)
- [ ] Seules les dépenses `Validées` entrent dans le calcul du total ACHATS
- [ ] Notification à l'auteur de la dépense en cas de rejet
- [ ] Workflow activable/désactivable dans les paramètres

---

# SPEC-08 — Bons de commande

**Priorité Wave :** Wave 2 | **Statut :** ✅ Validé

## User Stories

### US-08-01 — Créer un bon de commande
```
En tant qu'utilisateur Achats
Je veux créer un bon de commande pour un fournisseur
Afin de formaliser un engagement d'achat
```
**Priorité :** S

**Critères d'acceptation :**
- [ ] Numérotation auto : BC-AAAA-NNN
- [ ] Chantier lié (obligatoire)
- [ ] Fournisseur (obligatoire)
- [ ] Date d'émission et date de livraison souhaitée
- [ ] Lignes de commande : Désignation, Quantité, Unité, Prix unitaire, Total ligne
- [ ] Totaux calculés automatiquement
- [ ] Conditions de livraison / notes (texte libre)
- [ ] Export PDF
- [ ] Envoi par email au fournisseur

**Transitions de statut :**
`Brouillon` → `Envoyé` → `Partiellement livré` → `Livré` → `Annulé`

---

### US-08-02 — Convertir BC en dépense
```
En tant qu'utilisateur Achats
Je veux transformer un bon de commande livré en dépense
Afin d'éviter la double saisie
```
**Priorité :** S

**Critères d'acceptation :**
- [ ] Bouton "Convertir en dépense" sur le BC au statut `Livré` ou `Partiellement livré`
- [ ] Pré-remplissage automatique de la dépense (fournisseur, montant, chantier, description)
- [ ] Modification possible avant validation
- [ ] Lien BC → Dépense conservé (traçabilité)

---

### US-09-04 — Versioning d'un devis
```
En tant qu'utilisateur VENTES
Je veux conserver l'historique des versions de mes devis
Afin de garder une traçabilité complète des modifications
```
**Priorité :** S

**Critères d'acceptation :**
- [ ] Colonne `version` (integer, défaut 1) sur la table `quotes`
- [ ] Toute modification d'un devis au statut `Envoyé` crée automatiquement une nouvelle version (incrémentation)
- [ ] Les versions précédentes sont archivées : lecture seule, non modifiables
- [ ] Onglet « Versions » sur la fiche devis : liste des versions avec date, auteur, statut à ce moment
- [ ] Le PDF généré affiche le numéro de version : « DEV-2026-001 — Version 3 »
- [ ] Seule la dernière version est active pour facturation

---

# SPEC-09 — Devis

**Priorité Wave :** Wave 1 | **Statut :** ✅ Validé

## User Stories

### US-09-01 — Créer un devis
```
En tant qu'utilisateur VENTES
Je veux créer un devis pour un client
Afin de proposer une offre commerciale formelle
```
**Priorité :** M

**Critères d'acceptation :**
- [ ] Créable depuis : fiche chantier, fiche client, menu VENTES
- [ ] Numérotation auto : DEV-AAAA-NNN
- [ ] Client et Chantier liés (chantier optionnel)
- [ ] Date d'émission (auto = aujourd'hui) et Date d'expiration
- [ ] Sections/Lots : possibilité d'organiser les lignes en groupes nommés
- [ ] Lignes de devis : Désignation, Section, Quantité, Unité, Prix unitaire, Remise %, TVA %, Montant HT
- [ ] Pré-remplissage depuis la bibliothèque de prix (recherche article → auto-remplissage prix)
- [ ] Calcul en temps réel : Total HT, Remise globale, Total TVA, Total TTC
- [ ] Remise globale applicable en plus des remises par ligne
- [ ] Zone mentions légales (pré-remplie depuis paramètres, modifiable)
- [ ] Conditions de paiement (texte libre)
- [ ] Duplication d'un devis existant
- [ ] Brouillon sauvegardé automatiquement

---

### US-09-02 — Gestion des statuts devis
```
En tant qu'utilisateur VENTES
Je veux faire évoluer le statut d'un devis
Afin de suivre l'avancement commercial
```
**Priorité :** M

**Transitions :**
```
Brouillon → Envoyé → Accepté → Transformé en facture
                  ↓
               Refusé
                  ↓
               Expiré (automatique si date dépassée)
```

**Critères d'acceptation :**
- [ ] Passage à `Envoyé` : envoi email avec PDF en pièce jointe
- [ ] Lien de validation client (URL unique) → client clique "J'accepte" ou "Je refuse"
- [ ] Acceptation via lien → statut passe à `Accepté` + notification admin
- [ ] Devis `Accepté` : bouton "Créer la facture" (ou "Créer une situation")
- [ ] Dépassement de la date d'expiration → statut passe automatiquement à `Expiré` (job planifié)

---

### US-09-03 — Exporter un devis en PDF
```
En tant qu'utilisateur VENTES
Je veux exporter le devis en PDF professionnel
Afin de l'envoyer au client
```
**Priorité :** M

**Critères d'acceptation :**
- [ ] PDF inclut : logo entreprise, coordonnées, numéro devis, date, expiration
- [ ] En-tête client et chantier
- [ ] Tableau des lignes (par section si applicable)
- [ ] Totaux HT / Remise / TVA / TTC
- [ ] Mentions légales
- [ ] Pied de page : numéro de page, date génération
- [ ] Couleurs de l'entreprise appliquées (si personnalisées)

---

# SPEC-10 — Avenants

**Priorité Wave :** Wave 2 | **Statut :** ✅ Validé

## User Stories

### US-10-01 — Créer un avenant
```
En tant qu'utilisateur VENTES
Je veux créer un avenant à un devis existant
Afin de formaliser des travaux supplémentaires ou des modifications
```
**Priorité :** S

**Critères d'acceptation :**
- [ ] Lié à un devis `Accepté` ou directement à un chantier
- [ ] Numérotation : AVN-AAAA-NNN
- [ ] Description libre des modifications
- [ ] Lignes : Désignation, Quantité, Unité, Prix unitaire, Montant (positif ou négatif)
- [ ] Montant net avenant (ajout ou déduction)
- [ ] Même workflow de validation client que le devis
- [ ] Intégration au total facturable : Total contrat = Devis + Σ Avenants acceptés
- [ ] Export PDF

---

# SPEC-11 — Situations de travaux

**Priorité Wave :** Wave 2 | **Statut :** ✅ Validé

## User Stories

### US-11-01 — Créer une situation de travaux
```
En tant qu'utilisateur VENTES
Je veux émettre une facture d'avancement sur un chantier en cours
Afin de facturer progressivement selon l'état réel des travaux
```
**Priorité :** S

**Critères d'acceptation :**
- [ ] Lié à un chantier et un ou plusieurs devis
- [ ] Numérotation : SIT-AAAA-NNN-Sxx (xx = numéro de situation)
- [ ] Tableau d'avancement : pour chaque ligne du devis, saisir % réalisé à ce stade
- [ ] Calcul automatique :
  - Montant situation = Σ (% situation × montant ligne devis)
  - Déduction des situations précédentes
  - Montant net à facturer
- [ ] Gestion retenue de garantie : affichage RG déduite + net à payer
- [ ] Génération automatique de la facture de situation associée
- [ ] Export PDF situation

---

# SPEC-12 — Facturation

**Priorité Wave :** Wave 1 | **Statut :** ✅ Validé

## User Stories

### US-12-01 — Créer une facture
```
En tant qu'utilisateur VENTES
Je veux créer une facture
Afin de demander le règlement au client
```
**Priorité :** M

**Types de facture :**
- **Simple** : créée manuellement
- **Depuis devis** : transformation 1 clic (lignes reprises du devis)
- **D'acompte** : % du devis total (ex : 30% à la signature)
- **De situation** : générée par le module situations de travaux
- **Finale** : solde de tout compte

**Critères d'acceptation :**
- [ ] Numérotation auto : FAC-AAAA-NNN
- [ ] Champs : Client, Chantier (optionnel), Date d'émission, Date d'échéance
- [ ] Lignes : même format que devis
- [ ] Calculs HT / TVA / TTC
- [ ] Retenue de garantie affichée si taux > 0 paramétré
- [ ] Mention des acomptes déjà versés (déduction)
- [ ] Conditions de paiement (texte libre)
- [ ] Statuts : `Brouillon` → `Émise` → `Partiellement payée` → `Soldée` → `Annulée`
- [ ] Une facture `Soldée` n'est plus modifiable
- [ ] Export PDF + Envoi email

---

### US-12-02 — Avoir
```
En tant qu'utilisateur VENTES
Je veux émettre un avoir
Afin de corriger ou annuler une facture
```
**Priorité :** S

**Critères d'acceptation :**
- [ ] Lié à une facture existante (obligatoire)
- [ ] Numérotation : AVO-AAAA-NNN
- [ ] Montant partiel ou total de la facture d'origine
- [ ] Motif (texte libre)
- [ ] Imputation automatique sur le solde client
- [ ] Export PDF

---

# SPEC-13 — Paiements

**Priorité Wave :** Wave 1 | **Statut :** ✅ Validé

## User Stories

### US-13-01 — Enregistrer un paiement
```
En tant qu'utilisateur VENTES
Je veux enregistrer un paiement reçu d'un client
Afin de suivre les encaissements
```
**Priorité :** M

**Critères d'acceptation :**
- [ ] Lié à une ou plusieurs factures (ventilation possible)
- [ ] Champs : Date, Montant, Référence (texte libre, optionnel)
- [ ] Mode de paiement : **champ purement indicatif** — liste déroulante (Espèces, Chèque, Virement, MVola, Orange Money, Airtel Money, Autre) + champ texte libre "Précision"
- [ ] Justificatif (photo/scan, optionnel)
- [ ] Si montant paiement = solde restant → facture passe automatiquement à `Soldée`
- [ ] Si montant partiel → facture passe à `Partiellement payée`
- [ ] Ventilation sur plusieurs factures : saisir le montant imputé sur chaque facture

> ⚠️ **Rappel :** BuildFlow n'intègre aucune passerelle de paiement. Le mode de paiement est informatif uniquement.

---

### US-13-02 — Suivi des encaissements
```
En tant qu'utilisateur VENTES ou Comptable
Je veux voir les factures impayées ou en retard
Afin de relancer les clients
```
**Priorité :** M

**Critères d'acceptation :**
- [ ] Vue "Balance âgée" : factures regroupées par tranche de retard (0-30j, 30-60j, 60-90j, +90j)
- [ ] Filtre par client, chantier, période
- [ ] Indicateurs : Total impayé, Total en retard, Total à venir
- [ ] Bouton "Envoyer relance email" sur chaque facture en retard (email pré-rempli modifiable)

---

# SPEC-14 — Clôture & PV Réception

**Priorité Wave :** Wave 3 | **Statut :** ✅ Validé

## User Stories

### US-14-01 — Procès-Verbal de réception
```
En tant qu'Admin ou Chef de chantier
Je veux générer un PV de réception
Afin de formaliser la fin des travaux avec le client
```
**Priorité :** S

**Critères d'acceptation :**
- [ ] Accessible depuis la fiche chantier quand statut = `Terminé`
- [ ] Champs : Date réception, Participants, Réserves (liste)
- [ ] Réserves : description, délai de levée, responsable
- [ ] Signature client (case à cocher + nom imprimé, ou lien de signature numérique)
- [ ] Libération RG : bouton "Libérer la retenue de garantie" après levée des réserves
- [ ] Export PDF
- [ ] Passage automatique du chantier à `Clôturé` après PV signé

---

### US-14-02 — Rapport de clôture financière
```
En tant qu'Admin
Je veux générer un rapport de clôture
Afin de faire le bilan complet du chantier
```
**Priorité :** S

**Critères d'acceptation :**
- [ ] Généré automatiquement au passage à `Clôturé`
- [ ] Contenu : voir CDC §9.2
- [ ] Export PDF du rapport
- [ ] Stocké dans les documents du chantier

---

# SPEC-15 — Compte-rendus de chantier

**Priorité Wave :** Wave 2 | **Statut :** ✅ Validé

## User Stories

### US-15-01 — Créer un compte-rendu
```
En tant que Chef de chantier
Je veux rédiger un compte-rendu de réunion
Afin de formaliser les décisions et actions
```
**Priorité :** S

**Critères d'acceptation :**
- [ ] Numérotation : CR-AAAA-NNN
- [ ] Champs : Date, Lieu, Participants (multi-sélection depuis salariés + saisie libre)
- [ ] Ordre du jour (liste de points)
- [ ] Pour chaque point : discussion + décision
- [ ] Actions : liste (intitulé, responsable, délai)
- [ ] Date prochaine réunion
- [ ] Photos jointes (depuis galerie chantier)
- [ ] Export PDF
- [ ] Envoi email aux participants listés

---

# SPEC-16 — Tâches

**Priorité Wave :** Wave 2 | **Statut :** ✅ Validé

## User Stories

### US-16-01 — Gérer les tâches d'un chantier
```
En tant que Chef de chantier
Je veux créer et suivre les tâches à réaliser
Afin de coordonner mon équipe
```
**Priorité :** S

**Critères d'acceptation :**
- [ ] CRUD tâches avec tous les champs du CDC
- [ ] Sous-tâches (checklist) : ajout dynamique, cochage
- [ ] Assignation à un ou plusieurs salariés du chantier
- [ ] Commentaires sur la tâche (fil de discussion)
- [ ] Pièces jointes (documents, photos)
- [ ] Vue liste + vue Kanban (colonnes par statut)
- [ ] Filtres : assigné à, priorité, statut, date d'échéance
- [ ] Alerte tâche en retard : mise en évidence visuelle rouge

---

# SPEC-17 — Planning & Calendrier

**Priorité Wave :** Wave 2 | **Statut :** ✅ Validé

## User Stories

### US-17-01 — Calendrier des chantiers
```
En tant qu'utilisateur
Je veux voir l'ensemble des chantiers sur un calendrier
Afin d'avoir une vue planning globale
```
**Priorité :** S

**Critères d'acceptation :**
- [ ] Vue mensuelle et hebdomadaire
- [ ] Un chantier = barre colorée de sa date début à sa date fin
- [ ] Clic sur un chantier → accès à la fiche
- [ ] Filtre par région, statut, salarié
- [ ] Affichage des tâches en option
- [ ] Détection visuelle de conflits (salarié doublement affecté au même moment)
- [ ] Export PDF du planning hebdomadaire

---

# SPEC-18 — Pointage

**Priorité Wave :** Wave 2 | **Statut :** ✅ Validé

## User Stories

### US-18-01 — Pointage terrain (mobile)
```
En tant que salarié sur chantier
Je veux pointer mon arrivée et mon départ depuis mon téléphone
Afin d'enregistrer mes heures
```
**Priorité :** S

**Critères d'acceptation :**
- [ ] Interface PWA simplifiée : sélection chantier + bouton "Pointer l'entrée"
- [ ] Capture GPS automatique (latitude, longitude, précision) — non bloquante si refusée
- [ ] Heure enregistrée automatiquement (heure serveur)
- [ ] Bouton "Pointer la sortie" visible après check-in
- [ ] Calcul automatique des heures (sortie − entrée)
- [ ] Possible hors-ligne : stocké localement → synchronisé à la reconnexion

---

### US-18-02 — Saisie manuelle (chef de chantier)
```
En tant que Chef de chantier
Je veux saisir ou corriger le pointage de mon équipe
Afin de garantir l'exactitude des données
```
**Priorité :** S

**Critères d'acceptation :**
- [ ] Grille de saisie : lignes = salariés, colonnes = jours
- [ ] Saisie heures ou jours (selon type de contrat du salarié)
- [ ] Statut : Présent / Absent justifié / Absent non justifié
- [ ] Modification d'un pointage existant avec motif obligatoire
- [ ] Validation par l'admin optionnelle (selon paramétrage)

---

### US-18-03 — Récapitulatif & Paie
```
En tant qu'Admin ou RH
Je veux obtenir le récapitulatif mensuel de chaque salarié
Afin de préparer la paie
```
**Priorité :** S

**Critères d'acceptation :**
- [ ] Récapitulatif mensuel : heures totales, jours, absences, salaire brut calculé
- [ ] Bulletin de salaire simplifié générable en PDF
- [ ] Export CSV compatible logiciel de paie externe
- [ ] Filtre par salarié, chantier, période

---

# SPEC-19 — Documents chantier

**Priorité Wave :** Wave 2 | **Statut :** ✅ Validé

## User Stories

### US-19-01 — Gérer les documents d'un chantier
```
En tant qu'utilisateur autorisé
Je veux uploader et organiser des documents sur un chantier
Afin d'avoir tout centralisé
```
**Priorité :** S

**Critères d'acceptation :**
- [ ] Drag & drop ou bouton d'upload
- [ ] Types acceptés : PDF, DOCX, XLSX, JPG, PNG, DWG (max 50 Mo par fichier)
- [ ] Catégories de classement (liste du CDC)
- [ ] Prévisualisation in-app (PDF, images)
- [ ] Partage via lien sécurisé avec expiration (7 / 30 jours)
- [ ] Versionning : re-upload d'un document → nouvelle version, ancienne conservée
- [ ] Téléchargement individuel ou en ZIP (sélection multiple)
- [ ] Stocké hors webroot, accès contrôlé par le middleware

---

# SPEC-20 — Photos & Galerie

**Priorité Wave :** Wave 2 | **Statut :** ✅ Validé

## User Stories

### US-20-01 — Gérer les photos d'un chantier
```
En tant qu'utilisateur terrain
Je veux prendre ou uploader des photos depuis mon mobile
Afin de documenter visuellement le chantier
```
**Priorité :** S

**Critères d'acceptation :**
- [ ] Capture directe depuis caméra (PWA — API MediaDevices)
- [ ] Upload depuis galerie mobile (multi-sélection)
- [ ] Compression automatique : max 1920px large, conversion WebP
- [ ] Métadonnées : date, auteur, chantier, catégorie (phase), commentaire
- [ ] Galerie en vue grille avec miniatures
- [ ] Lightbox sur clic (navigation suivant/précédent)
- [ ] Filtres : catégorie, date, auteur
- [ ] Téléchargement ZIP de la sélection

---

# SPEC-21 — Matériels & Équipements

**Priorité Wave :** Wave 3 | **Statut :** ✅ Validé

## User Stories

### US-21-01 — Inventaire matériels
```
En tant qu'Admin
Je veux maintenir l'inventaire de mes équipements
Afin de les affecter aux chantiers
```
**Priorité :** C

**Critères d'acceptation :**
- [ ] CRUD équipements : nom, catégorie, immatriculation, valeur d'achat, état
- [ ] États : Disponible / En service / En maintenance / Hors service
- [ ] Affectation à un chantier (dates + coût journalier)
- [ ] Coût affectation → intégré automatiquement aux ACHATS du chantier (catégorie "Location matériel")
- [ ] Alerte de fin d'affectation (J-3)
- [ ] Historique des affectations

---

### US-21-02 — Maintenance
```
En tant qu'Admin
Je veux suivre la maintenance de mes équipements
Afin d'éviter les pannes
```
**Priorité :** C

**Critères d'acceptation :**
- [ ] Enregistrement intervention (type, date, coût, prestataire)
- [ ] Prochain entretien (date ou kilométrage)
- [ ] Alerte maintenance préventive J-7

---

# SPEC-22 — Gestion des stocks

**Priorité Wave :** Wave 3 | **Statut :** ✅ Validé

## User Stories

### US-22-01 — Gérer le stock
```
En tant qu'utilisateur Stocks
Je veux suivre les entrées et sorties de matériaux
Afin d'éviter les ruptures
```
**Priorité :** C

**Critères d'acceptation :**
- [x] CRUD articles stock (nom, catégorie, unité, stock initial)
- [x] Entrée stock : liée à un BC/livraison fournisseur ou saisie manuelle
- [x] Sortie stock : liée à un chantier (consommation)
- [x] Stock minimum : alerte notification quand seuil atteint
- [ ] Valorisation PAMP (Prix d'Achat Moyen Pondéré) calculée automatiquement
- [x] Transfert entre dépôts
- [x] Ajustement inventaire physique (avec motif)
- [x] Rapport consommation par chantier

### US-22-02 — Dépôts de Chantier & Achat Direct
```
En tant que Chef de chantier ou Gestionnaire
Je veux réceptionner une livraison fournisseur directement sur mon chantier
Afin que le stock local soit mis à jour sans passer par le dépôt central
```
**Priorité :** S

**Critères d'acceptation :**
- [x] Un dépôt (`Warehouse`) peut être rattaché à un projet (`project_id`).
- [x] Lors de la saisie d'un mouvement d'entrée, possibilité de choisir n'importe quel dépôt (central ou chantier).
- [x] Intégration BC : lors de la réception d'un Bon de Commande, l'utilisateur choisit le dépôt de destination (chantier spécifique ou stock central).

### US-22-03 — Approvisionnement par Transfert
```
En tant que Logisticien
Je veux transférer du matériel du dépôt central vers un dépôt de chantier
Afin d'alimenter le chantier en ressources déjà achetées
```
**Priorité :** S

**Critères d'acceptation :**
- [x] Formulaire de transfert : Dépôt Source → Dépôt Destination.
- [x] Le transfert génère deux mouvements automatiques : une sortie (source) et une entrée (destination).
- [x] Historique des transferts consultable.

---

# SPEC-23 — Notifications

**Priorité Wave :** Wave 1 (basique) / Wave 2 (complet) | **Statut :** ✅ Validé

## User Stories

### US-23-01 — Notifications in-app
```
En tant qu'utilisateur
Je veux recevoir des alertes dans l'application
Afin d'être informé des événements importants
```
**Priorité :** M (in-app) / S (email)

**Critères d'acceptation :**
- [ ] Cloche de notification dans la barre de navigation
- [ ] Badge rouge avec compteur (non lus)
- [ ] Liste déroulante des 20 dernières notifications
- [ ] Marquage lu/non lu, "Tout marquer comme lu"
- [ ] Page complète de toutes les notifications
- [ ] Clic sur notification → redirection vers l'objet concerné
- [ ] Email envoyé en parallèle pour les événements critiques (voir tableau CDC)
- [ ] Paramétrage activation/désactivation par type
- [ ] **Alerte dépassement budget** : notification in-app + email quand ACHATS dépassent le seuil paramétré (défaut 80%) puis à 100% du budget prévisionnel
- [ ] **Alerte retard chantier** : notification in-app + email quand la date de fin prévisionnelle est dépassée (job quotidien Laravel Scheduler) ; relance J+7

---

### US-23-02 — Relance automatique des impayés
```
En tant qu'Admin ou Comptable
Je veux que l'application relance automatiquement les clients en retard de paiement
Afin de réduire les créances sans action manuelle
```
**Priorité :** S

**Critères d'acceptation :**
- [ ] Séquence de relance configurable dans Paramètres entreprise :
  - Relance 1 : J+7 après échéance (email doux)
  - Relance 2 : J+14 (email ferme)
  - Relance 3 : J+30 (email mise en demeure)
- [ ] Template d'email modifiable par étape (corps HTML via éditeur WYSIWYG)
- [ ] Activation/désactivation globale (Paramètres) et par client (fiche client)
- [ ] Log des relances envoyées : date, étape, facture concernée, adresse email
- [ ] Bouton manuel « Envoyer relance » conservé en complément
- [ ] Dédoublonnage : une relance déjà envoyée à cette étape ne se réenvoie pas

---

# SPEC-24 — Dashboard & Rapports

**Priorité Wave :** Wave 1 (basique) / Wave 3 (avancé) | **Statut :** ✅ Validé

## User Stories

### US-24-01 — Dashboard principal
```
En tant qu'Admin ou Chef de chantier
Je veux voir les indicateurs clés à l'ouverture de l'application
Afin de piloter l'activité en un coup d'œil
```
**Priorité :** M

**Critères d'acceptation :**
- [ ] KPI cards : CA mois, Total dépenses mois, Bénéfice, Taux de marge moyen
- [ ] Alertes en haut : factures en retard (nombre + montant), devis expirant bientôt
- [ ] Liste chantiers actifs (5 derniers actifs)
- [ ] Graphique CA mensuel (12 derniers mois) — courbe Chart.js
- [ ] Graphique répartition dépenses par catégorie — camembert
- [ ] Chiffres adaptés aux droits de l'utilisateur (le chef de chantier voit ses chantiers seulement)

---

### US-24-02 — Rapports exportables
```
En tant qu'Admin ou Comptable
Je veux exporter des rapports financiers
Afin de les utiliser en dehors de BuildFlow
```
**Priorité :** S

**Critères d'acceptation :**
- [ ] Rapport sélectionnable par type (voir tableau CDC §19.2)
- [ ] Filtres période (date début / date fin)
- [ ] Filtres chantier et/ou client selon rapport
- [ ] Export PDF et Excel (XLSX)
- [ ] Génération en arrière-plan (queue) pour les rapports lourds + notification quand prêt

---

# SPEC-25 — Bibliothèque de prix

**Priorité Wave :** Wave 2 | **Statut :** ✅ Validé

## User Stories

### US-25-01 — Gérer la bibliothèque de matériaux
```
En tant qu'Admin
Je veux maintenir une bibliothèque de prix de référence
Afin d'accélérer la création de devis
```
**Priorité :** S

**Critères d'acceptation :**
- [ ] CRUD articles (nom, catégorie, unité, prix de référence par région)
- [ ] Prix par région : une ligne par région avec son propre tarif
- [ ] Import/Export CSV
- [ ] Lors d'une modification de prix → tracé dans `price_history` (ancien, nouveau, date, utilisateur)
- [ ] Utilisation dans les devis : champ recherche article → auto-remplissage du prix (modifiable)

---

### US-25-02 — Grille salariale
```
En tant qu'Admin
Je veux définir des tarifs de référence par métier et région
Afin de faciliter la saisie des dépenses main d'œuvre
```
**Priorité :** S

**Critères d'acceptation :**
- [ ] Grille : Métier × Région → Tarif horaire + Tarif journalier
- [ ] Héritage : si un salarié n'a pas de tarif propre → utilise la grille de référence
- [ ] Historique des modifications

---

### US-25-03 — Modèles de dosage (recettes techniques)
```
En tant qu'Admin ou Conducteur de travaux
Je veux créer des modèles de dosage par type d'ouvrage
Afin de standardiser le chiffrage et accélérer la création de devis
```
**Priorité :** S

**Critères d'acceptation :**
- [x] CRUD modèles de dosage : nom, unité de sortie, quantité de sortie (ex : 1 m³ béton B25)
- [x] Items du modèle : type (material / labor / equipment / subcontract), lien matériau, désignation libre, quantité par unité, taux de perte (`waste_rate`)
- [x] Quantité effective = `quantité × (1 + waste_rate / 100)`
- [x] Prévisualisation fiche modèle : liste des ressources et coût estimatif par unité d'ouvrage
- [ ] Duplication d'un modèle de dosage existant
- [ ] Import/Export CSV des modèles de dosage

---

### US-25-04 — Calcul DBE & prix de vente depuis un modèle de dosage
```
En tant qu'utilisateur VENTES
Je veux calculer le Déboursé de Base Estimatif (DBE) pour une ligne de devis
Afin d'obtenir un prix de vente cohérent basé sur les coûts réels de production
```
**Priorité :** S

**Formule de calcul :**

$$
\text{DBE\_unitaire} = \sum_i \left( q_i \times (1 + \frac{waste_i}{100}) \times p_i \right)
$$

$$
K = \left(1 + \frac{FG\%}{100}\right) \times \left(1 + \frac{Marge\%}{100}\right) \times \left(1 + \frac{Al\acute{e}as\%}{100}\right)
$$

$$
\text{Prix de vente} = \text{DBE\_unitaire} \times K
$$

**Critères d'acceptation :**
- [x] `QuoteCalculationService::calculateFromDosage(dosageModelId, quantity, regionId)` — retourne DBE ventilé par type (matériaux, main d'œuvre, matériel, sous-traitance) + breakdown ligne par ligne
- [x] `QuoteCalculationService::applyCoefficients(dbeTotal, quantity, fgRate, marginRate, aleaRate)` — retourne coefficient K et prix unitaire final
- [x] Les prix sont récupérés depuis `material_prices` en fonction de la région (fallback sur prix global)
- [x] Si un prix est manquant pour une ressource → `missing_prices[]` listé dans le résultat (avertissement non-bloquant)
- [x] Endpoint AJAX `POST /dosage/{dosage}/calculate` accessible depuis le formulaire devis
- [ ] Intégration formulaire devis : bouton "Calculer depuis dosage" sur une ligne → modal de sélection modèle + quantité + région → auto-remplissage prix unitaire
- [ ] Affichage marge / coefficient K calculé en temps réel lors de la saisie du prix de vente
- [ ] Les taux FG%, Marge%, Aléas% pré-remplis depuis les paramètres entreprise (modifiables à la volée)

---

# SPEC-26 — Paramètres entreprise

**Priorité Wave :** Wave 1 | **Statut :** ✅ Validé

## User Stories

### US-26-01 — Configurer l'entreprise
```
En tant qu'Admin Entreprise
Je veux configurer les paramètres de mon espace
Afin d'adapter BuildFlow à mon entreprise
```
**Priorité :** M

**Critères d'acceptation :**
- [ ] Informations entreprise : nom, logo (PNG/SVG, max 2 Mo), NIF/STAT, adresse, téléphone, email, site web
- [ ] Devise : MGA par défaut, liste d'autres devises disponibles
- [ ] Taux TVA par défaut (0% par défaut — Madagascar)
- [ ] Taux retenue de garantie par défaut (0% par défaut)
- [ ] Préfixes numérotation + numéro de départ par type de document
- [ ] Formules : FG%, Marge%, Aléas% → calcul K automatique et affiché
- [ ] Mentions légales par défaut (texte appliqué à tous les devis/factures)
- [ ] Modèles d'emails (devis, facture, relance) — corps HTML modifiable
- [ ] Gestion des régions (CRUD)
- [ ] Workflow validation dépenses : actif/inactif
- [ ] **Seuil d'alerte budget** : pourcentage configurable (0–100%, défaut 80%) déclenchant les notifications de dépassement
- [ ] **Relance automatique impayés** : activation globale + configuration séquence (voir US-23-02)
- [ ] **Règles de catégorisation dépenses** : liste de mots-clés → catégorie suggérée (CRUD configurable par l'admin)

---

# SPEC-27 — PWA & Hors-ligne

**Priorité Wave :** Wave 1 (installable) / Wave 2 (hors-ligne) | **Statut :** ✅ Validé

## User Stories

### US-27-01 — Application installable
```
En tant qu'utilisateur mobile
Je veux installer BuildFlow sur mon téléphone
Afin d'y accéder comme une application native
```
**Priorité :** M

**Critères d'acceptation :**
- [ ] Manifest.json correctement configuré (nom, icônes, couleurs, orientation)
- [ ] Service Worker enregistré (Cache First pour assets)
- [ ] Bannière d'installation affichée (beforeinstallprompt)
- [ ] Icône et splash screen personnalisés avec le branding BuildFlow
- [ ] Compatible Chrome Android + Safari iOS

---

### US-27-02 — Fonctionnalités hors-ligne
```
En tant qu'utilisateur terrain sans connexion
Je veux continuer à utiliser les fonctions essentielles
Afin de ne pas bloquer mon travail sur chantier
```
**Priorité :** S

**Critères d'acceptation :**
- [ ] Bandeau visible "Mode hors-ligne" quand déconnecté
- [ ] Pointage hors-ligne : stocké IndexedDB, synchronisé à la reconnexion
- [ ] Photos hors-ligne : stockées localement, envoyées à la reconnexion
- [ ] Dépenses hors-ligne : idem
- [ ] Tâches assignées consultables depuis le cache
- [ ] Conflits de sync : notification à l'utilisateur (last-write-wins par défaut)

---

# SPEC-28 — Multi-tenant & SaaS

**Priorité Wave :** Wave 1 | **Statut :** ✅ Validé

## User Stories

### US-28-01 — Inscription entreprise
```
En tant que nouveau client
Je veux créer mon espace BuildFlow
Afin de commencer à l'utiliser
```
**Priorité :** M

**Critères d'acceptation :**
- [ ] Page d'inscription publique : nom entreprise, nom/prénom, email, mot de passe
- [ ] Vérification email obligatoire avant accès
- [ ] Création automatique du compte Admin Entreprise
- [ ] Onboarding wizard (5 étapes) : infos entreprise, logo, régions, 1er chantier (optionnel)
- [ ] Affectation du plan Gratuit par défaut

---

### US-28-02 — Isolation des données
```
En tant que système
Je dois garantir qu'un tenant ne voit jamais les données d'un autre
Afin d'assurer la confidentialité
```
**Priorité :** M

**Critères d'acceptation :**
- [ ] Middleware `EnsureTenant` sur toutes les routes authentifiées
- [ ] Scope global Eloquent sur `company_id` sur tous les modèles
- [ ] Tests automatisés : tenter d'accéder à une ressource d'un autre tenant → 403/404
- [ ] Aucun ID devinable cross-tenant (utilisation d'UUID ou vérification systématique)

---

# Annexe — Matrice des permissions par rôle

| Module / Action              | Super Admin | Admin Ent. | Chef Chantier | Comptable | Terrain |
|------------------------------|:-----------:|:----------:|:-------------:|:---------:|:-------:|
| **Chantiers — Voir**         | ✅          | ✅         | ✅ (assignés) | ✅        | ✅ (assignés) |
| **Chantiers — Créer**        | ✅          | ✅         | ✅            | ❌        | ❌      |
| **Chantiers — Modifier**     | ✅          | ✅         | ✅            | ❌        | ❌      |
| **Chantiers — Supprimer**    | ✅          | ✅         | ❌            | ❌        | ❌      |
| **Clients — CRUD**           | ✅          | ✅         | ✅            | ✅        | ❌      |
| **Salariés — CRUD**          | ✅          | ✅         | 🔶 Voir       | ❌        | ❌      |
| **ACHATS — Voir**            | ✅          | ✅         | ✅            | ✅        | ❌      |
| **ACHATS — Créer**           | ✅          | ✅         | ✅            | ✅        | ✅      |
| **ACHATS — Valider**         | ✅          | ✅         | ✅            | ❌        | ❌      |
| **Devis — CRUD**             | ✅          | ✅         | ✅            | ✅        | ❌      |
| **Factures — CRUD**          | ✅          | ✅         | 🔶 Voir       | ✅        | ❌      |
| **Paiements — CRUD**         | ✅          | ✅         | ❌            | ✅        | ❌      |
| **Tâches — CRUD**            | ✅          | ✅         | ✅            | ❌        | 🔶 Voir+Statut |
| **Pointage — Saisir**        | ✅          | ✅         | ✅ (équipe)   | ❌        | ✅ (soi) |
| **Documents — Voir**         | ✅          | ✅         | ✅            | ✅        | ✅      |
| **Documents — Upload**       | ✅          | ✅         | ✅            | ✅        | ✅      |
| **Photos — Voir/Upload**     | ✅          | ✅         | ✅            | ❌        | ✅      |
| **Dashboard**                | ✅          | ✅ (tout)  | ✅ (ses chantiers) | ✅ (financier) | ❌ |
| **Rapports — Export**        | ✅          | ✅         | ✅ (limité)   | ✅        | ❌      |
| **Paramètres entreprise**    | ✅          | ✅         | ❌            | ❌        | ❌      |
| **Gestion utilisateurs**     | ✅          | ✅         | ❌            | ❌        | ❌      |
| **Stocks**                   | ✅          | ✅         | ✅            | ❌        | ❌      |
| **Matériels**                | ✅          | ✅         | 🔶 Voir       | ❌        | ❌      |

> 🔶 = Accès partiel | ✅ = Accès complet | ❌ = Aucun accès  
> Les rôles personnalisés créés par l'Admin peuvent surcharger cette matrice.
