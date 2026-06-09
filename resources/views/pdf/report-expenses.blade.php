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
<h1>Journal des dépenses — {{ $year }}</h1>
<table>
<tr><th>Date</th><th>Projet</th><th>Catégorie</th><th>Description</th><th class="text-right">Montant (Ar)</th><th>Statut</th></tr>
@foreach($expenses as $e)
<tr>
    <td>{{ $e->expense_date?->format('d/m/Y') }}</td>
    <td>{{ $e->project->name ?? '—' }}</td>
    <td>{{ $e->category->name ?? '—' }}</td>
    <td>{{ \Illuminate\Support\Str::limit($e->description, 40) }}</td>
    <td class="text-right">{{ number_format($e->amount, 0, ',', ' ') }}</td>
    <td>{{ ucfirst($e->status) }}</td>
</tr>
@endforeach
<tfoot>
<tr>
    <td colspan="4" class="text-right">Total</td>
    <td class="text-right">{{ number_format($expenses->sum('amount'), 0, ',', ' ') }}</td>
    <td></td>
</tr>
</tfoot>
</table>
</body>
</html>
