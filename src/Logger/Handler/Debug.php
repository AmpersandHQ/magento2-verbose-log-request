<?php
declare(strict_types=1);
namespace Ampersand\VerboseLogRequest\Logger\Handler;

use Ampersand\VerboseLogRequest\Service\IsVerbose;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\State;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Logger\Handler\Base as BaseLogHandler;

class Debug extends \Magento\Developer\Model\Logger\Handler\Debug
{
    /**
     * @var bool
     */
    private bool $isVerboseLogVirtualType = false;

    /**
     * @var BaseLogHandler
     */
    private BaseLogHandler $baseLogHandler;

    /**
     * @var IsVerbose
     */
    private IsVerbose $isVerboseFlag;

    /**
     * @param IsVerbose $isVerbose
     * @param BaseLogHandler $baseLogHandler
     * @param DriverInterface $filesystem
     * @param State $state
     * @param DeploymentConfig $deploymentConfig
     * @param string|null $filePath
     * @param false|true $isVerboseLogVirtualType
     * @throws \Exception
     */
    public function __construct(
        IsVerbose $isVerbose,
        BaseLogHandler $baseLogHandler,
        DriverInterface $filesystem,
        State $state,
        DeploymentConfig $deploymentConfig,
        ?string $filePath = null,
        $isVerboseLogVirtualType = false
    ) {
        parent::__construct($filesystem, $state, $deploymentConfig, $filePath);
        $this->baseLogHandler = $baseLogHandler;
        $this->isVerboseLogVirtualType = $isVerboseLogVirtualType;
        $this->isVerboseFlag = $isVerbose;
    }

    /**
     * @inheritDoc
     */
    public function isHandling(array $record): bool
    {
        if ($this->isVerboseLogVirtualType) {
            return $this->isHandlingVerboseDebugMode($record);
        }
        return $this->isHandlingDebugMode($record);
    }

    /**
     * We should log if
     * - Level is debug
     * - Dev log flag is enabled in core_config_data
     *      - OR the per process flag is set
     *
     * @param array $record
     * @return bool
     */
    private function isHandlingDebugMode(array $record): bool // @phpstan-ignore-line
    {
        // @phpstan-ignore-next-line | expects array{level: 100|200|250|300|400|500|550|600}, array given.
        $isHandling = parent::isHandling($record);
        if (!$this->isVerboseFlag->isVerbose()) {
            // We do not have any per process logging to handle so just return parent
            return $isHandling;
        }
        if ($isHandling) {
            // If true we are at the right log level, and we have debug logging enabled outright
            return $isHandling;
        }

        // Check with the base debug handler as it does the check for record level
        // And we know this is a verbose request by the first check in this function so if we are the right log level
        // we should handle this debug log
        // @phpstan-ignore-next-line | expects array{level: 100|200|250|300|400|500|550|600}, array given.
        return $this->baseLogHandler->isHandling($record);
    }

    /**
     * We should log if
     * - Level is debug
     * - the per process flag is set
     *
     * This allows us to use this mechanism of the handler to add very granular/verbose logging for systems which
     * already have debug logging active, we wouldnt wan't to install this in one of those systems and flood the
     * existing debug.log file with GB of data
     *
     * @param array $record
     * @return bool
     */
    private function isHandlingVerboseDebugMode(array $record): bool  // @phpstan-ignore-line
    {
        // @phpstan-ignore-next-line | expects array{level: 100|200|250|300|400|500|550|600}, array given.
        return ($this->isVerboseFlag->isVerbose() && $this->baseLogHandler->isHandling($record));
    }
}
