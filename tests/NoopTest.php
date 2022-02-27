<?php
declare(strict_types=1);

namespace Szemul\QueueWorker\Test;

use PHPUnit\Framework\TestCase;

class NoopTest extends TestCase
{
    public function testNoop(): void
    {
        $this->assertTrue(true);
    }
}
