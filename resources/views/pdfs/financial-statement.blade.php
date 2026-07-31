<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Rapport Financier - Brillio Africa</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #374151;
            line-height: 1.4;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }

        .header {
            width: 100%;
            border-bottom: 2px solid #4F46E5;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        .logo {
            color: #4F46E5;
            font-size: 26px;
            font-weight: bold;
            margin: 0;
        }

        .company-info {
            font-size: 11px;
            color: #6B7280;
            line-height: 1.4;
        }

        .report-title {
            text-align: right;
            vertical-align: top;
        }

        .report-title h2 {
            margin: 0;
            color: #111827;
            font-size: 20px;
        }

        .report-title p {
            margin: 5px 0 0 0;
            color: #4B5563;
            font-size: 12px;
        }

        .stats-grid {
            width: 100%;
            margin-bottom: 25px;
            border-spacing: 10px;
            margin-left: -10px;
            margin-right: -10px;
        }

        .stats-card {
            background-color: #F9FAFB;
            border: 1px solid #E5E7EB;
            border-radius: 8px;
            padding: 15px;
            vertical-align: top;
        }

        .stats-card-title {
            font-size: 10px;
            text-transform: uppercase;
            color: #6B7280;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .stats-card-value {
            font-size: 18px;
            font-weight: bold;
            color: #111827;
        }

        .stats-card-subtitle {
            font-size: 10px;
            color: #9CA3AF;
            margin-top: 5px;
        }

        .chart-container {
            border: 1px solid #E5E7EB;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 25px;
            background-color: #FFFFFF;
        }

        .chart-title {
            font-size: 13px;
            font-weight: bold;
            color: #111827;
            margin-bottom: 12px;
        }

        .table-title {
            font-size: 13px;
            font-weight: bold;
            color: #111827;
            margin-bottom: 10px;
            margin-top: 20px;
        }

        .transactions-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .transactions-table th {
            background-color: #F3F4F6;
            color: #4B5563;
            font-weight: bold;
            padding: 8px 10px;
            text-align: left;
            border-bottom: 1px solid #E5E7EB;
            font-size: 11px;
        }

        .transactions-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #E5E7EB;
            font-size: 11px;
        }

        .transactions-table tr:nth-child(even) {
            background-color: #F9FAFB;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 9px;
            font-weight: bold;
            border-radius: 4px;
        }

        .badge-in {
            background-color: #DEF7EC;
            color: #03543F;
        }

        .badge-out {
            background-color: #FDE8E8;
            color: #9B1C1C;
        }

        .text-right {
            text-align: right;
        }

        .text-green {
            color: #057A55;
        }

        .text-red {
            color: #E02424;
        }

        .footer {
            margin-top: 30px;
            border-top: 1px solid #E5E7EB;
            padding-top: 10px;
            text-align: center;
            font-size: 9px;
            color: #9CA3AF;
        }
    </style>
</head>

