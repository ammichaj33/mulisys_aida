<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des insolvables</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 9px; }
        .header { text-align: center; margin-bottom: 14px; }
        .header h1 { font-size: 16px; margin: 0; }
        .header p { font-size: 10px; margin: 4px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 4px; text-align: left; }
        th { background-color: #f0f0f0; font-weight: bold; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .muted { color: #555; }
        tfoot th { background-color: #e0e0e0; }
        .filters { margin-top: 6px; font-size: 9px; }
        .filters span { display: inline-block; margin-right: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Liste des insolvables</h1>
        <p>
            Période (échéance) :
            @if($overdueFrom) du {{ $overdueFrom->format('d/m/Y') }} @endif
            @if($overdueTo) au {{ $overdueTo->format('d/m/Y') }} @endif
            @if(!$overdueFrom && !$overdueTo) <span class="muted">toutes dates</span> @endif
        </p>
        <p>Date d'édition : {{ now()->format('d/m/Y H:i') }}</p>
        <div class="filters">
            @if(!empty($filters['status']))
                <span><strong>Statut :</strong> {{ $filters['status'] }}</span>
            @endif
            @if(!empty($filters['search']))
                <span><strong>Recherche :</strong> {{ $filters['search'] }}</span>
            @endif
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Référence</th>
                <th>Date dossier</th>
                <th>Bénéficiaire</th>
                <th>Téléphone</th>
                <th>Échéance</th>
                <th class="text-end">Montant</th>
                <th class="text-end">Total dû</th>
                <th class="text-end">Remboursé</th>
                <th class="text-end">Reste dû</th>
            </tr>
        </thead>
        <tbody>
            @forelse($loans as $loan)
                <tr>
                    <td>{{ $loan->refNumber }}</td>
                    <td class="text-center">{{ $loan->createdAt ? date('d/m/Y', strtotime($loan->createdAt)) : '-' }}</td>
                    <td>{{ $loan->member->firstName ?? '' }} {{ $loan->member->lastName ?? '' }}</td>
                    <td>{{ $loan->member->phoneNumber ?? '' }}</td>
                    <td class="text-center">
                        {{ !empty($loan->expectedEndDate) ? \Carbon\Carbon::parse($loan->expectedEndDate)->format('d/m/Y') : '-' }}
                    </td>
                    <td class="text-end">{{ number_format($loan->requestAmount ?? 0, 2, ',', ' ') }} USD</td>
                    <td class="text-end">{{ number_format($loan->totalAmountDue ?? 0, 2, ',', ' ') }} USD</td>
                    <td class="text-end">{{ number_format($loan->totalRepaid ?? 0, 2, ',', ' ') }} USD</td>
                    <td class="text-end">{{ number_format($loan->remainingAmount ?? 0, 2, ',', ' ') }} USD</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center">Aucun dossier insolvables pour cette sélection.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5" class="text-end">TOTAL ({{ $totals['count'] ?? 0 }})</th>
                <th class="text-end">{{ number_format($totals['requestAmount'] ?? 0, 2, ',', ' ') }} USD</th>
                <th class="text-end">{{ number_format($totals['totalAmountDue'] ?? 0, 2, ',', ' ') }} USD</th>
                <th class="text-end">{{ number_format($totals['totalRepaid'] ?? 0, 2, ',', ' ') }} USD</th>
                <th class="text-end">{{ number_format($totals['remainingAmount'] ?? 0, 2, ',', ' ') }} USD</th>
            </tr>
        </tfoot>
    </table>
</body>
</html>

