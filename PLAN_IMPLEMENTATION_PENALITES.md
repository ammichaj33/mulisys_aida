# Plan d'Implémentation - Nouvelle Gestion des Pénalités

## 📋 Vue d'ensemble

Ce document décrit le plan d'implémentation pour la nouvelle logique de calcul et gestion des pénalités selon les règles suivantes :

1. **Calcul des pénalités** : 
   - Premier retard : `(Capital mois + Intérêt mois) × 10%`
   - Retard consécutif : `((Capital M-1 + Intérêt M-1 + Pénalité M-1) + (Capital M + Intérêt M)) × 10%`
   - Condition : La pénalité du mois passé doit être `notPaid`

2. **Répartition des paiements** : Intérêts → Pénalités → Capital (dans cet ordre)

3. **Remboursements anticipés** : La pénalité est calculée sur le capital restant après l'avance

---

## 🎯 Phase 1 : Nouvelle Logique de Calcul des Pénalités

### 1.1 Modifier `PenaltyCalculationService::calculateLoanPenalty()`

**Fichier** : `app/Services/PenaltyCalculationService.php`

**Changements** :
- Remplacer la méthode `calculatePenaltyWithMonthlyRate()` par une nouvelle logique
- Ajouter une méthode pour vérifier si une pénalité `notPaid` existe pour le mois précédent
- Implémenter la formule conditionnelle :
  - Si pénalité précédente `notPaid` existe → Formule cumulée
  - Sinon → Formule simple

**Nouvelles méthodes à créer** :
```php
/**
 * Calculate penalty for a specific month with new rules
 */
private function calculatePenaltyForMonth($currentMonth, $previousMonth, $loan, $schedule, $repayments)

/**
 * Check if previous month has unpaid penalty
 */
private function hasUnpaidPenaltyForMonth($loanId, $month)

/**
 * Get penalty amount for a specific month
 */
private function getPenaltyAmountForMonth($loanId, $month)
```

**Logique de calcul** :
```php
POUR chaque mois en retard :
    1. Vérifier si le mois est en retard (échéance passée + paiement insuffisant)
    2. Récupérer la pénalité du mois précédent (status = 'notPaid')
    3. SI (pénalité précédente notPaid existe) :
         - Calculer : ((Capital M-1 + Intérêt M-1 + Pénalité M-1) + (Capital M + Intérêt M)) × 10%
       SINON :
         - Calculer : (Capital M + Intérêt M) × 10%
    4. Créer/mettre à jour la pénalité
```

### 1.2 Mettre à jour la configuration

**Fichier** : `config/penalties.php`

**Changements** :
- Ajouter `penalty_rate` : 10.0 (taux fixe de 10%)
- Conserver `tolerance_days` : 30 jours

---

## 🎯 Phase 2 : Répartition Automatique des Paiements

### 2.1 Créer un service de répartition des paiements

**Nouveau fichier** : `app/Services/PaymentAllocationService.php`

**Responsabilités** :
- Calculer les intérêts dus à une date donnée
- Calculer les pénalités dues
- Répartir un paiement dans l'ordre : Intérêts → Pénalités → Capital
- Suivre l'ordre chronologique des échéances

**Méthodes principales** :
```php
/**
 * Allocate payment amount: Interest → Penalties → Capital
 */
public function allocatePayment($loan, $paymentAmount, $paymentDate)

/**
 * Calculate interest due up to a specific date
 */
private function calculateInterestDue($loan, $date)

/**
 * Calculate penalties due (unpaid penalties)
 */
private function calculatePenaltiesDue($loanId)

/**
 * Allocate to capital (chronological order)
 */
private function allocateToCapital($loan, $remainingAmount, $date)
```

### 2.2 Modifier `CaissiereController::store()`

**Fichier** : `app/Http/Controllers/CaissiereController.php`

**Changements** :
- Intégrer `PaymentAllocationService` dans la méthode `store()`
- Remplacer la logique actuelle (paiement direct au crédit) par la répartition automatique
- Enregistrer la répartition dans la description du remboursement

