<?php
declare(strict_types=1);

namespace Szemul\QueueWorker\Test\Command;

use Carbon\CarbonImmutable;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Szemul\ErrorHandler\Terminator\TerminatorInterface;
use Szemul\Helper\DateHelper;
use Szemul\QueueWorker\Command\WorkerCommand;
use PHPUnit\Framework\TestCase;
use Szemul\QueueWorker\EventHandler\CommandEventHandlerInterface;
use Szemul\QueueWorker\SignalHandler\SignalHandlerInterface;
use Szemul\QueueWorker\Value\InterruptedValue;
use Szemul\QueueWorker\Worker\WorkerInterface;
use Throwable;

/**
 * @covers \Szemul\QueueWorker\Command\WorkerCommand
 */
class WorkerCommandTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private const NAME = 'test:test';

    private DateHelper|MockInterface       $dateHelper;
    private InterruptedValue|MockInterface $interruptedValue;
    private WorkerInterface|MockInterface  $worker;
    private WorkerCommand                  $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dateHelper       = Mockery::mock(DateHelper::class); // @phpstan-ignore-line
        $this->interruptedValue = Mockery::mock(InterruptedValue::class); // @phpstan-ignore-line
        $this->worker           = Mockery::mock(WorkerInterface::class); // @phpstan-ignore-line

        $this->expectGetAdditionalInputDefinitionsCalled();

        // @phpstan-ignore-next-line
        $this->sut = new WorkerCommand($this->dateHelper, $this->interruptedValue, $this->worker, self::NAME);
    }

    public function testGetDateHelper(): void
    {
        $this->assertSame($this->dateHelper, $this->sut->getDateHelper());
    }

    public function testGetEventHandler(): void
    {
        $this->assertNull($this->sut->getEventHandler());
    }

    public function testGetInterruptedValue(): void
    {
        $this->assertSame($this->interruptedValue, $this->sut->getInterruptedValue());
    }

    public function testGetSignalHandler(): void
    {
        $this->assertNull($this->sut->getSignalHandler());
    }

    public function testGetWorker(): void
    {
        $this->assertSame($this->worker, $this->sut->getWorker());
    }

    public function testReceiveSignalWithSigtermAndEventHandler(): void
    {
        $eventHandler = $this->getEventHandler();

        $this->expectInterruptedValueSetToInterrupted()
            ->expectSignalReceivedEvent($eventHandler, SIGTERM)
            ->expectInterruptEvent($eventHandler);

        $this->sut->setEventHandler($eventHandler)
            ->receiveSignal(SIGTERM);
    }

    public function testReceiveSignalWithSigpipeAndEventHandler(): void
    {
        $eventHandler = $this->getEventHandler();

        $this->expectSignalReceivedEvent($eventHandler, SIGPIPE);

        $this->sut->setEventHandler($eventHandler)
            ->receiveSignal(SIGPIPE);
    }

    public function testReceiveSignalWithSigtermWithoutEventHandler(): void
    {
        $this->expectInterruptedValueSetToInterrupted();

        $this->sut->receiveSignal(SIGTERM);
    }

    public function testReceiveSignalWithSigpipeWithEventHandler(): void
    {
        $this->sut->receiveSignal(SIGPIPE);

        // Noop assert
        $this->assertTrue(true);
    }

    public function testSetEventHandler(): void
    {
        $eventHandler = $this->getEventHandler();

        $this->assertSame($eventHandler, $this->sut->setEventHandler($eventHandler)->getEventHandler());
    }

    public function testSetSignalHandler(): void
    {
        /** @var SignalHandlerInterface $signalHandler */
        $signalHandler = Mockery::mock(SignalHandlerInterface::class);

        $this->assertSame($signalHandler, $this->sut->setSignalHandler($signalHandler)->getSignalHandler());
    }

    public function testExecuteWithIterationLimit(): void
    {
        $input        = $this->getInput(10, 2);
        $output       = $this->getOutput();
        $eventHandler = $this->getEventHandler();

        $this->sut->setEventHandler($eventHandler);

        $this->expectWorkCalled(2, $input)
            ->expectCurrentTimeRetrieved(2)
            ->expectInterruptedChecked()
            ->expectExecuteEvents($eventHandler, 2)
            ->expectFinishedEvent($eventHandler);

        $this->assertSame(0, $this->runExecute($input, $output));
    }

    public function testExecuteWithTimeLimit(): void
    {
        $input        = $this->getInput(3, 0);
        $output       = $this->getOutput();
        $eventHandler = $this->getEventHandler();

        $this->sut->setEventHandler($eventHandler);

        $this->expectWorkCalled(3, $input)
            ->expectCurrentTimeRetrieved(3)
            ->expectInterruptedChecked()
            ->expectExecuteEvents($eventHandler, 3)
            ->expectFinishedEvent($eventHandler);

        $this->assertSame(0, $this->runExecute($input, $output));
    }

    public function testExecuteWithException(): void
    {
        $input        = $this->getInput(2, 0);
        $output       = $this->getOutput();
        $eventHandler = $this->getEventHandler();
        $exception    = new RuntimeException();

        $this->sut->setEventHandler($eventHandler);

        $this->expectWorkCalledAndThrowsException($exception, $input)
            ->expectCurrentTimeRetrieved(0)
            ->expectInterruptedChecked()
            ->expectExecuteEventsWithException($eventHandler)
            ->expectExceptionEvent($eventHandler, $exception);

        $this->assertSame(TerminatorInterface::EXIT_CODE_UNCAUGHT_EXCEPTION, $this->runExecute($input, $output));
    }

    public function testExecuteWithInterrupted(): void
    {
        $input        = $this->getInput(2, 0);
        $output       = $this->getOutput();
        $eventHandler = $this->getEventHandler();

        $this->sut->setEventHandler($eventHandler);

        $this->expectCurrentTimeRetrieved(0)
            ->expectInterruptedChecked(true)
            ->expectBasicEvents($eventHandler)
            ->expectCommandInterruptedEvent($eventHandler);

        $this->assertSame(TerminatorInterface::EXIT_CODE_SIGNAL_ABORT, $this->runExecute($input, $output));
    }

    private function expectInterruptedValueSetToInterrupted(): static
    {
        $this->interruptedValue->shouldReceive('setInterrupted') // @phpstan-ignore-line
            ->once()
            ->with(true);

        return $this;
    }

    private function expectSignalReceivedEvent(CommandEventHandlerInterface|MockInterface $eventHandler, int $signal): static
    {
        $eventHandler->shouldReceive('handleSignalReceived') // @phpstan-ignore-line
            ->once()
            ->with($signal);

        return $this;
    }

    private function expectInterruptEvent(CommandEventHandlerInterface|MockInterface $eventHandler): static
    {
        $eventHandler->shouldReceive('handleInterrupt') // @phpstan-ignore-line
            ->once()
            ->withNoArgs();

        return $this;
    }

    private function expectBasicEvents(CommandEventHandlerInterface|MockInterface $eventHandler): static
    {
        $eventHandler->shouldReceive('handleBeforeLoop')->once(); // @phpstan-ignore-line
        $eventHandler->shouldReceive('handleCommandFinally')->once(); // @phpstan-ignore-line

        return $this;
    }

    private function expectExecuteEvents(CommandEventHandlerInterface|MockInterface $eventHandler, int $iterations): static
    {
        $this->expectBasicEvents($eventHandler);
        $eventHandler->shouldReceive('handleIterationStart')->times($iterations); // @phpstan-ignore-line
        $eventHandler->shouldReceive('handleIterationComplete')->times($iterations); // @phpstan-ignore-line

        return $this;
    }

    private function expectExecuteEventsWithException(CommandEventHandlerInterface|MockInterface $eventHandler): static
    {
        $this->expectBasicEvents($eventHandler);
        $eventHandler->shouldReceive('handleIterationStart')->once(); // @phpstan-ignore-line

        return $this;
    }

    private function expectCommandInterruptedEvent(CommandEventHandlerInterface|MockInterface $eventHandler): static
    {
        // @phpstan-ignore-next-line
        $eventHandler->shouldReceive('handleCommandInterrupted')->once();

        return $this;
    }

    private function expectExceptionEvent(CommandEventHandlerInterface|MockInterface $eventHandler, Throwable $exception): static
    {
        // @phpstan-ignore-next-line
        $eventHandler->shouldReceive('handleCommandException')->once()->with($exception);

        return $this;
    }

    private function expectFinishedEvent(CommandEventHandlerInterface|MockInterface $eventHandler): static
    {
        // @phpstan-ignore-next-line
        $eventHandler->shouldReceive('handleCommandFinished')->once();

        return $this;
    }

    private function expectGetAdditionalInputDefinitionsCalled(): static
    {
        // @phpstan-ignore-next-line
        $this->worker->shouldReceive('getAdditionalInputDefinitions')->once()->withNoArgs()->andReturn([]);

        return $this;
    }

    private function expectWorkCalled(int $iterations, InputInterface $input): static
    {
        // @phpstan-ignore-next-line
        $this->worker->shouldReceive('work')->times($iterations)->with($this->interruptedValue, $input);

        return $this;
    }

    private function expectWorkCalledAndThrowsException(Throwable $throwable, InputInterface $input): static
    {
        // @phpstan-ignore-next-line
        $this->worker->shouldReceive('work')->once()->with($this->interruptedValue, $input)->andThrow($throwable);

        return $this;
    }

    private function expectCurrentTimeRetrieved(int $iterations): static
    {
        $start = CarbonImmutable::create(2022, 03, 20, 1, 2, 3)->setMicro(456789);
        // @phpstan-ignore-next-line
        $this->dateHelper->shouldReceive('getCurrentTime')->once()->withNoArgs()->andReturn($start);

        for ($i = 0; $i < $iterations; $i++) {
            $this->dateHelper->shouldReceive('getCurrentTime') // @phpstan-ignore-line
                ->once()
                ->withNoArgs()
                ->andReturn($start->addSeconds($i + 1));
        }

        return $this;
    }

    private function expectInterruptedChecked(bool $isInterrupted = false): static
    {
        $this->interruptedValue->shouldReceive('isInterrupted') // @phpstan-ignore-line
            ->atLeast()
            ->once()
            ->withNoArgs()
            ->andReturn($isInterrupted);

        return $this;
    }

    private function getInput(int $targetRunTimeSeconds, int $maxIterations): InputInterface|MockInterface
    {
        $input = Mockery::mock(InputInterface::class);

        $input->shouldReceive('getOption') // @phpstan-ignore-line
            ->with('target-run-time-seconds')
            ->andReturn((string)$targetRunTimeSeconds);

        $input->shouldReceive('getOption') // @phpstan-ignore-line
            ->with('max-iterations')
            ->andReturn((string)$maxIterations);

        return $input; // @phpstan-ignore-line
    }

    private function getOutput(): OutputInterface|MockInterface
    {
        return Mockery::mock(OutputInterface::class); // @phpstan-ignore-line
    }

    private function getEventHandler(): MockInterface|CommandEventHandlerInterface
    {
        /** @var CommandEventHandlerInterface&MockInterface $eventHandler */
        $eventHandler = Mockery::mock(CommandEventHandlerInterface::class);

        return $eventHandler;
    }

    private function runExecute(InputInterface $input, OutputInterface $output): int
    {
        $method = (new \ReflectionObject($this->sut))->getMethod('execute');

        $method->setAccessible(true);

        return $method->invoke($this->sut, $input, $output);
    }
}
