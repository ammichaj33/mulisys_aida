<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport détaillé de trésorerie - {{ $startDate }} au {{ $endDate }}</title>
    <style>
        @page {
            margin: 15mm;
            size: A4 landscape;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #333;
            padding-bottom: 8px;
        }
        .header h1 {
            margin: 0;
            font-size: 16px;
            color: #333;
            font-weight: bold;
        }
        .header p {
            margin: 3px 0;
            font-size: 10px;
        }
        .filters {
            margin-top: 10px;
            margin-bottom: 15px;
            padding: 8px;
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            font-size: 8px;
        }
        .filters-row {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }
        .filter-item {
            display: flex;
            align-items: center;
        }
        .filter-label {
            font-weight: bold;
            margin-right: 5px;
            color: #333;
        }
        .filter-value {
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #333;
            padding: 5px;
            text-align: left;
            font-size: 8px;
        }
        th {
            background-color: #333;
            color: white;
            font-weight: bold;
            text-align: center;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 8px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 8px;
        }
        .generated-info {
            margin-top: 15px;
            font-size: 9px;
            color: #333;
        }
        .visa-section {
            margin-top: 20px;
            font-size: 9px;
            color: #333;
        }
        .visa-label {
            margin-bottom: 10px;
        }
        .visa-signatures {
            display: flex;
            justify-content: flex-start;
            gap: 100px;
            margin-top: 30px;
        }
        .visa-item {
            text-align: left;
        }
        .visa-item-title {
            font-weight: bold;
            margin-bottom: 50px;
            border-bottom: 1px solid #333;
            padding-bottom: 5px;
            width: 150px;
        }
        .total-row {
            background-color: #f0f0f0;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Rapport détaillé de trésorerie</h1>
        <p>Période du {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
        <p>Généré le {{ now()->format('d/m/Y à H:i') }}</p>
    </div>

    @if(isset($filters))
    <div class="filters">
        <strong>Filtres appliqués :</strong>
        <div class="filters-row" style="margin-top: 5px;">
            <div class="filter-item">
                <span class="filter-label">Date de début:</span>
                <span class="filter-value">{{ \Carbon\Carbon::parse($filters['startDate'])->format('d/m/Y') }}</span>
            </div>
            <div class="filter-item">
                <span class="filter-label">Date de fin:</span>
                <span class="filter-value">{{ \Carbon\Carbon::parse($filters['endDate'])->format('d/m/Y') }}</span>
            </div>
            @if($filters['transactionType'])
                <div class="filter-item">
                    <span class="filter-label">Type:</span>
                    <span class="filter-value">{{ $filters['transactionType'] == 'income' ? 'Entrées' : 'Sorties' }}</span>
                </div>
            @endif
            @if($filters['categoryId'])
                @php
                    $selectedCategory = $categories->firstWhere('categoryId', $filters['categoryId']);
                @endphp
                @if($selectedCategory)
                    <div class="filter-item">
                        <span class="filter-label">Catégorie:</span>
                        <span class="filter-value">{{ $selectedCategory->categoryName }}</span>
                    </div>
                @endif
            @endif
            @if($filters['status'] && $filters['status'] != 'confirmed')
                <div class="filter-item">
                    <span class="filter-label">Statut:</span>
                    <span class="filter-value">
                        @if($filters['status'] == 'pending')
                            En attente
                        @elseif($filters['status'] == 'cancelled')
                            Annulé
                        @else
                            {{ ucfirst($filters['status']) }}
                        @endif
                    </span>
                </div>
            @endif
            @if($filters['search'])
                <div class="filter-item">
                    <span class="filter-label">Recherche:</span>
                    <span class="filter-value">{{ $filters['search'] }}</span>
                </div>
            @endif
        </div>
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">N°</th>
                <th style="width: 10%;">Date</th>
                <th style="width: 20%;">Motif</th>
                <th style="width: 15%;">Types d'opération</th>
                <th style="width: 12%;">Compte</th>
                <th style="width: 13%;">Mode Paiement</th>
                <th style="width: 13%;">Client</th>
                <th style="width: 12%;">Montant</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $index => $transaction)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ $transaction->transactionDate->format('d/m/Y') }}</td>
                <td>{{ Str::limit($transaction->description ?? $transaction->category->categoryName ?? 'N/A', 40) }}</td>
                <td class="text-center">
                    @if($transaction->transactionType == 'income')
                        Entrée
                    @else
                        Sortie
                    @endif
                </td>
                <td>{{ $transaction->account->accountName ?? 'N/A' }}</td>
                <td class="text-center">
                    @php
                        $paymentMethods = [
                            'cash' => 'Espèce',
                            'bank' => 'Banque',
                            'mobile_money' => 'Mobile Money',
                            'check' => 'Chèque'
                        ];
                    @endphp
                    {{ $paymentMethods[$transaction->paymentMethod] ?? $transaction->paymentMethod }}
                </td>
                <td>
                    @if($transaction->member)
                        {{ $transaction->member->firstName }} {{ $transaction->member->lastName }}
                    @else
                        N/A
                    @endif
                </td>
                <td class="text-right">{{ number_format($transaction->amount, 2, ',', ' ') }} USD</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center">Aucune transaction pour cette période</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="7" class="text-right"><strong>TOTAL</strong></td>
                <td class="text-right"><strong>{{ number_format($totalIncome - $totalExpense, 2, ',', ' ') }} USD</strong></td>
            </tr>
        </tfoot>
    </table>

    <div class="generated-info">
        <p>Généré le {{ now()->format('d/m/Y') }} à {{ now()->format('H:i') }} par {{ $user->fullName ?? $user->username ?? 'Système' }}</p>
    </div>

    <div class="visa-section">
        <div class="visa-label">
            <strong>Visa:</strong>
        </div>
        <div class="visa-signatures">
            <div class="visa-item">
                <div class="visa-item-title">Caisse</div>
            </div>
            <div class="visa-item">
                <div class="visa-item-title">Gérance</div>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>Document généré automatiquement par le système de gestion de crédits</p>
    </div>
</body>
</html>
