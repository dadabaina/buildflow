<x-layouts.app title="Analyse Prévu vs Réel">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Analyses & Rapports</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">Prévu vs Réel</li>
    </x-slot>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('reports.planned-vs-real') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-8">
                    <label class="form-label fw-bold">Sélectionner un chantier pour l'analyse</label>
                    <select name="project_id" class="form-select select2" required>
                        <option value="">-- Choisir un chantier --</option>
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}" {{ $projectId == $p->id ? 'selected' : '' }}>
                                {{ $p->reference }} - {{ $p->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bx bx-analyse me-1"></i> Lancer l'analyse
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if($project)
    <div class="row g-4">
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h5 class="mb-0 fw-bold">Comparaison par poste budgétaire</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th>Poste</th>
                                    <th class="text-end">Prévu (DBE)</th>
                                    <th class="text-end">Réel (Dépenses)</th>
                                    <th class="text-end">Écart (Ar)</th>
                                    <th class="text-center">Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $categories = ['Matériaux', 'Main d\'œuvre', 'Matériel', 'Sous-traitance'];
                                    $totalPlanned = 0;
                                    $totalReal = 0;
                                @endphp
                                @foreach($categories as $cat)
                                    @php
                                        $pVal = $analysis['planned'][$cat] ?? 0;
                                        $rVal = $analysis['real'][$cat] ?? 0;
                                        $diff = $pVal - $rVal;
                                        $totalPlanned += $pVal;
                                        $totalReal += $rVal;
                                    @endphp
                                    <tr>
                                        <td class="fw-bold">{{ $cat }}</td>
                                        <td class="text-end">{{ number_format($pVal, 0, ',', ' ') }} Ar</td>
                                        <td class="text-end">{{ number_format($rVal, 0, ',', ' ') }} Ar</td>
                                        <td class="text-end {{ $diff < 0 ? 'text-danger fw-bold' : 'text-success' }}">
                                            {{ $diff > 0 ? '+' : '' }}{{ number_format($diff, 0, ',', ' ') }} Ar
                                        </td>
                                        <td class="text-center">
                                            @if($pVal == 0 && $rVal > 0)
                                                <span class="badge bg-label-warning">Hors budget</span>
                                            @elseif($diff < 0)
                                                <span class="badge bg-label-danger">Dépassement</span>
                                            @elseif($rVal > 0)
                                                <span class="badge bg-label-success">Maîtrisé</span>
                                            @else
                                                <span class="badge bg-label-secondary">En attente</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-light fw-bold">
                                <tr>
                                    <td>TOTAL</td>
                                    <td class="text-end">{{ number_format($totalPlanned, 0, ',', ' ') }} Ar</td>
                                    <td class="text-end">{{ number_format($totalReal, 0, ',', ' ') }} Ar</td>
                                    <td class="text-end {{ ($totalPlanned - $totalReal) < 0 ? 'text-danger' : 'text-success' }}">
                                        {{ number_format($totalPlanned - $totalReal, 0, ',', ' ') }} Ar
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="mb-0 fw-bold">Interprétation de l'analyse</h6>
                </div>
                <div class="card-body">
                    @if($totalPlanned == 0)
                        <div class="alert alert-info border-0 mb-0">
                            <i class="bx bx-info-circle me-2"></i>
                            <strong>Note :</strong> Aucun dosage n'a été utilisé pour le devis de ce chantier. La comparaison se base sur les montants globaux.
                        </div>
                    @elseif($totalReal > $totalPlanned)
                        <div class="alert alert-danger border-0 mb-0">
                            <i class="bx bx-trending-down me-2"></i>
                            <strong>Alerte :</strong> Vos dépenses réelles dépassent le budget prévu de {{ number_format(abs($totalPlanned - $totalReal), 0, ',', ' ') }} Ar. Vérifiez les consommations sur le terrain.
                        </div>
                    @else
                        <div class="alert alert-success border-0 mb-0">
                            <i class="bx bx-trending-up me-2"></i>
                            <strong>Félicitations :</strong> Pour le moment, vos dépenses sont conformes au budget prévisionnel. Votre marge est protégée.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="text-center py-5">
        <i class="bx bx-spreadsheet fs-1 text-muted opacity-25 d-block mb-3"></i>
        <h5 class="text-muted">Sélectionnez un chantier pour afficher l'analyse détaillée.</h5>
        <p class="text-muted small">Cette analyse compare vos dépenses réelles validées avec les dosages (DBE) prévus dans vos devis acceptés.</p>
    </div>
    @endif
</x-layouts.app>
