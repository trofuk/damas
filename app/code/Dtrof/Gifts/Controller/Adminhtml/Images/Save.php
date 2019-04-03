<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Dtrof\Gifts\Controller\Adminhtml\Images;

use Magento\Backend\App\Action;

class Save extends Action
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
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Dtrof_Gifts::save');
    }


    /**
     * Save product action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $resource = $this->_objectManager->create('\Dtrof\Gifts\Model\Upload\Image');
        $attr_resource = $this->_objectManager->create('\Dtrof\Gifts\Model\Configuration\Source\Attributes');
        $resultRedirect = $this->resultRedirectFactory->create();
        $data =  $resource->savePhoto($_FILES['img']);
        if(!empty($data)){
            $attr_resource->saveAttributeImages($data);
        }

        return $resultRedirect->setPath('*/*/index');
    }

}
