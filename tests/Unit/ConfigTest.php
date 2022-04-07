<?php

namespace GloCurrency\Tingg\Tests\Unit;

use Illuminate\Foundation\Testing\WithFaker;
use GloCurrency\Tingg\Tests\TestCase;
use GloCurrency\Tingg\Config;
use BrokeYourBike\Tingg\Interfaces\ConfigInterface;

class ConfigTest extends TestCase
{
    use WithFaker;

    /** @test */
    public function it_implemets_config_interface(): void
    {
        $this->assertInstanceOf(ConfigInterface::class, new Config());
    }

    /** @test */
    public function it_will_return_empty_string_if_value_not_found()
    {
        $configPrefix = 'services.tingg.api';

        // config is empty
        config([$configPrefix => []]);

        $config = new Config();

        $this->assertSame('', $config->getUrl());
        $this->assertSame('', $config->getCallbackUrl());
        $this->assertSame('', $config->getUsername());
        $this->assertSame('', $config->getPassword());
    }

    /** @test */
    public function it_can_return_values()
    {
        $url = $this->faker->url();
        $callbackUrl = $this->faker->url();
        $username = $this->faker->userName();
        $password = $this->faker->password();

        $configPrefix = 'services.tingg.api';

        config(["{$configPrefix}.url" => $url]);
        config(["{$configPrefix}.callback_url" => $callbackUrl]);
        config(["{$configPrefix}.username" => $username]);
        config(["{$configPrefix}.password" => $password]);

        $config = new Config();

        $this->assertSame($url, $config->getUrl());
        $this->assertSame($callbackUrl, $config->getCallbackUrl());
        $this->assertSame($username, $config->getUsername());
        $this->assertSame($password, $config->getPassword());
    }
}
