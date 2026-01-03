<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cashflow par catégorie - {{ $startDate }} au {{ $endDate }}</title>
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
            font-size: 14px;
            color: #333;
        }
        .summary-item p {
            margin: 5px 0;
            font-size: 12px;
            font-weight: bold;
        }
        .summary-item.income p {
            color: #27ae60;
        }
        .summary-item.expense p {
            color: #e74c3c;
        }
        .summary-item.balance p {
            color: #3498db;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
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
        .income {
            color: #27ae60;
            font-weight: bold;
        }
        .expense {
            color: #e74c3c;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>RAPPORT DE CASHFLOW PAR CATÉGORIE</h1>
        <p>Période du {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
        <p>Généré le {{ now()->format('d/m/Y à H:i') }}</p>
    </div>

    <div class="summary">
        <div class="summary-item income">
            <h3>Total Entrées</h3>
            <p>+{{ number_format($totalIncome, 2, ',', ' ') }} USD</p>
        </div>
        <div class="summary-item expense">
            <h3>Total Sorties</h3>
            <p>-{{ number_format($totalExpense, 2, ',', ' ') }} USD</p>
        </div>
        <div class="summary-item balance">
            <h3>Solde</h3>
            <p>{{ number_format($balance, 2, ',', ' ') }} USD</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 40%;">Catégorie</th>
                <th style="width: 15%;">Type</th>
                <th style="width: 20%;">Entrées</th>
                <th style="width: 20%;">Sorties</th>
                <th style="width: 15%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($categories as $category)
            <tr>
                <td>{{ $category->categoryName }}</td>
                <td class="text-center">
                    @if($category->categoryType == 'income')
                        <span style="color: #27ae60; font-weight: bold;">Entrée</span>
                    @else
                        <span style="color: #e74c3c; font-weight: bold;">Sortie</span>
                    @endif
                </td>
                <td class="text-right income">
                    @if($category->totalIncome > 0)
                        +{{ number_format($category->totalIncome, 2, ',', ' ') }} USD
                    @else
                        -
                    @endif
                </td>
                <td class="text-right expense">
                    @if($category->totalExpense > 0)
                        -{{ number_format($category->totalExpense, 2, ',', ' ') }} USD
                    @else
                        -
                    @endif
                </td>
                <td class="text-right {{ $category->total >= 0 ? 'income' : 'expense' }}">
                    {{ number_format($category->total, 2, ',', ' ') }} USD
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center">Aucune catégorie avec transactions pour cette période</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background-color: #f0f0f0; font-weight: bold;">
                <td colspan="2" class="text-center"><strong>TOTAL</strong></td>
                <td class="text-right income"><strong>+{{ number_format($totalIncome, 2, ',', ' ') }} USD</strong></td>
                <td class="text-right expense"><strong>-{{ number_format($totalExpense, 2, ',', ' ') }} USD</strong></td>
                <td class="text-right {{ $balance >= 0 ? 'income' : 'expense' }}">
                    <strong>{{ number_format($balance, 2, ',', ' ') }} USD</strong>
                </td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Document généré automatiquement par le système de gestion de crédits</p>
    </div>
</body>
</html>

