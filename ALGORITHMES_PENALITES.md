# Algorithmes et Formules - Gestion des Pénalités

## 📐 Formules de Calcul

### Formule 1 : Premier Retard
```
Pénalité = (Capital_mois + Intérêt_mois) × 10%
```

**Condition** : Aucune pénalité `notPaid` pour le mois précédent

### Formule 2 : Retard Consécutif
```
Pénalité = ((Capital_M-1 + Intérêt_M-1 + Pénalité_M-1) + (Capital_M + Intérêt_M)) × 10%
```

**Condition** : 
- Pénalité `notPaid` existe pour le mois précédent (M-1)
- Retard confirmé pour le mois actuel (M)

---

## 🔄 Algorithme de Calcul des Pénalités

### Étape 1 : Vérification des Échéances

```php
POUR chaque échéance dans le calendrier :
    1. Vérifier si la date d'échéance est passée
    2. Calculer le montant attendu à cette échéance
    3. Calculer le montant réellement remboursé AVANT cette échéance
    4. SI (montant remboursé < montant attendu) :
         → Échéance en retard
         → Continuer au calcul de pénalité
    5. SINON :
         → Échéance régularisée
         → Passer à l'échéance suivante
```

### Étape 2 : Calcul de la Pénalité

```php
POUR chaque échéance en retard :
    1. Récupérer les données du mois actuel :
       - Capital_mois = capital_restant selon l'échéancier
       - Intérêt_mois = intérêt dû selon l'échéancier
       - Date_échéance = date de l'échéance
    
    2. Vérifier si le mois est en retard (au-delà de la tolérance) :
       - Date_tolérance = Date_échéance + tolerance_days (30 jours)
       - SI (Date_actuelle > Date_tolérance) :
           → Mois en retard
           → Continuer
       - SINON :
           → Pas de pénalité
           → Passer à l'échéance suivante
    
    3. Calculer le capital réellement restant :
       - Prendre en compte les paiements partiels
       - Prendre en compte les remboursements anticipés
       - Capital_réel = Capital_théorique - Paiements_alloués
    
    4. Vérifier la pénalité du mois précédent :
       - Mois_précédent = Date_échéance - 1 mois
       - Pénalité_précédente = Penalty::where('penaltyMonth', Mois_précédent)
                                      ->where('status', 'notPaid')
                                      ->first()
    
    5. Calculer la pénalité :
       SI (Pénalité_précédente existe ET status = 'notPaid') :
           // Formule cumulée
           Capital_M-1 = capital du mois précédent (selon échéancier)
           Intérêt_M-1 = intérêt du mois précédent (selon échéancier)
           Pénalité_M-1 = montant de la pénalité précédente
           
           Pénalité = ((Capital_M-1 + Intérêt_M-1 + Pénalité_M-1) 
                      + (Capital_M + Intérêt_M)) × 0.10
       SINON :
           // Formule simple (premier retard)
           Pénalité = (Capital_M + Intérêt_M) × 0.10
    
    6. Vérifier si une pénalité existe déjà pour ce mois :
       - Pénalité_existante = Penalty::where('penaltyMonth', Mois_actuel)
                                      ->where('status', 'notPaid')
                                      ->first()
    
    7. SI (Pénalité_existante n'existe pas ET Pénalité > 0) :
           → Créer la pénalité
       SINON SI (Pénalité_existante existe ET montant différent) :
           → Mettre à jour la pénalité (si nécessaire)
```

---

## 💰 Algorithme de Répartition des Paiements

### Ordre de Priorité : Intérêts → Pénalités → Capital

```php
FONCTION allocatePayment(loan, paymentAmount, paymentDate) :
    
    // Étape 1 : Calculer les intérêts dus
    intérêts_dus = calculateInterestDue(loan, paymentDate)
    
    // Étape 2 : Calculer les pénalités dues
    pénalités_dues = calculatePenaltiesDue(loan->loanDocId)
    
    // Étape 3 : Répartir le paiement
    montant_restant = paymentAmount
    
    // 3.1 : Payer les intérêts en priorité
    intérêts_payés = min(montant_restant, intérêts_dus)
    montant_restant = montant_restant - intérêts_payés
    
    // 3.2 : Payer les pénalités ensuite
    pénalités_payées = min(montant_restant, pénalités_dues)
    montant_restant = montant_restant - pénalités_payées
    
    // 3.3 : Le reste va au capital (ordre chronologique)
    capital_alloué = allocateToCapital(loan, montant_restant, paymentDate)
    
    // Étape 4 : Marquer les pénalités payées
    SI (pénalités_payées > 0) :
        marquerPénalitésPayées(loan->loanDocId, pénalités_payées)
    
    // Étape 5 : Retourner la répartition
    RETOURNER {
        intérêts: intérêts_payés,
        pénalités: pénalités_payées,
        capital: capital_alloué,
        total: paymentAmount
    }
```

