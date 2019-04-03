<?php
namespace Dtrof\Gifts\Controller\Adminhtml\Configuration;

use Magento\Backend\App\Action;

class Remove extends \Magento\Backend\App\Action
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
        $status_id = $this->attributes->removeAttributeLink(array(
            "link_id"=>$this->request["link_id"],
            "parent_link"=>$this->request["parent_link_id"]
        ));
        $response = array(
            'status' => $status_id
        );
        echo json_encode($response);
    }
}
