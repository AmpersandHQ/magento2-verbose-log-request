<?php
declare(strict_types=1);

namespace Ampersand\VerboseLogRequest\Test\Integration;

use Ampersand\VerboseLogRequest\Service\IsVerbose\Storage;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Logger\Handler\System as SystemLogHandler;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class LogsWrittenTest extends TestCase
{
    /** @var LoggerInterface */
    private $logger;

    /** @var LoggerInterface */
    private $verboseLogger;

    /** @var \Magento\Framework\DB\Adapter\AdapterInterface  */
    private $connection;

    /** @var string */
    private $logDir = '';

    /**
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\RuntimeException
     */
    protected function setUp(): void
    {
        /** @var \Magento\TestFramework\ObjectManager $objectManager */
        $objectManager = Bootstrap::getObjectManager();

        $this->connection = Bootstrap::getObjectManager()->get(ResourceConnection::class)->getConnection();
        $this->logger = $objectManager->get(LoggerInterface::class);

        /**
         * Construct the verbose logger with its hander
         *
         * It's a virtual type so needs instantiated separately
         *
         * @see \Magento\TestFramework\Application::initLogger()
         */
        $this->verboseLogger = $objectManager->create(
            \Ampersand\VerboseLogRequest\Logger\VerboseDebugLogger::class,
            [
                'name' => 'integration-tests-verbose-logger',
                'handlers' => [
                    'system' => $objectManager->create(
                        \Magento\Framework\Logger\Handler\System::class,
                        [
                            'exceptionHandler' => $objectManager->create(
                                \Magento\Framework\Logger\Handler\Exception::class,
                                ['filePath' => Bootstrap::getInstance()->getAppTempDir()]
                            ),
                            'filePath' => Bootstrap::getInstance()->getAppTempDir()
                        ]
                    ),
                    'debug'  => $objectManager->create(
                        \Ampersand\VerboseLogRequest\Logger\Handler\Debug::class,
                        ['filePath' => Bootstrap::getInstance()->getAppTempDir()]
                    ),
                ]
            ]
        );

        foreach ($this->logger->getHandlers() as $handler) {
            if ($handler instanceof SystemLogHandler) {
                $this->logDir = dirname($handler->getUrl());
                break;
            }
        }

        $this->setDeployConfigDevDebugLogging(0);
        Storage::reset(true);
    }

    /**
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\RuntimeException
     * @throws \ReflectionException
     */
    protected function tearDown(): void
    {
        $this->setDatabaseLoggingAlias('disabled');
        $this->setDeployConfigDevDebugLogging(null);
        Storage::reset(true);
    }

    /**
     *
     */
    public function testDebugLogsAreDisabledWhenMagentoFlagIsDisabled()
    {
        $debugLogMessage = uniqid('some_debug_log_entry_', true);
        $systemLogMessage =  uniqid('some_system_log_entry_', true);
        $this->logger->debug($debugLogMessage);
        $this->logger->info($systemLogMessage);

        $this->assertStringContainsString(
            $systemLogMessage,
            $this->getLineFromLog('system.log', $systemLogMessage)
        );
        $this->assertLogFileDoesNotContain('system.log', $debugLogMessage);
        $this->assertLogFileDoesNotContain('debug.log', $debugLogMessage);
    }

    /**
     * @depends testDebugLogsAreDisabledWhenMagentoFlagIsDisabled
     */
    public function testDebugLogsAreEnabledWhenMagentoFlagIsEnabled()
    {
        $this->setDeployConfigDevDebugLogging(1);
        $debugLogMessage = uniqid('some_debug_log_entry_', true);
        $systemLogMessage =  uniqid('some_system_log_entry_', true);
        $this->logger->debug($debugLogMessage);
        $this->logger->info($systemLogMessage);

        $this->assertStringContainsString(
            $systemLogMessage,
            $this->getLineFromLog('system.log', $systemLogMessage)
        );
        $this->assertStringContainsString(
            $debugLogMessage,
            $this->getLineFromLog('debug.log', $debugLogMessage)
        );
    }

    /**
     * @depends testDebugLogsAreDisabledWhenMagentoFlagIsDisabled
     */
    public function testDebugLogsAreWrittenWhenVerbose()
    {
        $debugLogMessage = uniqid('some_debug_log_entry_', true);
        $this->logger->debug($debugLogMessage);
        $this->assertLogFileDoesNotContain('debug.log', $debugLogMessage);

        Storage::setFlag(true, true);

        $this->logger->debug($debugLogMessage);
        $this->assertStringContainsString(
            $debugLogMessage,
            $this->getLineFromLog('debug.log', $debugLogMessage)
        );
    }

    /**
     * @depends testDebugLogsAreWrittenWhenVerbose
     */
    public function testVerboseDebugLogsAreOnlyWrittenWhenVerbose()
    {
        $debugLogMessage = uniqid('some_verbose_debug_log_entry_', true);
        $this->verboseLogger->debug($debugLogMessage);
        $this->assertLogFileDoesNotContain('debug.log', $debugLogMessage);

        Storage::setFlag(true, true);

        $this->verboseLogger->debug($debugLogMessage);

        $this->assertStringContainsString(
            $debugLogMessage,
            $this->getLineFromLog('debug.log', $debugLogMessage)
        );
    }

    /**
     * @link https://github.com/Seldaek/monolog/blob/60d9aab/src/Monolog/Logger.php#L324
     *
     * @depends testVerboseDebugLogsAreOnlyWrittenWhenVerbose
     */
    public function testNoInfiniteLoopDetected()
    {
        $this->assertLogFileDoesNotContain('debug.log', 'infinite logging loop');
        $this->assertLogFileDoesNotContain('system.log', 'infinite logging loop');
    }

    /**
     * @throws \ReflectionException
     */
    public function testDatabaseLogsAreWrittenWhenVerbose()
    {
        $this->setDatabaseLoggingAlias('file');

        $query = 'select ' . time();
        $this->connection->query($query);

        $this->assertStringContainsString(
            $query,
            $this->getLineFromLog('verbose_db.log', $query)
        );
        $this->assertStringContainsString(
            $query,
            $this->getLogFileContents('verbose_db.log', 'TRACE: #1')
        );
    }

    /**
     * @param $logfile
     * @param $doesNotContain
     */
    private function assertLogFileDoesNotContain($logfile, $doesNotContain)
    {
        $contents = $this->getLogFileContents($logfile);
        $this->assertStringNotContainsString($doesNotContain, $contents);
    }

    /**
     * @param $logfile
     * @param $logMessage
     * @return mixed|null
     */
    private function getLineFromLog($logfile, $logMessage)
    {
        $contents = $this->getLogFileContents($logfile);
        $this->assertStringContainsString($logMessage, $contents, 'Log file does not contain message');

        // Get the line containing this unique message
        $contents = array_filter(
            explode(PHP_EOL, $contents),
            function ($line) use ($logMessage) {
                return strpos($line, $logMessage) !== false;
            }
        );

        $this->assertCount(1, $contents, 'We should only a unique entry with this log message');
        $line = array_pop($contents);
        return $line;
    }

    /**
     * @param $logfile
     * @return false|string
     */
    private function getLogFileContents($logfile)
    {
        $logFile = $this->getLogFilePath($logfile);
        if (is_file($logFile)) {
            $this->assertFileExists($logFile, ' log file does not exist');
            clearstatcache(true, $logFile);
            $contents = \file_get_contents($logFile);
            return $contents;
        }
        return "$logFile does not exist" . PHP_EOL;
    }

    /**
     * @param $logfile
     * @return string
     */
    private function getLogFilePath($logfile)
    {
        return rtrim($this->logDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $logfile;
    }

    /**
     * Use reflection to set this value in the deployment configuration flat data
     *
     * @see \Magento\Developer\Model\Logger\Handler\Debug::isLoggingEnabled()
     *
     * @param $value
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\RuntimeException
     */
    private function setDeployConfigDevDebugLogging($value)
    {
        $deploymentConfig = Bootstrap::getObjectManager()->get(DeploymentConfig::class);
        $flatDeployConfigData = $deploymentConfig->get();

        $flatDeployConfigData['dev/debug/debug_logging'] = $value;
        if ($value === null) {
            unset($flatDeployConfigData['dev/debug/debug_logging']);
        }

        $deploymentConfigReflection = new \ReflectionObject($deploymentConfig);
        $flatDeployConfig = $deploymentConfigReflection->getProperty('flatData');
        $flatDeployConfig->setAccessible(true);
        $flatDeployConfig->setValue($deploymentConfig, $flatDeployConfigData);
    }

    /**
     * The database logger is so low level it would have to reboot the integration tests from scratch
     *
     * Work around it by toggling on the database logger using reflection
     *
     * @param string $alias
     * @throws \ReflectionException
     */
    private function setDatabaseLoggingAlias($alias)
    {
        $connectionReflection = new \ReflectionObject($this->connection);
        $loggerReflection = $connectionReflection->getProperty('logger');
        $loggerReflection->setAccessible(true);

        $databaseLogger = $loggerReflection->getValue($this->connection);
        $databaseLoggerReflection = new \ReflectionObject($databaseLogger);

        $loggerProperty = $aliasProperty = null;

        // Navigate parents classes to get appropriate properties
        // We have the ampersand module extending the core, but plugins can put a third layer of interception on this
        for ($i=0; $i<5; $i++) {
            $props = $databaseLoggerReflection->getProperties();
            foreach ($props as $prop) {
                if (!$aliasProperty && $prop->getName() === 'loggerAlias') {
                    $prop->setAccessible(true);
                    $aliasProperty = $prop;
                }
                if (!$loggerProperty && $prop->getName() === 'logger') {
                    $prop->setAccessible(true);
                    $loggerProperty = $prop;
                }
            }
            if (isset($loggerProperty, $aliasProperty)) {
                break;
            }
            $databaseLoggerReflection = $databaseLoggerReflection->getParentClass();
        }

        if (!isset($loggerProperty, $aliasProperty)) {
            $this->fail('Could not get reflection properties');
        }

        $loggerProperty->setValue($databaseLogger, null); // to ensure lazy load picks it up and regenerates
        $aliasProperty->setValue($databaseLogger, $alias);
    }
}
