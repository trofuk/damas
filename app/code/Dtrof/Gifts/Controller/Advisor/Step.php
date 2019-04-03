<?php

namespace Dtrof\Gifts\Controller\Advisor;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class Step extends Action
{
    /** @var \Magento\Framework\View\Result\PageFactory  */
    protected $resultPageFactory;

    protected $attributes;

    protected $request;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Load the page defined in view/frontend/layout/samplenewpage_index_index.xml
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->request = $this->getRequest()->getParams();
        $this->attributes = $objectManager->create('\Dtrof\Gifts\Model\Configuration\Source\Attributes');
        $attribute = $this->attributes->getAttributeById($this->request["attribute_id"]);
        $next_id = $this->attributes->childAttribute($this->request["attribute_id"]);

        $this->_view->loadLayout();
        $response = array(
            'request' => $this->request,
            'next_id' => $next_id,
            'container' => $attribute['attribute_code'],
            'last'=> ($this->request["attribute_id"] == $next_id) ? 1 : 0,
            'content' => $this->_view->getLayout()->getBlock('root')->toHtml(),
        );
        echo json_encode($response);
    }

}