**Nouvelle logique** :
```php
1. Calculer les intérêts dus à la date du paiement
2. Calculer les pénalités dues (notPaid)
3. Répartir le paiement :
   - Intérêts dus (en priorité)
   - Pénalités dues (ensuite)
   - Capital (reste)
4. Créer le remboursement avec la description détaillée
5. Marquer les pénalités payées si nécessaire
```

### 2.3 Modifier `CaissiereController::recordRepayment()`

**Fichier** : `app/Http/Controllers/CaissiereController.php`

**Changements** :
- Appliquer la même logique de répartition dans `recordRepayment()`

### 2.4 Ajouter des champs de suivi (optionnel)

**Migration** : `database/migrations/XXXX_XX_XX_add_payment_allocation_to_repayments.php`

**Champs à ajouter** (optionnel, pour traçabilité) :
- `interest_amount` : decimal(15,2) - Montant alloué aux intérêts
- `penalty_amount` : decimal(15,2) - Montant alloué aux pénalités
- `capital_amount` : decimal(15,2) - Montant alloué au capital

**Note** : Ces champs sont optionnels si on préfère stocker la répartition dans la description.

---

## 🎯 Phase 3 : Gestion des Remboursements Anticipés

### 3.1 Modifier le calcul des pénalités pour les avances

**Fichier** : `app/Services/PenaltyCalculationService.php`

**Changements** :
- Dans `calculateRemainingCapitalAtDueDate()`, prendre en compte les remboursements anticipés
- Si un paiement anticipé réduit le capital, la pénalité doit être calculée sur le capital restant réel

**Logique** :
```php
1. Calculer le capital théorique selon l'échéancier
2. Soustraire les paiements anticipés (qui réduisent le capital)
3. Utiliser le capital restant réel pour le calcul de la pénalité
```

### 3.2 Vérifier l'ordre chronologique

**Fichier** : `app/Services/PaymentAllocationService.php`

**Changements** :
- S'assurer que les paiements sont alloués dans l'ordre chronologique
- M1 doit être régularisé avant M2
- Si M1 n'est pas payé, les paiements ultérieurs sont appliqués à M1

---

## 🎯 Phase 4 : Mise à jour des Contrôleurs et Vues

### 4.1 Mettre à jour `PenaltyController`

**Fichier** : `app/Http/Controllers/PenaltyController.php`

**Changements** :
- Adapter `calculatePenaltyDetails()` pour afficher la nouvelle formule
- Mettre à jour les messages d'information

### 4.2 Mettre à jour les vues

**Fichiers** :
- `resources/views/caissiere/repayments/create.blade.php`
- `resources/views/caissiere/repayments/show.blade.php`
- `resources/views/penalties/show.blade.php`

**Changements** :
- Afficher la répartition du paiement (Intérêts / Pénalités / Capital)
- Afficher la formule utilisée pour chaque pénalité
- Indiquer si c'est un premier retard ou un retard consécutif

---

## 🎯 Phase 5 : Tests et Validation

### 5.1 Tests unitaires

**Nouveau fichier** : `tests/Unit/Services/PenaltyCalculationServiceTest.php`

**Scénarios à tester** :
1. Premier mois de retard → Formule simple
2. Retard consécutif → Formule cumulée
3. Pénalité précédente payée → Formule simple
4. Retard non consécutif → Formule cumulée (si pénalité précédente notPaid)
5. Remboursement anticipé → Pénalité sur capital restant

### 5.2 Tests d'intégration

**Nouveau fichier** : `tests/Feature/PaymentAllocationTest.php`

**Scénarios à tester** :
1. Paiement couvrant intérêts + pénalités + capital
2. Paiement partiel (seulement intérêts)
3. Paiement couvrant intérêts + pénalités (pas de capital)
4. Ordre chronologique des paiements

### 5.3 Tests manuels

