<?php

namespace App\Traits;

use App\Scopes\CompanyScope;

trait BelongsToCompany
{
    public static function bootBelongsToCompany(): void
    {
        static::addGlobalScope(new CompanyScope());

        static::creating(function ($model) {
            if (empty($model->company_id)) {
                $companyId = session('company_id') ?? auth()->user()?->company_id;
                if ($companyId) {
                    $model->company_id = $companyId;
                }
            }
        });
    }
}
