<?php
declare(strict_types=1);

namespace Szemul\QueueWorker\EventHandler;

use Psr\Log\LoggerInterface;
use Szemul\Queue\Message\MessageInterface;
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

    public function handleSignalReceived(int $signal): void
    {
        $this->logger->info(
            'Signal received: ' . $this->getSignalName($signal),
        );
    }

    public function handleInterrupt(): void
    {
        $this->logger->info('Execution interrupted, waiting for current iteration to complete then quitting');
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

    protected function getSignalName(int $signal): string
    {
        // Source: @hanshenrik https://stackoverflow.com/questions/58471528/get-signal-name-from-signal-number-in-php
        foreach (get_defined_constants(true)['pcntl'] as $name => $num) {
            // the _ is to ignore SIG_IGN and SIG_DFL and SIG_ERR and SIG_BLOCK and SIG_UNBLOCK and SIG_SETMARK, and maybe more, who knows
            if ($num === $signal && str_starts_with($name, 'SIG') && $name[3] !== '_') {
                return $name;
            }
        }

        return 'UNKNOWN (' . $signal . ')';
    }
}
