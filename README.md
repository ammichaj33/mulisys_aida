# Application de Gestion de Microcrédits - Laravel

## Description
Application Laravel pour la gestion de microcrédits avec gestion des rôles via Spatie Laravel Permission.

## Prérequis
- PHP >= 7.3
- Composer
- MySQL/MariaDB
- Node.js et NPM (pour les assets front-end)

## Installation

### 1. Configuration de la base de données
Créez une base de données MySQL et configurez le fichier `.env` :

```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=loans_laravel
DB_USERNAME=root
DB_PASSWORD=
```

### 2. Installation des dépendances
```bash
composer install
npm install
```

### 3. Génération de la clé d'application
```bash
php artisan key:generate
```

### 4. Exécution des migrations
```bash
php artisan migrate
```

### 5. Exécution des seeders (création des rôles et utilisateurs de test)
```bash
php artisan db:seed --class=RolePermissionSeeder
```

### 6. Création du lien symbolique pour les uploads
```bash
php artisan storage:link
```

### 7. Compilation des assets
```bash
npm run dev
```

## Utilisateurs de test

Après avoir exécuté le seeder, vous pouvez vous connecter avec les comptes suivants :

| Rôle | Username | Mot de passe |
|------|----------|--------------|
| Directeur | admin | password |
| Réceptionniste | reception | password |
| Chargé des crédits | charge | password |
| Gérant | gerant | password |
| Caissière | caissiere | password |

## Rôles et Permissions

### Réceptionniste
- Créer et gérer les demandes de crédit
- Créer et gérer les membres
- Voir les demandes de crédit

### Chargé des crédits
- Valider ou rejeter les demandes de crédit
- Voir les demandes de crédit et les membres

### Gérant
- Validation finale des demandes de crédit
- Voir les demandes de crédit et les membres

### Caissière
- Enregistrer les remboursements
- Gérer les pénalités
- Voir les crédits validés

### Directeur
- Accès aux rapports et statistiques
- Vue d'ensemble de l'activité

## Structure des dossiers

```
loans-app/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── ReceptionnisteController.php
│   │   │   ├── ChargeCreditsController.php
│   │   │   ├── GerantController.php
│   │   │   ├── CaissiereController.php
│   │   │   ├── DirecteurController.php
│   │   │   ├── MemberController.php
│   │   │   ├── LoanController.php
│   │   │   ├── RepaymentController.php
│   │   │   └── ReportController.php
│   │   └── Middleware/
│   │       └── RoleMiddleware.php
│   └── Models/
│       ├── User.php
│       ├── Member.php
│       ├── LoanDoc.php
│       ├── LoanRepayment.php
│       ├── RepaymentType.php
│       ├── Penalty.php
│       ├── Historique.php
│       ├── SmsNotification.php
│       └── InterestCancellation.php
├── database/
│   ├── migrations/
│   └── seeders/
│       └── RolePermissionSeeder.php
├── resources/
│   └── views/
│       ├── layouts/
│       │   └── app.blade.php
│       ├── auth/
│       │   └── login.blade.php
│       ├── receptionniste/
│       ├── charge_credits/
│       ├── gerant/
│       ├── caissiere/
│       ├── directeur/
│       ├── members/
│       ├── loans/
│       ├── repayments/
│       └── reports/
└── routes/
    └── web.php
```

## Fonctionnalités principales

### Gestion des membres
- Création, modification, suppression et consultation des membres
- Historique des crédits par membre

### Gestion des crédits
- Création de demandes de crédit
- Workflow de validation (Réceptionniste → Chargé des crédits → Gérant → Caissière)
- Calcul automatique des intérêts dégressifs
- Génération d'échéanciers de remboursement

### Gestion des remboursements
- Enregistrement des remboursements
- Suivi des montants remboursés
- Calcul automatique des montants restants

### Gestion des pénalités
- Application de pénalités pour retard
- Application de pénalités pour montant inférieur
- Suivi des pénalités payées/non payées

### Rapports et statistiques
- Rapport des crédits
- Rapport des intérêts
- Rapport financier
- Rapport des membres
- Statistiques par rôle

## Démarrage du serveur de développement
```bash
php artisan serve
```

L'application sera accessible à l'adresse : http://localhost:8000

## Sécurité
- Authentification obligatoire pour toutes les routes
- Gestion des rôles et permissions avec Spatie Laravel Permission
- Middleware personnalisé pour la vérification des rôles
- Validation des formulaires

## Support
Pour toute question ou problème, veuillez contacter l'administrateur système.

## License
Propriétaire - Tous droits réservés
