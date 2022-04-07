<?php

namespace GloCurrency\Tingg\Tests\Fixtures;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use GloCurrency\Tingg\Tests\Database\Factories\MobileMoneyProviderFixtureFactory;
use GloCurrency\MiddlewareBlocks\Contracts\MobileMoneyProviderInterface as MMobileMoneyProviderInterface;
use BrokeYourBike\BaseModels\BaseUuid;

class MobileMoneyProviderFixture extends BaseUuid implements MMobileMoneyProviderInterface
{
    use HasFactory;

    protected $table = 'mobile_money_providers';

    public function getId(): string
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getCountryCode(): string
    {
        return $this->country_code;
    }

    public function getName(): string
    {
        return $this->code;
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return MobileMoneyProviderFixtureFactory::new();
    }
}
