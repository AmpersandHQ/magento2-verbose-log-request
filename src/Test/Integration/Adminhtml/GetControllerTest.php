<?php
declare(strict_types=1);
namespace Ampersand\VerboseLogRequest\Test\Integration\Adminhtml;

use Magento\TestFramework\Helper\Bootstrap;

class GetControllerTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    public function testNotAllowed()
    {
        $allowedEmails = Bootstrap::getObjectManager()
            ->get(\Ampersand\VerboseLogRequest\Service\Adminhtml\AllowedEmails::class);
        $allowedEmails->__construct([], []); // no allowed emails or domains
        $this->dispatch('backend/ampersandverboselogrequest/key/get');
        $this->assertRedirect($this->stringContains('admin/dashboard/index'));
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
        $this->dispatch('backend/ampersandverboselogrequest/key/get');
        $this->assertStringContainsString('The current key is', $this->getResponse()->getBody());
        $this->assertStringContainsString('The current key will expire at', $this->getResponse()->getBody());
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
        $this->dispatch('backend/ampersandverboselogrequest/key/get');
        $this->assertStringContainsString('The current key is', $this->getResponse()->getBody());
        $this->assertStringContainsString('The current key will expire at', $this->getResponse()->getBody());
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
        $this->dispatch('backend/ampersandverboselogrequest/key/get');
        $this->assertStringContainsString('The current key is', $this->getResponse()->getBody());
        $this->assertStringContainsString('The current key will expire at', $this->getResponse()->getBody());
    }
}
