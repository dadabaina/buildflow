<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $teamKey = $columnNames['team_foreign_key'] ?? 'company_id';

        // 1. Roles Table
        if (!Schema::hasColumn($tableNames['roles'], $teamKey)) {
            Schema::table($tableNames['roles'], function (Blueprint $table) use ($teamKey) {
                $table->unsignedBigInteger($teamKey)->nullable()->after('id');
                $table->index($teamKey);
                
                // Drop old unique and add new one with company_id
                $table->dropUnique(['name', 'guard_name']);
                $table->unique([$teamKey, 'name', 'guard_name']);
            });
        }

        // 2. model_has_permissions
        Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($teamKey, $columnNames, $tableNames) {
            if (!Schema::hasColumn($tableNames['model_has_permissions'], $teamKey)) {
                $table->unsignedBigInteger($teamKey)->after(config('permission.column_names.permission_pivot_key', 'permission_id'));
                $table->index($teamKey);
            }
            
            // Need to drop FK to drop Primary
            $table->dropForeign('model_has_permissions_permission_id_foreign');
            
            // Check if primary is still old one
            $table->dropPrimary(['permission_id', $columnNames['model_morph_key'], 'model_type']);
            
            $table->primary([$teamKey, 'permission_id', $columnNames['model_morph_key'], 'model_type'], 'model_has_permissions_permission_model_type_primary');
            
            // Restore FK
            $table->foreign('permission_id')
                ->references('id')
                ->on($tableNames['permissions'])
                ->onDelete('cascade');
        });

        // 3. model_has_roles
        Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($teamKey, $columnNames, $tableNames) {
            if (!Schema::hasColumn($tableNames['model_has_roles'], $teamKey)) {
                $table->unsignedBigInteger($teamKey)->after(config('permission.column_names.role_pivot_key', 'role_id'));
                $table->index($teamKey);
            }

            // Need to drop FK to drop Primary
            $table->dropForeign('model_has_roles_role_id_foreign');

            $table->dropPrimary(['role_id', $columnNames['model_morph_key'], 'model_type']);
            
            $table->primary([$teamKey, 'role_id', $columnNames['model_morph_key'], 'model_type'], 'model_has_roles_role_model_type_primary');

            // Restore FK
            $table->foreign('role_id')
                ->references('id')
                ->on($tableNames['roles'])
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $teamKey = $columnNames['team_foreign_key'] ?? 'company_id';

        Schema::table($tableNames['roles'], function (Blueprint $table) use ($teamKey) {
            $table->dropUnique([$teamKey, 'name', 'guard_name']);
            $table->dropColumn($teamKey);
            $table->unique(['name', 'guard_name']);
        });

        Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($teamKey, $columnNames) {
            $table->dropPrimary('model_has_permissions_permission_model_type_primary');
            $table->dropColumn($teamKey);
            $table->primary(['permission_id', $columnNames['model_morph_key'], 'model_type'], 'model_has_permissions_permission_model_type_primary');
        });

        Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($teamKey, $columnNames) {
            $table->dropPrimary('model_has_roles_role_model_type_primary');
            $table->dropColumn($teamKey);
            $table->primary(['role_id', $columnNames['model_morph_key'], 'model_type'], 'model_has_roles_role_model_type_primary');
        });
    }
};
