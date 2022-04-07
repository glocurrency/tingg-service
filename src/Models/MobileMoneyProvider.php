<?php

namespace GloCurrency\Tingg\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use GloCurrency\Tingg\Database\Factories\MobileMoneyProviderFactory;
use BrokeYourBike\BaseModels\BaseUuid;

/**
 * GloCurrency\Tingg\Models\MobileMoneyProvider
 *
 * @property string $id
 * @property string $mobile_money_provider_id
 * @property string $code
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class MobileMoneyProvider extends BaseUuid
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'tingg_mobile_money_providers';

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return MobileMoneyProviderFactory::new();
    }
}
