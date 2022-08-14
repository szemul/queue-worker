<?php
declare(strict_types=1);

namespace Szemul\QueueWorker\Worker;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Szemul\QueueWorker\Value\InterruptedValue;

interface WorkerInterface
{
    /** @return array<InputOption|InputArgument> */
    public function getAdditionalInputDefinitions(): array;

    public function work(InterruptedValue $interruptedValue, InputInterface $input): void;
}
