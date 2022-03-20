<?php
declare(strict_types=1);

namespace Szemul\QueueWorker\Test\EventHandler;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use RuntimeException;
use Szemul\QueueWorker\EventHandler\CommandEventHandlerInterface;
use Szemul\QueueWorker\EventHandler\CommandEventHandlerRegistry;
use PHPUnit\Framework\TestCase;

class CommandEventHandlerRegistryTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testAdd()
    {
        $sut = $this->getSut();
        /** @var CommandEventHandlerInterface $handler */
        $handler = Mockery::mock(CommandEventHandlerInterface::class);

        $this->assertEmpty($sut->getHandlers());
        $result = $sut->add($handler);
        $this->assertSame($sut, $result);
        $this->assertSame([$handler], $sut->getHandlers());
    }

    public function testHandleBeforeLoop(): void
    {
        (new CommandEventHandlerRegistry(...$this->getHandlers(2, 'handleBeforeLoop')))->handleBeforeLoop();
    }

    public function testHandleCommandException(): void
    {
        $exception = new RuntimeException('test');
        (new CommandEventHandlerRegistry(
            ...$this->getHandlers(2, 'handleCommandException', $exception),
        ))->handleCommandException($exception);
    }

    public function testHandleCommandFinally(): void
    {
        $this->getSut(...$this->getHandlers(2, 'handleCommandFinally'))->handleCommandFinally();
    }

    public function testHandleCommandFinished(): void
    {
        $this->getSut(...$this->getHandlers(2, 'handleCommandFinished'))->handleCommandFinished();
    }

    public function testHandleCommandInterrupted(): void
    {
        $this->getSut(...$this->getHandlers(2, 'handleCommandInterrupted'))->handleCommandInterrupted();
    }

    public function testHandleInterrupt(): void
    {
        $this->getSut(...$this->getHandlers(2, 'handleInterrupt'))->handleInterrupt();
    }

    public function testHandleIterationComplete(): void
    {
        $this->getSut(...$this->getHandlers(2, 'handleIterationComplete'))->handleIterationComplete();
    }

    public function testHandleIterationStart(): void
    {
        $this->getSut(...$this->getHandlers(2, 'handleIterationStart'))->handleIterationStart();
    }

    public function testHandleSignalReceived(): void
    {
        $this->getSut(...$this->getHandlers(2, 'handleSignalReceived', SIGINT))->handleSignalReceived(SIGINT);
    }

    private function getSut(CommandEventHandlerInterface ...$handlers): CommandEventHandlerRegistry
    {
        return new CommandEventHandlerRegistry(...$handlers);
    }

    /** @return array<int, CommandEventHandlerInterface|MockInterface> */
    private function getHandlers(int $count, string $testedMethod, mixed ...$args): array
    {
        $handlers = [];

        for ($i = 0; $i < $count; $i++) {
            /** @var CommandEventHandlerInterface|MockInterface $handler */
            $handler = Mockery::mock(CommandEventHandlerInterface::class);

            $handler->shouldReceive($testedMethod)->once()->with(...$args); // @phpstan-ignore-line

            $handlers[] = $handler;
        }

        return $handlers;
    }
}
