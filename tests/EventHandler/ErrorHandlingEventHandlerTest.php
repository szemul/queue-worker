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

        $this->errorHandlerRegistry = Mockery::mock(ErrorHandlerRegistry::class); // @phpstan-ignore-line

        $this->sut = new ErrorHandlingEventHandler($this->errorHandlerRegistry); // @phpstan-ignore-line
    }

    public function testHandleBeforeLoop(): void
    {
        // Noop test, this method should not do anything
        $this->sut->handleBeforeLoop();
        $this->assertTrue(true);
    }

    public function testHandleCommandException(): void
    {
        $exception = $this->expectExceptionHandled();
        $this->sut->handleCommandException($exception);
    }

    public function testHandleCommandFinally(): void
    {
        // Noop test, this method should not do anything
        $this->sut->handleCommandFinally();
        $this->assertTrue(true);
    }

    public function testHandleCommandFinished(): void
    {
        // Noop test, this method should not do anything
        $this->sut->handleCommandFinished();
        $this->assertTrue(true);
    }

    public function testHandleCommandInterrupted(): void
    {
        // Noop test, this method should not do anything
        $this->sut->handleCommandInterrupted();
        $this->assertTrue(true);
    }

    public function testHandleInterrupt(): void
    {
        // Noop test, this method should not do anything
        $this->sut->handleInterrupt();
        $this->assertTrue(true);
    }

    public function testHandleIterationComplete(): void
    {
        // Noop test, this method should not do anything
        $this->sut->handleIterationComplete();
        $this->assertTrue(true);
    }

    public function testHandleIterationStart(): void
    {
        // Noop test, this method should not do anything
        $this->sut->handleIterationStart();
        $this->assertTrue(true);
    }

    public function testHandleMessageProcessed(): void
    {
        // Noop test, this method should not do anything
        $this->sut->handleMessageProcessed(Mockery::mock(MessageInterface::class)); // @phpstan-ignore-line
        $this->assertTrue(true);
    }

    public function testHandleMessageReceived(): void
    {
        // Noop test, this method should not do anything
        $this->sut->handleMessageReceived(Mockery::mock(MessageInterface::class)); // @phpstan-ignore-line
        $this->assertTrue(true);
    }

    public function testHandleSignalReceived(): void
    {
        // Noop test, this method should not do anything
        $this->sut->handleSignalReceived(SIGINT);
        $this->assertTrue(true);
    }

    public function testHandleWorkerException(): void
    {
        $exception = $this->expectExceptionHandled();
        $this->sut->handleWorkerException($exception);
    }

    public function testHandleWorkerFinally(): void
    {
        // Noop test, this method should not do anything
        $this->sut->handleWorkerFinally();
        $this->assertTrue(true);
    }

    public function expectExceptionHandled(): RuntimeException
    {
        $exception = new RuntimeException('test');

        /** @phpstan-ignore-next-line */
        $this->errorHandlerRegistry->shouldReceive('handleException')->once()->with($exception);

        return $exception;
    }
}
