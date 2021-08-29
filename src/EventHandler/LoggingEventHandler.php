<?php
declare(strict_types=1);

namespace Szemul\QueueWorker\EventHandler;

use Psr\Log\LoggerInterface;
use Szemul\QueueWorker\Message\MessageInterface;
use Throwable;

class LoggingEventHandler implements CommandEventHandlerInterface, WorkerEventHandlerInterface
{
    public function __construct(protected LoggerInterface $logger)
    {
    }

    public function handleBeforeLoop(): void
    {
        $this->logger->info('Starting worker');
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
    }

    public function handleCommandInterrupted(): void
    {
        $this->logger->info('Shutting down after signal');
    }

    public function handleCommandFinished(): void
    {
        $this->logger->info('Worker shutting down');
    }

    public function handleMessageReceived(MessageInterface $message): void
    {
        $this->logger->info('Processing message');
    }

    public function handleMessageProcessed(MessageInterface $message): void
    {
        $this->logger->info('Message processed');
    }

    public function handleWorkerException(Throwable $e): void
    {
    }

    public function handleWorkerFinally(): void
    {
    }
}
