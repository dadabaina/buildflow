import os
import re

def process_file(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    # Pattern to find the breadcrumb slot
    pattern = re.compile(r'(<x-slot\s+name=["\']breadcrumb["\']>)(.*?)(</x-slot>)', re.DOTALL)
    
    def replace_slot(match):
        prefix = match.group(1)
        inner_content = match.group(2)
        suffix = match.group(3)
        
        # 1. First, let's fix the corrupted links if they exist
        # Example of corruption: <a href="{{ route(" class="text-decoration-none opacity-50 text-dark">Chantiers</a>
        # We know what the links SHOULD be based on the file path or common patterns.
        # But wait, maybe I can just extract the text and reconstruct the probable route.
        # Or better, let's look at the original file if I can... but I can't easily.
        
        # Actually, most of these routes follow a pattern: resource.index
        # Let's try to find the text and match it to a known route.
        
        known_routes = {
            'Chantiers': "{{ route('projects.index') }}",
            'Paiements': "{{ route('payments.index') }}",
            'Situations': "{{ route('progress-billings.index') }}",
            'Devis': "{{ route('quotes.index') }}",
            'Bons de commande': "{{ route('purchase-orders.index') }}",
            'Réceptions': "{{ route('reception-reports.index') }}",
            'Rapports': "{{ route('reports.index') }}",
            'Paramètres': "{{ route('settings.index') }}",
            'Clients': "{{ route('clients.index') }}",
            'Fournisseurs': "{{ route('suppliers.index') }}",
            'Employés': "{{ route('employees.index') }}",
            'Matériaux': "{{ route('materials.index') }}",
            'Équipements': "{{ route('equipments.index') }}",
            'Dépenses': "{{ route('expenses.index') }}",
            'Factures': "{{ route('invoices.index') }}",
            'Utilisateurs': "{{ route('users.index') }}",
            'Entrepôts': "{{ route('warehouses.index') }}",
            'Avenants': "{{ route('amendments.index') }}",
            'Pointages': "{{ route('attendances.index') }}",
            'Dosages': "{{ route('dosage.index') }}",
            'Documents': "{{ route('documents.index') }}",
        }

        # Extract items
        li_pattern = re.compile(r'<li.*?>(.*?)</li>', re.DOTALL)
        lis = li_pattern.findall(inner_content)
        
        new_lis = []
        new_lis.append('<li class="breadcrumb-item"><a href="{{ route(\'dashboard\') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>')
        
        processed_items = []
        for li_inner in lis:
            if 'dashboard' in li_inner or 'Accueil' in li_inner:
                continue
            
            # Extract text
            text = re.sub(r'<.*?>', '', li_inner).strip()
            if not text: continue

            # Try to find if it was a link (even if corrupted)
            is_link = 'href=' in li_inner or '</a>' in li_inner
            
            if is_link:
                # Reconstruct link if it's a known route
                route = known_routes.get(text)
                if not route:
                    # Try to find route in the corrupted href
                    route_match = re.search(r'route\([\'"]([^\'"]+)[\'"]\)', li_inner)
                    if route_match:
                        route = f"{{{{ route('{route_match.group(1)}') }}}}"
                    else:
                        # Fallback: if we can't find it, we might have to guess or keep as text
                        route = None
                
                if route:
                    processed_items.append({'type': 'link', 'href': route, 'text': text})
                else:
                    processed_items.append({'type': 'text', 'text': text})
            else:
                processed_items.append({'type': 'text', 'text': text})

        for i, item in enumerate(processed_items):
            is_last = (i == len(processed_items) - 1)
            if is_last:
                new_lis.append(f'<li class="breadcrumb-item active fw-bold text-dark">{item["text"]}</li>')
            else:
                if item['type'] == 'link':
                    new_lis.append(f'<li class="breadcrumb-item"><a href="{item["href"]}" class="text-decoration-none opacity-50 text-dark">{item["text"]}</a></li>')
                else:
                    # Intermediate text items also get the styling
                    new_lis.append(f'<li class="breadcrumb-item text-decoration-none opacity-50 text-dark">{item["text"]}</li>')

        return f'{prefix}\n        ' + '\n        '.join(new_lis) + f'\n    {suffix}'

    new_content = pattern.sub(replace_slot, content)
    if new_content != content:
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(new_content)
        return True
    return False

# List of files
files = [
    "resources/views/notifications/index.blade.php",
    "resources/views/payments/form.blade.php",
    "resources/views/payments/index.blade.php",
    "resources/views/progress-billings/form.blade.php",
    "resources/views/progress-billings/index.blade.php",
    "resources/views/progress-billings/show.blade.php",
    "resources/views/projects/form.blade.php",
    "resources/views/projects/index.blade.php",
    "resources/views/projects/show.blade.php",
    "resources/views/purchase-orders/form.blade.php",
    "resources/views/purchase-orders/index.blade.php",
    "resources/views/purchase-orders/show.blade.php",
    "resources/views/quotes/form.blade.php",
    "resources/views/quotes/index.blade.php",
    "resources/views/quotes/show.blade.php",
    "resources/views/reception-reports/form.blade.php",
    "resources/views/reception-reports/index.blade.php",
    "resources/views/reception-reports/show.blade.php",
    "resources/views/reports/attendance.blade.php",
    "resources/views/reports/expenses.blade.php",
    "resources/views/reports/index.blade.php",
    "resources/views/reports/projects.blade.php",
    "resources/views/settings/expense-categories.blade.php",
    "resources/views/settings/index.blade.php",
    "resources/views/settings/job-types.blade.php",
    "resources/views/settings/material-categories.blade.php",
    "resources/views/settings/regions.blade.php",
    "resources/views/settings/salary-rates.blade.php",
    "resources/views/settings/unit-types.blade.php",
    "resources/views/site-reports/form.blade.php",
    "resources/views/site-reports/index.blade.php",
    "resources/views/site-reports/show.blade.php",
    "resources/views/stock-movements/dashboard.blade.php",
    "resources/views/stock-movements/form.blade.php",
    "resources/views/stock-movements/index.blade.php",
    "resources/views/stock-movements/show.blade.php",
    "resources/views/suppliers/form.blade.php",
    "resources/views/suppliers/index.blade.php",
    "resources/views/suppliers/show.blade.php",
    "resources/views/tasks/form.blade.php",
    "resources/views/tasks/index.blade.php",
    "resources/views/tasks/kanban.blade.php",
    "resources/views/tasks/show.blade.php",
    "resources/views/users/form.blade.php",
    "resources/views/users/index.blade.php",
    "resources/views/warehouses/form.blade.php",
    "resources/views/warehouses/index.blade.php",
    "resources/views/amendments/form.blade.php",
    "resources/views/amendments/index.blade.php",
    "resources/views/amendments/show.blade.php",
    "resources/views/attendances/form.blade.php",
    "resources/views/attendances/index.blade.php",
    "resources/views/attendances/recap.blade.php",
    "resources/views/auth/profile.blade.php",
    "resources/views/clients/form.blade.php",
    "resources/views/clients/index.blade.php",
    "resources/views/clients/show.blade.php",
    "resources/views/dashboard.blade.php",
    "resources/views/documents/form.blade.php",
    "resources/views/documents/index.blade.php",
    "resources/views/dosage/form.blade.php",
    "resources/views/dosage/index.blade.php",
    "resources/views/dosage/show.blade.php",
    "resources/views/employees/form.blade.php",
    "resources/views/employees/index.blade.php",
    "resources/views/employees/show.blade.php",
    "resources/views/equipments/form.blade.php",
    "resources/views/equipments/index.blade.php",
    "resources/views/equipments/show.blade.php",
    "resources/views/expense-categories/index.blade.php",
    "resources/views/expenses/form.blade.php",
    "resources/views/expenses/index.blade.php",
    "resources/views/expenses/show.blade.php",
    "resources/views/invoices/form.blade.php",
    "resources/views/invoices/index.blade.php",
    "resources/views/invoices/show.blade.php",
    "resources/views/materials/form.blade.php",
    "resources/views/materials/index.blade.php",
    "resources/views/materials/show.blade.php",
]

base_path = "/media/rado/New Volume/BuildFlow/"
updated_count = 0
for f in files:
    full_path = os.path.join(base_path, f)
    if os.path.exists(full_path):
        if process_file(full_path):
            updated_count += 1
            print(f"Updated: {f}")

print(f"Total updated: {updated_count}")
