<?php
declare(strict_types=1);

namespace Szemul\QueueWorker\SignalHandler;

class PcntlSignalHandler implements SignalHandlerInterface
{
    /** @var int[] */
    protected array $handledSignals;
    protected ?SignalReceiverInterface $receiver = null;

    public function __construct(int ...$handledSignals)
    {
        $this->handledSignals = $handledSignals;
    }

    public function setReceiver(SignalReceiverInterface $receiver): static
    {
        $this->receiver = $receiver;

        return $this;
    }

    /** @codeCoverageIgnore */
    public function register(): void
    {
        foreach ($this->handledSignals as $signal) {
            pcntl_signal($signal, [$this, 'handleSignal'], true);
        }
    }

    public function handleSignal(int $signal): void
    {
        $this->receiver?->receiveSignal($signal);
    }
}
