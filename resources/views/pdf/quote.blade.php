<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
* { box-sizing: border-box; }
body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 11px;
    color: #1f2937;
    margin: 0;
    padding: 24px 30px;
}

/* ── Header ── */
.header-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
.header-table td { border: none; padding: 0; vertical-align: top; }
.company-name { font-size: 18px; font-weight: bold; color: #1a56db; }
.company-info { font-size: 10px; color: #6b7280; line-height: 1.6; margin-top: 4px; }
.doc-title { text-align: right; }
.doc-title h1 { font-size: 22px; color: #1a56db; margin: 0 0 4px 0; }
.doc-ref { font-size: 13px; font-weight: bold; color: #374151; }
.doc-meta { font-size: 10px; color: #6b7280; margin-top: 4px; line-height: 1.6; }
.version-badge { display: inline-block; background: #e5e7eb; color: #374151;
    padding: 2px 6px; border-radius: 3px; font-size: 9px; }

/* ── Divider ── */
.divider { border: none; border-top: 2px solid #1a56db; margin: 14px 0; }

/* ── Parties ── */
.parties-table { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
.parties-table td { width: 50%; vertical-align: top; padding: 0 8px 0 0; border: none; }
.parties-table td:last-child { padding: 0 0 0 8px; }
.party-box { background: #f8faff; border: 1px solid #dbeafe; border-radius: 4px; padding: 10px 12px; }
.party-label { font-size: 9px; text-transform: uppercase; color: #6b7280; margin-bottom: 4px; letter-spacing: 0.05em; }
.party-name { font-size: 13px; font-weight: bold; color: #111827; margin-bottom: 3px; }
.party-detail { font-size: 10px; color: #4b5563; line-height: 1.5; }

/* ── Meta info row ── */
.meta-row { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
.meta-row td { border: none; padding: 0 8px 0 0; width: 25%; vertical-align: top; }
.meta-cell { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 4px;
    padding: 6px 10px; text-align: center; }
.meta-cell .label { font-size: 9px; color: #9ca3af; text-transform: uppercase; display: block; margin-bottom: 2px; }
.meta-cell .value { font-size: 11px; font-weight: bold; color: #111827; }
.meta-cell .value.expired { color: #dc2626; }

/* ── Items table ── */
.items-table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
.items-table thead tr { background: #1a56db; }
.items-table thead th {
    color: #fff;
    font-size: 9px;
    text-transform: uppercase;
    padding: 6px 8px;
    letter-spacing: 0.04em;
    font-weight: bold;
}
.items-table thead th.right { text-align: right; }
.items-table tbody tr:nth-child(even) { background: #f9fafb; }
.items-table tbody tr.section-row { background: #dbeafe; }
.items-table tbody td { padding: 5px 8px; font-size: 10px; border-bottom: 1px solid #e5e7eb; }
.items-table tbody td.right { text-align: right; }
.items-table tbody td.section-title {
    font-weight: bold; font-size: 11px; color: #1a56db;
    padding-left: 10px; border-left: 3px solid #1a56db;
}

/* ── Totals ── */
.totals-table { width: 48%; float: right; border-collapse: collapse; margin-top: 4px; }
.totals-table td { padding: 4px 8px; font-size: 10px; border-bottom: 1px solid #f3f4f6; }
.totals-table td.label { color: #6b7280; }
.totals-table td.value { text-align: right; font-weight: bold; color: #111827; }
.totals-table td.value.red { color: #dc2626; }
.totals-table tr.total-row td { background: #1a56db; color: #fff; font-size: 13px; font-weight: bold; }
.totals-table tr.total-row td.value { text-align: right; }

/* ── Notes ── */
.clearfix::after { content: ''; display: table; clear: both; }
.section-box { margin-top: 16px; padding: 10px 12px; border-left: 3px solid #1a56db;
    background: #f8faff; font-size: 10px; line-height: 1.6; }
.section-box .title { font-size: 11px; font-weight: bold; color: #1a56db; margin-bottom: 4px; }

/* ── Footer ── */
.footer { margin-top: 32px; font-size: 9px; color: #9ca3af; border-top: 1px solid #e5e7eb;
    padding-top: 6px; text-align: center; }
</style>
</head>
<body>

{{-- ══ EN-TÊTE ══ --}}
<table class="header-table">
<tr>
    <td style="width:55%">
        @if($company->logo_path && file_exists(storage_path('app/public/' . $company->logo_path)))
            <img src="{{ storage_path('app/public/' . $company->logo_path) }}"
                 style="max-height:48px; max-width:160px; margin-bottom:6px;" alt="Logo">
            <br>
        @endif
        <div class="company-name">{{ $company->name }}</div>
        <div class="company-info">
            @if($company->address){{ $company->address }}<br>@endif
            @if($company->city){{ $company->city }}<br>@endif
            @if($company->phone)Tél : {{ $company->phone }}<br>@endif
            @if($company->email){{ $company->email }}@endif
        </div>
    </td>
    <td class="doc-title">
        <h1>DEVIS</h1>
        <div class="doc-ref">{{ $quote->reference }}</div>
        @if($quote->version > 1)
            <div style="margin-top:3px;">
                <span class="version-badge">Version {{ $quote->version }}</span>
            </div>
        @endif
        <div class="doc-meta">
            Statut : <strong>{{ $quote->status_libelle }}</strong>
        </div>
    </td>
</tr>
</table>

<hr class="divider">

{{-- ══ CLIENT / CHANTIER ══ --}}
<table class="parties-table">
<tr>
    <td>
        <div class="party-box">
            <div class="party-label">Émetteur</div>
            <div class="party-name">{{ $company->name }}</div>
            <div class="party-detail">
                @if($company->address){{ $company->address }}<br>@endif
                @if($company->phone){{ $company->phone }}<br>@endif
                @if($company->email){{ $company->email }}@endif
            </div>
        </div>
    </td>
    <td>
        <div class="party-box">
            <div class="party-label">Client</div>
            <div class="party-name">{{ $quote->client?->name ?? '—' }}</div>
            <div class="party-detail">
                @if($quote->client?->phone){{ $quote->client->phone }}<br>@endif
                @if($quote->client?->email){{ $quote->client->email }}<br>@endif
                @if($quote->client?->address){{ $quote->client->address }}@endif
            </div>
        </div>
    </td>
</tr>
</table>

{{-- ══ DATES / CHANTIER ══ --}}
<table class="meta-row">
<tr>
    <td>
        <div class="meta-cell">
            <span class="label">Date d'émission</span>
            <span class="value">{{ $quote->quote_date->format('d/m/Y') }}</span>
        </div>
    </td>
    <td>
        <div class="meta-cell">
            <span class="label">Valide jusqu'au</span>
            <span class="value {{ $quote->valid_until?->isPast() ? 'expired' : '' }}">
                {{ $quote->valid_until?->format('d/m/Y') ?? '—' }}
            </span>
        </div>
    </td>
    <td>
        <div class="meta-cell">
            <span class="label">Chantier</span>
            <span class="value">{{ $quote->project?->name ?? 'Indépendant' }}</span>
        </div>
    </td>
    <td>
        <div class="meta-cell">
            <span class="label">TVA</span>
            <span class="value">{{ (float)$quote->tva_rate }} %</span>
        </div>
    </td>
</tr>
</table>

{{-- ══ LIGNES DU DEVIS ══ --}}
<table class="items-table">
<thead>
<tr>
    <th style="width:44%">Désignation</th>
    <th style="width:10%" class="right">Qté</th>
    <th style="width:7%">Unité</th>
    <th style="width:12%" class="right">Prix unit. HT</th>
    <th style="width:8%" class="right">Remise %</th>
    <th style="width:13%" class="right">Total HT</th>
</tr>
</thead>
<tbody>

@php
    $hasAnySections = $quote->sections->isNotEmpty();
@endphp

@if($hasAnySections)
    @foreach($quote->sections as $section)
        <tr class="section-row">
            <td class="section-title" colspan="6">{{ $section->title }}</td>
        </tr>
        @foreach($section->items as $item)
        <tr>
            <td style="padding-left:14px">{{ $item->description }}</td>
            <td class="right">{{ number_format($item->quantity, 2, ',', ' ') }}</td>
            <td>{{ $item->unit ?? '' }}</td>
            <td class="right">{{ number_format($item->unit_price, 0, ',', ' ') }}</td>
            <td class="right">{{ $item->discount > 0 ? number_format($item->discount, 1, ',', ' ').'%' : '—' }}</td>
            <td class="right">{{ number_format($item->total_ht, 0, ',', ' ') }}</td>
        </tr>
        @endforeach
    @endforeach
    @php $unsectioned = $quote->items->whereNull('quote_section_id') @endphp
    @if($unsectioned->isNotEmpty())
        <tr class="section-row">
            <td class="section-title" colspan="6">Divers</td>
        </tr>
        @foreach($unsectioned as $item)
        <tr>
            <td>{{ $item->description }}</td>
            <td class="right">{{ number_format($item->quantity, 2, ',', ' ') }}</td>
            <td>{{ $item->unit ?? '' }}</td>
            <td class="right">{{ number_format($item->unit_price, 0, ',', ' ') }}</td>
            <td class="right">{{ $item->discount > 0 ? number_format($item->discount, 1, ',', ' ').'%' : '—' }}</td>
            <td class="right">{{ number_format($item->total_ht, 0, ',', ' ') }}</td>
        </tr>
        @endforeach
    @endif
@else
    @forelse($quote->items as $item)
    <tr>
        <td>{{ $item->description }}</td>
        <td class="right">{{ number_format($item->quantity, 2, ',', ' ') }}</td>
        <td>{{ $item->unit ?? '' }}</td>
        <td class="right">{{ number_format($item->unit_price, 0, ',', ' ') }}</td>
        <td class="right">{{ $item->discount > 0 ? number_format($item->discount, 1, ',', ' ').'%' : '—' }}</td>
        <td class="right">{{ number_format($item->total_ht, 0, ',', ' ') }}</td>
    </tr>
    @empty
    <tr><td colspan="6" style="text-align:center; color:#9ca3af; padding: 12px;">Aucune ligne.</td></tr>
    @endforelse
@endif

</tbody>
</table>

{{-- ══ TOTAUX ══ --}}
<div class="clearfix">
<table class="totals-table">
<tr>
    <td class="label">Sous-total HT</td>
    <td class="value">{{ number_format($quote->subtotal_ht, 0, ',', ' ') }} Ar</td>
</tr>
@if($quote->discount_amount > 0)
<tr>
    <td class="label">Remise ({{ (float)$quote->discount_global }}{{ $quote->discount_type === 'percent' ? ' %' : ' Ar' }})</td>
    <td class="value red">− {{ number_format($quote->discount_amount, 0, ',', ' ') }} Ar</td>
</tr>
@endif
<tr>
    <td class="label">Base taxable HT</td>
    <td class="value">{{ number_format($quote->taxable_ht, 0, ',', ' ') }} Ar</td>
</tr>
<tr>
    <td class="label">TVA ({{ (float)$quote->tva_rate }} %)</td>
    <td class="value">{{ number_format($quote->tva_amount, 0, ',', ' ') }} Ar</td>
</tr>
<tr class="total-row">
    <td class="label">TOTAL TTC</td>
    <td class="value">{{ number_format($quote->total_ttc, 0, ',', ' ') }} Ar</td>
</tr>
</table>
</div>

{{-- ══ NOTES & CONDITIONS ══ --}}
@if($quote->notes)
<div class="section-box" style="margin-top:24px">
    <div class="title">Notes / Remarques</div>
    {!! nl2br(e($quote->notes)) !!}
</div>
@endif

@if($quote->terms)
<div class="section-box">
    <div class="title">Conditions générales</div>
    {!! nl2br(e($quote->terms)) !!}
</div>
@endif

{{-- ══ PIED DE PAGE ══ --}}
<div class="footer">
    {{ $company->name ?? 'BuildFlow' }}
    @if($company->address) — {{ $company->address }}@endif
    &nbsp;|&nbsp; Généré le {{ now()->format('d/m/Y à H:i') }}
    &nbsp;|&nbsp; {{ $quote->reference }}
    @if($quote->version > 1) — v{{ $quote->version }}@endif
</div>

</body>
</html>
