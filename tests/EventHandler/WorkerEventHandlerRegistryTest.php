<?php
declare(strict_types=1);

namespace Szemul\QueueWorker\Test\EventHandler;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use RuntimeException;
use Szemul\Queue\Message\MessageInterface;
use Szemul\QueueWorker\EventHandler\WorkerEventHandlerInterface;
use Szemul\QueueWorker\EventHandler\WorkerEventHandlerRegistry;
use PHPUnit\Framework\TestCase;

class WorkerEventHandlerRegistryTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testAdd(): void
    {
        $sut = $this->getSut();
        /** @var WorkerEventHandlerInterface $handler */
        $handler = Mockery::mock(WorkerEventHandlerInterface::class);

        $this->assertEmpty($sut->getHandlers());
        $result = $sut->add($handler);
        $this->assertSame($sut, $result);
        $this->assertSame([$handler], $sut->getHandlers());

    }

    public function testHandleMessageProcessed(): void
    {
        $message = $this->getMessage();
        $this->getSut(...$this->getHandlers(2, 'handleMessageProcessed', $message))->handleMessageProcessed($message);
    }

    public function testHandleMessageReceived(): void
    {
        $message = $this->getMessage();
        $this->getSut(...$this->getHandlers(2, 'handleMessageReceived', $message))->handleMessageReceived($message);
    }

    public function testHandleWorkerException(): void
    {
        $exception = new RuntimeException('test');
        $this->getSut(...$this->getHandlers(2, 'handleWorkerException', $exception))->handleWorkerException($exception);

    }

    public function testHandleWorkerFinally(): void
    {
        $this->getSut(...$this->getHandlers(2, 'handleWorkerFinally'))->handleWorkerFinally();
    }

    private function getSut(WorkerEventHandlerInterface ...$handlers): WorkerEventHandlerRegistry
    {
        return new WorkerEventHandlerRegistry(...$handlers);
    }

    /** @return array<int, WorkerEventHandlerInterface|MockInterface> */
    private function getHandlers(int $count, string $testedMethod, mixed ...$args): array
    {
        $handlers = [];

        for ($i = 0; $i < $count; $i++) {
            /** @var WorkerEventHandlerInterface|MockInterface $handler */
            $handler = Mockery::mock(WorkerEventHandlerInterface::class);

            $handler->shouldReceive($testedMethod)->once()->with(...$args); // @phpstan-ignore-line

            $handlers[] = $handler;
        }

        return $handlers;
    }

    private function getMessage(): MessageInterface
    {
        return Mockery::mock(MessageInterface::class); //@phpstan-ignore-line
    }
}
