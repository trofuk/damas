<?php
namespace Dtrof\Ajax\Controller\QuickView;
/**
 * Created by PhpStorm.
 * User: maxim
 * Date: 27.07.16
 * Time: 11:34
 */
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\NoSuchEntityException;


class Product extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Product constructor.
     * @param Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Load the page defined in view/frontend/layout/samplenewpage_index_index.xml
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if ($this->getRequest()->isAjax()) {
            $this->_view->loadLayout();
            $content = $this->_view->getLayout()->getBlock('root')->toHtml();
            $this->getResponse()->setBody($content);
        } else {
            echo 'error - not ajax request';
        }
    }
    
}