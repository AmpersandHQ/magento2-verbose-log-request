<?php
declare(strict_types=1);
namespace Ampersand\VerboseLogRequest\Plugin\Adminhtml;

use Ampersand\VerboseLogRequest\Service\Adminhtml\UserChecker;
use Magento\Backend\Block\Widget\Button\ButtonList;
use Magento\Backend\Block\Widget\Button\ToolbarInterface;
use Magento\Framework\View\Element\AbstractBlock;

/**
 * Plugin for \Magento\Backend\Block\Widget\Button\Toolbar.
 */
class ToolbarPlugin
{
    /**
     * @var UserChecker
     */
    private UserChecker $userChecker;

    /**
     * @param UserChecker $userChecker
     */
    public function __construct(
        UserChecker $userChecker
    ) {
        $this->userChecker = $userChecker;
    }

    /**
     * Add a 'Get Verbose Log Key' to the admin account toolbar when it is allowed for that user, based on the email
     *
     * @param ToolbarInterface $subject
     * @param AbstractBlock $context
     * @param ButtonList $buttonList
     *
     * @see \Magento\LoginAsCustomerAdminUi\Plugin\Button\ToolbarPlugin
     */
    public function beforePushButtons(
        ToolbarInterface $subject,
        AbstractBlock $context,
        ButtonList $buttonList
    ): void {
        if ($context->getNameInLayout() !== 'adminhtml.system.account.edit') {
            return;
        }
        if (!$this->userChecker->isAdminUserAllowed()) {
            return;
        }

        $buttonList->add(
            'get_verbose_log_key',
            [
                'label' => __('Get Verbose Log Key'),
                'onclick' => 'setLocation(\'' . $context->getUrl('ampersandverboselogrequest/key/get') . '\')',
                'class' => 'primary'
            ],
            -1
        );
    }
}
