<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendrier de remboursement - {{ $loan->refNumber }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9px;
            margin: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 8px;
            border-bottom: 1px solid #333;
            padding-bottom: 5px;
        }
        .header h1 {
            margin: 0;
            font-size: 14px;
            color: #7c5cba;
        }
        .header p {
            margin: 2px 0;
            font-size: 8px;
        }
        .info-section {
            margin-bottom: 8px;
            background-color: #f8f9fa;
            padding: 6px;
            border-radius: 3px;
        }
        .info-section h3 {
            margin: 0 0 5px 0;
            font-size: 10px;
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 3px;
        }
        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 3px;
        }
        .info-col {
            display: table-cell;
            width: 50%;
            padding: 2px 3px;
        }
        .info-label {
            font-weight: bold;
            color: #555;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }
        table thead {
            background-color: #7c5cba;
            color: white;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 3px 4px;
            text-align: left;
        }
        table th {
            font-weight: bold;
            font-size: 8px;
        }
        table td {
            font-size: 8px;
        }
        .text-center {
            text-align: center;
        }
        .text-end {
            text-align: right;
        }
        .text-danger {
            color: #dc3545;
        }
        .text-success {
            color: #28a745;
        }
        .text-muted {
            color: #6c757d;
        }
        .fw-bold {
            font-weight: bold;
        }
        .table-danger {
            background-color: #f8d7da;
        }
        tfoot {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .tfoot-success {
            background-color: #d4edda;
        }
        .tfoot-primary {
            background-color: #d1e7fd;
        }
        .tfoot-info {
            background-color: #d1ecf1;
        }
        .tfoot-warning {
            background-color: #fff3cd;
        }
        del {
            text-decoration: line-through;
        }
        small {
            font-size: 7px;
        }
        .alert {
            padding: 10px;
            margin-top: 15px;
            border-radius: 5px;
            border: 1px solid;
        }
        .alert-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        .alert-warning {
            background-color: #fff3cd;
            border-color: #ffeaa7;
            color: #856404;
        }
        .alert h4 {
            margin: 0 0 5px 0;
            font-size: 11px;
        }
        .alert ul {
            margin: 5px 0 0 15px;
            padding: 0;
        }
        .alert li {
            font-size: 8px;
            margin-bottom: 2px;
        }
        .footer {
            margin-top: 10px;
            text-align: center;
            font-size: 6px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 5px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>CALENDRIER DE REMBOURSEMENT</h1>
        <p><strong>Système de Microcrédit</strong></p>
        <p>Date d'impression : {{ date('d/m/Y H:i') }}</p>
    </div>

    <!-- Informations du crédit -->
    <div class="info-section">
        <h3>Informations du crédit</h3>
        
        <!-- Identification du crédit -->
        <div class="info-row">
            <div class="info-col">
                <span class="info-label">Référence :</span> {{ $loan->refNumber }}
            </div>
            <div class="info-col">
                <span class="info-label">Statut :</span> 
                @php
                    $statusLabels = [
                        'draft' => 'Brouillon',
                        'accepted' => 'Accepté',
                        'validated' => 'Validé',
                        'done' => 'Terminé',
                        'rejected' => 'Rejeté',
                        'toreviewed' => 'À réviser',
                        'finalized' => 'Finalisé'
                    ];
                @endphp
                {{ $statusLabels[$loan->status] ?? ucfirst($loan->status) }}
            </div>
        </div>
        
        <!-- Informations du membre -->
        <div class="info-row">
            <div class="info-col">
                <span class="info-label">Membre :</span> {{ $loan->member->firstName }} {{ $loan->member->lastName }}
            </div>
            <div class="info-col">
                <span class="info-label">Téléphone :</span> {{ $loan->member->phoneNumber }}
            </div>
        </div>
        <div class="info-row">
            <div class="info-col" style="width: 100%;">
                <span class="info-label">Adresse du membre :</span> {{ $loan->member->address ?? 'N/A' }}
            </div>
        </div>
        
        <!-- Informations financières -->
        <div class="info-row">
            <div class="info-col">
                <span class="info-label">Montant demandé :</span> {{ number_format($loan->requestAmount, 2, ',', ' ') }} USD
            </div>
            <div class="info-col">
                <span class="info-label">Durée :</span> {{ $loan->loanMonths }} mois
            </div>
        </div>
        <div class="info-row">
            <div class="info-col">
                <span class="info-label">Taux d'intérêt :</span> {{ $loan->interestRate }}%
            </div>
            <div class="info-col">
                <span class="info-label">Intérêt total à payer :</span> 
                {{ number_format($interestCalculation['total_interest'] - $totalCancelledInterest, 2, ',', ' ') }} USD
            </div>
        </div>
        
        <!-- Informations temporelles -->
        <div class="info-row">
            <div class="info-col">
                <span class="info-label">Date de soummission :</span> {{ date('d/m/Y', strtotime($loan->submitDate)) }}
            </div>
            <div class="info-col">
                <span class="info-label">Date de fin (échéance) :</span> 
                @php
                    $dateFin = \Carbon\Carbon::parse($loan->submitDate)->addMonths($loan->loanMonths);
                @endphp
                {{ $dateFin->format('d/m/Y') }}
            </div>
        </div>
        
        <!-- Description -->
        <div class="info-row">
            <div class="info-col" style="width: 100%;">
                <span class="info-label">Description :</span> {{ $loan->description ?? 'N/A' }}
            </div>
        </div>
    </div>

    <!-- Calendrier de remboursement -->
    <h3 style="margin-top: 20px; font-size: 12px;">Calendrier de remboursement</h3>
    <table>
        <thead>
            <tr>
                <th class="text-center">N°</th>
                <th class="text-center">Mois</th>
                <th class="text-end">Capital restant</th>
                <th class="text-end">Montant Decre</th>
                <th class="text-end">Intérêt</th>
                <th class="text-end">Remb. Decre</th>
            </tr>
        </thead>
        <tbody>
            @foreach($interestCalculation['schedule'] as $index => $payment)
                @php
                    $paymentMonth = date('Y-m', strtotime($payment['date']));
                    $isCancelled = false;
                    $cancelledAmount = 0;
                    
                    // Vérifier si ce mois a des intérêts annulés
                    foreach ($cancelledInterests as $cancelled) {
                        if ($cancelled->cancelledMonth == $paymentMonth) {
                            $isCancelled = true;
                            $cancelledAmount = $cancelled->cancelledInterestAmount;
                            break;
                        }
                    }
                @endphp
            <tr class="{{ $isCancelled ? 'table-danger' : '' }}">
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ date('d M-y', strtotime($payment['date'])) }}</td>
                <td class="text-end">{{ number_format($payment['capital_restant'], 2, ',', ' ') }}</td>
                <td class="text-end">{{ number_format($payment['remboursement_fixe'], 2, ',', ' ') }}</td>
                <td class="text-end">
                    @if($isCancelled)
                        <del class="text-danger">{{ number_format($payment['interet'], 2, ',', ' ') }}</del>
                        <br><small class="text-success fw-bold">ANNULÉ</small>
                    @else
                        {{ number_format($payment['interet'], 2, ',', ' ') }}
                    @endif
                </td>
                <td class="text-end">
                    @if($isCancelled)
                        <del class="text-muted">{{ number_format($payment['montant_total'], 2, ',', ' ') }}</del>
                        <br><small class="text-success fw-bold">{{ number_format($payment['remboursement_fixe'], 2, ',', ' ') }}</small>
                    @else
                        <strong>{{ number_format($payment['montant_total'], 2, ',', ' ') }}</strong>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="2"><strong>TOTAL ORIGINAL</strong></th>
                <th class="text-end">-</th>
                <th class="text-end">{{ number_format($loan->requestAmount, 2, ',', ' ') }}</th>
                <th class="text-end">{{ number_format($interestCalculation['total_interest'], 2, ',', ' ') }}</th>
                <th class="text-end"><strong>{{ number_format($interestCalculation['total_amount'], 2, ',', ' ') }}</strong></th>
            </tr>
            @if($totalCancelledInterest > 0)
            <tr class="tfoot-success">
                <th colspan="2"><strong>ÉCONOMIES (Remboursement anticipé)</strong></th>
                <th class="text-end">-</th>
                <th class="text-end">-</th>
                <th class="text-end text-success"><strong>-{{ number_format($totalCancelledInterest, 2, ',', ' ') }}</strong></th>
                <th class="text-end text-success"><strong>-{{ number_format($totalCancelledInterest, 2, ',', ' ') }}</strong></th>
            </tr>
            @endif
            <tr class="tfoot-primary">
                <th colspan="2"><strong>MONTANT FINAL DÛ</strong></th>
                <th class="text-end">-</th>
                <th class="text-end">{{ number_format($loan->requestAmount, 2, ',', ' ') }}</th>
                <th class="text-end">{{ number_format($interestCalculation['total_interest'] - $totalCancelledInterest, 2, ',', ' ') }}</th>
                <th class="text-end"><strong>{{ number_format($totalAmountDue, 2, ',', ' ') }}</strong></th>
            </tr>
            <tr class="tfoot-info">
                <th colspan="2"><strong>MONTANT REMBOURSÉ</strong></th>
                <th class="text-end">-</th>
                <th class="text-end">-</th>
                <th class="text-end">-</th>
                <th class="text-end"><strong>{{ number_format($totalRepaid, 2, ',', ' ') }}</strong></th>
            </tr>
            <tr class="{{ $remainingAmount <= 0 ? 'tfoot-success' : 'tfoot-warning' }}">
                <th colspan="2"><strong>RESTE DÛ</strong></th>
                <th class="text-end">-</th>
                <th class="text-end">-</th>
                <th class="text-end">-</th>
                <th class="text-end"><strong>{{ number_format($remainingAmount, 2, ',', ' ') }}</strong></th>
            </tr>
        </tfoot>
    </table>

    <!-- Section Visa -->
    <div style="margin-top: 5px; padding-top: 2px;">
        <h4 style="text-align: center; margin-bottom: 10px; font-size: 10px; font-weight: bold;">VISAS</h4>
        <div style="display: table; width: 100%;">
            <div style="display: table-row;">
                <div style="display: table-cell; width: 20%; text-align: center; padding: 5px; vertical-align: top;">
                    <div style="border-bottom: 1px solid #333; padding-bottom: 3px; margin-bottom: 3px; min-height: 30px;">
                        <p style="margin: 0; font-size: 8px; font-weight: bold;">Membre</p>
                    </div>
                    <p style="margin: 2px 0 0 0; font-size: 7px; color: #666;">Signature</p>
                </div>
                <div style="display: table-cell; width: 20%; text-align: center; padding: 5px; vertical-align: top;">
                    <div style="border-bottom: 1px solid #333; padding-bottom: 3px; margin-bottom: 3px; min-height: 30px;">
                        <p style="margin: 0; font-size: 8px; font-weight: bold;">Réception</p>
                    </div>
                    <p style="margin: 2px 0 0 0; font-size: 7px; color: #666;">Signature</p>
                </div>
                <div style="display: table-cell; width: 20%; text-align: center; padding: 5px; vertical-align: top;">
                    <div style="border-bottom: 1px solid #333; padding-bottom: 3px; margin-bottom: 3px; min-height: 30px;">
                        <p style="margin: 0; font-size: 8px; font-weight: bold;">Service des crédits</p>
                    </div>
                    <p style="margin: 2px 0 0 0; font-size: 7px; color: #666;">Signature</p>
                </div>
                <div style="display: table-cell; width: 20%; text-align: center; padding: 5px; vertical-align: top;">
                    <div style="border-bottom: 1px solid #333; padding-bottom: 3px; margin-bottom: 3px; min-height: 30px;">
                        <p style="margin: 0; font-size: 8px; font-weight: bold;">Géranance</p>
                    </div>
                    <p style="margin: 2px 0 0 0; font-size: 7px; color: #666;">Signature</p>
                </div>
                <div style="display: table-cell; width: 20%; text-align: center; padding: 5px; vertical-align: top;">
                    <div style="border-bottom: 1px solid #333; padding-bottom: 3px; margin-bottom: 3px; min-height: 30px;">
                        <p style="margin: 0; font-size: 8px; font-weight: bold;">Caisse</p>
                    </div>
                    <p style="margin: 2px 0 0 0; font-size: 7px; color: #666;">Signature</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p><strong>Système de Microcrédit</strong> | Calendrier généré le {{ date('d/m/Y à H:i') }}</p>
        <p>Document officiel - À conserver</p>
    </div>
</body>
</html>




