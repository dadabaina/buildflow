<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
body { font-family: Arial, sans-serif; color: #333; font-size: 14px; }
.item { padding: 12px 16px; border-left: 3px solid #1a56db; background: #f8f9fa; margin-bottom: 10px; border-radius: 4px; }
.item a { color: #1a56db; text-decoration: none; font-weight: bold; }
.footer { margin-top: 32px; font-size: 11px; color: #999; border-top: 1px solid #eee; padding-top: 12px; }
</style>
</head>
<body>
<p>Bonjour,</p>

<p>
    Récapitulatif du <strong>{{ $digestDate->format('d/m/Y') }}</strong> pour
    <strong>{{ $typeLabel }}</strong> ({{ $company->name }}) —
    {{ $items->count() }} {{ $items->count() > 1 ? 'événements' : 'événement' }} aujourd'hui.
</p>

@foreach($items as $item)
<div class="item">
    <p style="margin: 0 0 4px 0;">{{ $item['message'] }}</p>
    <a href="{{ $item['url'] }}">Voir le détail →</a>
</div>
@endforeach

<p class="footer">
    Cet email récapitule une fois par jour les notifications « {{ $typeLabel }} » de BuildFlow.
    Pour modifier les destinataires, rendez-vous dans Paramètres → Notifications par email.
</p>
</body>
</html>
