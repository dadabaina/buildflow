<x-layouts.app title="Rapport Financier - {{ $year }}">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('reports.index') }}" class="text-decoration-none opacity-50 text-dark">Rapports</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">Financier</li>
    </x-slot>

    <!-- Header & Filter -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <div>
                            <h4 class="mb-1 fw-bold">Rapport Financier Annuel</h4>
                            <p class="text-muted small mb-0">Analyse des flux de trésorerie et rentabilité pour l'exercice {{ $year }}.</p>
                        </div>
                        <form method="GET" class="d-flex gap-2">
                            <select name="year" class="form-select border-0 bg-light" onchange="this.form.submit()">
                                @foreach($years as $y)
                                    <option value="{{ $y }}" @selected($year == $y)>Année {{ $y }}</option>
                                @endforeach
                            </select>
                            <button name="export" value="pdf" class="btn btn-outline-danger btn-icon shadow-sm" title="Exporter en PDF">
                                <i class="bx bxs-file-pdf"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart Row -->
    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header border-bottom bg-transparent py-4">
                    <h6 class="mb-0 fw-bold"><i class="bx bx-chart me-2 text-primary"></i>Évolution Mensuelle (CA vs Dépenses)</h6>
                </div>
                <div class="card-body">
                    <div id="financialChart" style="min-height: 350px;"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Top Projects Table -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header border-bottom bg-transparent py-4">
                    <h6 class="mb-0 fw-bold"><i class="bx bx-crown me-2 text-warning"></i>Top 10 Projets par Facturation</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 py-3 border-0 small text-uppercase text-muted">Projet</th>
                                    <th class="py-3 border-0 small text-uppercase text-muted text-end">Facturé TTC</th>
                                    <th class="py-3 border-0 small text-uppercase text-muted text-end">Encaissé</th>
                                    <th class="pe-4 py-3 border-0 small text-uppercase text-muted text-end">Marge brute</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topProjects as $project)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark">{{ $project->name }}</div>
                                        <small class="text-muted">{{ $project->client?->name }}</small>
                                    </td>
                                    <td class="text-end fw-bold text-amount">{{ number_format($project->total_invoiced, 0, ',', ' ') }}</td>
                                    <td class="text-end text-success fw-bold text-amount">{{ number_format($project->total_paid, 0, ',', ' ') }}</td>
                                    <td class="pe-4 text-end">
                                        @php $marge = $project->total_invoiced - ($project->expenses_total ?? 0); @endphp
                                        <span class="fw-bold text-amount {{ $marge >= 0 ? 'text-primary' : 'text-danger' }}">
                                            {{ number_format($marge, 0, ',', ' ') }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center py-5 text-muted">Aucune donnée disponible.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Recap Sidebar -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header border-bottom bg-transparent py-4">
                    <h6 class="mb-0 fw-bold">Récapitulatif Mensuel (Ar)</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @php
                            $months = [
                                1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril', 5 => 'Mai', 6 => 'Juin',
                                7 => 'Juillet', 8 => 'Août', 9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
                            ];
                        @endphp
                        @foreach($months as $idx => $name)
                            @php
                                $ca = $monthly[$idx] ?? 0;
                                $dep = $expenses[$idx] ?? 0;
                                $bal = $ca - $dep;
                            @endphp
                            @if($ca > 0 || $dep > 0)
                            <div class="list-group-item border-0 border-bottom border-light px-4 py-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-bold text-dark">{{ $name }}</span>
                                    <span class="badge {{ $bal >= 0 ? 'bg-label-success' : 'bg-label-danger' }} text-amount">
                                        {{ $bal >= 0 ? '+' : '' }}{{ number_format($bal, 0, ',', ' ') }}
                                    </span>
                                </div>
                                <div class="d-flex gap-3 small">
                                    <span class="text-success fw-medium text-amount"><i class="bx bx-up-arrow-alt"></i> {{ number_format($ca, 0, ',', ' ') }}</span>
                                    <span class="text-danger fw-medium text-amount"><i class="bx bx-down-arrow-alt"></i> {{ number_format($dep, 0, ',', ' ') }}</span>
                                </div>
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .bg-label-success { background-color: #e8fadf !important; color: #71dd37 !important; }
        .bg-label-danger { background-color: #ffe5e5 !important; color: #ff3e1d !important; }
        .bg-label-primary { background-color: #e7e7ff !important; color: #696cff !important; }
    </style>
    @endpush

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var options = {
                series: [{
                    name: 'Chiffre d\'Affaires',
                    data: @json(array_values(array_replace(array_fill(1, 12, 0), $monthly->toArray())))
                }, {
                    name: 'Dépenses Validées',
                    data: @json(array_values(array_replace(array_fill(1, 12, 0), $expenses->toArray())))
                }],
                chart: {
                    type: 'area',
                    height: 350,
                    toolbar: { show: false },
                    zoom: { enabled: false }
                },
                colors: ['#696cff', '#ff3e1d'],
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 3 },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.4,
                        opacityTo: 0.1,
                        stops: [0, 90, 100]
                    }
                },
                xaxis: {
                    categories: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'],
                },
                yaxis: {
                    labels: {
                        formatter: function (value) {
                            return (value / 1000).toLocaleString() + "k";
                        }
                    }
                },
                tooltip: {
                    y: {
                        formatter: function (val) {
                            return val.toLocaleString() + " Ar";
                        }
                    }
                }
            };

            var chart = new ApexCharts(document.querySelector("#financialChart"), options);
            chart.render();
        });
    </script>
    @endpush
</x-layouts.app>
