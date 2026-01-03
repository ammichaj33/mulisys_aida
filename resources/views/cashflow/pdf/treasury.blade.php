<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport de trésorerie - {{ $startDate }} au {{ $endDate }}</title>
    <style>
        @page {
            margin: 20mm;
            size: A4 portrait;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }
        .header p {
            margin: 5px 0;
            font-size: 12px;
        }
        .summary {
            display: flex;
            justify-content: space-around;
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f5f5f5;
        }
        .summary-item {
            text-align: center;
        }
        .summary-item h3 {
            margin: 0;
            font-size: 12px;
            color: #333;
        }
        .summary-item p {
            margin: 5px 0;
            font-size: 11px;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
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
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .total-row {
            background-color: #f0f0f0;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>RAPPORT DE TRÉSORERIE</h1>
        <p>Période du {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
        <p>Généré le {{ now()->format('d/m/Y à H:i') }}</p>
    </div>

    <div class="summary">
        <div class="summary-item">
            <h3>Solde d'ouverture</h3>
            <p>{{ number_format($openingBalance, 2, ',', ' ') }} USD</p>
        </div>
        <div class="summary-item">
            <h3>Entrées période</h3>
            <p style="color: #27ae60;">+{{ number_format($periodIncome, 2, ',', ' ') }} USD</p>
        </div>
        <div class="summary-item">
            <h3>Sorties période</h3>
            <p style="color: #e74c3c;">-{{ number_format($periodExpense, 2, ',', ' ') }} USD</p>
        </div>
        <div class="summary-item">
            <h3>Solde de clôture</h3>
            <p style="color: #3498db;">{{ number_format($closingBalance, 2, ',', ' ') }} USD</p>
        </div>
    </div>

    <h2 style="font-size: 14px; margin-top: 20px; margin-bottom: 10px;">Détail par compte</h2>
    <table>
        <thead>
            <tr>
                <th style="width: 25%;">Compte</th>
                <th style="width: 15%;">Type</th>
                <th style="width: 15%;">Solde d'ouverture</th>
                <th style="width: 15%;">Entrées</th>
                <th style="width: 15%;">Sorties</th>
                <th style="width: 15%;">Solde de clôture</th>
            </tr>
        </thead>
        <tbody>
            @forelse($accounts as $account)
            <tr>
                <td>{{ $account->accountName }}</td>
                <td class="text-center">
                    @if($account->accountType == 'cash')
                        Espèces
                    @elseif($account->accountType == 'bank')
                        Banque
                    @else
                        Mobile Money
                    @endif
                </td>
                <td class="text-right">{{ number_format($account->openingBalance, 2, ',', ' ') }} USD</td>
                <td class="text-right" style="color: #27ae60;">
                    @if($account->periodIncome > 0)
                        +{{ number_format($account->periodIncome, 2, ',', ' ') }} USD
                    @else
                        -
                    @endif
                </td>
                <td class="text-right" style="color: #e74c3c;">
                    @if($account->periodExpense > 0)
                        -{{ number_format($account->periodExpense, 2, ',', ' ') }} USD
                    @else
                        -
                    @endif
                </td>
                <td class="text-right" style="color: #3498db; font-weight: bold;">
                    {{ number_format($account->closingBalance, 2, ',', ' ') }} USD
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">Aucun compte</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="2" class="text-center"><strong>TOTAL</strong></td>
                <td class="text-right"><strong>{{ number_format($openingBalance, 2, ',', ' ') }} USD</strong></td>
                <td class="text-right" style="color: #27ae60;">
                    <strong>+{{ number_format($periodIncome, 2, ',', ' ') }} USD</strong>
                </td>
                <td class="text-right" style="color: #e74c3c;">
                    <strong>-{{ number_format($periodExpense, 2, ',', ' ') }} USD</strong>
                </td>
                <td class="text-right" style="color: #3498db;">
                    <strong>{{ number_format($closingBalance, 2, ',', ' ') }} USD</strong>
                </td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Document généré automatiquement par le système de gestion de crédits</p>
    </div>
</body>
</html>

