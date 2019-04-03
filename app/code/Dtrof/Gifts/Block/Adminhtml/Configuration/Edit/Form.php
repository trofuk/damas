<?php
namespace Dtrof\Gifts\Block\Adminhtml\Configuration\Edit;

/**
 * Adminhtml blog post edit form
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Init form
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('configuration_form');
        $this->setTitle(__('Configuration Information'));
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $configuration_id = $this->getRequest()->getParam('id');

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $attributes = $objectManager->create('\Dtrof\Gifts\Model\Configuration\Source\Attributes');

        /** @var \Dtrof\Gifts\Model\Configuration $model */
        $model = $this->_coreRegistry->registry('oyigifts_configuration');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );

        $rootAttribute = $attributes->getRootAttribute($configuration_id);

        $fieldset = $form->addFieldset(
            $rootAttribute['attribute_code'],
            ['legend' => $rootAttribute['frontend_label'].' - '.$attributes->getEntityName($rootAttribute['store_id'], $rootAttribute['entity_id']), 'class' => 'fieldset-wide']
        );
        $attribute = $attributes->getAttributeByCode($rootAttribute['attribute_code']);
        $options = $attributes->getAttributeOptions($attribute['attribute_id'], $rootAttribute['store_id']);
        $next_id = $attributes->childAttribute($attribute['attribute_id']);
        $nextAttribute = $attributes->getAttributeById($next_id);

        $next_step = $nextAttribute['attribute_code'];

        foreach ($options as $option) {
            $class = $attributes->checkSelected($configuration_id, $rootAttribute['attribute_id'], $option['option_id'], $option['value_id'], 0);
            $link_id = $attributes->checkAttributeLink([
                'configuration_id' => $configuration_id,
                'attribute_id' => $rootAttribute["attribute_id"],
                'option_id' => $option["option_id"],
                'value_id' => $option["value_id"]
            ]);
            $parent_link_id = 0;

            $after_element_html = '<button id="'.$rootAttribute['attribute_code'].'_'.$option['value_id'].'"
                    onclick="window.oyiGiftsObj.showNextAttribute('.$rootAttribute['store_id'].','.$configuration_id.','.$rootAttribute['attribute_id'].','.$option['option_id'].','.$option['value_id'].',\''.$rootAttribute['attribute_code'].'\',\'\');"
                    class="scalable '.$class.' link_'.$link_id.'_'.$parent_link_id.'"
                    type="button"><span><span><span>'.$option['value'].'</span></span></span></button>';
            if($class != '') {
                $after_element_html .= '<button type="button"
                    class="remove_'.$link_id.'_'.$parent_link_id.' scalable '.$class.'"
                    style="margin-left: -5px; padding-left: 5px; padding-right: 5px;"
                    onclick="window.oyiGiftsObj.removeAttribute('.$link_id.','.$parent_link_id.', \''.$rootAttribute['attribute_code'].'\');">
                <span><span><span>X</span></span></span></button>';
            }
            $fieldset->addField(
                'option_'.$option['option_id'],
                'hidden',
                array(
                    'class' => 'sss',
                    'after_element_html' => $after_element_html
                )
            );
        }

        $fieldset->addField(
            'step_'.$rootAttribute['attribute_code'], 'hidden', array(
                'after_element_html' => '<div id="content_'.$rootAttribute['attribute_code'].'"></div>'
            )
        );

        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
