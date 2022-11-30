<?php
declare(strict_types=1);

namespace Ampersand\VerboseLogRequest\Test\Integration;

use Magento\Framework\Session\SaveHandler\Redis\Config as RedisConfig;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class RedisLogLevelTest extends TestCase
{
    public function testGetDefaultLogLevel()
    {
        $redisConfig = Bootstrap::getObjectManager()->get(RedisConfig::class);
        $this->assertEquals('asdf', $redisConfig->getLogLevel());
    }

    public function testGetVerboseLogLevel()
    {
        $this->markTestSkipped('TODO');
        $redisConfig = Bootstrap::getObjectManager()->get(RedisConfig::class);
        $this->assertEquals('asdf', $redisConfig->getLogLevel());
    }
}
