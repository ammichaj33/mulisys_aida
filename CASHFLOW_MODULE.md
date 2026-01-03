# Module Cashflow (Trésorerie)

## Vue d'ensemble

Le module Cashflow permet de gérer toutes les transactions financières de l'application (entrées et sorties d'argent). Il offre une traçabilité complète des flux de trésorerie avec la possibilité de lier les transactions aux crédits et aux membres.

## Structure de la base de données

### Table `cashflow_categories`

Cette table stocke les catégories de transactions (entrées et sorties).

**Champs :**
- `categoryId` (PK) : Identifiant unique
- `categoryName` : Nom de la catégorie
- `categoryType` : Type (`income` ou `expense`)
- `parentCategoryId` (FK, nullable) : Catégorie parente pour les sous-catégories
- `description` : Description de la catégorie
- `isActive` : Statut actif/inactif
- `createdAt`, `updatedAt` : Timestamps

**Catégories par défaut suggérées :**

**Entrées (Income) :**
- Remboursements de crédits
- Intérêts collectés
- Pénalités collectées
- Frais d'étude
- Autres revenus
- Dépôts de membres
- Subventions

**Sorties (Expense) :**
- Octroi de crédits (capital injecté)
- Frais opérationnels
- Salaires
- Loyer/Bureaux
- Services publics (électricité, eau, internet)
- Marketing/Publicité
- Fournitures de bureau
- Maintenance
- Autres dépenses

### Table `cashflow_accounts`

Cette table stocke les différents comptes de trésorerie (caisse, banque, mobile money).

**Champs :**
- `accountId` (PK) : Identifiant unique
- `accountName` : Nom du compte
- `accountType` : Type (`cash`, `bank`, `mobile_money`)
- `initialBalance` : Solde initial
- `currentBalance` : Solde actuel (calculé automatiquement)
- `description` : Description du compte
- `isActive` : Statut actif/inactif
- `createdAt`, `updatedAt` : Timestamps

### Table `cashflow_transactions`

Cette table stocke toutes les transactions financières.

**Champs :**
- `cashflowTransactionId` (PK) : Identifiant unique
- `transactionDate` : Date de la transaction
- `transactionType` : Type (`income` ou `expense`)
- `categoryIdFk` (FK) : Catégorie de la transaction
- `accountIdFk` (FK, nullable) : Compte concerné
- `amount` : Montant de la transaction
- `description` : Description détaillée
- `paymentMethod` : Mode de paiement (`cash`, `bank`, `mobile_money`, `check`)
- `referenceNumber` : Numéro de référence (chèque, virement, etc.)
- `loanDocIdFk` (FK, nullable) : Lien vers un crédit
- `memberIdFk` (FK, nullable) : Lien vers un membre
- `userIdFk` (FK) : Utilisateur qui a enregistré
- `status` : Statut (`pending`, `confirmed`, `cancelled`)
- `confirmedAt` : Date de confirmation
- `confirmedBy` (FK, nullable) : Utilisateur qui a confirmé
- `attachmentPath` : Chemin vers un fichier joint (justificatif)
- `createdAt`, `updatedAt` : Timestamps

## Modèles

### CashflowCategory

**Relations :**
- `parentCategory()` : Belongs to CashflowCategory (catégorie parente)
- `childCategories()` : Has many CashflowCategory (sous-catégories)
- `transactions()` : Has many CashflowTransaction

### CashflowAccount

**Relations :**
- `transactions()` : Has many CashflowTransaction

**Méthodes :**
- `updateBalance()` : Recalcule le solde actuel basé sur les transactions confirmées

### CashflowTransaction

**Relations :**
- `category()` : Belongs to CashflowCategory
- `account()` : Belongs to CashflowAccount
- `loanDoc()` : Belongs to LoanDoc
- `member()` : Belongs to Member
- `user()` : Belongs to User (créateur)
- `confirmedByUser()` : Belongs to User (confirateur)

**Scopes :**
- `income()` : Transactions de type entrée
- `expense()` : Transactions de type sortie
- `confirmed()` : Transactions confirmées

## Contrôleurs

### CashflowController