### Calcul des Intérêts Dus

```php
FONCTION calculateInterestDue(loan, date) :
    // Calculer l'échéancier
    schedule = calculateMonthlyDegressiveInterest(...)
    
    intérêts_dus = 0
    
    POUR chaque échéance dans schedule :
        SI (date_échéance <= date) :
            // Vérifier si l'échéance a été payée
            montant_payé = calculateActualRepaidBeforeDate(repayments, date_échéance)
            montant_attendu = échéance.montant_total
            
            SI (montant_payé < montant_attendu) :
                // Échéance non régularisée
                // Calculer la proportion d'intérêts dans le montant dû
                intérêts_échéance = échéance.interet
                intérêts_dus = intérêts_dus + intérêts_échéance
    
    RETOURNER intérêts_dus
```

### Calcul des Pénalités Dues

```php
FONCTION calculatePenaltiesDue(loanId) :
    pénalités = Penalty::where('loanDocIdFk', loanId)
                       ->where('status', 'notPaid')
                       ->get()
    
    total = 0
    POUR chaque pénalité dans pénalités :
        total = total + pénalité.amount
    
    RETOURNER total
```

### Allocation au Capital (Ordre Chronologique)

```php
FONCTION allocateToCapital(loan, amount, date) :
    // Récupérer l'échéancier
    schedule = calculateMonthlyDegressiveInterest(...)
    
    montant_restant = amount
    capital_alloué = 0
    
    // Parcourir les échéances dans l'ordre chronologique
    POUR chaque échéance dans schedule (ordre chronologique) :
        SI (montant_restant <= 0) :
            ARRÊTER
        
        SI (date_échéance <= date) :
            // Vérifier si l'échéance est régularisée
            montant_payé = calculateActualRepaidBeforeDate(repayments, date_échéance)
            montant_attendu = échéance.montant_total
            
            SI (montant_payé < montant_attendu) :
                // Échéance non régularisée
                // Calculer ce qui reste à payer pour cette échéance
                reste_à_payer = montant_attendu - montant_payé
                
                // Allouer au capital de cette échéance
                capital_échéance = échéance.remboursement_fixe
                proportion_capital = capital_échéance / montant_attendu
                capital_dû = reste_à_payer × proportion_capital
                
                // Allouer le montant disponible
                allocation = min(montant_restant, capital_dû)
                capital_alloué = capital_alloué + allocation
                montant_restant = montant_restant - allocation
    
    RETOURNER capital_alloué
```

---

## 📅 Gestion de l'Ordre Chronologique

### Principe

Les remboursements doivent couvrir les dettes dans l'ordre chronologique :
- M1 doit être régularisé avant M2
- Si M1 n'est pas payé, les paiements ultérieurs sont appliqués à M1

### Algorithme de Vérification

```php
FONCTION vérifierOrdreChronologique(loan, paymentDate) :
    schedule = calculateMonthlyDegressiveInterest(...)
    
    POUR chaque échéance dans schedule (ordre chronologique) :
        SI (date_échéance < paymentDate) :
            // Vérifier si cette échéance est régularisée
            montant_payé = calculateActualRepaidBeforeDate(repayments, date_échéance)
            montant_attendu = échéance.montant_total
            
            SI (montant_payé < montant_attendu) :
                // Échéance non régularisée
                // Les paiements doivent d'abord régulariser cette échéance
                RETOURNER false  // Ordre non respecté
    
    RETOURNER true  // Ordre respecté
```

---

## 🔄 Remboursements Anticipés

### Principe

Si un client fait un remboursement anticipé, le capital est réduit en avance. La pénalité doit être calculée sur le capital restant réel, pas sur le capital théorique.

### Calcul du Capital Restant Réel

