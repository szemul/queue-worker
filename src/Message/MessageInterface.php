<?php
declare(strict_types=1);

namespace Szemul\QueueWorker\Message;

interface MessageInterface
{
    /** @return mixed[] */
    public function getPayload(): array;

    public function getJobName(): ?string;
}
