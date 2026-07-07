<?php

namespace App\Services;

use App\Models\ExpenseTemplate;
use App\Models\SalaryRate;

class ExpenseTemplateService
{
    /**
     * Calcule le coût réel (Déboursé) pour une quantité d'ouvrage
     * à partir d'un modèle de dépense. Contrairement au Dosage (DBE),
     * ce calcul ne reçoit aucun coefficient de marge : il représente
     * un coût réel, pas un prix de vente.
     *
     * @return array {
     *   total: float,
     *   breakdown: array[],      // détail par ligne, prêt à devenir des Expense
     *   missing_prices: array[], // ressources sans prix connu
     * }
     */
    public function calculate(int $templateId, float $quantity, ?int $regionId = null): array
    {
        $template = ExpenseTemplate::with(['items.material.prices', 'items.jobType', 'items.expenseCategory'])
            ->findOrFail($templateId);

        // Facteur de conversion : nombre d'applications du modèle nécessaires
        $factor = $quantity / (float) $template->output_quantity;

        $total = 0.0;
        $breakdown = [];
        $missingPrices = [];

        foreach ($template->items as $item) {
            $effectiveQtyPerUnit = $item->effectiveQuantityPerUnit();
            $totalQty            = round($effectiveQtyPerUnit * $factor, 4);

            // Récupération du prix unitaire — même ordre de priorité que pour le Dosage :
            // 1. Matériau lié (catalogue Materials, prix régional)
            // 2. Grille salariale (labor avec job_type_id)
            // Si aucune des deux n'a de prix connu (ex: grille salariale non renseignée),
            // on retombe sur le prix saisi manuellement sur la ligne — permet de corriger
            // un prix manquant sans devoir retirer le lien matériau/métier.
            $unitPrice = null;
            if ($item->material_id && $item->material) {
                $unitPrice = $item->material->currentPrice($regionId);
            } elseif ($item->item_type === 'labor' && $item->job_type_id) {
                $unitPrice = SalaryRate::currentRate(
                    $template->company_id,
                    $item->job_type_id,
                    $regionId
                );
            }

            if ($unitPrice === null && $item->unit_price !== null) {
                $unitPrice = (float) $item->unit_price;
            }

            $lineCost = $unitPrice !== null ? round($totalQty * $unitPrice, 2) : 0.0;

            if ($unitPrice === null) {
                $missingPrices[] = $item->display_name;
            }

            $total += $lineCost;

            $breakdown[] = [
                'expense_template_item_id' => $item->id,
                'item_type'                => $item->item_type,
                'description'              => $item->display_name,
                'unit'                     => $item->display_unit,
                'expense_category_id'      => $item->expense_category_id,
                'quantity'                 => $totalQty,
                'unit_price'               => $unitPrice ?? 0.0,
                'line_cost'                => $lineCost,
                'has_price'                => $unitPrice !== null,
            ];
        }

        return [
            'total'          => round($total, 2),
            'breakdown'      => $breakdown,
            'missing_prices' => $missingPrices,
        ];
    }
}
