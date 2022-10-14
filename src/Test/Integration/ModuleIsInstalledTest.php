<?php
declare(strict_types=1);

namespace Ampersand\VerboseLogRequest\Test\Integration;

use Ampersand\VerboseLogRequest\Service\GetKey;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Module\ModuleListInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class ModuleIsInstalledTest extends TestCase
{
    public function testModuleIsInstalled()
    {
        $moduleNames = Bootstrap::getObjectManager()->get(ModuleListInterface::class)->getNames();
        $this->assertContains('Ampersand_VerboseLogRequest', $moduleNames);
    }

    public function testKeyWasGenerated()
    {
        $deploymentConfig = Bootstrap::getObjectManager()->get(DeploymentConfig::class);
        $existingConfigValue = $deploymentConfig->get(GetKey::DEPLOYMENT_CONFIG_KEY_PATH);
        $this->assertIsString(
            $existingConfigValue,
            'ampersand/verbose_log_request/key should be a string'
        );
        $this->assertTrue(
            strlen($existingConfigValue) > 0,
            'ampersand/verbose_log_request/key should be a non-empty string'
        );
        $this->assertTrue(
            strlen($existingConfigValue) >= 32,
            'ampersand/verbose_log_request/key should be a 32 char string'
        );
    }
}
