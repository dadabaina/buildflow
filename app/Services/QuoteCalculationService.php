<?php

namespace App\Services;

use App\Models\DosageModel;
use App\Models\DosageItem;
use App\Models\Material;
use App\Models\SalaryRate;
use App\Models\Quote;
use App\Models\QuoteItem;

class QuoteCalculationService
{
    /**
     * Calcule le DBE (Déboursé Estimatif) pour une quantité d'ouvrage
     * à partir d'un modèle de dosage.
     *
     * @param  int        $dosageModelId  ID du modèle de dosage
     * @param  float      $quantity       Quantité d'ouvrage (ex: 10 m³)
     * @param  int|null   $regionId       Région pour la recherche de prix (optionnel)
     * @return array {
     *   dbe_materials: float,
     *   dbe_labor: float,
     *   dbe_equipment: float,
     *   dbe_subcontract: float,
     *   dbe_total: float,
     *   breakdown: array[],      // détail par ligne
     *   missing_prices: array[], // matériaux sans prix
     * }
     */
    public function calculateFromDosage(int $dosageModelId, float $quantity, ?int $regionId = null): array
    {
        $model = DosageModel::with(['items.material.prices', 'items.jobType'])->findOrFail($dosageModelId);

        // Facteur de conversion : nombre d'applications du modèle nécessaires
        $factor = $quantity / (float) $model->output_quantity;

        $totals = [
            'dbe_materials'   => 0.0,
            'dbe_labor'       => 0.0,
            'dbe_equipment'   => 0.0,
            'dbe_subcontract' => 0.0,
            'dbe_total'       => 0.0,
        ];

        $breakdown    = [];
        $missingPrices = [];

        foreach ($model->items as $item) {
            // Quantité de cette ressource pour la quantité totale d'ouvrage
            $effectiveQtyPerUnit = $item->effectiveQuantityPerUnit();
            $totalQty            = $effectiveQtyPerUnit * $factor;

            // Récupération du prix unitaire — ordre de priorité :
            // 1. Material lié (matériaux, ou tout type avec un matériau)
            // 2. Grille salariale (labor avec job_type_id)
            // 3. Prix direct sur la ligne (equipment / subcontract)
            $unitPrice = null;
            if ($item->material_id && $item->material) {
                $unitPrice = $item->material->currentPrice($regionId);
            } elseif ($item->item_type === 'labor' && $item->job_type_id) {
                $unitPrice = SalaryRate::currentRate(
                    $model->company_id,
                    $item->job_type_id,
                    $regionId
                );
            } elseif ($item->unit_price !== null) {
                $unitPrice = (float) $item->unit_price;
            }

            $lineCost = $unitPrice !== null ? round($totalQty * $unitPrice, 2) : null;

            if ($unitPrice === null) {
                $missingPrices[] = $item->display_name;
                $lineCost = 0.0;
            }

            // Ventilation par type
            $typeKey = 'dbe_' . $item->item_type;   // dbe_material → dbe_materials
            if ($item->item_type === 'material') {
                $totals['dbe_materials'] += $lineCost;
            } elseif ($item->item_type === 'labor') {
                $totals['dbe_labor'] += $lineCost;
            } elseif ($item->item_type === 'equipment') {
                $totals['dbe_equipment'] += $lineCost;
            } elseif ($item->item_type === 'subcontract') {
                $totals['dbe_subcontract'] += $lineCost;
            }

            $breakdown[] = [
                'dosage_item_id'    => $item->id,
                'item_type'         => $item->item_type,
                'description'       => $item->display_name,
                'unit'              => $item->display_unit,
                'quantity_per_unit' => (float) $item->quantity_per_unit,
                'waste_rate'        => (float) $item->waste_rate,
                'effective_qty_pu'  => $effectiveQtyPerUnit,
                'total_quantity'    => round($totalQty, 4),
                'unit_price'        => $unitPrice,
                'line_cost'         => $lineCost,
                'has_price'         => $unitPrice !== null,
            ];
        }

        $totals['dbe_total'] = array_sum([
            $totals['dbe_materials'],
            $totals['dbe_labor'],
            $totals['dbe_equipment'],
            $totals['dbe_subcontract'],
        ]);

        return array_merge($totals, [
            'breakdown'      => $breakdown,
            'missing_prices' => $missingPrices,
            'dosage_model'   => [
                'id'              => $model->id,
                'name'            => $model->name,
                'output_unit'     => $model->output_unit,
                'output_quantity' => (float) $model->output_quantity,
            ],
        ]);
    }

