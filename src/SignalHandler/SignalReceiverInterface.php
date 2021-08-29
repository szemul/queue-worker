<?php
declare(strict_types=1);

namespace Szemul\QueueWorker\SignalHandler;

interface SignalReceiverInterface
{
    public function receiveSignal(int $signal): void;
}
