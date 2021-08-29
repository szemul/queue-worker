<?php
declare(strict_types=1);

namespace Szemul\QueueWorker\MessageProcessor;

use Szemul\QueueWorker\Message\MessageInterface;

interface MessageProcessorInterface
{
    public function process(MessageInterface $message): void;
}
