<?php
declare(strict_types=1);

namespace Szemul\QueueWorker\SignalHandler;

interface SignalHandlerInterface
{
    public function setReceiver(SignalReceiverInterface $receiver): static;

    public function handleSignal(int $signal): void;
}
