<?php
declare(strict_types=1);

namespace Szemul\QueueWorker\EventHandler;

use Throwable;

class CommandEventHandlerRegistry implements CommandEventHandlerInterface
{
    /** @var CommandEventHandlerInterface[] */
    protected array $handlers = [];

    public function __construct(CommandEventHandlerInterface ...$handlers)
    {
        $this->handlers = $handlers;
    }

    public function add(CommandEventHandlerInterface $handler): static
    {
        $this->handlers[] = $handler;

        return $this;
    }

    public function handleBeforeLoop(): void
    {
        foreach ($this->handlers as $handler) {
            $handler->handleBeforeLoop();
        }
    }

    public function handleIterationStart(): void
    {
        foreach ($this->handlers as $handler) {
            $handler->handleIterationStart();
        }
    }

    public function handleIterationComplete(): void
    {
        foreach ($this->handlers as $handler) {
            $handler->handleIterationComplete();
        }
    }

    public function handleCommandFinally(): void
    {
        foreach ($this->handlers as $handler) {
            $handler->handleCommandFinally();
        }
    }

    public function handleCommandException(Throwable $e): void
    {
        foreach ($this->handlers as $handler) {
            $handler->handleCommandException($e);
        }
    }

    public function handleCommandInterrupted(): void
    {
        foreach ($this->handlers as $handler) {
            $handler->handleCommandInterrupted();
        }
    }

    public function handleCommandFinished(): void
    {
        foreach ($this->handlers as $handler) {
            $handler->handleCommandFinished();
        }
    }
}
