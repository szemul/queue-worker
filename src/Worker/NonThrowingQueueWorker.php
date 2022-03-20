<?php
declare(strict_types=1);

namespace Szemul\QueueWorker\Worker;

use Szemul\QueueWorker\Value\InterruptedValue;
use Throwable;

class NonThrowingQueueWorker extends QueueWorker
{
    public function work(InterruptedValue $interruptedValue): void
    {
        try {
            parent::work($interruptedValue);
        } catch (Throwable) {
            // Noop, this is the non throwing worker
        }
    }
}
