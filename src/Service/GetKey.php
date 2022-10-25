<?php
declare(strict_types=1);
namespace Ampersand\VerboseLogRequest\Service;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\ConfigOptionsListConstants;

class GetKey
{
    const DEPLOYMENT_CONFIG_KEY_PATH = 'ampersand/verbose_log_request/key';

    /**
     * @var DeploymentConfig
     */
    private DeploymentConfig $deploymentConfig;

    /**
     * @param DeploymentConfig $deploymentConfig
     */
    public function __construct(DeploymentConfig $deploymentConfig)
    {
        $this->deploymentConfig = $deploymentConfig;
    }

    /**
     * Get the current key value for passing along in the header or env variable
     *
     * Uses the following to generate a MD5
     * 1. The generated value added in config.php by src/Setup/Patch/Data/DevModeGenerateKey.php
     *    (something unique per project)
     * 2. The database deploy config information (host/port/pass/etc)
     *    (something unique per environment that is not usually visible to developers)
     * 3. The current date and hour
     *    (so we have a moving target, and that we're protected against forgetting to unset the key in modheaders)
     *
     * This is not a super secret, all that it allows is the generation of dev level logging on an environment but we
     * should still make it a little bit difficult
     *
     * @return false|string
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\RuntimeException
     */
    public function execute()
    {
        if (!$this->deploymentConfig->isAvailable()) {
            return false;
        }
        $keyFromDeployConfig = $this->deploymentConfig->get(self::DEPLOYMENT_CONFIG_KEY_PATH, '');
        if (!(is_string($keyFromDeployConfig) && strlen($keyFromDeployConfig) > 0)) {
            return false;
        }
        $dbOptions = $this->deploymentConfig->get(ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTION_DEFAULT);
        if (!(is_array($dbOptions) && !empty($dbOptions))) {
            return false;
        }

        // not used for cryptographic purposes
        // phpcs:ignore Magento2.Security.InsecureFunction
        $key = md5(date("Y-m-d-H") . json_encode($dbOptions) . $keyFromDeployConfig);
        return $key;
    }
}
