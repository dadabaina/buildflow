<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">

    <div class="app-brand demo">
        <a href="{{ route('dashboard') }}" class="app-brand-link">
            <span class="app-brand-logo demo me-1">
                <svg width="25" viewBox="0 0 25 42" version="1.1" xmlns="http://www.w3.org/2000/svg">
                    <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                        <g transform="translate(3.000000, 1.000000)">
                            <polygon fill="#696CFF" opacity="0.2" points="18.1 -0.3 1.2 15.6 0 23.4 18.1 24.5"></polygon>
                            <polygon fill="#696CFF" points="18.1 -0.3 1.2 15.6 12.7 18.6"></polygon>
                            <polygon fill="#696CFF" opacity="0.5" points="11.7 20.2 8.6 17.4 19.3 3.4"></polygon>
                            <polygon fill="#696CFF" points="18.1 -0.3 19.3 3.4 11.7 20.2 0 23.4 18.1 24.5"></polygon>
                            <polygon fill="#696CFF" opacity="0.6" points="0 23.4 11.7 20.2 18.1 24.5"></polygon>
                        </g>
                    </g>
                </svg>
            </span>
            <span class="app-brand-text demo menu-text fw-bold">BuildFlow</span>
        </a>
        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="icon-base bx bx-chevron-left icon-sm"></i>
        </a>
    </div>

    <div class="menu-divider mt-0"></div>
    <div class="menu-inner-shadow"></div>

    @php
        $user = auth()->user();
        $isAdmin = $user->hasRole(['admin', 'super_admin']) || $user->email === 'admin@demo.mg';

        $canReports = $isAdmin || $user->can('reports.view');
        $canProjects = $isAdmin || $user->can('projects.view');
        $canTasks = $isAdmin || $user->can('tasks.view');
        $canAttendances = $isAdmin || $user->can('attendances.view');
        $canSalaryPayments = $isAdmin || $user->can('salary_payments.view');
        $canDocuments = $isAdmin || $user->can('documents.view');
        $canSiteReports = $isAdmin || $user->can('site_reports.view');
        $canReceptionReports = $isAdmin || $user->can('reception_reports.view');
        $canSuppliers = $isAdmin || $user->can('suppliers.view');
        $canEmployees = $isAdmin || $user->can('employees.view');
        $canPurchaseOrders = $isAdmin || $user->can('purchase_orders.view');
        $canMaterials = $isAdmin || $user->can('materials.view');
        $canDosage = $isAdmin || $user->can('dosage.view');
        $canExpenseTemplates = $isAdmin || $user->can('expense_templates.view');
        $canEquipments = $isAdmin || $user->can('equipments.view');
        $canWarehouses = $isAdmin || $user->can('warehouses.view');
        $canStock = $isAdmin || $user->can('stock.view');
        $canClients = $isAdmin || $user->can('clients.view');
        $canQuotes = $isAdmin || $user->can('quotes.view');
        $canAmendments = $isAdmin || $user->can('amendments.view');
        $canProgressBillings = $isAdmin || $user->can('progress_billings.view');
        $canInvoices = $isAdmin || $user->can('invoices.view');
        $canPayments = $isAdmin || $user->can('payments.view');
        $canExpenses = $isAdmin || $user->can('expenses.view');
        $canSettings = $isAdmin || $user->can('settings.view');
        $canUsers = $isAdmin || $user->can('users.view');
        $canRoles = $isAdmin || $user->can('roles.view');

        $showExploitation = $canProjects || $canTasks || $canAttendances || $canSalaryPayments || $canDocuments || $canSiteReports || $canReceptionReports;
        $showRapportsTerrain = $canSiteReports || $canReceptionReports;
        $showLogistique = $canSuppliers || $canEmployees || $canPurchaseOrders || $canMaterials || $canDosage || $canExpenseTemplates || $canEquipments || $canWarehouses || $canStock;
        $showPartenaires = $canSuppliers || $canEmployees;
        $showBibliotheque = $canMaterials || $canDosage || $canExpenseTemplates;
        $showMaterielsStock = $canEquipments || $canWarehouses || $canStock;
        $showFinance = $canClients || $canQuotes || $canAmendments || $canProgressBillings || $canInvoices || $canPayments || $canExpenses;
        $showEtudesDevis = $canQuotes || $canAmendments;
        $showFacturation = $canProgressBillings || $canInvoices || $canPayments;
        $showAdministration = $canSettings || $canUsers || $canRoles;
    @endphp

    <ul class="menu-inner py-1">

        <!-- 1. Pilotage -->
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Pilotage</span>
        </li>
        <li class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <a href="{{ route('dashboard') }}" class="menu-link">
                <i class="menu-icon icon-base bx bx-home-alt"></i>
                <div>Tableau de bord</div>
            </a>
        </li>
        @if($canReports)
        <li class="menu-item {{ request()->routeIs('reports.*') ? 'active' : '' }}">
            <a href="{{ route('reports.index') }}" class="menu-link">
                <i class="menu-icon icon-base bx bx-bar-chart-square"></i>
                <div>Analyses & Rapports</div>
            </a>
        </li>
        @endif

        <!-- 2. Exploitation -->
        @if($showExploitation)
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Exploitation</span>
        </li>
        @if($canProjects)
        <li class="menu-item {{ request()->routeIs('projects.*') ? 'active' : '' }}">
            <a href="{{ route('projects.index') }}" class="menu-link">
                <i class="menu-icon icon-base bx bx-building-house"></i>
                <div>Chantiers</div>
            </a>
        </li>
        @endif
        @if($canTasks)
        <li class="menu-item {{ request()->routeIs('tasks.*') ? 'active' : '' }}">
            <a href="{{ route('tasks.index', ['status' => 'en_cours']) }}" class="menu-link">
                <i class="menu-icon icon-base bx bx-task"></i>
                <div>Tâches & Suivi</div>
            </a>
        </li>
        @endif
        @if($canAttendances || $canSalaryPayments)
        <li class="menu-item {{ request()->routeIs('attendances.*', 'pointage.*', 'salary-payments.*') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon icon-base bx bx-time-five"></i>
                <div>Pointage</div>
            </a>
            <ul class="menu-sub">
                @if($canAttendances)
                <li class="menu-item {{ request()->routeIs('pointage.*') ? 'active' : '' }}">
                    <a href="{{ route('pointage.kiosque') }}" class="menu-link"><div>Kiosque (Entrée / Sortie)</div></a>
                </li>
                <li class="menu-item {{ request()->routeIs('attendances.*') ? 'active' : '' }}">
                    <a href="{{ route('attendances.index') }}" class="menu-link"><div>Suivi & Historique</div></a>
                </li>
                @endif
                @if($canSalaryPayments)
                <li class="menu-item {{ request()->routeIs('salary-payments.*') ? 'active' : '' }}">
                    <a href="{{ route('salary-payments.index') }}" class="menu-link"><div>Paiements Salariés</div></a>
                </li>
                @endif
            </ul>
        </li>
        @endif
        @if($canDocuments)
        <li class="menu-item {{ request()->routeIs('documents.*') ? 'active' : '' }}">
            <a href="{{ route('documents.index') }}" class="menu-link">
                <i class="menu-icon icon-base bx bx-folder-open"></i>
                <div>Documents (GED)</div>
            </a>
        </li>
        @endif
        @if($showRapportsTerrain)
        <li class="menu-item {{ request()->routeIs('site-reports.*', 'reception-reports.*') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon icon-base bx bx-notepad"></i>
                <div>Rapports de terrain</div>
            </a>
            <ul class="menu-sub">
                @if($canSiteReports)
                <li class="menu-item {{ request()->routeIs('site-reports.*') ? 'active' : '' }}">
                    <a href="{{ route('site-reports.index') }}" class="menu-link"><div>Comptes-rendus</div></a>
                </li>
                @endif
                @if($canReceptionReports)
                <li class="menu-item {{ request()->routeIs('reception-reports.*') ? 'active' : '' }}">
                    <a href="{{ route('reception-reports.index') }}" class="menu-link"><div>PV Réception</div></a>
                </li>
                @endif
            </ul>
        </li>
        @endif
        @endif

        <!-- 3. Logistique -->
        @if($showLogistique)
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Logistique</span>
        </li>
        @if($showPartenaires)
        <li class="menu-item {{ request()->routeIs('suppliers.*', 'employees.*') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon icon-base bx bx-group"></i>
                <div>Partenaires & Staff</div>
            </a>
            <ul class="menu-sub">
                @if($canSuppliers)
                <li class="menu-item {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                    <a href="{{ route('suppliers.index') }}" class="menu-link"><div>Fournisseurs</div></a>
                </li>
                @endif
                @if($canEmployees)
                <li class="menu-item {{ request()->routeIs('employees.*') ? 'active' : '' }}">
                    <a href="{{ route('employees.index') }}" class="menu-link"><div>Employés</div></a>
                </li>
                @endif
            </ul>
        </li>
        @endif
        @if($canPurchaseOrders)
        <li class="menu-item {{ request()->routeIs('purchase-orders.*') ? 'active' : '' }}">
            <a href="{{ route('purchase-orders.index') }}" class="menu-link">
                <i class="menu-icon icon-base bx bx-cart"></i>
                <div>Bons de commande</div>
            </a>
        </li>
        @endif
        @if($showBibliotheque)
        <li class="menu-item {{ request()->routeIs('materials.*', 'dosage.*', 'expense-templates.*') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon icon-base bx bx-cube-alt"></i>
                <div>Bibliothèque Technique</div>
            </a>
            <ul class="menu-sub">
                @if($canMaterials)
                <li class="menu-item {{ request()->routeIs('materials.*') ? 'active' : '' }}">
                    <a href="{{ route('materials.index') }}" class="menu-link"><div>Matériaux</div></a>
                </li>
                @endif
                @if($canDosage)
                <li class="menu-item {{ request()->routeIs('dosage.*') ? 'active' : '' }}">
                    <a href="{{ route('dosage.index') }}" class="menu-link"><div>Dosages DBE</div></a>
                </li>
                @endif
                @if($canExpenseTemplates)
                <li class="menu-item {{ request()->routeIs('expense-templates.*') ? 'active' : '' }}">
                    <a href="{{ route('expense-templates.index') }}" class="menu-link"><div>Modèles de dépense</div></a>
                </li>
                @endif
            </ul>
        </li>
        @endif
        @if($showMaterielsStock)
        <li class="menu-item {{ request()->routeIs('equipments.*', 'warehouses.*', 'stock.*') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon icon-base bx bx-wrench"></i>
                <div>Matériels & Stock</div>
            </a>
            <ul class="menu-sub">
                @if($canEquipments)
                <li class="menu-item {{ request()->routeIs('equipments.*') ? 'active' : '' }}">
                    <a href="{{ route('equipments.index') }}" class="menu-link"><div>Parc Matériel</div></a>
                </li>
                @endif
                @if($canWarehouses)
                <li class="menu-item {{ request()->routeIs('warehouses.*') ? 'active' : '' }}">
                    <a href="{{ route('warehouses.index') }}" class="menu-link"><div>Dépôts / Magasins</div></a>
                </li>
                @endif
                @if($canStock)
                <li class="menu-item {{ request()->routeIs('stock.*') ? 'active' : '' }}">
                    <a href="{{ route('stock.dashboard') }}" class="menu-link"><div>État des Stocks</div></a>
                </li>
                @endif
            </ul>
        </li>
        @endif
        @endif

        <!-- 4. Finance -->
        @if($showFinance)
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Finance</span>
        </li>
        @if($canClients)
        <li class="menu-item {{ request()->routeIs('clients.*') ? 'active' : '' }}">
            <a href="{{ route('clients.index') }}" class="menu-link">
                <i class="menu-icon icon-base bx bx-user-voice"></i>
                <div>Clients</div>
            </a>
        </li>
        @endif
        @if($showEtudesDevis)
        <li class="menu-item {{ request()->routeIs('quotes.*', 'amendments.*') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon icon-base bx bx-spreadsheet"></i>
                <div>Études & Devis</div>
            </a>
            <ul class="menu-sub">
                @if($canQuotes)
                <li class="menu-item {{ request()->routeIs('quotes.*') ? 'active' : '' }}">
                    <a href="{{ route('quotes.index') }}" class="menu-link"><div>Devis</div></a>
                </li>
                @endif
                @if($canAmendments)
                <li class="menu-item {{ request()->routeIs('amendments.*') ? 'active' : '' }}">
                    <a href="{{ route('amendments.index') }}" class="menu-link"><div>Avenants</div></a>
                </li>
                @endif
            </ul>
        </li>
        @endif
        @if($showFacturation)
        <li class="menu-item {{ request()->routeIs('invoices.*', 'progress-billings.*', 'payments.*') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon icon-base bx bx-receipt"></i>
                <div>Facturation</div>
            </a>
            <ul class="menu-sub">
                @if($canProgressBillings)
                <li class="menu-item {{ request()->routeIs('progress-billings.*') ? 'active' : '' }}">
                    <a href="{{ route('progress-billings.index') }}" class="menu-link"><div>Situations (Décompte)</div></a>
                </li>
                @endif
                @if($canInvoices)
                <li class="menu-item {{ request()->routeIs('invoices.*') ? 'active' : '' }}">
                    <a href="{{ route('invoices.index') }}" class="menu-link"><div>Factures Clients</div></a>
                </li>
                @endif
                @if($canPayments)
                <li class="menu-item {{ request()->routeIs('payments.*') ? 'active' : '' }}">
                    <a href="{{ route('payments.index') }}" class="menu-link"><div>Paiements Reçus</div></a>
                </li>
                @endif
            </ul>
        </li>
        @endif
        @if($canExpenses)
        <li class="menu-item {{ request()->routeIs('expenses.*') ? 'active' : '' }}">
            <a href="{{ route('expenses.index') }}" class="menu-link">
                <i class="menu-icon icon-base bx bx-money"></i>
                <div>Dépenses & Frais</div>
            </a>
        </li>
        @endif
        @endif

        <!-- 5. Administration -->
        @if($showAdministration)
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Administration</span>
        </li>

        @if($canSettings)
        <li class="menu-item {{ request()->routeIs('settings.*') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon icon-base bx bx-cog"></i>
                <div>Paramètres</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('settings.index') ? 'active' : '' }}">
                    <a href="{{ route('settings.index') }}" class="menu-link"><div>Entreprise</div></a>
                </li>
                <li class="menu-item {{ request()->routeIs('settings.regions.*') ? 'active' : '' }}">
                    <a href="{{ route('settings.regions.index') }}" class="menu-link"><div>Régions</div></a>
                </li>
                <li class="menu-item {{ request()->routeIs('settings.job_types.*') ? 'active' : '' }}">
                    <a href="{{ route('settings.job_types.index') }}" class="menu-link"><div>Postes & Fonctions</div></a>
                </li>
                <li class="menu-item {{ request()->routeIs('settings.unit_types.*') ? 'active' : '' }}">
                    <a href="{{ route('settings.unit_types.index') }}" class="menu-link"><div>Unités de mesure</div></a>
                </li>
                <li class="menu-item {{ request()->routeIs('settings.expense_categories.*') ? 'active' : '' }}">
                    <a href="{{ route('settings.expense_categories.index') }}" class="menu-link"><div>Catégories dépenses</div></a>
                </li>
                <li class="menu-item {{ request()->routeIs('settings.salary_rates.*') ? 'active' : '' }}">
                    <a href="{{ route('settings.salary_rates.index') }}" class="menu-link"><div>Grille salariale</div></a>
                </li>
                <li class="menu-item {{ request()->routeIs('settings.material_categories.*') ? 'active' : '' }}">
                    <a href="{{ route('settings.material_categories.index') }}" class="menu-link"><div>Catégories matériaux</div></a>
                </li>
            </ul>
        </li>
        @endif

        @if($canUsers || $canRoles)
        <li class="menu-item {{ request()->routeIs('users.*', 'roles.*') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon icon-base bx bx-shield-quarter"></i>
                <div>Accès & Sécurité</div>
            </a>
            <ul class="menu-sub">
                @if($canUsers)
                <li class="menu-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <a href="{{ route('users.index') }}" class="menu-link"><div>Utilisateurs</div></a>
                </li>
                @endif
                @if($canRoles)
                <li class="menu-item {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                    <a href="{{ route('roles.index') }}" class="menu-link"><div>Rôles & Permissions</div></a>
                </li>
                @endif
            </ul>
        </li>
        @endif
        @endif

    </ul>
</aside>
