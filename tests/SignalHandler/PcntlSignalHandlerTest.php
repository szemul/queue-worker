<?php
declare(strict_types=1);

namespace Szemul\QueueWorker\Test\SignalHandler;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Szemul\QueueWorker\SignalHandler\PcntlSignalHandler;
use PHPUnit\Framework\TestCase;
use Szemul\QueueWorker\SignalHandler\SignalReceiverInterface;

class PcntlSignalHandlerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testFunctionality(): void
    {
        $sut      = new PcntlSignalHandler();
        $receiver = \Mockery::mock(SignalReceiverInterface::class);

        $sut->handleSignal(SIGINT);

        $receiver->shouldReceive('receiveSignal')->once()->with(SIGINT);

        $this->assertSame($sut, $sut->setReceiver($receiver));
        $sut->handleSignal(SIGINT);
    }
}
