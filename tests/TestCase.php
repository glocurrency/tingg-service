<?php

namespace GloCurrency\Tingg\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use GloCurrency\Tingg\TinggServiceProvider;
use GloCurrency\Tingg\Tingg;
use GloCurrency\Tingg\Tests\Fixtures\TransactionFixture;
use GloCurrency\Tingg\Tests\Fixtures\ProcessingItemFixture;
use GloCurrency\Tingg\Tests\Fixtures\MobileMoneyProviderFixture;

abstract class TestCase extends OrchestraTestCase
{
    protected function getEnvironmentSetUp($app)
    {
        Tingg::useTransactionModel(TransactionFixture::class);
        Tingg::useProcessingItemModel(ProcessingItemFixture::class);
        Tingg::useMobileMoneyProviderModel(MobileMoneyProviderFixture::class);
    }

    protected function getPackageProviders($app)
    {
        return [TinggServiceProvider::class];
    }

    /**
     * Create the HTTP mock for API.
     *
     * @return array<\GuzzleHttp\Handler\MockHandler|\GuzzleHttp\HandlerStack> [$httpMock, $handlerStack]
     */
    protected function mockApiFor(string $class): array
    {
        $httpMock = new \GuzzleHttp\Handler\MockHandler();
        $handlerStack = \GuzzleHttp\HandlerStack::create($httpMock);

        $this->app->when($class)
            ->needs(\GuzzleHttp\ClientInterface::class)
            ->give(function () use ($handlerStack) {
                return new \GuzzleHttp\Client(['handler' => $handlerStack]);
            });

        return [$httpMock, $handlerStack];
    }
}
