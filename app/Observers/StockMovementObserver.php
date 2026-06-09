<?php

namespace App\Observers;

use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class StockMovementObserver
{
    /**
     * Handle the StockMovement "created" event.
     */
    public function created(StockMovement $stockMovement): void
    {
        $this->updateMaterialStock($stockMovement);
    }

    /**
     * Handle the StockMovement "deleted" event.
     */
    public function deleted(StockMovement $stockMovement): void
    {
        $this->updateMaterialStock($stockMovement);
    }

    /**
     * Recalculate and update the cached stock quantity in Material model.
     */
    protected function updateMaterialStock(StockMovement $stockMovement): void
    {
        if (!$stockMovement->material_id) {
            return;
        }

        $material = $stockMovement->material;
        if (!$material) {
            return;
        }

        // Calculate total stock for this material across all warehouses in the company
        $balance = DB::table('stock_movements')
            ->where('material_id', $material->id)
            ->where('company_id', $material->company_id)
            ->selectRaw('SUM(CASE WHEN type="entree" THEN quantity WHEN type="sortie" THEN -quantity ELSE 0 END) as total')
            ->value('total') ?? 0;

        $material->update(['stock_quantity' => $balance]);
    }
}
