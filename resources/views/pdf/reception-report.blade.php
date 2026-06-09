<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #333; margin: 20px; }
h1 { font-size: 18px; color: #1a56db; border-bottom: 2px solid #1a56db; padding-bottom: 6px; }
h3 { font-size: 13px; color: #1a56db; margin-top: 16px; margin-bottom: 4px; }
table { width: 100%; border-collapse: collapse; margin-top: 8px; }
th { background: #1a56db; color: #fff; padding: 5px 8px; text-align: left; font-size: 10px; }
td { padding: 5px 8px; border-bottom: 1px solid #e5e7eb; font-size: 10px; }
.meta-row { display: flex; gap: 24px; margin: 10px 0; }
.meta-item { flex: 1; }
.meta-label { font-size: 9px; color: #6b7280; text-transform: uppercase; }
.meta-value { font-size: 12px; font-weight: bold; }
.section-block { margin: 10px 0; padding: 8px 12px; border-left: 3px solid #1a56db; background: #f8faff; }
.signature-box { margin-top: 40px; }
.sig-line { display: inline-block; width: 180px; border-top: 1px solid #333; margin-top: 40px; margin-right: 40px; text-align: center; font-size: 9px; }
</style>
</head>
<body>
<table style="width:100%;margin-bottom:12px;border:none">
<tr>
    <td style="border:none;padding:0"><h1>Procès-Verbal de Réception</h1></td>
    <td style="border:none;padding:0;text-align:right;font-size:10px;color:#6b7280">
        Réf : {{ $report->reference }}<br>
        Date réception : {{ $report->reception_date?->format('d/m/Y') }}
    </td>
</tr>
</table>

<table style="border:none">
<tr style="border:none">
    <td style="border:none;padding:2px 8px 2px 0"><strong>Projet :</strong> {{ $report->project->name ?? '—' }}</td>
    <td style="border:none;padding:2px 0"><strong>Client :</strong> {{ $report->client_name }}</td>
</tr>
</table>

<h3>Réserves émises</h3>
<div class="section-block">{!! nl2br(e($report->reserves ?? 'Aucune réserve.')) !!}</div>

<h3>Retenue de garantie</h3>
<table>
<tr><th>Montant RG (Ar)</th><th>Date de libération prévue</th><th>Statut</th></tr>
<tr>
    <td>{{ number_format($report->rg_amount ?? 0, 0, ',', ' ') }}</td>
    <td>{{ $report->rg_release_date?->format('d/m/Y') ?? '—' }}</td>
    <td>{{ $report->status === 'rg_libere' ? 'RG Libérée' : ($report->status === 'signe' ? 'Signé' : 'Brouillon') }}</td>
</tr>
</table>

@if($report->notes)
<h3>Notes</h3>
<div class="section-block">{!! nl2br(e($report->notes)) !!}</div>
@endif

<div class="signature-box">
<span class="sig-line">Maître d'ouvrage<br>(Client)</span>
<span class="sig-line">Maître d'œuvre<br>(Entreprise)</span>
<span class="sig-line">Date</span>
</div>
</body>
</html>
