<?php
declare(strict_types=1);
namespace Ampersand\VerboseLogRequest\Service\Adminhtml;

class AllowedEmails
{
    /** @var string[] */
    private array $allowedEmails = [];

    /** @var string[] */
    private array $allowedDomains = [];

    /**
     * @param string[] $allowedEmails
     * @param string[] $allowedDomains
     */
    public function __construct(array $allowedEmails = [], array $allowedDomains = [])
    {
        $this->allowedEmails = $allowedEmails;
        $this->allowedDomains = $allowedDomains;
    }

    /**
     * Return the list of allowed email addreses as defined in the di system
     *
     * @return array|string[]
     */
    public function getAllowedEmails()
    {
        return $this->allowedEmails;
    }

    /**
     * Return the list of allowed email domains as defined in the di system
     *
     * @return array|string[]
     */
    public function getAllowedDomains()
    {
        return $this->allowedDomains;
    }
}
