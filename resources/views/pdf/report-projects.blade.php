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
.text-right { text-align: right; }
tfoot td { font-weight: bold; background: #f3f4f6; }
</style>
</head>
<body>
<h1>Suivi des chantiers</h1>
<table>
<tr><th>Chantier</th><th>Client</th><th>Région</th><th>Statut</th><th class="text-right">Dépenses (Ar)</th><th class="text-right">Facturé (Ar)</th></tr>
@foreach($projects as $p)
<tr>
    <td>{{ $p->name }}</td>
    <td>{{ $p->client->name ?? '—' }}</td>
    <td>{{ $p->region->name ?? '—' }}</td>
    <td>{{ str_replace('_',' ',ucfirst($p->status)) }}</td>
    <td class="text-right">{{ number_format($p->expenses_total ?? 0, 0, ',', ' ') }}</td>
    <td class="text-right">{{ number_format($p->invoiced_total ?? 0, 0, ',', ' ') }}</td>
</tr>
@endforeach
<tfoot>
<tr>
    <td colspan="4" class="text-right">Totaux</td>
    <td class="text-right">{{ number_format($projects->sum('expenses_total'), 0, ',', ' ') }}</td>
    <td class="text-right">{{ number_format($projects->sum('invoiced_total'), 0, ',', ' ') }}</td>
</tr>
</tfoot>
</table>
</body>
</html>
