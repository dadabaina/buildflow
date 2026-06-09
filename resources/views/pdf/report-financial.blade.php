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
.text-right { text-align: right; }
tfoot td { font-weight: bold; background: #f3f4f6; }
</style>
</head>
<body>
<h1>Rapport Financier — {{ $year }}</h1>

<h3>CA mensuel encaissé</h3>
<table>
<tr>
@php $months_fr = ['Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc']; @endphp
@foreach($months_fr as $m)<th>{{ $m }}</th>@endforeach
</tr>
<tr>
@foreach(range(1,12) as $m)
<td class="text-right">{{ number_format($monthly[$m] ?? 0, 0, ',', ' ') }}</td>
@endforeach
</tr>
</table>

<h3>Dépenses mensuelles</h3>
<table>
<tr>
@foreach($months_fr as $m)<th>{{ $m }}</th>@endforeach
</tr>
<tr>
@foreach(range(1,12) as $m)
<td class="text-right">{{ number_format($expenses[$m] ?? 0, 0, ',', ' ') }}</td>
@endforeach
</tr>
</table>

<h3>Top 10 projets</h3>
<table>
<tr><th>Projet</th><th class="text-right">Facturé (Ar)</th><th class="text-right">Encaissé (Ar)</th></tr>
@foreach($topProjects as $p)
<tr>
    <td>{{ $p->name }}</td>
    <td class="text-right">{{ number_format($p->total_invoiced ?? 0, 0, ',', ' ') }}</td>
    <td class="text-right">{{ number_format($p->total_paid ?? 0, 0, ',', ' ') }}</td>
</tr>
@endforeach
</table>
</body>
</html>
