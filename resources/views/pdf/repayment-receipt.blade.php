<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $receipt_title }}</title>
    <style>
        @page {
            size: A5 portrait;
            margin: 0;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 8px;
            line-height: 1.2;
            margin: 0;
            padding: 0;
            color: #000;
            background: #fff;
        }
        
        .page-wrapper {
            width: 148mm;
            height: 210mm;
            position: relative;
            page-break-after: always;
            page-break-inside: avoid;
        }
        
        .receipt-container {
            width: 100%;
            height: 210mm; /* Toute la page A5 */
            page-break-inside: avoid;
            page-break-after: avoid;
            overflow: hidden;
        }
        
        .receipt {
            width: 100%;
            height: 210mm;
            padding: 10px;
            page-break-inside: avoid;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
            color: #7c5cba;
            letter-spacing: 1px;
        }
        
        .header p {
            margin: 4px 0;
            font-size: 11px;
        }
        
        .section {
            margin-bottom: 10px;
        }
        
        .section-title {
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 5px;
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 3px;
        }
        
        .section-content {
            margin-left: 0;
        }
        
        .section-content p {
            margin: 3px 0;
            font-size: 10px;
        }
        
        .divider {
            border-top: 1px dashed #000;
            margin: 8px 0;
            padding: 0;
        }
        
        .divider-thick {
            border-top: 2px solid #000;
            margin: 10px 0;
        }
        
        .footer {
            text-align: center;
            border-top: 2px solid #000;
            padding-top: 10px;
            margin-top: 15px;
        }
        
        .footer p {
            margin: 3px 0;
            font-size: 9px;
        }
        
        .bold {
            font-weight: bold;
        }
        
        .text-center {
            text-align: center;
        }
        
        .amount {
            font-size: 16px;
            font-weight: bold;
            color: #28a745;
        }
        
        .reference {
            font-size: 12px;
            font-weight: bold;
            color: #7c5cba;
        }
        
        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 4px;
        }
        
        .info-label {
            display: table-cell;
            width: 40%;
            font-weight: bold;
            font-size: 10px;
        }
        
        .info-value {
            display: table-cell;
            width: 60%;
            font-size: 10px;
        }
        
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 30px;
            padding-top: 5px;
            text-align: center;
            font-size: 9px;
        }
        
        .highlight-box {
            background: #f8f9fa;
            border: 1px solid #ddd;
            padding: 8px;
            border-radius: 4px;
            margin: 8px 0;
        }
        
        .receipt-label {
            text-align: center;
            font-size: 9px;
            color: #666;
            margin-bottom: 8px;
            padding: 5px;
            background: #f0f0f0;
            border: 1px dashed #999;
        }
    </style>
