<?php
declare(strict_types=1);

namespace Szemul\QueueWorker\Test\Builder;

use Mockery;
use Szemul\Helper\DateHelper;
use Szemul\Queue\Queue\ConsumerInterface;
use Szemul\QueueWorker\Builder\QueueCommandBuilder;
use PHPUnit\Framework\TestCase;
use Szemul\QueueWorker\EventHandler\CommandEventHandlerInterface;
use Szemul\QueueWorker\EventHandler\WorkerEventHandlerInterface;
use Szemul\QueueWorker\MessageProcessor\MessageProcessorInterface;
use Szemul\QueueWorker\SignalHandler\SignalHandlerInterface;
use Szemul\QueueWorker\Value\InterruptedValue;
use Szemul\QueueWorker\Worker\NonThrowingQueueWorker;
use Szemul\QueueWorker\Worker\QueueWorker;

/**
 * @covers \Szemul\QueueWorker\Builder\QueueCommandBuilder
 */
class QueueCommandBuilderTest extends TestCase
{
    private const NAME = 'test:test';

    private DateHelper                $dateHelper;
    private InterruptedValue          $interruptedValue;
    private ConsumerInterface         $consumer;
    private MessageProcessorInterface $messageProcessor;
    private QueueCommandBuilder       $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dateHelper       = Mockery::mock(DateHelper::class); // @phpstan-ignore-line
        $this->interruptedValue = Mockery::mock(InterruptedValue::class); // @phpstan-ignore-line
        $this->consumer         = Mockery::mock(ConsumerInterface::class); // @phpstan-ignore-line
        $this->messageProcessor = Mockery::mock(MessageProcessorInterface::class); // @phpstan-ignore-line

        // @phpstan-ignore-next-line
        $this->sut              = new QueueCommandBuilder($this->dateHelper, $this->interruptedValue);
    }

    public function testWithNoOptionalValues(): void
    {
        $command = $this->sut->build(self::NAME, $this->consumer, $this->messageProcessor);

        /** @var QueueWorker $worker */
        $worker = $command->getWorker();

        $this->assertSame(self::NAME, $command->getName());
        $this->assertNull($command->getEventHandler());
        $this->assertNull($command->getSignalHandler());
        $this->assertSame($this->dateHelper, $command->getDateHelper());
        $this->assertSame($this->interruptedValue, $command->getInterruptedValue());

        $this->assertNotInstanceOf(NonThrowingQueueWorker::class, $worker);
        $this->assertInstanceOf(QueueWorker::class, $worker);
        $this->assertNull($worker->getEventHandler());
        $this->assertSame($this->messageProcessor, $worker->getProcessor());
        $this->assertSame($this->consumer, $worker->getQueue());
    }

    public function testNonThrowingWithNoOptionalValues(): void
    {
        $command = $this->sut->build(self::NAME, $this->consumer, $this->messageProcessor, false);

        /** @var QueueWorker $worker */
        $worker = $command->getWorker();

        $this->assertSame(self::NAME, $command->getName());
        $this->assertNull($command->getEventHandler());
        $this->assertNull($command->getSignalHandler());
        $this->assertSame($this->dateHelper, $command->getDateHelper());
        $this->assertSame($this->interruptedValue, $command->getInterruptedValue());

        $this->assertInstanceOf(NonThrowingQueueWorker::class, $worker);
        $this->assertNull($worker->getEventHandler());
        $this->assertSame($this->messageProcessor, $worker->getProcessor());
        $this->assertSame($this->consumer, $worker->getQueue());
    }

    public function testWithOptionalValues(): void
    {
        /** @var WorkerEventHandlerInterface $workerEventHandler */
        $workerEventHandler   = Mockery::mock(WorkerEventHandlerInterface::class);
        /** @var CommandEventHandlerInterface $commandEventHandler */
        $commandEventHandler  = Mockery::mock(CommandEventHandlerInterface::class);
        /** @var SignalHandlerInterface $commandSignalHandler */
        $commandSignalHandler = Mockery::mock(SignalHandlerInterface::class);

        $command = $this->sut->setWorkerEventHandler($workerEventHandler)
            ->setCommandSignalHandler($commandSignalHandler)
            ->setCommandEventHandler($commandEventHandler)
            ->build(self::NAME, $this->consumer, $this->messageProcessor);

        /** @var QueueWorker $worker */
        $worker = $command->getWorker();

        $this->assertNotInstanceOf(NonThrowingQueueWorker::class, $worker);
        $this->assertInstanceOf(QueueWorker::class, $worker);

        $this->assertSame(self::NAME, $command->getName());
        $this->assertSame($commandEventHandler, $command->getEventHandler());
        $this->assertSame($commandSignalHandler, $command->getSignalHandler());
        $this->assertSame($this->dateHelper, $command->getDateHelper());
        $this->assertSame($this->interruptedValue, $command->getInterruptedValue());

        $this->assertNotInstanceOf(NonThrowingQueueWorker::class, $worker);
        $this->assertInstanceOf(QueueWorker::class, $worker);
        $this->assertSame($workerEventHandler, $worker->getEventHandler());
        $this->assertSame($this->messageProcessor, $worker->getProcessor());
        $this->assertSame($this->consumer, $worker->getQueue());
    }
}
