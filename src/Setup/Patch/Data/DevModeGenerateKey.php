<?php
declare(strict_types=1);
namespace Ampersand\VerboseLogRequest\Setup\Patch\Data;

use Ampersand\VerboseLogRequest\Service\GetKey;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\App\State;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\Config\Data\ConfigData;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Math\Random;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class DevModeGenerateKey implements DataPatchInterface
{
    /**
     * @var State
     */
    private State $state;

    /**
     * @var DeploymentConfig
     */
    private DeploymentConfig $deploymentConfig;

    /**
     * @var Writer
     */
    private Writer $deploymentConfigWriter;

    /**
     * @var Random
     */
    private Random $random;

    /**
     * @param State $state
     * @param DeploymentConfig $deploymentConfig
     * @param Writer $deploymentConfigWriter
     * @param Random $random
     */
    public function __construct(
        State $state,
        DeploymentConfig $deploymentConfig,
        Writer $deploymentConfigWriter,
        Random $random
    ) {
        $this->state = $state;
        $this->deploymentConfig = $deploymentConfig;
        $this->deploymentConfigWriter = $deploymentConfigWriter;
        $this->random = $random;
    }

    /**
     * Generate ampersand/verbose_log_request/key when installing locally for the first time
     *
     * This can then be committed so we have a key usable straight away
     * Can be manually overridden in env.php by someone with server access to that environment file
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\RuntimeException
     * @return \Magento\Framework\Setup\Patch\DataPatchInterface|void
     */
    public function apply()
    {
        if ($this->state->getMode() !== State::MODE_DEVELOPER) {
            return; // Do not run this key generation on non developer machines
        }

        $existingConfigValue = $this->deploymentConfig->get(GetKey::DEPLOYMENT_CONFIG_KEY_PATH);
        if (is_string($existingConfigValue) && strlen($existingConfigValue)) {
            return; // We have a key and it already exists
        }

        /**
         * The following is based on the same checks and mechanisms in
         * @see \Magento\EncryptionKey\Model\ResourceModel\Key\Change::changeEncryptionKey
         */
        if (!$this->deploymentConfigWriter->checkIfWritable()) {
            throw new LocalizedException(__('Deployment configuration file is not writable.'));
        }

        // md5() here is not for cryptographic use.
        // phpcs:ignore Magento2.Security.InsecureFunction
        $key = md5($this->random->getRandomString(ConfigOptionsListConstants::STORE_KEY_RANDOM_STRING_SIZE));

        $segment = new ConfigData(ConfigFilePool::APP_CONFIG);
        $segment->set(GetKey::DEPLOYMENT_CONFIG_KEY_PATH, $key);

        $configData = [$segment->getFileKey() => $segment->getData()];
        $this->deploymentConfigWriter->saveConfig($configData);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getAliases()
    {
        return [];
    }
}