    /**
     * Calcule le prix de vente unitaire à partir du DBE et des coefficients.
     *
     * Prix = DBE_unitaire × (1 + fg_rate/100) × (1 + margin_rate/100) × (1 + alea_rate/100)
     *
     * @param  float $dbeTotal    DBE total pour la quantité saisie
     * @param  float $quantity    Quantité de l'ouvrage
     * @param  float $fgRate      Frais généraux %
     * @param  float $marginRate  Marge %
     * @param  float $aleaRate    Aléas %
     * @return array { dbe_unit: float, unit_price: float, coefficient: float }
     */
    public function applyCoefficients(
        float $dbeTotal,
        float $quantity,
        float $fgRate = 0,
        float $marginRate = 0,
        float $aleaRate = 0
    ): array {
        $dbeUnit    = $quantity > 0 ? $dbeTotal / $quantity : 0;
        $coefficient = (1 + $fgRate / 100)
                     * (1 + $marginRate / 100)
                     * (1 + $aleaRate / 100);
        $unitPrice  = round($dbeUnit * $coefficient, 2);

        return [
            'dbe_unit'    => round($dbeUnit, 2),
            'coefficient' => round($coefficient, 4),
            'unit_price'  => $unitPrice,
            'margin_info' => [
                'fg_amount'     => round($dbeUnit * $fgRate / 100, 2),
                'margin_amount' => round($dbeUnit * (1 + $fgRate / 100) * $marginRate / 100, 2),
                'alea_amount'   => round($dbeUnit * (1 + $fgRate / 100) * (1 + $marginRate / 100) * $aleaRate / 100, 2),
            ],
        ];
    }

    /**
     * Calcule le DBE complet et applique les coefficients, puis met à jour la ligne de devis.
     *
     * @param  QuoteItem  $item
     * @param  int        $dosageModelId
     * @param  float      $quantity
     * @param  float      $fgRate
     * @param  float      $marginRate
     * @param  float      $aleaRate
     * @param  int|null   $regionId
     * @param  bool       $save          Enregistrer l'item immédiatement
     * @return QuoteItem
     */
    public function applyDosageToItem(
        QuoteItem $item,
        int       $dosageModelId,
        float     $quantity,
        float     $fgRate    = 0,
        float     $marginRate = 0,
        float     $aleaRate  = 0,
        ?int      $regionId  = null,
        bool      $save      = true
    ): QuoteItem {
        $dbeResult = $this->calculateFromDosage($dosageModelId, $quantity, $regionId);
        $priceResult = $this->applyCoefficients(
            $dbeResult['dbe_total'],
            $quantity,
            $fgRate,
            $marginRate,
            $aleaRate
        );

        $item->dosage_model_id = $dosageModelId;
        $item->quantity        = $quantity;
        $item->dbe_materials   = $dbeResult['dbe_materials'];
        $item->dbe_labor       = $dbeResult['dbe_labor'];
        $item->dbe_equipment   = $dbeResult['dbe_equipment'];
        $item->dbe_subcontract = $dbeResult['dbe_subcontract'];
        $item->dbe_total       = $dbeResult['dbe_total'];
        $item->fg_rate         = $fgRate;
        $item->margin_rate     = $marginRate;
        $item->alea_rate       = $aleaRate;
        $item->unit_price      = $priceResult['unit_price'];
        $item->total_ht        = round($quantity * $priceResult['unit_price'], 2);
        $item->price_override  = false;

        if ($save) {
            $item->save();
        }

        return $item;
    }

    /**
     * Recalcule toutes les lignes d'un devis qui utilisent un dosage,
     * puis met à jour les totaux du devis.
     */
    public function recalculateQuote(Quote $quote, ?int $regionId = null): void
    {
        foreach ($quote->items as $item) {
            if ($item->dosage_model_id && !$item->price_override) {
                $this->applyDosageToItem(
                    $item,
                    $item->dosage_model_id,
                    (float) $item->quantity,
                    (float) $item->fg_rate,
                    (float) $item->margin_rate,
                    (float) $item->alea_rate,
                    $regionId,
                    true
                );
            }
        }

        $quote->recalculateTotals();
    }
}
