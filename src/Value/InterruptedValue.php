<?php
declare(strict_types=1);

namespace Szemul\QueueWorker\Value;

class InterruptedValue
{
    public function __construct(private bool $interrupted = false)
    {
    }

    public function isInterruped(): bool
    {
        return $this->interrupted;
    }

    public function setInterrupted(bool $interrupted): self
    {
        $this->interrupted = $interrupted;

        return $this;
    }
}
