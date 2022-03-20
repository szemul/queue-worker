<?php
declare(strict_types=1);

namespace Szemul\QueueWorker\Test\Worker;

use JetBrains\PhpStorm\Pure;
use RuntimeException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use Szemul\Queue\Message\MessageInterface;
use Szemul\Queue\Queue\ConsumerInterface;
use Szemul\QueueWorker\EventHandler\WorkerEventHandlerInterface;
use Szemul\QueueWorker\MessageProcessor\MessageProcessorInterface;
use Szemul\QueueWorker\Value\InterruptedValue;
use Szemul\QueueWorker\Worker\QueueWorker;
use PHPUnit\Framework\TestCase;
use Throwable;

/** @covers \Szemul\QueueWorker\Worker\QueueWorker */
class QueueWorkerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected ConsumerInterface|MockInterface         $queue;
    protected MessageProcessorInterface|MockInterface $processor;
    protected QueueWorker                             $sut;
    protected InterruptedValue                        $interruptedValue;

    protected function setUp(): void
    {
        parent::setUp();

        $this->queue     = Mockery::mock(ConsumerInterface::class); // @phpstan-ignore-line
        $this->processor = Mockery::mock(MessageProcessorInterface::class); // @phpstan-ignore-line

        $this->sut = $this->getSut();

        $this->interruptedValue = new InterruptedValue();
    }

    public function testGetQueue(): void
    {
        $this->assertSame($this->queue, $this->sut->getQueue());
    }

    public function testGetProcessor(): void
    {
        $this->assertSame($this->processor, $this->sut->getProcessor());
    }

    public function testGetEventHandler(): void
    {
        $this->assertNull($this->sut->getEventHandler());
    }

    public function testSetEventHandler(): void
    {
        $eventHandler = $this->getEventHandler();
        $this->assertSame($this->sut, $this->sut->setEventHandler($eventHandler));
        $this->assertSame($eventHandler, $this->sut->getEventHandler());
    }

    public function testWorkWithNoMessage(): void
    {
        $this->expectMessageRetrieved(null);

        $this->sut->setEventHandler($this->getEventHandler())->work($this->interruptedValue);
    }

    public function testWorkWithInterrupted(): void
    {
        $message = $this->getMessage();

        $this->expectMessageRetrieved($message)
            ->expectMessageAborted($message);

        $this->sut->setEventHandler($this->getEventHandler())->work($this->interruptedValue->setInterrupted(true));
    }

    public function testWorkWithSuccess(): void
    {
        $eventHandler = $this->getEventHandler();
        $message      = $this->getMessage();

        $this->expectMessageRetrieved($message)
            ->expectMessageProcessed($message)
            ->expectMessageFinished($message)
            ->expectMessageReceivedEvent($eventHandler, $message)
            ->expectMessageProcessedEvent($eventHandler, $message)
            ->expectWorkerFinallyEvent($eventHandler);

        $this->sut->setEventHandler($eventHandler)->work($this->interruptedValue);
    }

    public function testWorkWithSuccessWithoutEventHandler(): void
    {
        $message      = $this->getMessage();

        $this->expectMessageRetrieved($message)
            ->expectMessageProcessed($message)
            ->expectMessageFinished($message);

        $this->sut->work($this->interruptedValue);
    }

    public function testWorkWithException(): void
    {
        $eventHandler = $this->getEventHandler();
        $message      = $this->getMessage();
        $exception    = new RuntimeException('Test');
        $this->expectExceptionObject($exception);

        $this->expectMessageRetrieved($message)
            ->expectMessageProcessedWithException($message, $exception)
            ->expectMessageReceivedEvent($eventHandler, $message)
            ->expectWorkerExceptionEvent($eventHandler, $exception)
            ->expectWorkerFinallyEvent($eventHandler);

        $this->sut->setEventHandler($eventHandler)->work($this->interruptedValue);
    }

    protected function expectMessageRetrieved(?MessageInterface $message): static
    {
        $this->queue->shouldReceive('getMessage')->once()->withNoArgs()->andReturn($message); // @phpstan-ignore-line

        return $this;
    }

    protected function expectMessageProcessed(MessageInterface $message): static
    {
        $this->processor->shouldReceive('process')->once()->with($message); // @phpstan-ignore-line

        return $this;
    }

    protected function expectMessageProcessedWithException(MessageInterface $message, Throwable $exception): static
    {
        $this->processor->shouldReceive('process')->once()->with($message)->andThrow($exception); // @phpstan-ignore-line

        return $this;
    }

    protected function expectMessageAborted(MessageInterface $message): static
    {
        $this->queue->shouldReceive('abortMessage')->once()->with($message); // @phpstan-ignore-line

        return $this;
    }

    protected function expectMessageFinished(MessageInterface $message): static
    {
        $this->queue->shouldReceive('finishMessage')->once()->with($message); // @phpstan-ignore-line

        return $this;
    }

    protected function expectMessageReceivedEvent(MockInterface $eventHandler, MessageInterface $message): static
    {
        $eventHandler->shouldReceive('handleMessageReceived')->once()->with($message); // @phpstan-ignore-line

        return $this;
    }

    protected function expectMessageProcessedEvent(MockInterface $eventHandler, MessageInterface $message): static
    {
        $eventHandler->shouldReceive('handleMessageProcessed')->once()->with($message); // @phpstan-ignore-line

        return $this;
    }

    protected function expectWorkerFinallyEvent(MockInterface $eventHandler): static
    {
        $eventHandler->shouldReceive('handleWorkerFinally')->once()->withNoArgs(); // @phpstan-ignore-line

        return $this;
    }

    protected function expectWorkerExceptionEvent(MockInterface $eventHandler, Throwable $exception): static
    {
        $eventHandler->shouldReceive('handleWorkerException')->once()->with($exception); // @phpstan-ignore-line

        return $this;
    }

    protected function getEventHandler(): WorkerEventHandlerInterface|MockInterface
    {
        return Mockery::mock(WorkerEventHandlerInterface::class); // @phpstan-ignore-line
    }

    protected function getMessage(): MessageInterface
    {
        return Mockery::mock(MessageInterface::class); // @phpstan-ignore-line
    }

    #[Pure]
    protected function getSut(): QueueWorker
    {
        return new QueueWorker($this->queue, $this->processor);
    }
}
