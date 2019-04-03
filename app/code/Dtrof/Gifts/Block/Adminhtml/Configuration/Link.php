<?php
namespace Dtrof\Gifts\Block\Adminhtml\Configuration;

class Link extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    protected $attributes;
    protected $request;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->attributes = $objectManager->create('\Dtrof\Gifts\Model\Configuration\Source\Attributes');
    }

    /**
     * Initialize blog post edit block
     *
     * @return void
     */
    protected function _construct()
    {
//        $this->_objectId = 'id';
        $this->_blockGroup = 'Dtrof_Gifts';
        $this->_controller = 'adminhtml_configuration';
        $this->request = $this->getRequest()->getParams();
        parent::_construct();
    }

    public function getAttributes()
    {
        $next_id = $this->attributes->childAttribute($this->request["attribute_id"]);
        $attribute = $this->attributes->getAttributeById($next_id);
        if($next_id == $this->request["attribute_id"]) {
            $link_id = $this->request["parent_id"];
        } else {
            $link_id = $this->attributes->saveAttributeLink(array(
                "configuration_id"=>$this->request["configuration_id"],
                "attribute_id"=>$this->request["attribute_id"],
                "option_id"=>$this->request["option_id"],
                "value_id"=>$this->request["value_id"]
            ));
        }
        $attribute['options'] = $this->attributes->getAttributeOptions($next_id, $this->request["store_id"]);
        foreach($attribute['options'] as $key=>$option) {
            $attribute['options'][$key] = $option;
            $attribute['options'][$key]['class'] = $this->attributes->checkSelected($this->request["configuration_id"], $attribute["attribute_id"], $option['option_id'], $option['value_id'], $link_id);
            $attribute['options'][$key]['link_id'] = $this->attributes->checkAttributeLink([
                'configuration_id' => $this->request["configuration_id"],
                'attribute_id' => $attribute["attribute_id"],
                'option_id' => $option["option_id"],
                'value_id' => $option["value_id"]
            ]);
            $attribute['options'][$key]['parent_link_id'] = $link_id;
        }
        $attribute['request'] = $this->request;
        if($next_id == $this->request["attribute_id"]) {
            $parent_id = $this->attributes->getParentId($this->request["attribute_id"]);
            $attribute['parent'] = $this->attributes->getAttributeCode($parent_id);
        } else {
            $attribute['parent'] = $this->attributes->getAttributeCode($this->request["attribute_id"]);
        }
        $attribute['selected_name'] = ucfirst($this->attributes->getOptionValue($link_id));
        return $attribute;
    }

}