Gère les transactions (CRUD + confirmation/annulation).

**Méthodes :**
- `index()` : Liste des transactions avec filtres
- `create()` : Formulaire de création
- `store()` : Enregistrement d'une nouvelle transaction
- `show()` : Détail d'une transaction
- `edit()` : Formulaire de modification
- `update()` : Mise à jour d'une transaction
- `destroy()` : Suppression d'une transaction
- `confirm()` : Confirmation d'une transaction en attente
- `cancel()` : Annulation d'une transaction

**Fonctionnalités :**
- Filtrage par date, type, catégorie, compte, statut
- Recherche par description, référence, membre, crédit
- Calcul automatique des totaux (entrées, sorties, solde)
- Mise à jour automatique du solde des comptes

### CashflowCategoryController

Gère les catégories de transactions.

**Méthodes :**
- `index()` : Liste des catégories
- `create()` : Formulaire de création
- `store()` : Enregistrement d'une catégorie
- `edit()` : Formulaire de modification
- `update()` : Mise à jour d'une catégorie
- `destroy()` : Suppression d'une catégorie (si non utilisée)

### CashflowAccountController

Gère les comptes de trésorerie.

**Méthodes :**
- `index()` : Liste des comptes
- `create()` : Formulaire de création
- `store()` : Enregistrement d'un compte
- `show()` : Détail d'un compte avec statistiques
- `edit()` : Formulaire de modification
- `update()` : Mise à jour d'un compte
- `destroy()` : Suppression d'un compte (si non utilisé)
- `recalculateBalance()` : Recalcul manuel du solde

### CashflowReportController

Gère les rapports de trésorerie.

**Méthodes :**
- `cashflowByPeriod()` : Cashflow par période (journalier)
- `cashflowByCategory()` : Cashflow par catégorie
- `treasuryReport()` : Rapport de trésorerie (format comptable)

**Rapports disponibles :**
1. **Cashflow par période** : Affiche les transactions groupées par date avec totaux quotidiens
2. **Cashflow par catégorie** : Affiche les totaux par catégorie
3. **Rapport de trésorerie** : Format comptable avec solde d'ouverture, mouvements, solde de clôture (par compte)

## Routes

Toutes les routes sont préfixées par `/cashflow` et protégées par des permissions.

### Transactions
- `GET /cashflow` : Liste des transactions (`view-cashflow`)
- `GET /cashflow/create` : Formulaire de création (`create-cashflow`)
- `POST /cashflow` : Enregistrement (`create-cashflow`)
- `GET /cashflow/{id}` : Détail (`view-cashflow`)
- `GET /cashflow/{id}/edit` : Formulaire de modification (`edit-cashflow`)
- `PUT /cashflow/{id}` : Mise à jour (`edit-cashflow`)
- `DELETE /cashflow/{id}` : Suppression (`delete-cashflow`)
- `POST /cashflow/{id}/confirm` : Confirmation (`confirm-cashflow`)
- `POST /cashflow/{id}/cancel` : Annulation (`edit-cashflow`)

### Catégories
- `GET /cashflow/categories` : Liste (`manage-cashflow-categories`)
- `GET /cashflow/categories/create` : Formulaire (`manage-cashflow-categories`)
- `POST /cashflow/categories` : Enregistrement (`manage-cashflow-categories`)
- `GET /cashflow/categories/{id}/edit` : Formulaire (`manage-cashflow-categories`)
- `PUT /cashflow/categories/{id}` : Mise à jour (`manage-cashflow-categories`)
- `DELETE /cashflow/categories/{id}` : Suppression (`manage-cashflow-categories`)

### Comptes
- `GET /cashflow/accounts` : Liste (`manage-cashflow-accounts`)
- `GET /cashflow/accounts/create` : Formulaire (`manage-cashflow-accounts`)
- `POST /cashflow/accounts` : Enregistrement (`manage-cashflow-accounts`)
- `GET /cashflow/accounts/{id}` : Détail (`view-cashflow`)
- `GET /cashflow/accounts/{id}/edit` : Formulaire (`manage-cashflow-accounts`)
- `PUT /cashflow/accounts/{id}` : Mise à jour (`manage-cashflow-accounts`)
- `DELETE /cashflow/accounts/{id}` : Suppression (`manage-cashflow-accounts`)
- `POST /cashflow/accounts/{id}/recalculate` : Recalcul (`manage-cashflow-accounts`)

