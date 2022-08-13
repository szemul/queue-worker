<?php
declare(strict_types=1);

namespace Szemul\QueueWorker\Worker;

use Symfony\Component\Console\Input\InputInterface;
use Szemul\QueueWorker\Value\InterruptedValue;
use Throwable;

class NonThrowingQueueWorker extends QueueWorker
{
    public function work(InterruptedValue $interruptedValue, InputInterface $input): void
    {
        try {
            parent::work($interruptedValue, $input);
        } catch (Throwable) {
            // Noop, this is the non throwing worker
        }
    }
}
