<?php
declare(strict_types=1);

namespace Szemul\QueueWorker\Test\EventHandler;

use Mockery;
use Psr\Log\LogLevel;
use RuntimeException;
use Szemul\Queue\Message\MessageInterface;
use Szemul\QueueWorker\EventHandler\LoggingEventHandler;
use PHPUnit\Framework\TestCase;
use WMDE\PsrLogTestDoubles\LegacyLoggerSpy;
use WMDE\PsrLogTestDoubles\LogCall;

class LoggingEventHandlerTest extends TestCase
{
    private LegacyLoggerSpy     $logger;
    private LoggingEventHandler $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logger = new LegacyLoggerSpy();
        $this->sut    = new LoggingEventHandler($this->logger);
    }

    public function testHandleBeforeLoop(): void
    {
        $this->sut->handleBeforeLoop();
        $this->assertCount(1, $this->logger->getLogCalls());
        $this->assertEquals(new LogCall(LogLevel::INFO, 'Starting worker'), $this->logger->getFirstLogCall());
    }

    public function testHandleCommandException(): void
    {
        $exception = $this->getException();
        $this->sut->handleCommandException($exception);
        $this->assertCount(0, $this->logger->getLogCalls());
    }

    public function testHandleCommandFinally(): void
    {
        $this->sut->handleCommandFinally();
        $this->assertCount(0, $this->logger->getLogCalls());
    }

    public function testHandleCommandFinished(): void
    {
        $this->sut->handleCommandFinished();
        $this->assertCount(1, $this->logger->getLogCalls());
        $this->assertEquals(new LogCall(LogLevel::INFO, 'Worker shutting down'), $this->logger->getFirstLogCall());
    }

    public function testHandleCommandInterrupted(): void
    {
        $this->sut->handleCommandInterrupted();
        $this->assertCount(1, $this->logger->getLogCalls());
        $this->assertEquals(new LogCall(LogLevel::INFO, 'Shutting down after signal'), $this->logger->getFirstLogCall());
    }

    public function testHandleInterrupt(): void
    {
        $this->sut->handleInterrupt();
        $this->assertCount(1, $this->logger->getLogCalls());
        $this->assertEquals(
            new LogCall(
                LogLevel::INFO,
                'Execution interrupted, waiting for current iteration to complete then quitting',
            ),
            $this->logger->getFirstLogCall(),
        );
    }

    public function testHandleIterationComplete(): void
    {
        $this->sut->handleIterationComplete();
        $this->assertCount(0, $this->logger->getLogCalls());
    }

    public function testHandleIterationStart(): void
    {
        $this->sut->handleIterationStart();
        $this->assertCount(0, $this->logger->getLogCalls());
    }

    public function testHandleMessageProcessed(): void
    {
        $this->sut->handleMessageProcessed(Mockery::mock(MessageInterface::class)); // @phpstan-ignore-line
        $this->assertCount(1, $this->logger->getLogCalls());
        $this->assertEquals(new LogCall(LogLevel::INFO, 'Message processed'), $this->logger->getFirstLogCall());
    }

    public function testHandleMessageReceived(): void
    {
        $this->sut->handleMessageReceived(Mockery::mock(MessageInterface::class)); // @phpstan-ignore-line
        $this->assertCount(1, $this->logger->getLogCalls());
        $this->assertEquals(new LogCall(LogLevel::INFO, 'Processing message'), $this->logger->getFirstLogCall());
    }

    public function testHandleSignalReceived(): void
    {
        $this->sut->handleSignalReceived(SIGINT);
        $this->assertCount(1, $this->logger->getLogCalls());
        $this->assertEquals(new LogCall(LogLevel::INFO, 'Signal received: SIGINT'), $this->logger->getFirstLogCall());
    }

    public function testHandleSignalReceivedWithInvalid(): void
    {
        $this->sut->handleSignalReceived(12345);
        $this->assertCount(1, $this->logger->getLogCalls());
        $this->assertEquals(new LogCall(LogLevel::INFO, 'Signal received: UNKNOWN (12345)'), $this->logger->getFirstLogCall());
    }

    public function testHandleWorkerException(): void
    {
        $this->sut->handleWorkerException($this->getException());
        $this->assertCount(0, $this->logger->getLogCalls());
    }

    public function testHandleWorkerFinally(): void
    {
        $this->sut->handleWorkerFinally();
        $this->assertCount(0, $this->logger->getLogCalls());
    }

    public function getException(): RuntimeException
    {
        return new RuntimeException('test');
    }
}
