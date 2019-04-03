<?php
namespace Dtrof\Gifts\Controller\Adminhtml\Configuration;

use Magento\Backend\App\Action;

class Link extends \Magento\Backend\App\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var array
     */
    protected $request;

    protected $attributes;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        parent::__construct($context);
    }

    /**
     * Edit Gifts post
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->request = $this->getRequest()->getParams();
        $this->attributes = $objectManager->create('\Dtrof\Gifts\Model\Configuration\Source\Attributes');
        $link_id = $this->attributes->saveAttributeLink(array(
            "configuration_id"=>$this->request["configuration_id"],
            "attribute_id"=>$this->request["attribute_id"],
            "option_id"=>$this->request["option_id"],
            "value_id"=>$this->request["value_id"]
        ));
        $this->attributes->saveLinkTree($link_id,$this->request["parent_id"]);
        $next_id = $this->attributes->childAttribute($this->request["attribute_id"]);
        $this->_view->loadLayout();
        $response = array(
            'request' => $this->request,
            'link_id' => $link_id,
            'next_id' => $next_id,
            'content' => $this->_view->getLayout()->getBlock('root')->toHtml(),
            'last' => ($next_id == $this->request["attribute_id"]) ? 1 : 0
        );
        echo json_encode($response);
    }
}