### Rapports
- `GET /cashflow/reports/by-period` : Cashflow par période (`view-cashflow-reports`)
- `GET /cashflow/reports/by-category` : Cashflow par catégorie (`view-cashflow-reports`)
- `GET /cashflow/reports/treasury` : Rapport de trésorerie (`view-cashflow-reports`)

## Permissions

### Permissions disponibles

1. **view-cashflow** : Voir les transactions
2. **create-cashflow** : Créer des transactions
3. **edit-cashflow** : Modifier des transactions
4. **delete-cashflow** : Supprimer des transactions
5. **confirm-cashflow** : Confirmer des transactions
6. **manage-cashflow-categories** : Gérer les catégories
7. **manage-cashflow-accounts** : Gérer les comptes
8. **view-cashflow-reports** : Voir les rapports

### Attribution par rôle

**Caissière :**
- Toutes les permissions (gestion complète)

**Gérant :**
- `view-cashflow` : Voir les transactions
- `view-cashflow-reports` : Voir les rapports

**Directeur :**
- `view-cashflow` : Voir les transactions
- `view-cashflow-reports` : Voir les rapports

## Vues

### Transactions
- `cashflow/index.blade.php` : Liste avec filtres et totaux
- `cashflow/create.blade.php` : Formulaire de création
- `cashflow/edit.blade.php` : Formulaire de modification
- `cashflow/show.blade.php` : Détail d'une transaction

### Catégories
- `cashflow/categories/index.blade.php` : Liste des catégories
- `cashflow/categories/create.blade.php` : Formulaire de création
- `cashflow/categories/edit.blade.php` : Formulaire de modification

### Comptes
- `cashflow/accounts/index.blade.php` : Liste des comptes
- `cashflow/accounts/create.blade.php` : Formulaire de création
- `cashflow/accounts/show.blade.php` : Détail avec statistiques
- `cashflow/accounts/edit.blade.php` : Formulaire de modification

### Rapports
- `cashflow/reports/by-period.blade.php` : Cashflow par période
- `cashflow/reports/by-category.blade.php` : Cashflow par catégorie
- `cashflow/reports/treasury.blade.php` : Rapport de trésorerie

### PDF
- `cashflow/pdf/by-period.blade.php` : PDF cashflow par période
- `cashflow/pdf/by-category.blade.php` : PDF cashflow par catégorie
- `cashflow/pdf/treasury.blade.php` : PDF rapport de trésorerie

## Menu sidebar

Le menu "Trésorerie" est ajouté dans la sidebar avec un système de dropdown (accordion). Il s'affiche automatiquement si l'utilisateur a au moins une permission cashflow.

**Sous-menus :**
- Transactions
- Nouvelle transaction
- Catégories
- Comptes
- Rapports

Le menu s'ouvre automatiquement si l'utilisateur est sur une page cashflow.

## Installation

### 1. Migrations

Exécuter les migrations pour créer les tables :

```bash
php artisan migrate
```

### 2. Seeders

Les permissions sont déjà ajoutées dans `CompleteRolePermissionSeeder` et `PermissionSeeder`. Exécuter :

```bash
php artisan db:seed --class=CompleteRolePermissionSeeder
```

### 3. Catégories par défaut (optionnel)

Créer un seeder pour les catégories par défaut si nécessaire :

```bash
php artisan make:seeder CashflowCategorySeeder
```

## Utilisation

### Enregistrer une transaction

1. Aller dans **Trésorerie > Nouvelle transaction**
2. Remplir le formulaire :
   - Date de la transaction
   - Type (Entrée/Sortie)
   - Catégorie
   - Compte (optionnel)
   - Montant
   - Mode de paiement
   - Description
   - Lier à un crédit ou membre (optionnel)
3. Cliquer sur **Enregistrer**

### Filtrer les transactions

