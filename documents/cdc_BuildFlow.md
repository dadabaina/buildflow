# **📘 CAHIER DES CHARGES COMPLET — BuildFlow**

## **SaaS de Gestion de Chantier BTP** 

---

# **1\. Présentation du projet**

## **Nom**

BuildFlow

## **Type**

Application SaaS Web \+ PWA

## **Cible**

* PME BTP  
* Artisans  
* Entreprises construction  
* Sociétés techniques

## **Zone**

* Madagascar  
* Afrique francophone

---

# **2\. Objectifs**

* Centraliser la gestion chantier  
* Suivre dépenses (ACHATS)  
* Suivre revenus (VENTES)  
* Calculer rentabilité réelle  
* Gérer clients et salariés indépendamment  
* Digitaliser opérations terrain

---

# **3\. Concept clé**

Un chantier \=

* 🔻 **ACHATS (coûts)**  
* 🔺 **VENTES (revenus)**

👉 La rentabilité dépend de la différence entre les deux.

---

# **4\. Architecture technique**

Backend : Laravel  
 Frontend : Bootstrap 5 \+ Blade  
 DB : MySQL  
 Mobile : PWA

---

# **5\. Modules fonctionnels**

---

# **MODULE 1 — Authentification**

* login / logout  
* reset password

Rôles :

* Admin  
* L’Admin peut créer un rôle (chef de chantier) et définir en cochant les modules qu’il va autoriser à gérer et affecté à un ou plusieurs utilisateurs.


# **MODULE 2 — Gestion utilisateurs**

* CRUD  
* rôles  
* permissions

---

# **MODULE 3 — Gestion CLIENTS**

## **Fonctionnalités**

* création client  
* modification  
* suppression

## **Champs**

* nom  
* téléphone  
* email  
* adresse  
* région

---

## **Fiche client**

* chantiers associés  
* devis  
* factures  
* paiements  
* total facturé  
* total payé  
* solde

---

# **MODULE 4 — Gestion SALARIÉS**

## **Fonctionnalités**

* CRUD salariés  
* métier  
* région

## **Règle métier**

👉 Un salarié peut travailler dans toutes les régions

---

## **Affectation**

* salarié ↔ chantier (many-to-many)

---

## **Fiche salarié**

* chantiers  
* heures  
* pointages  
* performances

---

# **MODULE 5 — Gestion chantiers**

## **Création**

* nom  
* client  
* budget  
* dates  
* statut

---

## **Affectations**

* salariés  
* matériels  
* modèles de prix

---

# **MODULE 6 — Fiche chantier**

## **Contenu global**

* client  
* salariés  
* tâches  
* dépenses (ACHATS)  
* devis / factures (VENTES)  
* documents  
* photos

---

## **Indicateurs clés**

* total achats  
* total ventes  
* total encaissé  
* reste à payer  
* bénéfice  
* marge

---

## **Export PDF**

---

# **MODULE 7 — ACHATS (Dépenses chantier)**

## **Objectif**

Suivre tous les coûts

---

## **Fonctionnalités**

* ajout dépense  
* catégories  
* justificatifs

---

## **Catégories**

* matériaux  
* salaires  
* transport  
* matériel  
* sous-traitance  
* divers

---

## **Résultats**

* total coûts chantier  
* coût réel

---

# **MODULE 8 — VENTES (NOUVEAU STRUCTURANT)**

---

## **8.1 Devis**

* création devis  
* lignes (matériaux, main d’œuvre)  
* calcul automatique  
* PDF  
* validation client

---

## **8.2 Facturation**

* génération facture  
* transformation devis → facture  
* gestion acomptes

---

## **8.3 Paiements**

* enregistrement paiements  
* multi-paiements  
* modes (cash, mobile money, virement) juste pour informations 

---

## **Suivi financier**

* total facturé  
* total encaissé  
* reste à payer

---

# **MODULE 9 — Documents chantier**

* capture mobile  
* upload fichiers  
* classement

---

# **MODULE 10 — Tâches**

* création  
* assignation  
* suivi

---

# **MODULE 11 — Planning**

* calendrier  
* affectation

---

# **MODULE 12 — Pointage**

* check-in/out  
* validation

---

# **MODULE 13 — Photos**

* capture  
* galerie

---

# **MODULE 14 — Notifications**

* système  
* email

---

# **MODULE 15 — Dashboard**

* ventes  
* achats  
* bénéfices  
* chantiers

---

# **MODULE 16 — Matériels**

* inventaire  
* affectation

---

# **MODULE 17 — Stock**

* entrées/sorties  
* alertes

---

# **MODULE 18 — PWA**

* offline léger  
* mobile

---

# **6\. Modèles de prix**

## **Objectif**

Standardiser les coûts

---

## **Prix matériaux**

* modèles par région  
* prix référence

---

## **Salaires**

* modèles par région  
* tarif par métier

---

## **Règle clé**

👉 Les prix sont :

* suggérés  
* MAIS modifiables en temps réel

---

# **7\. Historique des prix**

* ancien prix  
* nouveau prix  
* date  
* utilisateur

---

# **8\. Calcul financier chantier**

---

## **Déboursé sec (coûts)**

DS=Matériaux+Main d′oeuvre+Mateˊriel+Sous-traitanceDS \= Matériaux \+ Main\\ d'oeuvre \+ Matériel \+ Sous\\text{-}traitanceDS=Mateˊriaux+Main d′oeuvre+Matériel+Sous-traitance

---

## **Total ACHATS**

Achats=∑DépensesAchats \= \\sum DépensesAchats=∑Dépenses

---

## **Total VENTES**

Ventes=∑FacturesVentes \= \\sum FacturesVentes=∑Factures

---

## **Bénéfice chantier**

Bénéfice=Ventes−AchatsBénéfice \= Ventes \- AchatsBénéfice=Ventes−Achats

---

## **Marge**

Marge=Prix de Vente−Coût RéelMarge \= Prix\\ de\\ Vente \- Coût\\ RéelMarge=Prix de Vente−Coût Réel

---

## **Taux de marge**

Taux=MargeCoût×100Taux \= \\frac{Marge}{Coût} \\times 100Taux=CoûtMarge​×100

---

# **9\. Formules paramétrables**

## **Admin peut définir :**

* marge (%)  
* frais généraux  
* aléas  
* TVA  
* coefficient

---

## **Exemple coefficient**

K=11−(FG+Marge+Aleˊas)K \= \\frac{1}{1-(FG \+ Marge \+ Aléas)}K=1−(FG+Marge+Aleˊas)1​

---

# **10\. Base de données**

## **Tables principales**

* users  
* clients  
* employees  
* projects

---

## **ACHATS**

* expenses  
* expense\_categories

---

## **VENTES**

* quotes  
* invoices  
* payments

---

## **Autres**

* tasks  
* documents  
* photos  
* attendances

---

## **Pricing**

* material\_models  
* material\_prices  
* salary\_models  
* salary\_rates  
* price\_history

---

# **11\. UX/UI**

* Bootstrap 5  
* mobile-first  
* simple  
* rapide

---

# **12\. Sécurité**

* hash password  
* permissions  
* sauvegardes

---

# **13\. Performance**

* compression images  
* cache  
* optimisation SQL