</head>
<body>
    <div class="page-wrapper">
    <div class="receipt-container">
        <div class="receipt">
            
            <!-- En-tête -->
            <div class="header">
                <h1>{{ $company_name }}</h1>
                <p>{{ $receipt_title }}</p>
                <p>N° {{ $repayment->loanRepaymentId }}</p>
            </div>
            
            <!-- Informations du remboursement -->
            <div class="section">
                <div class="section-title">INFORMATIONS DU REMBOURSEMENT</div>
                <div class="section-content">
                    <div class="info-row">
                        <div class="info-label">Date:</div>
                        <div class="info-value">{{ $repayment->repaymentDate->format('d/m/Y') }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Heure:</div>
                        <div class="info-value">{{ $repayment->createdAt->format('H:i') }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Type:</div>
                        <div class="info-value">{{ $repayment->repaymentType->repaymentName }}</div>
                    </div>
                    <div class="highlight-box">
                        <div class="text-center">
                            <p style="margin: 0; font-size: 10px;">MONTANT REMBOURSÉ</p>
                            <p class="amount" style="margin: 5px 0; font-size: 18px;">{{ number_format($repayment->amount, 2, ',', ' ') }} USD</p>
                        </div>
                    </div>
                    @if($penaltyAmount > 0)
                        <div class="info-row">
                            <div class="info-label">Pénalités payées:</div>
                            <div class="info-value">{{ number_format($penaltyAmount, 2, ',', ' ') }} USD</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">TOTAL PAYÉ:</div>
                            <div class="info-value amount" style="font-size: 14px;">{{ number_format($repayment->amount + $penaltyAmount, 2, ',', ' ') }} USD</div>
                        </div>
                    @endif
                </div>
            </div>
            
            <div class="divider-thick"></div>
            
            <!-- Informations du membre -->
            <div class="section">
                <div class="section-title">INFORMATIONS DU MEMBRE</div>
                <div class="section-content">
                    <p class="bold" style="font-size: 11px;">{{ $repayment->loanDoc->member->firstName }} {{ $repayment->loanDoc->member->lastName }}</p>
                    <p style="font-size: 10px;">Tél: {{ $repayment->loanDoc->member->phoneNumber }}</p>
                    @if($repayment->loanDoc->member->address)
                    <p style="font-size: 10px;">{{ $repayment->loanDoc->member->address }}</p>
                    @endif
                </div>
            </div>
            
            <div class="divider"></div>
            
            <!-- Informations du crédit -->
            <div class="section">
                <div class="section-title">INFORMATIONS DU CRÉDIT</div>
                <div class="section-content">
                    <div class="info-row">
                        <div class="info-label">Référence:</div>
                        <div class="info-value reference">{{ $repayment->loanDoc->refNumber }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Montant crédit:</div>
                        <div class="info-value">{{ number_format($repayment->loanDoc->requestAmount, 2, ',', ' ') }} USD</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Durée:</div>
                        <div class="info-value">{{ $repayment->loanDoc->loanMonths }} mois</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Taux:</div>
                        <div class="info-value">{{ $repayment->loanDoc->interestRate }}%</div>
                    </div>
                </div>
            </div>
            
            <div class="divider"></div>
            
            <!-- État du crédit -->
            <div class="section">
                <div class="section-title">ÉTAT DU CRÉDIT</div>
                <div class="section-content">
                    <div class="info-row">
                        <div class="info-label">Total dû:</div>
                        <div class="info-value">{{ number_format($totalAmountDue, 2, ',', ' ') }} USD</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Déjà remboursé:</div>
                        <div class="info-value">{{ number_format($totalRepaid, 2, ',', ' ') }} USD</div>
                    </div>
                    <div class="highlight-box">
                        <div class="text-center">
                            <p style="margin: 0; font-size: 10px;">RESTE DÛ</p>
                            <p class="amount" style="margin: 5px 0; font-size: 16px; color: #dc3545;">{{ number_format($remainingAmount, 2, ',', ' ') }} USD</p>
                        </div>
                    </div>
                </div>
            </div>
            
            @if($penaltyAmount > 0)
                <div class="divider"></div>
                <div class="section">
                    <div class="section-title">DÉTAIL DES PÉNALITÉS</div>
                    <div class="section-content">
                        @foreach($recentPenalties as $penalty)
                            <p style="font-size: 10px;"><span class="bold">Échéance {{ $penalty->penaltyMonth }}:</span> {{ number_format($penalty->amount, 2, ',', ' ') }} USD</p>
                        @endforeach
                    </div>
                </div>
            @endif
            
            <div class="divider-thick"></div>
            
            <!-- Signature -->
            <div class="signature-line">
                <p>Signature du membre</p>
            </div>
            
            <!-- Pied de page -->
            <div class="footer">
                <p class="bold" style="font-size: 11px;">Merci pour votre confiance</p>
                <p style="font-size: 9px;">Généré le {{ $generated_at }}</p>
                <p style="font-size: 9px;">Enregistré par: {{ $repayment->user->fullName ?? 'Système' }}</p>
            </div>
        </div>
    </div>
    </div>
</body>
</html>
