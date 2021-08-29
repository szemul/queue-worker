<?php
declare(strict_types=1);

namespace Szemul\QueueWorker\Worker;

use Szemul\QueueWorker\EventHandler\WorkerEventHandlerInterface;
use Szemul\QueueWorker\MessageProcessor\MessageProcessorInterface;
use Szemul\QueueWorker\Queue\QueueInterface;
use Szemul\QueueWorker\Value\InterruptedValue;
use Throwable;

class QueueWorker implements WorkerInterface
{
    protected ?WorkerEventHandlerInterface $eventHandler = null;

    public function __construct(protected QueueInterface $queue, protected MessageProcessorInterface $processor)
    {
    }

    public function setEventHandler(WorkerEventHandlerInterface $eventHandler): static
    {
        $this->eventHandler = $eventHandler;

        return $this;
    }

    public function work(InterruptedValue $interruptedValue): void
    {
        $message = $this->queue->getMessage();

        if (null === $message || $interruptedValue->isInterruped()) {
            $this->queue->abortMessage($message);

            return;
        }

        try {
            $this->eventHandler?->handleMessageReceived($message);

            $this->processor->process($message);

            $this->eventHandler?->handleMessageProcessed($message);
            $this->queue->finishMessage($message);
        } catch (Throwable $e) {
            // event handler process
            $this->eventHandler?->handleWorkerException($e);
        } finally {
            $this->eventHandler?->handleWorkerFinally();
        }
    }
}
