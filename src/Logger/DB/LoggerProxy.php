<?php
declare(strict_types=1);
namespace Ampersand\VerboseLogRequest\Logger\DB;

use Ampersand\VerboseLogRequest\Service\IsVerbose;
use Magento\Framework\DB\Logger\FileFactory;
use Magento\Framework\DB\Logger\LoggerProxy as VanillaLoggerProxy;
use Magento\Framework\DB\Logger\QuietFactory;

class LoggerProxy extends VanillaLoggerProxy
{
    /**
     * Enable verbose database query logging when requested
     *
     * @param FileFactory $fileFactory
     * @param QuietFactory $quietFactory
     * @param bool|string $loggerAlias
     * @param bool $logAllQueries
     * @param float $logQueryTime
     * @param bool $logCallStack
     * @param IsVerbose $isVerbose
     */
    public function __construct(
        FileFactory $fileFactory,
        QuietFactory $quietFactory,
        $loggerAlias,
        $logAllQueries,
        $logQueryTime,
        $logCallStack,
        IsVerbose $isVerbose
    ) {
        if ($isVerbose->isVerbose()) {
            $loggerAlias = VanillaLoggerProxy::LOGGER_ALIAS_FILE;
        }
        // @phpstan-ignore-next-line Parameter #3 $loggerAlias expects bool, bool|string given.
        parent::__construct($fileFactory, $quietFactory, $loggerAlias, $logAllQueries, $logQueryTime, $logCallStack);
    }
}
