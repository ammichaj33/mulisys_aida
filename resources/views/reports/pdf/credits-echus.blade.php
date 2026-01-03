<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapport des crédits échus par périodes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 16px;
            margin: 0;
        }
        .header p {
            font-size: 10px;
            margin: 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #000;
            padding: 4px;
            text-align: left;
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .text-end {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        tfoot th {
            background-color: #e0e0e0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Rapport des crédits échus par périodes</h1>
        <p>Période : du {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
        <p>Date d'édition : {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>N° Doss</th>
                <th>Date</th>
                <th>Bénéficiaire</th>
                <th class="text-end">Montant</th>
                <th class="text-end">Intérêt</th>
                <th class="text-end">Total remboursé</th>
                <th class="text-end">Reste à rembourser</th>
                <th class="text-end">Pénalité</th>
                <th>Échéance</th>
            </tr>
        </thead>
        <tbody>
            @foreach($loans as $loan)
            <tr>
                <td>{{ $loan->refNumber }}</td>
                <td>{{ $loan->submitDate->format('d/m/Y') }}</td>
                <td>{{ $loan->member->firstName }} {{ $loan->member->lastName }}</td>
                <td class="text-end">{{ number_format($loan->requestAmount, 2, ',', ' ') }} USD</td>
                <td class="text-end">{{ number_format($loan->totalAmountDue - $loan->requestAmount, 2, ',', ' ') }} USD</td>
                <td class="text-end">{{ number_format($loan->totalRepaid, 2, ',', ' ') }} USD</td>
                <td class="text-end">{{ number_format($loan->remainingAmount, 2, ',', ' ') }} USD</td>
                <td class="text-end">{{ number_format($loan->totalPenalty, 2, ',', ' ') }} USD</td>
                <td>{{ $loan->dueDate->format('d-m-Y') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3" class="text-end">TOTAL</th>
                <th class="text-end">{{ number_format($totalAmount, 2, ',', ' ') }} USD</th>
                <th class="text-end">{{ number_format($totalInterest, 2, ',', ' ') }} USD</th>
                <th class="text-end">{{ number_format($totalRepaid, 2, ',', ' ') }} USD</th>
                <th class="text-end">{{ number_format($totalRemaining, 2, ',', ' ') }} USD</th>
                <th class="text-end">{{ number_format($totalPenalty, 2, ',', ' ') }} USD</th>
                <th></th>
            </tr>
        </tfoot>
    </table>
</body>
</html>

