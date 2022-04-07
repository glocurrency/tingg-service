<?php

namespace GloCurrency\Tingg\Tests\Unit\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use GloCurrency\Tingg\Tests\TestCase;
use GloCurrency\Tingg\Models\MobileMoneyProvider;
use BrokeYourBike\BaseModels\BaseUuid;

class MobileMoneyProviderTest extends TestCase
{
    /** @test */
    public function it_extends_base_model(): void
    {
        $parent = get_parent_class(MobileMoneyProvider::class);

        $this->assertSame(BaseUuid::class, $parent);
    }

    /** @test */
    public function it_uses_soft_deletes(): void
    {
        $usedTraits = class_uses(MobileMoneyProvider::class);

        $this->assertArrayHasKey(SoftDeletes::class, $usedTraits);
    }
}
