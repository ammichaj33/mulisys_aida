# Configuration des Pénalités

## Variables d'environnement

Ajoutez ces variables à votre fichier `.env` :

```env
# Configuration des pénalités
PENALTY_TOLERANCE_DAYS=5
PENALTY_MONTHLY_RATE=1.0
PENALTY_CALCULATION_BASE=remaining_amount
PENALTY_AUTO_CALCULATE=true
MAX_PENALTY_PERCENTAGE=10.0
```

## Description des paramètres

- **PENALTY_TOLERANCE_DAYS** : Nombre de jours de tolérance après la date d'échéance (défaut: 5)
- **PENALTY_MONTHLY_RATE** : Taux de pénalité mensuel en pourcentage (défaut: 1.0% = 12% annuel ÷ 12)
- **PENALTY_CALCULATION_BASE** : Base de calcul des pénalités
  - `remaining_amount` : Sur le montant restant dû
  - `original_amount` : Sur le montant original du crédit
- **PENALTY_AUTO_CALCULATE** : Calcul automatique des pénalités (défaut: true)
- **MAX_PENALTY_PERCENTAGE** : Pourcentage maximum de pénalité (défaut: 10.0%)

## Utilisation

### Calcul manuel des pénalités
```bash
php artisan penalties:calculate
```

### Calcul automatique
Les pénalités sont calculées automatiquement tous les jours à 2h00 du matin.

### Interface web
Accédez à `/penalties` pour voir et gérer les pénalités.

## Fonctionnement

1. Le système vérifie tous les crédits validés
2. Pour chaque échéance dépassée de plus de `PENALTY_TOLERANCE_DAYS` jours
3. Calcule une pénalité de `PENALTY_MONTHLY_RATE`% par mois sur le capital restant
4. Formule : **Capital restant × Taux mensuel × Mois de retard**
5. Limite la pénalité à `MAX_PENALTY_PERCENTAGE`%
6. Crée un enregistrement de pénalité dans la base de données

## Exemples de calcul

### Scénario 1 : Aucun paiement
- Crédit : 200 USD
- Échéance 1 : 12 Nov 2025 (86.67 USD)
- Retard : 1 mois
- Pénalité : 200 × 1% × 1 = **2.00 USD**

### Scénario 2 : Paiement partiel (55 USD)
- Crédit : 200 USD
- Paiement avant échéance : 55 USD
- Capital restant : 145 USD
- Retard : 1 mois
- Pénalité : 145 × 1% × 1 = **1.45 USD**

### Scénario 3 : Paiements partiels multiples
- Échéance 1 : 200 - 55 = 145 USD → 145 × 1% × 1 = **1.45 USD**
- Échéance 2 : 133.33 - 20 = 113.33 USD → 113.33 × 1% × 1 = **1.13 USD**
- Échéance 3 : 66.66 USD → 66.66 × 1% × 1 = **0.67 USD**
- **Total** : 1.45 + 1.13 + 0.67 = **3.25 USD**
