<?php
declare(strict_types=1);

namespace Szemul\QueueWorker\Worker;

use Symfony\Component\Console\Input\InputInterface;
use Szemul\Queue\Queue\ConsumerInterface;
use Szemul\QueueWorker\EventHandler\WorkerEventHandlerInterface;
use Szemul\QueueWorker\MessageProcessor\MessageProcessorInterface;
use Szemul\QueueWorker\Value\InterruptedValue;
use Throwable;

class QueueWorker implements WorkerInterface
{
    protected ?WorkerEventHandlerInterface $eventHandler = null;

    public function __construct(protected ConsumerInterface $queue, protected MessageProcessorInterface $processor)
    {
    }

    public function setEventHandler(?WorkerEventHandlerInterface $eventHandler): static
    {
        $this->eventHandler = $eventHandler;

        return $this;
    }

    public function getEventHandler(): ?WorkerEventHandlerInterface
    {
        return $this->eventHandler;
    }

    public function getQueue(): ConsumerInterface
    {
        return $this->queue;
    }

    public function getProcessor(): MessageProcessorInterface
    {
        return $this->processor;
    }

    /** @throws Throwable */
    public function work(InterruptedValue $interruptedValue, InputInterface $input): void
    {
        $message = $this->queue->getMessage();

        if (null === $message) {
            return;
        }

        if ($interruptedValue->isInterrupted()) {
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

            throw $e;
        } finally {
            $this->eventHandler?->handleWorkerFinally();
        }
    }

    public function getAdditionalInputDefinitions(): array
    {
        return [];
    }
}
