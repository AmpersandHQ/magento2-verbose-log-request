<?php
declare(strict_types=1);

namespace Ampersand\VerboseLogRequest\Test\Integration;

use Ampersand\VerboseLogRequest\Service\IsVerbose\Storage;
use Magento\Framework\Session\SaveHandler\Redis\Config as RedisConfig;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class RedisLogLevelTest extends TestCase
{
    public function testGetDefaultLogLevel()
    {
        $redisConfig = Bootstrap::getObjectManager()->get(RedisConfig::class);
        $this->assertNull($redisConfig->getLogLevel(), 'Default log level is not null');
    }

    public function testGetVerboseLogLevel()
    {
        Storage::setFlag(true, true);
        $redisConfig = Bootstrap::getObjectManager()->get(RedisConfig::class);
        $this->assertEquals('7', $redisConfig->getLogLevel(), 'Default log level is not 7');
        Storage::reset(true);
    }
}
