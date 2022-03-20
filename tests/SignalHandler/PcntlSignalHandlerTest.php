<?php
declare(strict_types=1);

namespace Szemul\QueueWorker\Test\SignalHandler;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use Szemul\QueueWorker\SignalHandler\PcntlSignalHandler;
use PHPUnit\Framework\TestCase;
use Szemul\QueueWorker\SignalHandler\SignalReceiverInterface;

class PcntlSignalHandlerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testFunctionality(): void
    {
        /** @var SignalReceiverInterface|MockInterface $receiver */
        $receiver = \Mockery::mock(SignalReceiverInterface::class);
        $sut      = new PcntlSignalHandler();

        $sut->handleSignal(SIGINT);

        $receiver->shouldReceive('receiveSignal')->once()->with(SIGINT); // @phpstan-ignore-line

        $this->assertSame($sut, $sut->setReceiver($receiver));
        $sut->handleSignal(SIGINT);
    }
}