<body>

    <!-- Header Block -->
    <div class="header" style="overflow: hidden; width: 100%; border-bottom: 2px solid #4F46E5; padding-bottom: 15px; margin-bottom: 25px;">
        <div style="float: left; width: 60%;">
            <h1 class="logo" style="margin: 0; color: #4F46E5; font-size: 26px; font-weight: bold;">Brillio Africa</h1>
            <div class="company-info" style="font-size: 11px; color: #6B7280; line-height: 1.4;">
                <strong>Brillio Africa SARL</strong><br>
                Fidjrossè-Kpota, Cotonou, Bénin<br>
                IFU : 3202653526854 | RCCM : RB/COT/26 B 42787
            </div>
        </div>
        <div class="report-title" style="float: right; width: 40%; text-align: right;">
            <h2 style="margin: 0; color: #111827; font-size: 20px;">ÉTAT FINANCIER</h2>
            <p style="margin: 5px 0 0 0; color: #4B5563; font-size: 12px;">Période : {{ $startDate->format('d/m/Y') }} au {{ $endDate->format('d/m/Y') }}</p>
            <p style="font-size: 10px; color: #9CA3AF; margin-top: 2px;">Généré le {{ now()->format('d/m/Y H:i') }}</p>
        </div>
        <div style="clear: both;"></div>
    </div>

    <!-- Key Metrics Dashboard Grid -->
    <div class="stats-grid" style="overflow: hidden; width: 100%; margin-bottom: 25px;">
        <!-- Row 1 -->
        <div style="overflow: hidden; width: 100%; margin-bottom: 15px;">
            <div class="stats-card" style="float: left; width: 31%; margin-right: 2%; border-left: 4px solid #10B981;">
                <div class="stats-card-title">Recettes (Cash In)</div>
                <div class="stats-card-value" style="color: #10B981;">+{{ number_format($revenue, 0, ',', ' ') }} FCFA</div>
                <div class="stats-card-subtitle">Achats de packs crédits</div>
            </div>
            <div class="stats-card" style="float: left; width: 31%; margin-right: 2%; border-left: 4px solid #EF4444;">
                <div class="stats-card-title">Dépenses (Cash Out)</div>
                <div class="stats-card-value" style="color: #EF4444;">-{{ number_format($payouts, 0, ',', ' ') }} FCFA</div>
                <div class="stats-card-subtitle">Retraits mentors validés</div>
            </div>
            <div class="stats-card" style="float: left; width: 31%; border-left: 4px solid #4F46E5;">
                <div class="stats-card-title">Solde Net (Cash Flow)</div>
                <div class="stats-card-value" style="color: #4F46E5;">{{ ($netIncome >= 0 ? '+' : '') . number_format($netIncome, 0, ',', ' ') }} FCFA</div>
                <div class="stats-card-subtitle">Flux net de trésorerie</div>
            </div>
            <div style="clear: both;"></div>
        </div>
        
        <!-- Row 2 -->
        <div style="overflow: hidden; width: 100%;">
            <div class="stats-card" style="float: left; width: 31%; margin-right: 2%; border-left: 4px solid #8B5CF6;">
                <div class="stats-card-title">Revenus Services</div>
                <div class="stats-card-value" style="color: #8B5CF6;">{{ number_format($targetingRevenueCredits, 0, ',', ' ') }} Cr.</div>
                <div class="stats-card-subtitle">≈ {{ number_format($estimatedTargetingRevenueFcfa, 0, ',', ' ') }} FCFA (Ciblage)</div>
            </div>
            <div class="stats-card" style="float: left; width: 31%; margin-right: 2%; border-left: 4px solid #6366F1;">
                <div class="stats-card-title">Revenus Organisations</div>
                <div class="stats-card-value" style="color: #6366F1;">{{ number_format($orgRevenue, 0, ',', ' ') }} FCFA</div>
                <div class="stats-card-subtitle">Abonnements & packs orgs</div>
            </div>
            <div class="stats-card" style="float: left; width: 31%; background-color: #F3F4F6;">
                <div class="stats-card-title">Statut Période</div>
                <div class="stats-card-value" style="font-size: 14px; margin-top: 4px;">
                    @if($netIncome > 0)
                        🟢 Excédentaire
                    @elseif($netIncome < 0)
                        🔴 Déficitaire
                    @else
                        🟡 Équilibré
                    @endif
                </div>
                <div class="stats-card-subtitle">Indicateur de rentabilité</div>
            </div>
            <div style="clear: both;"></div>
        </div>
    </div>

    <!-- Native SVG Chart Container -->
    @php
        $maxVal = max(max($chartData['revenue'] ?? [0]), max($chartData['payouts'] ?? [0]), 1000);
        $width = 680;
        $height = 160;
        $paddingLeft = 55;
        $paddingBottom = 25;
        $paddingTop = 15;
        $paddingRight = 15;

        $plotWidth = $width - $paddingLeft - $paddingRight;
        $plotHeight = $height - $paddingTop - $paddingBottom;

        $count = count($chartData['labels'] ?? []);
        $pointsRevenue = [];
        $pointsPayouts = [];

        foreach (($chartData['revenue'] ?? []) as $i => $val) {
            $x = $paddingLeft + ($count > 1 ? ($i * ($plotWidth / ($count - 1))) : 0);
            $y = $paddingTop + $plotHeight - ($val / $maxVal * $plotHeight);
            $pointsRevenue[] = "$x,$y";
        }

        foreach (($chartData['payouts'] ?? []) as $i => $val) {
            $x = $paddingLeft + ($count > 1 ? ($i * ($plotWidth / ($count - 1))) : 0);
            $y = $paddingTop + $plotHeight - ($val / $maxVal * $plotHeight);
            $pointsPayouts[] = "$x,$y";
        }

        $revenuePolyline = implode(' ', $pointsRevenue);
        $payoutsPolyline = implode(' ', $pointsPayouts);
    @endphp

    <div class="chart-container">
        <div class="chart-title">Évolution journalière des flux (Recettes vs Dépenses)</div>
        <svg width="{{ $width }}" height="{{ $height }}" style="font-family: sans-serif; font-size: 9px; overflow: visible;">
            <!-- Gridlines -->
            @for($j = 0; $j <= 4; $j++)
                @php
                    $yGrid = $paddingTop + ($plotHeight * (4 - $j) / 4);
                    $labelGrid = round($maxVal * $j / 4);
                @endphp
                <line x1="{{ $paddingLeft }}" y1="{{ $yGrid }}" x2="{{ $width - $paddingRight }}" y2="{{ $yGrid }}" stroke="#E5E7EB" stroke-width="1" stroke-dasharray="3,3" />
                <text x="{{ $paddingLeft - 8 }}" y="{{ $yGrid + 3 }}" text-anchor="end" fill="#6B7280">{{ number_format($labelGrid, 0, ',', ' ') }}</text>
            @endfor

            <!-- Polyline Lines -->
            @if(count($pointsRevenue) > 1)
                <polyline fill="none" stroke="#10B981" stroke-width="2.5" points="{{ $revenuePolyline }}" />
            @endif
            @if(count($pointsPayouts) > 1)
                <polyline fill="none" stroke="#EF4444" stroke-width="2.5" points="{{ $payoutsPolyline }}" stroke-dasharray="4,2" />
            @endif

            <!-- Bottom Line (X-Axis) -->
            <line x1="{{ $paddingLeft }}" y1="{{ $paddingTop + $plotHeight }}" x2="{{ $width - $paddingRight }}" y2="{{ $paddingTop + $plotHeight }}" stroke="#9CA3AF" stroke-width="1" />

            <!-- X Axis Labels (Limit to ~6 labels to prevent clutter) -->
            @php $step = max(1, ceil($count / 6)); @endphp
            @foreach(($chartData['labels'] ?? []) as $i => $label)
                @if($i % $step === 0 || $i === $count - 1)
                    @php
                        $xLabel = $paddingLeft + ($count > 1 ? ($i * ($plotWidth / ($count - 1))) : 0);
                    @endphp
                    <text x="{{ $xLabel }}" y="{{ $paddingTop + $plotHeight + 14 }}" text-anchor="middle" fill="#6B7280">{{ $label }}</text>
                @endif
            @endforeach
        </svg>

        <div style="margin-top: 10px; text-align: center; font-size: 10px;">
            <span style="display: inline-block; width: 10px; height: 10px; background-color: #10B981; margin-right: 4px; vertical-align: middle;"></span>
            <span style="color: #4B5563; margin-right: 20px;">Recettes (Cash In)</span>
            <span style="display: inline-block; width: 10px; height: 10px; background-color: #EF4444; margin-right: 4px; vertical-align: middle;"></span>
            <span style="color: #4B5563;">Dépenses (Cash Out)</span>
        </div>
    </div>

    <!-- Table Details -->
    <div class="table-title">Relevé des Opérations de la Période</div>
    <table class="transactions-table">
        <thead>
            <tr>
                <th style="width: 15%;">Date</th>
                <th style="width: 15%;">Référence</th>
                <th style="width: 30%;">Bénéficiaire / Donneur</th>
                <th style="width: 12%;">Type</th>
                <th style="width: 13%;">Libellé</th>
                <th style="width: 15%; text-align: right;">Montant</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $t)
                <tr>
                    <td>{{ $t['date']->format('d/m/Y H:i') }}</td>
                    <td style="font-family: monospace;">{{ $t['reference'] }}</td>
                    <td>
                        @if(($t['user']->user_type ?? '') === 'organization' && $t['user']->organization)
                            {{ $t['user']->organization->name }}
                        @else
                            {{ $t['user']->name ?? 'Utilisateur inconnu' }}
                        @endif
                        <br><span style="font-size: 9px; color: #9CA3AF;">{{ $t['user']->email ?? '' }}</span>
                    </td>
                    <td>
                        @if($t['type'] === 'in')
                            <span class="badge badge-in">Recette</span>
                        @else
                            <span class="badge badge-out">Dépense</span>
                        @endif
                    </td>
                    <td>{{ $t['label'] }}</td>
                    <td class="text-right {{ $t['type'] === 'in' ? 'text-green' : 'text-red' }}" style="font-weight: bold;">
                        {{ $t['type'] === 'in' ? '+' : '-' }}{{ number_format($t['amount'], 0, ',', ' ') }} FCFA
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #6B7280; padding: 20px;">Aucune transaction sur cette période.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Brillio Africa SARL • Capital Social : 2 000 000 FCFA • RCCM : RB/COT/26 B 42787 • Cotonou, Bénin
    </div>

</body>

</html>