**Checklist** :
- [ ] Calculer pénalité pour premier retard
- [ ] Calculer pénalité pour retard consécutif
- [ ] Vérifier répartition Intérêts → Pénalités → Capital
- [ ] Vérifier ordre chronologique
- [ ] Tester remboursement anticipé
- [ ] Vérifier que les pénalités payées ne sont plus incluses dans le calcul

---

## 📝 Ordre d'Implémentation Recommandé

### Étape 1 : Préparation
1. Créer `PaymentAllocationService` (structure de base)
2. Mettre à jour la configuration des pénalités

### Étape 2 : Calcul des pénalités
1. Modifier `PenaltyCalculationService::calculateLoanPenalty()`
2. Implémenter la logique conditionnelle (premier retard vs consécutif)
3. Tester le calcul des pénalités

### Étape 3 : Répartition des paiements
1. Implémenter `PaymentAllocationService::allocatePayment()`
2. Intégrer dans `CaissiereController::store()`
3. Intégrer dans `CaissiereController::recordRepayment()`
4. Tester la répartition

### Étape 4 : Remboursements anticipés
1. Adapter le calcul pour tenir compte des avances
2. Tester les remboursements anticipés

### Étape 5 : Interface utilisateur
1. Mettre à jour les vues pour afficher la répartition
2. Mettre à jour les messages et descriptions

### Étape 6 : Tests finaux
1. Tests unitaires complets
2. Tests d'intégration
3. Tests manuels
4. Validation avec données réelles

---

## 🔍 Points d'Attention

### 1. Cohérence des données
- S'assurer que les pénalités sont recalculées après chaque paiement
- Vérifier que le statut des pénalités est correctement mis à jour

### 2. Performance
- Le calcul des pénalités peut être coûteux pour beaucoup de crédits
- Considérer un cache ou un calcul asynchrone si nécessaire

### 3. Migration des données existantes
- Les pénalités existantes doivent être recalculées avec la nouvelle formule
- Créer un script de migration si nécessaire

### 4. Logs et traçabilité
- Logger chaque calcul de pénalité
- Logger chaque répartition de paiement
- Conserver l'historique des changements

---

## 📊 Structure des Fichiers à Modifier/Créer

### Fichiers à modifier :
- `app/Services/PenaltyCalculationService.php`
- `app/Http/Controllers/CaissiereController.php`
- `app/Http/Controllers/PenaltyController.php`
- `config/penalties.php`

### Fichiers à créer :
- `app/Services/PaymentAllocationService.php`
- `database/migrations/XXXX_XX_XX_add_payment_allocation_to_repayments.php` (optionnel)
- `tests/Unit/Services/PenaltyCalculationServiceTest.php`
- `tests/Feature/PaymentAllocationTest.php`

### Vues à modifier :
- `resources/views/caissiere/repayments/create.blade.php`
- `resources/views/caissiere/repayments/show.blade.php`
- `resources/views/penalties/show.blade.php`

---

## ✅ Checklist de Validation

### Fonctionnalités
- [ ] Calcul pénalité premier retard fonctionne
- [ ] Calcul pénalité retard consécutif fonctionne
- [ ] Répartition Intérêts → Pénalités → Capital fonctionne
- [ ] Ordre chronologique respecté
- [ ] Remboursements anticipés pris en compte
- [ ] Pénalités payées exclues du calcul

### Interface
- [ ] Affichage de la répartition dans les vues
- [ ] Messages d'information clairs
- [ ] Formule de calcul visible

### Tests
- [ ] Tests unitaires passent
- [ ] Tests d'intégration passent
- [ ] Tests manuels validés

### Documentation
- [ ] Code commenté
- [ ] Documentation des méthodes
- [ ] Guide utilisateur mis à jour (si nécessaire)

---

## 🚀 Prochaines Étapes

Une fois ce plan validé, nous procéderons à l'implémentation étape par étape, en commençant par la Phase 1 (Nouvelle Logique de Calcul des Pénalités).

