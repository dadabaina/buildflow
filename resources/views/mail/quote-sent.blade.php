<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
body { font-family: Arial, sans-serif; color: #333; font-size: 14px; }
.btn { display: inline-block; padding: 10px 24px; background: #1a56db; color: #fff;
       text-decoration: none; border-radius: 4px; font-weight: bold; }
.footer { margin-top: 32px; font-size: 11px; color: #999; border-top: 1px solid #eee; padding-top: 12px; }
</style>
</head>
<body>
<p>Bonjour {{ $quote->client?->name ?? 'cher client' }},</p>

<p>
    Veuillez trouver ci-joint le devis <strong>{{ $quote->reference }}</strong>
    @if($quote->title)— {{ $quote->title }}@endif
    émis par <strong>{{ $quote->company->name ?? 'BuildFlow' }}</strong>.
</p>

@if($quote->valid_until)
<p>Ce devis est valable jusqu'au <strong>{{ $quote->valid_until->format('d/m/Y') }}</strong>.</p>
@endif

<p>Vous pouvez consulter et valider ce devis en ligne via le lien ci-dessous :</p>

<p style="text-align:center; margin: 24px 0;">
    <a href="{{ route('quotes.public', $quote->client_token) }}" class="btn">
        Voir &amp; valider le devis en ligne
    </a>
</p>

<p>N'hésitez pas à nous contacter pour toute question.</p>

<p>Cordialement,<br>
<strong>{{ $quote->company->name ?? 'L\'équipe BuildFlow' }}</strong></p>

<div class="footer">
    Ce message est généré automatiquement. Merci de ne pas y répondre directement.
</div>
</body>
</html>
