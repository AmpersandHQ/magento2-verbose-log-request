<?php
declare(strict_types=1);
namespace Ampersand\VerboseLogRequest\Test\Unit\Service\IsVerbose;

use Ampersand\VerboseLogRequest\Service\IsVerbose\Storage;
use PHPUnit\Framework\TestCase;

class StorageTest extends TestCase
{
    /**
     *
     */
    protected function setUp(): void
    {
        Storage::reset(true);
    }

    /**
     *
     */
    public function testSetDefaultStates()
    {
        $this->assertFalse(Storage::flagIsSet(), 'Flag should not start out as set');
        $this->assertFalse(Storage::getFlag(), 'Flag should default to false');
    }

    /**
     * @depends testSetDefaultStates
     */
    public function testForceFlagForTests()
    {
        Storage::setFlag(false);
        Storage::setFlag(true, true);
        $this->assertTrue(Storage::getFlag(), 'Flag should be true');
        Storage::setFlag(false);
        $this->assertTrue(Storage::getFlag(), 'Flag should be true');
        Storage::setFlag(false, true);
        $this->assertFalse(Storage::getFlag(), 'Flag should be false');
    }

    /**
     * @depends testForceFlagForTests
     */
    public function testSetFlagTrue()
    {
        Storage::setFlag(true);
        Storage::setFlag(false);
        $this->assertTrue(Storage::getFlag(), 'Flag should be true');
    }

    /**
     * @depends testForceFlagForTests
     */
    public function testSetFlagFalse()
    {
        Storage::setFlag(false);
        Storage::setFlag(true);
        $this->assertFalse(Storage::getFlag(), 'Flag should be true');
    }
}
