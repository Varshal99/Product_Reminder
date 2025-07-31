<?php

declare(strict_types=1);

namespace Product\Reminder\Controller\Adminhtml\EmailLog;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\View\Result\Page;

/**
 * Class Index
 *
 * Controller for displaying the Product Reminder Email Log grid in the Magento Admin Panel.
 */
class Index extends Action
{
    /**
     * Authorization level of a basic admin session
     */
    public const ADMIN_RESOURCE = 'Product_Reminder::email_log';

    /**
     * @var PageFactory
     */
    protected PageFactory $resultPageFactory;

    /**
     * Index constructor.
     *
     * @param Context $context Application context
     * @param PageFactory $resultPageFactory Result page factory
     */
    public function __construct(Context $context, PageFactory $resultPageFactory)
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Execute method to render the Email Log grid page.
     *
     * @return Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Product_Reminder::email_log');
        $resultPage->getConfig()->getTitle()->prepend(__('Product Reminder Email Log'));
        return $resultPage;
    }
}
