<?php
declare(strict_types=1);
namespace Ampersand\VerboseLogRequest\Test\Unit\Service;

use Ampersand\VerboseLogRequest\Service\GetKey;
use Magento\Framework\App\DeploymentConfig;
use PHPUnit\Framework\TestCase;

class GetKeyTest extends TestCase
{
    /**
     * @var GetKey
     */
    private GetKey $getKey;

    /**
     * @var DeploymentConfig|\PHPUnit\Framework\MockObject\MockObject
     */
    private DeploymentConfig $deploymentConfig;

    /**
     *
     */
    protected function setUp(): void
    {
        $this->deploymentConfig = $this->createMock(DeploymentConfig::class);
        $this->getKey = new GetKey($this->deploymentConfig);
    }

    public function testDeploymentConfigIsNotAvailable()
    {
        $this->deploymentConfig->expects($this->once())
            ->method('isAvailable')
            ->willReturn(false);
        $this->assertFalse($this->getKey->execute());
    }

    public function testDeploymentConfigHasNoKeySet()
    {
        $this->deploymentConfig->expects($this->once())
            ->method('isAvailable')
            ->willReturn(true);
        $this->deploymentConfig->expects($this->once())
            ->method('get')
            ->with('ampersand/verbose_log_request/key', '')
            ->willReturn(false);

        $this->assertFalse($this->getKey->execute());
    }

    public function testDeploymentConfigHasEmptyKeySet()
    {
        $this->deploymentConfig->expects($this->once())
            ->method('isAvailable')
            ->willReturn(true);
        $this->deploymentConfig->expects($this->once())
            ->method('get')
            ->with('ampersand/verbose_log_request/key', '')
            ->willReturn('');

        $this->assertFalse($this->getKey->execute());
    }

    public function testDeploymentConfigHasBadDefaultDbSet()
    {
        $this->deploymentConfig->expects($this->once())
            ->method('isAvailable')
            ->willReturn(true);
        $this->deploymentConfig->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(
                [$this->equalTo('ampersand/verbose_log_request/key'), $this->equalTo('')],
                [$this->equalTo('db/connection/default'), $this->equalTo('')],
            )
            ->willReturnOnConsecutiveCalls(
                'some_key_from_deploy_config',
                'should_be_an_array'
            );

        $this->assertFalse($this->getKey->execute());
    }

    public function testDeploymentConfigHasEmptyDefaultDbSet()
    {
        $this->deploymentConfig->expects($this->once())
            ->method('isAvailable')
            ->willReturn(true);
        $this->deploymentConfig->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(
                [$this->equalTo('ampersand/verbose_log_request/key'), $this->equalTo('')],
                [$this->equalTo('db/connection/default'), $this->equalTo('')],
            )
            ->willReturnOnConsecutiveCalls(
                'some_key_from_deploy_config',
                []
            );

        $this->assertFalse($this->getKey->execute());
    }

    public function testDeploymentConfigHasKey()
    {
        $this->deploymentConfig->expects($this->once())
            ->method('isAvailable')
            ->willReturn(true);
        $this->deploymentConfig->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(
                [$this->equalTo('ampersand/verbose_log_request/key'), $this->equalTo('')],
                [$this->equalTo('db/connection/default'), $this->equalTo('')],
            )
            ->willReturnOnConsecutiveCalls(
                'some_good_key_from_deploy_config',
                ['some' => 'permutation', 'of' => 'database', 'values' => 'here']
            );

        // phpcs:ignore Magento2.Security.InsecureFunction
        $expectedKey = md5(
            date("Y-m-d-H") .
            json_encode(['some' => 'permutation', 'of' => 'database', 'values' => 'here']) .
            'some_good_key_from_deploy_config'
        );
        $this->assertEquals($expectedKey, $this->getKey->execute());
    }
}