```php
FONCTION calculateRealRemainingCapital(loan, dueDate) :
    // Capital théorique selon l'échéancier
    capital_théorique = getCapitalFromSchedule(loan, dueDate)
    
    // Calculer les paiements alloués au capital (ordre chronologique)
    paiements_alloués = 0
    
    POUR chaque remboursement avant dueDate :
        // Calculer la part allouée au capital dans ce remboursement
        répartition = allocatePayment(loan, remboursement.amount, remboursement.date)
        paiements_alloués = paiements_alloués + répartition.capital
    
    // Capital réel = Capital théorique - Paiements alloués
    capital_réel = capital_théorique - paiements_alloués
    
    RETOURNER max(0, capital_réel)  // Ne peut pas être négatif
```

---

## 📊 Exemples de Calculs

### Exemple 1 : Premier Retard

**Données** :
- Mois 1 (Janvier) : Capital = 1000, Intérêt = 100
- Échéance non payée
- Pas de pénalité précédente

**Calcul** :
```
Pénalité = (1000 + 100) × 10% = 110 USD
```

### Exemple 2 : Retard Consécutif

**Données** :
- Mois 1 (Janvier) : Capital = 1000, Intérêt = 100, Pénalité = 110 (notPaid)
- Mois 2 (Février) : Capital = 900, Intérêt = 90
- Échéance M2 non payée

**Calcul** :
```
Pénalité M2 = ((1000 + 100 + 110) + (900 + 90)) × 10%
            = (1210 + 990) × 10%
            = 220 USD
```

### Exemple 3 : Répartition d'un Paiement

**Données** :
- Paiement : 500 USD
- Intérêts dus : 150 USD
- Pénalités dues : 100 USD
- Capital restant : 1000 USD

**Répartition** :
```
1. Intérêts : min(500, 150) = 150 USD
   Reste : 500 - 150 = 350 USD

2. Pénalités : min(350, 100) = 100 USD
   Reste : 350 - 100 = 250 USD

3. Capital : 250 USD
```

**Résultat** :
- Intérêts payés : 150 USD
- Pénalités payées : 100 USD
- Capital remboursé : 250 USD
- Total : 500 USD

---

## ⚠️ Cas Spéciaux

### Cas 1 : Paiement Partiel

Si le paiement ne couvre pas tous les intérêts :
- Tous les intérêts dus sont payés en priorité
- Le reste (s'il y en a) va aux pénalités
- Le reste final va au capital

### Cas 2 : Paiement Insuffisant pour Intérêts

Si le paiement est inférieur aux intérêts dus :
- Tout le paiement va aux intérêts
- Rien pour les pénalités
- Rien pour le capital

### Cas 3 : Retard Non Consécutif

Si M1 a une pénalité `notPaid`, M2 est payé, puis M3 est en retard :
- Le paiement M2 est appliqué à M1 (ordre chronologique)
- M1 reste non régularisé si le paiement M2 est insuffisant
- M3 utilise la formule cumulée avec M1 (car pénalité M1 toujours `notPaid`)

---

## 🔍 Validation et Vérifications

### Vérifications à Effectuer

1. **Cohérence des montants** :
   - Intérêts payés + Pénalités payées + Capital remboursé = Montant total du paiement

2. **Ordre chronologique** :
   - M1 doit être régularisé avant M2
   - Vérifier que les paiements sont alloués dans l'ordre

3. **Statut des pénalités** :
   - Les pénalités payées ne doivent plus être incluses dans le calcul
   - Seules les pénalités `notPaid` sont prises en compte

4. **Capital réel** :
   - Le capital utilisé pour le calcul doit être le capital réel (après paiements)
   - Pas le capital théorique de l'échéancier

---

## 📝 Notes d'Implémentation

### Arrondissements

- Tous les montants doivent être arrondis à 2 décimales
- Utiliser `round($amount, 2)` pour tous les calculs

### Tolérance

- Utiliser une tolérance de 0.001 (0.1 centime) pour les comparaisons de nombres flottants
- Éviter les erreurs d'arrondi dans les comparaisons

### Logs

- Logger chaque calcul de pénalité avec les détails :
  - Mois concerné
  - Formule utilisée (simple ou cumulée)
  - Montants (Capital, Intérêt, Pénalité)
  - Résultat

- Logger chaque répartition de paiement :
  - Montant total
  - Répartition (Intérêts, Pénalités, Capital)
  - Date du paiement

