<?php
declare(strict_types=1);

namespace Szemul\QueueWorker\EventHandler;

use Throwable;

interface CommandEventHandlerInterface
{
    public function handleBeforeLoop(): void;

    public function handleIterationStart(): void;

    public function handleIterationComplete(): void;

    public function handleCommandFinally(): void;

    public function handleCommandException(Throwable $e): void;

    public function handleCommandInterrupted(): void;

    public function handleCommandFinished(): void;

    public function handleSignalReceived(int $signal): void;

    public function handleInterrupt(): void;
}
