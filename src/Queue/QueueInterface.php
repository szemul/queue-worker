<?php
declare(strict_types=1);

namespace Szemul\QueueWorker\Queue;

use Szemul\QueueWorker\Message\MessageInterface;

interface QueueInterface
{
    public function getMessage(): ?MessageInterface;

    public function abortMessage(MessageInterface $message): void;

    public function finishMessage(MessageInterface $message): void;
}
