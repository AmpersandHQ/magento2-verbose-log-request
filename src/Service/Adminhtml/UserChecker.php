<?php
declare(strict_types=1);
namespace Ampersand\VerboseLogRequest\Service\Adminhtml;

use Magento\Backend\Model\Auth\Session as AuthSession;

class UserChecker
{
    /**
     * @var AllowedEmails
     */
    private AllowedEmails $allowedEmails;

    /**
     * @var AuthSession
     */
    private AuthSession $session;

    /**
     * @param AllowedEmails $allowedEmails
     * @param AuthSession $session
     */
    public function __construct(AllowedEmails $allowedEmails, AuthSession $session)
    {
        $this->allowedEmails = $allowedEmails;
        $this->session = $session;
    }

    /**
     * Detect whether this admin user has a permitted email address, to be able to see the verbose log key
     *
     * @return bool
     */
    public function isAdminUserAllowed()
    {
        $adminUser = $this->session->getUser();
        $email =  $adminUser ? $adminUser->getEmail() : '';

        if (in_array($email, $this->allowedEmails->getAllowedEmails(), true)) {
            return true;
        }

        foreach ($this->allowedEmails->getAllowedDomains() as $domain) {
            $endsWith = '@' . $domain;
            if (substr($email, -strlen($endsWith)) === $endsWith) {
                return true;
            }
        }
        return false;
    }
}