Sur la page **Trésorerie > Transactions**, utiliser les filtres :
- Date de début / fin
- Type (Entrée/Sortie)
- Catégorie
- Statut
- Recherche textuelle

### Gérer les catégories

1. Aller dans **Trésorerie > Catégories**
2. Créer, modifier ou supprimer des catégories
3. Les catégories peuvent avoir des sous-catégories

### Gérer les comptes

1. Aller dans **Trésorerie > Comptes**
2. Créer des comptes (Caisse, Banque, Mobile Money)
3. Définir le solde initial
4. Le solde actuel est calculé automatiquement

### Consulter les rapports

1. Aller dans **Trésorerie > Rapports**
2. Choisir le type de rapport :
   - **Cashflow par période** : Transactions groupées par date
   - **Cashflow par catégorie** : Totaux par catégorie
   - **Rapport de trésorerie** : Format comptable avec soldes
3. Filtrer par période
4. Générer le PDF si nécessaire

## Intégration avec les autres modules

### Liens avec les crédits

Les transactions peuvent être liées aux crédits via `loanDocIdFk`. Cela permet de :
- Traçer les remboursements dans le cashflow
- Lier les octrois de crédits comme sorties
- Générer des rapports par crédit

### Liens avec les membres

Les transactions peuvent être liées aux membres via `memberIdFk`. Utile pour :
- Traçer les dépôts de membres
- Lier les transactions aux membres
- Générer des rapports par membre

## Calculs automatiques

### Solde des comptes

Le solde actuel d'un compte est calculé automatiquement :
```
Solde actuel = Solde initial + Entrées confirmées - Sorties confirmées
```

La méthode `updateBalance()` est appelée automatiquement lors de :
- Création d'une transaction confirmée
- Modification d'une transaction confirmée
- Confirmation d'une transaction
- Annulation d'une transaction

### Totaux dans les listes

Les totaux (entrées, sorties, solde) sont calculés en temps réel selon les filtres appliqués.

## Sécurité

- Toutes les routes sont protégées par le middleware `auth`
- L'accès est contrôlé par des permissions spécifiques
- Seules les transactions non confirmées peuvent être supprimées
- Les transactions annulées ne peuvent pas être modifiées
- Les catégories et comptes utilisés ne peuvent pas être supprimés

## Notes importantes

1. **Statut des transactions** :
   - `pending` : En attente de confirmation
   - `confirmed` : Confirmée (prise en compte dans les calculs)
   - `cancelled` : Annulée (non prise en compte)

2. **Mise à jour des soldes** :
   - Seules les transactions confirmées sont prises en compte
   - Le solde est recalculé automatiquement
   - Un recalcul manuel est possible via le bouton "Recalculer"

3. **Suppression** :
   - Seules les transactions non confirmées peuvent être supprimées
   - Pour annuler une transaction confirmée, utiliser le bouton "Annuler"

4. **Catégories** :
   - Les catégories sont filtrées automatiquement selon le type de transaction
   - Les catégories avec sous-catégories ne peuvent pas être supprimées

5. **Comptes** :
   - Les comptes avec transactions ne peuvent pas être supprimés
   - Le solde initial peut être modifié, le solde actuel sera recalculé

## Évolutions futures possibles

1. **Transactions automatiques** :
   - Enregistrement automatique lors de l'octroi d'un crédit
   - Enregistrement automatique lors d'un remboursement
   - Enregistrement automatique des intérêts et pénalités

2. **Budgets prévisionnels** :
   - Table `cashflow_budgets` pour définir des budgets par catégorie
   - Comparaison budget vs réel
   - Alertes de dépassement

3. **Pièces jointes** :
   - Upload de justificatifs (factures, reçus)
   - Stockage dans `storage/app/cashflow/attachments`

4. **Validation en workflow** :
   - Workflow de validation multi-niveaux
   - Notifications pour les transactions en attente

5. **Export Excel** :
   - Export des transactions en Excel
   - Export des rapports en Excel

6. **Graphiques** :
   - Graphiques d'évolution des flux
   - Graphiques par catégorie
   - Tableaux de bord visuels

