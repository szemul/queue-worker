<?php
declare(strict_types=1);

namespace Szemul\QueueWorker\EventHandler;

use Szemul\ErrorHandler\ErrorHandlerRegistry;
use Szemul\Queue\Message\MessageInterface;
use Throwable;

class ErrorHandlingEventHandler implements CommandEventHandlerInterface, WorkerEventHandlerInterface
{
    public function __construct(protected ErrorHandlerRegistry $errorHandlerRegistry)
    {
    }

    public function handleBeforeLoop(): void
    {
    }

    public function handleIterationStart(): void
    {
    }

    public function handleIterationComplete(): void
    {
    }

    public function handleCommandFinally(): void
    {
    }

    public function handleCommandException(Throwable $e): void
    {
        $this->errorHandlerRegistry->handleException($e);
    }

    public function handleCommandInterrupted(): void
    {
    }

    public function handleCommandFinished(): void
    {
    }

    public function handleMessageReceived(MessageInterface $message): void
    {
    }

    public function handleMessageProcessed(MessageInterface $message): void
    {
    }

    public function handleWorkerException(Throwable $e): void
    {
        $this->errorHandlerRegistry->handleException($e);
    }

    public function handleWorkerFinally(): void
    {
    }
}
