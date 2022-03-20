<?php
declare(strict_types=1);

namespace Szemul\QueueWorker\Test\Value;

use Szemul\QueueWorker\Value\InterruptedValue;
use PHPUnit\Framework\TestCase;

class InterruptedValueTest extends TestCase
{
    public function testFunctionality(): void
    {
        $sut = new InterruptedValue();
        $this->assertFalse($sut->isInterrupted());
        $sut->setInterrupted(true);
        $this->assertTrue($sut->isInterrupted());
        $sut->setInterrupted(false);
        $this->assertFalse($sut->isInterrupted());

        $sut2 = new InterruptedValue(true);
        $this->assertTrue($sut2->isInterrupted());
    }
}
