<?php
declare(strict_types=1);

namespace Szemul\QueueWorker\EventHandler;

use Szemul\QueueWorker\Message\MessageInterface;
use Throwable;

class WorkerEventHandlerRegistry implements WorkerEventHandlerInterface
{
    /** @var WorkerEventHandlerInterface[] */
    protected array $handlers;

    public function __construct(WorkerEventHandlerInterface ...$handlers)
    {
        $this->handlers = $handlers;
    }

    public function add(WorkerEventHandlerInterface $handler): static
    {
        $this->handlers[] = $handler;

        return $this;
    }

    public function handleMessageReceived(MessageInterface $message): void
    {
        foreach ($this->handlers as $handler) {
            $handler->handleMessageReceived($message);
        }
    }

    public function handleMessageProcessed(MessageInterface $message): void
    {
        foreach ($this->handlers as $handler) {
            $handler->handleMessageProcessed($message);
        }
    }

    public function handleWorkerException(Throwable $e): void
    {
        foreach ($this->handlers as $handler) {
            $handler->handleWorkerException($e);
        }
    }

    public function handleWorkerFinally(): void
    {
        foreach ($this->handlers as $handler) {
            $handler->handleWorkerFinally();
        }
    }
}
