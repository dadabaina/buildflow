<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #333; margin: 20px; }
h1 { font-size: 18px; color: #1a56db; border-bottom: 2px solid #1a56db; padding-bottom: 6px; }
table { width: 100%; border-collapse: collapse; margin-top: 8px; }
th { background: #1a56db; color: #fff; padding: 5px 8px; text-align: left; font-size: 10px; }
td { padding: 5px 8px; border-bottom: 1px solid #e5e7eb; font-size: 10px; }
</style>
</head>
<body>
<h1>Récapitulatif pointage — {{ \Carbon\Carbon::create()->month($month)->translatedFormat('F') }} {{ $year }}</h1>
<table>
<tr><th>Date</th><th>Employé</th><th>Projet</th><th>Heures</th><th>Statut</th></tr>
@foreach($attendances as $a)
<tr>
    <td>{{ $a->date?->format('d/m/Y') }}</td>
    <td>{{ $a->employee->full_name ?? '—' }}</td>
    <td>{{ $a->project->name ?? '—' }}</td>
    <td>{{ $a->hours ?? '—' }}</td>
    <td>{{ ucfirst($a->status) }}</td>
</tr>
@endforeach
</table>
</body>
</html>
