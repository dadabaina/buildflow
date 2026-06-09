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
.badge { padding: 2px 6px; border-radius: 4px; font-size: 9px; color: #fff; }
.bg-warning { background:#f59e0b; }
.bg-primary { background:#1a56db; }
.meta { display: flex; gap: 20px; margin: 10px 0; }
.meta-item { flex: 1; }
.meta-label { font-size: 9px; color: #6b7280; text-transform: uppercase; }
.meta-value { font-size: 12px; font-weight: bold; }
.section-block { margin: 12px 0; padding: 8px 12px; border-left: 3px solid #1a56db; background: #f8faff; }
</style>
</head>
<body>
<table style="width:100%;margin-bottom:12px;border:none">
<tr>
    <td style="border:none;padding:0"><h1>{{ $report->title }}</h1></td>
    <td style="border:none;padding:0;text-align:right;font-size:10px;color:#6b7280">
        Réf : {{ $report->reference }}<br>
        Date : {{ $report->report_date?->format('d/m/Y') }}<br>
        Lieu : {{ $report->location }}
    </td>
</tr>
</table>

<div class="meta">
    <div class="meta-item"><div class="meta-label">Projet</div><div class="meta-value">{{ $report->project->name ?? '—' }}</div></div>
    <div class="meta-item"><div class="meta-label">Météo</div><div class="meta-value">{{ $report->weather ?? '—' }}</div></div>
    <div class="meta-item"><div class="meta-label">Statut</div><div class="meta-value">{{ $report->status === 'finalise' ? 'Finalisé' : 'Brouillon' }}</div></div>
</div>

@if($report->participants)
<h3>Participants</h3>
<table><tr><th>Nom</th><th>Qualité</th></tr>
@foreach(json_decode($report->participants, true) ?? [] as $p)
<tr><td>{{ $p['name'] ?? '' }}</td><td>{{ $p['role'] ?? '' }}</td></tr>
@endforeach
</table>
@endif

<h3>Contenu / Compte-rendu</h3>
<div class="section-block">{!! nl2br(e($report->content)) !!}</div>

@if($report->items->count())
<h3>Points d'action</h3>
<table>
<tr><th>Description</th><th>Responsable</th><th>Échéance</th><th>Statut</th></tr>
@foreach($report->items as $item)
<tr>
    <td>{{ $item->description }}</td>
    <td>{{ $item->responsible }}</td>
    <td>{{ $item->due_date?->format('d/m/Y') }}</td>
    <td>{{ ucfirst($item->status) }}</td>
</tr>
@endforeach
</table>
@endif

@if($report->next_meeting_date)
<p style="margin-top:12px"><strong>Prochaine réunion :</strong> {{ $report->next_meeting_date?->format('d/m/Y') }}</p>
@endif
</body>
</html>
