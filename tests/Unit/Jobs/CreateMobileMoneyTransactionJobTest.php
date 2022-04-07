<?php

namespace GloCurrency\Tingg\Tests\Unit\Jobs;

use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use GloCurrency\Tingg\Tests\TestCase;
use GloCurrency\Tingg\Jobs\CreateMobileMoneyTransactionJob;
use GloCurrency\MiddlewareBlocks\Enums\QueueTypeEnum as MQueueTypeEnum;
use GloCurrency\MiddlewareBlocks\Contracts\ProcessingItemInterface as MProcessingItemInterface;

class CreateMobileMoneyTransactionJobTest extends TestCase
{
    /** @test */
    public function it_has_tries_defined(): void
    {
        /** @var MProcessingItemInterface */
        $processingItem = $this->getMockBuilder(MProcessingItemInterface::class)->getMock();

        $job = (new CreateMobileMoneyTransactionJob($processingItem));
        $this->assertSame(1, $job->tries);
    }

    /** @test */
    public function it_will_execute_after_commit()
    {
        /** @var MProcessingItemInterface */
        $processingItem = $this->getMockBuilder(MProcessingItemInterface::class)->getMock();

        $job = (new CreateMobileMoneyTransactionJob($processingItem));
        $this->assertTrue($job->afterCommit);
    }

    /** @test */
    public function it_has_dispatch_queue_specified()
    {
        /** @var MProcessingItemInterface */
        $processingItem = $this->getMockBuilder(MProcessingItemInterface::class)->getMock();

        $job = (new CreateMobileMoneyTransactionJob($processingItem));
        $this->assertEquals(MQueueTypeEnum::SERVICES->value, $job->queue);
    }

    /** @test */
    public function it_implements_should_be_unique(): void
    {
        /** @var MProcessingItemInterface */
        $processingItem = $this->getMockBuilder(MProcessingItemInterface::class)->getMock();

        $job = (new CreateMobileMoneyTransactionJob($processingItem));
        $this->assertInstanceOf(ShouldBeUnique::class, $job);
        $this->assertSame($processingItem->getId(), $job->uniqueId());
    }

    /** @test */
    public function it_implements_should_be_encrypted(): void
    {
        /** @var MProcessingItemInterface */
        $processingItem = $this->getMockBuilder(MProcessingItemInterface::class)->getMock();

        $job = (new CreateMobileMoneyTransactionJob($processingItem));
        $this->assertInstanceOf(ShouldBeEncrypted::class, $job);
    }
}
