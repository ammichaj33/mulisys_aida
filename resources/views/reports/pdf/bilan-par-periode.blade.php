<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bilan crédit</title>
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
        .total-row {
            border-top: 2px solid #000;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Bilan crédit</h1>
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
                <td><strong>Capital injecté</strong></td>
                <td class="text-end"><strong>{{ number_format($capitalInjecte, 2, ',', ' ') }} USD</strong></td>
            </tr>
            <tr>
                <td><strong>Intérêt attendu</strong></td>
                <td class="text-end"><strong>{{ number_format($interetAttendu, 2, ',', ' ') }} USD</strong></td>
            </tr>
            <tr>
                <td><strong>Intérêt collecté</strong></td>
                <td class="text-end"><strong>{{ number_format($interetCollecte, 2, ',', ' ') }} USD</strong></td>
            </tr>
            <tr>
                <td><strong>Reste des intérêts à collecter</strong></td>
                <td class="text-end"><strong>{{ number_format($resteInteretACollecter, 2, ',', ' ') }} USD</strong></td>
            </tr>
            <tr>
                <td><strong>Total pénalité collecter</strong></td>
                <td class="text-end"><strong>{{ number_format($totalPenaliteCollecter, 2, ',', ' ') }} USD</strong></td>
            </tr>
            <tr>
                <td><strong>Pénalité collecter</strong></td>
                <td class="text-end"><strong>{{ number_format($penaliteCollecter, 2, ',', ' ') }} USD</strong></td>
            </tr>
            <tr>
                <td><strong>Reste pénalité à collecter</strong></td>
                <td class="text-end"><strong>{{ number_format($restePenaliteACollecter, 2, ',', ' ') }} USD</strong></td>
            </tr>
            <tr>
                <td><strong>Frais d'étude collectés</strong></td>
                <td class="text-end"><strong>{{ number_format($fraisEtudeCollectes, 2, ',', ' ') }} USD</strong></td>
            </tr>
            <tr class="total-row">
                <td><strong>Situation mutuelle</strong></td>
                <td class="text-end"><strong>{{ number_format($situationMutuelle, 2, ',', ' ') }} USD</strong></td>
            </tr>
        </tbody>
    </table>
</body>
</html>

