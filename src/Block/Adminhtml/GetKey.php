<?php
declare(strict_types=1);
namespace Ampersand\VerboseLogRequest\Block\Adminhtml;

use Ampersand\VerboseLogRequest\Service\Adminhtml\UserChecker;
use Ampersand\VerboseLogRequest\Service\GetKey as GetKeyService;
use Magento\Framework\View\Element\Template;

class GetKey extends Template
{
    /**
     * @var UserChecker
     */
    private UserChecker $userChecker;

    /**
     * @var string
     */
    private string $timestamp;

    /**
     * @var string|false
     */
    private $key;

    /**
     * @param Template\Context $context
     * @param GetKeyService $getKey
     * @param UserChecker $userChecker
     * @param mixed[] $data
     */
    public function __construct(
        Template\Context $context,
        GetKeyService $getKey,
        UserChecker $userChecker,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->userChecker = $userChecker;

        $this->key = $getKey->execute();
        $this->timestamp = date('Y-m-d H', strtotime('now +1 hour')) . ':00:00';
    }

    /**
     * Is the current admin user allowed to acces the key
     *
     * @return bool
     */
    public function isAllowed()
    {
        return $this->userChecker->isAdminUserAllowed();
    }

    /**
     * Get the current verbose log key
     *
     * @return false|string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Get the timestamp that the current key expires
     *
     * @return string
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }
}
