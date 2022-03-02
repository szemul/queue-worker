<?php
declare(strict_types=1);

namespace Szemul\QueueWorker\Builder;

use Szemul\Helper\DateHelper;
use Szemul\Queue\Queue\ConsumerInterface;
use Szemul\QueueWorker\Command\WorkerCommand;
use Szemul\QueueWorker\EventHandler\CommandEventHandlerInterface;
use Szemul\QueueWorker\EventHandler\WorkerEventHandlerInterface;
use Szemul\QueueWorker\MessageProcessor\MessageProcessorInterface;
use Szemul\QueueWorker\SignalHandler\SignalHandlerInterface;
use Szemul\QueueWorker\Value\InterruptedValue;
use Szemul\QueueWorker\Worker\QueueWorker;

class QueueCommandBuilder
{

    protected ?WorkerEventHandlerInterface  $workerEventHandler  = null;
    protected ?CommandEventHandlerInterface $commandEventHandler = null;
    protected ?SignalHandlerInterface       $signalHandler       = null;

    public function __construct(protected DateHelper $dateHelper, protected InterruptedValue $interruptedValue)
    {
    }

    public function setWorkerEventHandler(WorkerEventHandlerInterface $workerEventHandler): static
    {
        $this->workerEventHandler = $workerEventHandler;

        return $this;
    }

    public function setCommandEventHandler(CommandEventHandlerInterface $commandEventHandler): static
    {
        $this->commandEventHandler = $commandEventHandler;

        return $this;
    }

    public function setCommandSignalHandler(SignalHandlerInterface $signalHandler): static
    {
        $this->signalHandler = $signalHandler;

        return $this;
    }

    public function build(string $name, ConsumerInterface $consumer, MessageProcessorInterface $processor): WorkerCommand
    {
        $worker = (new QueueWorker($consumer, $processor))
            ->setEventHandler($this->workerEventHandler);

        return (new WorkerCommand($this->dateHelper, $this->interruptedValue, $worker, $name))
            ->setEventHandler($this->commandEventHandler)
            ->setSignalHandler($this->signalHandler);
    }
}
