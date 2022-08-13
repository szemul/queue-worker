<?php
declare(strict_types=1);

namespace Szemul\QueueWorker\Test\Worker;

use JetBrains\PhpStorm\Pure;
use RuntimeException;
use Szemul\QueueWorker\Worker\NonThrowingQueueWorker;
use Szemul\QueueWorker\Worker\QueueWorker;

class NonThrowingQueueWorkerTest extends QueueWorkerTest
{
    public function testWorkWithException(): void
    {
        $eventHandler = $this->getEventHandler();
        $message      = $this->getMessage();
        $exception    = new RuntimeException('Test');

        $this->expectMessageRetrieved($message)
            ->expectMessageProcessedWithException($message, $exception)
            ->expectMessageReceivedEvent($eventHandler, $message)
            ->expectWorkerExceptionEvent($eventHandler, $exception)
            ->expectWorkerFinallyEvent($eventHandler);

        $this->sut->setEventHandler($eventHandler)->work($this->interruptedValue, $this->input);
    }

    #[Pure]
    protected function getSut(): QueueWorker
    {
        return new NonThrowingQueueWorker($this->queue, $this->processor);
    }
}
