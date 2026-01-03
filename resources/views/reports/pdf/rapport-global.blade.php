<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapport Global des crédits par période</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            font-size: 18px;
            margin: 0;
        }
        .header p {
            font-size: 12px;
            margin: 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .text-end {
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Rapport Global des crédits par période</h1>
        <p>Période : du {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
        <p>Date d'édition : {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Catégorie</th>
                <th class="text-end">Montant</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Total crédit</strong></td>
                <td class="text-end"><strong>{{ number_format($totalCredit, 2, ',', ' ') }} USD</strong></td>
            </tr>
            <tr>
                <td><strong>Total intérêt</strong></td>
                <td class="text-end"><strong>{{ number_format($totalInterest, 2, ',', ' ') }} USD</strong></td>
            </tr>
            <tr>
                <td><strong>Total remboursé</strong></td>
                <td class="text-end"><strong>{{ number_format($totalRepaid, 2, ',', ' ') }} USD</strong></td>
            </tr>
            <tr>
                <td><strong>Reste à rembourser</strong></td>
                <td class="text-end"><strong>{{ number_format($totalRemaining, 2, ',', ' ') }} USD</strong></td>
            </tr>
            <tr>
                <td><strong>Total pénalité</strong></td>
                <td class="text-end"><strong>{{ number_format($totalPenalty, 2, ',', ' ') }} USD</strong></td>
            </tr>
        </tbody>
    </table>
</body>
</html>

