<?php
declare(strict_types=1);

declare(ticks=1);

namespace Szemul\QueueWorker\Command;

use Szemul\Helper\DateHelper;
use Szemul\QueueWorker\EventHandler\CommandEventHandlerInterface;
use Szemul\QueueWorker\SignalHandler\SignalHandlerInterface;
use Szemul\QueueWorker\SignalHandler\SignalReceiverInterface;
use Szemul\QueueWorker\Value\InterruptedValue;
use Szemul\QueueWorker\Worker\WorkerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Szemul\ErrorHandler\Terminator\TerminatorInterface;
use Throwable;

class WorkerCommand extends Command implements SignalReceiverInterface
{
    protected bool                          $isAborted                   = false;
    protected int                           $defaultMaxIterations        = 0;
    protected int                           $defaultTargetRuntimeSeconds = 600;
    protected ?CommandEventHandlerInterface $eventHandler                = null;

    public function __construct(
        protected DateHelper $dateHelper,
        protected InterruptedValue $interruptedValue,
        protected WorkerInterface $worker,
        string $name = null,
    ) {
        parent::__construct($name);
    }

    public function setEventHandler(CommandEventHandlerInterface $eventHandler): static
    {
        $this->eventHandler = $eventHandler;

        return $this;
    }

    public function registerInSignalHandler(SignalHandlerInterface $signalHandler): static
    {
        $signalHandler->setReceiver($this);

        return $this;
    }

    protected function configure(): void
    {
        $this->addOption(
            'max-iterations',
            'i',
            InputOption::VALUE_REQUIRED,
            'The maximum number of iterations to process',
            $this->defaultMaxIterations,
        );

        $this->addOption(
            'target-run-time-seconds',
            't',
            InputOption::VALUE_REQUIRED,
            'The targeted run time for the worker in seconds',
            $this->defaultTargetRuntimeSeconds,
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->processInputs($input);

        $targetRunTimeSeconds = (int)$input->getOption('max-iterations');
        $maxIterations        = (int)$input->getOption('max-iterations');

        if ($maxIterations < 1) {
            $maxIterations = PHP_INT_MAX;
        }

        $iterations = 0;
        $stopAt     = $this->dateHelper->getCurrentTime()->addSeconds($targetRunTimeSeconds);

        $this->eventHandler?->handleBeforeLoop();

        try {
            do {
                if ($this->interruptedValue->isInterruped()) {
                    break;
                }

                $this->eventHandler?->handleIterationStart();
                $this->worker->work($this->interruptedValue);
                $this->eventHandler?->handleIterationComplete();
            } while ($stopAt->greaterThan($this->dateHelper->getCurrentTime()) && ++$iterations < $maxIterations);
        } catch (Throwable $e) {
            $this->eventHandler?->handleCommandException($e);

            return TerminatorInterface::EXIT_CODE_UNCAUGHT_EXCEPTION;
        } finally {
            $this->eventHandler?->handleCommandFinally();
        }

        if ($this->interruptedValue->isInterruped()) {
            $this->eventHandler?->handleCommandInterrupted();

            return TerminatorInterface::EXIT_CODE_SIGNAL_ABORT;
        }

        $this->eventHandler?->handleCommandFinished();

        return TerminatorInterface::EXIT_CODE_OK;
    }

    protected function processInputs(InputInterface $input): void
    {
        // Do nothing by default
    }

    public function receiveSignal(int $signal): void
    {
        switch ($signal) {
            case SIGTERM:
            case SIGHUP:
            case SIGINT:
                $this->interruptedValue->setInterrupted(true);
                break;
        }
    }
}
