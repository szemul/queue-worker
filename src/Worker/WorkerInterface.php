<?php
declare(strict_types=1);

namespace Szemul\QueueWorker\Worker;

use Szemul\QueueWorker\Value\InterruptedValue;

interface WorkerInterface
{
    public function work(InterruptedValue $interruptedValue): void;
}
