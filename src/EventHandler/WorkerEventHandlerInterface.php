<?php
declare(strict_types=1);

namespace Szemul\QueueWorker\EventHandler;

use Szemul\QueueWorker\Message\MessageInterface;
use Throwable;

interface WorkerEventHandlerInterface
{
    public function handleMessageReceived(MessageInterface $message): void;

    public function handleMessageProcessed(MessageInterface $message): void;

    public function handleWorkerException(Throwable $e): void;

    public function handleWorkerFinally(): void;
}
