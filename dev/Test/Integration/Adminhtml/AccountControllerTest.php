<?php
declare(strict_types=1);
namespace Ampersand\VerboseLogRequest\Test\Integration\Adminhtml;

use Magento\TestFramework\Helper\Bootstrap;

class AccountControllerTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    public function testNotAllowed()
    {
        $allowedEmails = Bootstrap::getObjectManager()
            ->get(\Ampersand\VerboseLogRequest\Service\Adminhtml\AllowedEmails::class);
        $allowedEmails->__construct([], []); // no allowed emails or domains
        $this->dispatch('backend/admin/system_account/index');
        $this->assertStringContainsString('My Account', $this->getResponse()->getBody());
        $this->assertStringNotContainsString('Get Verbose Log Key', $this->getResponse()->getBody());
    }

    public function testAllowedEmail()
    {
        $emails = [
            'someother@email.com',
            // https://github.com/AmpersandHQ/magento-docker-test-instance/blob/ec8b3cf09d286e19f01b40e3a09d5e17d65a7edc/Dockerfile-assets/magento-install.sh#L109
            'admin@example.com',
            'another@example.com',
        ];
        $allowedEmails = Bootstrap::getObjectManager()
            ->get(\Ampersand\VerboseLogRequest\Service\Adminhtml\AllowedEmails::class);
        $allowedEmails->__construct($emails, []);
        $this->dispatch('backend/admin/system_account/index');
        $this->assertStringContainsString('My Account', $this->getResponse()->getBody());
        $this->assertStringContainsString('Get Verbose Log Key', $this->getResponse()->getBody());
    }

    public function testAllowedDomain()
    {
        $domains = [
            'email.com',
            // https://github.com/AmpersandHQ/magento-docker-test-instance/blob/ec8b3cf09d286e19f01b40e3a09d5e17d65a7edc/Dockerfile-assets/magento-install.sh#L109
            '@example.com',
            'example.com',
            'foobar.com'
        ];
        $allowedEmails = Bootstrap::getObjectManager()
            ->get(\Ampersand\VerboseLogRequest\Service\Adminhtml\AllowedEmails::class);
        $allowedEmails->__construct([], $domains);
        $this->dispatch('backend/admin/system_account/index');
        $this->assertStringContainsString('My Account', $this->getResponse()->getBody());
        $this->assertStringContainsString('Get Verbose Log Key', $this->getResponse()->getBody());
    }

    public function testAllowedEmailAndDomain()
    {
        $emails = [
            'someother@email.com',
            // https://github.com/AmpersandHQ/magento-docker-test-instance/blob/ec8b3cf09d286e19f01b40e3a09d5e17d65a7edc/Dockerfile-assets/magento-install.sh#L109
            'admin@example.com',
            'another@example.com',
        ];
        $domains = [
            'email.com',
            // https://github.com/AmpersandHQ/magento-docker-test-instance/blob/ec8b3cf09d286e19f01b40e3a09d5e17d65a7edc/Dockerfile-assets/magento-install.sh#L109
            '@example.com',
            'example.com',
            'foobar.com'
        ];

        $allowedEmails = Bootstrap::getObjectManager()
            ->get(\Ampersand\VerboseLogRequest\Service\Adminhtml\AllowedEmails::class);
        $allowedEmails->__construct($emails, $domains);
        $this->dispatch('backend/admin/system_account/index');
        $this->assertStringContainsString('My Account', $this->getResponse()->getBody());
        $this->assertStringContainsString('Get Verbose Log Key', $this->getResponse()->getBody());
    }
}
