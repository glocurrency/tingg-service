<?php

namespace GloCurrency\Tingg;

use BrokeYourBike\Tingg\Interfaces\ConfigInterface;

final class Config implements ConfigInterface
{
    private function getAppConfigValue(string $key): string
    {
        $value = \Illuminate\Support\Facades\Config::get("services.tingg.api.$key");

        return is_string($value) ? $value : '';
    }

    public function getUrl(): string
    {
        return $this->getAppConfigValue('url');
    }

    public function getCallbackUrl(): string
    {
        return $this->getAppConfigValue('callback_url');
    }

    public function getUsername(): string
    {
        return $this->getAppConfigValue('username');
    }

    public function getPassword(): string
    {
        return $this->getAppConfigValue('password');
    }
}
