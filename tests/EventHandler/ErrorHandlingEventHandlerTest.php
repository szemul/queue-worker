<?php
declare(strict_types=1);

namespace Szemul\QueueWorker\Test\EventHandler;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use RuntimeException;
use Szemul\ErrorHandler\ErrorHandlerRegistry;
use Szemul\Queue\Message\MessageInterface;
use Szemul\QueueWorker\EventHandler\ErrorHandlingEventHandler;
use PHPUnit\Framework\TestCase;

class ErrorHandlingEventHandlerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private ErrorHandlerRegistry|MockInterface $errorHandlerRegistry;
    private ErrorHandlingEventHandler          $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->errorHandlerRegistry = Mockery::mock(ErrorHandlerRegistry::class);

        $this->sut = new ErrorHandlingEventHandler($this->errorHandlerRegistry); // @phpstan-ignore-line
    }

    public function testHandleBeforeLoop(): void
    {
        // Noop test, this method should not do anything
        $this->expectNotToPerformAssertions();
        $this->sut->handleBeforeLoop();
    }

    public function testHandleCommandException(): void
    {
        $exception = $this->expectExceptionHandled();
        $this->sut->handleCommandException($exception);
    }

    public function testHandleCommandFinally(): void
    {
        // Noop test, this method should not do anything
        $this->expectNotToPerformAssertions();
        $this->sut->handleCommandFinally();
    }

    public function testHandleCommandFinished(): void
    {
        // Noop test, this method should not do anything
        $this->expectNotToPerformAssertions();
        $this->sut->handleCommandFinished();
    }

    public function testHandleCommandInterrupted(): void
    {
        // Noop test, this method should not do anything
        $this->expectNotToPerformAssertions();
        $this->sut->handleCommandInterrupted();
    }

    public function testHandleInterrupt(): void
    {
        // Noop test, this method should not do anything
        $this->expectNotToPerformAssertions();
        $this->sut->handleInterrupt();
    }

    public function testHandleIterationComplete(): void
    {
        // Noop test, this method should not do anything
        $this->expectNotToPerformAssertions();
        $this->sut->handleIterationComplete();
    }

    public function testHandleIterationStart(): void
    {
        // Noop test, this method should not do anything
        $this->expectNotToPerformAssertions();
        $this->sut->handleIterationStart();
    }

    public function testHandleMessageProcessed(): void
    {
        // Noop test, this method should not do anything
        $this->expectNotToPerformAssertions();
        $this->sut->handleMessageProcessed(Mockery::mock(MessageInterface::class)); // @phpstan-ignore-line
    }

    public function testHandleMessageReceived(): void
    {
        // Noop test, this method should not do anything
        $this->expectNotToPerformAssertions();
        $this->sut->handleMessageReceived(Mockery::mock(MessageInterface::class)); // @phpstan-ignore-line
    }

    public function testHandleSignalReceived(): void
    {
        // Noop test, this method should not do anything
        $this->expectNotToPerformAssertions();
        $this->sut->handleSignalReceived(SIGINT);
    }

    public function testHandleWorkerException(): void
    {
        $exception = $this->expectExceptionHandled();
        $this->sut->handleWorkerException($exception);
    }

    public function testHandleWorkerFinally(): void
    {
        // Noop test, this method should not do anything
        $this->expectNotToPerformAssertions();
        $this->sut->handleWorkerFinally();
    }

    public function expectExceptionHandled(): RuntimeException
    {
        $exception = new RuntimeException('test');

        /** @phpstan-ignore-next-line */
        $this->errorHandlerRegistry->shouldReceive('handleException')->once()->with($exception);

        return $exception;
    }
}
