<?php
declare(strict_types=1);
namespace Ampersand\VerboseLogRequest\Plugin;

use Ampersand\VerboseLogRequest\Service\IsVerbose;
use Magento\Framework\Session\SaveHandler\Redis\Config as RedisConfig;

class AdjustRedisLogLevel
{
    /**
     * @var IsVerbose
     */
    private IsVerbose $isVerbose;

    /**
     * @param IsVerbose $isVerbose
     */
    public function __construct(
        IsVerbose $isVerbose
    ) {
        $this->isVerbose = $isVerbose;
    }

    /**
     * Bump redis log level to 7 when on a verbose request.
     *
     * @param RedisConfig $subject
     * @param string $result
     * @return mixed|string
     */
    public function afterGetLogLevel(RedisConfig $subject, $result)
    {
        if ($this->isVerbose->isVerbose()) {
            return '7';
        }
        return $result;
    }
}
