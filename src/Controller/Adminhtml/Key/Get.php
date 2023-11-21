<?php
declare(strict_types=1);
namespace Ampersand\VerboseLogRequest\Controller\Adminhtml\Key;

use Ampersand\VerboseLogRequest\Service\Adminhtml\UserChecker;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;

class Get extends Action implements HttpGetActionInterface
{
    /**
     * @var PageFactory
     */
    private PageFactory $pageFactory;

    /**
     * @var Redirect
     */
    protected Redirect $resultRedirect;

    /**
     * @var UserChecker
     */
    private UserChecker $userChecker;

    /**
     * @param Context $context
     * @param UserChecker $userChecker
     * @param PageFactory $pageFactory
     * @param Redirect $redirect
     */
    public function __construct(
        Context $context,
        UserChecker $userChecker,
        PageFactory $pageFactory,
        Redirect $redirect
    ) {
        parent::__construct($context);
        $this->userChecker = $userChecker;
        $this->pageFactory = $pageFactory;
        $this->resultRedirect = $redirect;
    }

    /**
     * Display the Verbose Log Request Key to the allowed admin user
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if (!$this->userChecker->isAdminUserAllowed()) {
            return $this->resultRedirect->setPath('admin/dashboard/index');
        }

        $resultPage = $this->pageFactory->create();
        // @phpstan-ignore-next-line
        $resultPage->getConfig()->getTitle()->prepend(__('Ampersand Verbose Log Request Key'));
        return $resultPage;
    }
}
