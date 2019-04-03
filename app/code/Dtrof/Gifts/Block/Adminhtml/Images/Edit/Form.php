<?php
namespace Dtrof\Gifts\Block\Adminhtml\Images\Edit;

/**
 * Adminhtml blog post edit form
 */

use Magento\Framework\App\Filesystem\DirectoryList;
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
        $this->setId('images_form');
        $this->setTitle(__('Gift Images Information'));
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
        $model = $this->_coreRegistry->registry('oyigifts_images');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post','enctype'=>'multipart/form-data']]
        );

        $attribute = $attributes->getAttribute($configuration_id);

        $fieldset = $form->addFieldset(
            $attribute['attribute_code'],
            ['legend' => $attribute['frontend_label'], 'class' => 'fieldset-wide']
        );
        //todo $options stores_id

        $options = $attributes->getAttributeOptions($attribute['attribute_id'], 0);
        $images = $attributes->getAttributeImages($attribute['attribute_id']);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->create('\Magento\Framework\Filesystem');
        $media = $resource->getDirectoryWrite(DirectoryList::MEDIA)->getAbsolutePath();

        foreach ($options as $option) {
            $img = '';
            if(isset($images[$option['option_id']])){
                if(file_exists($media.'gifts/'.$images[$option['option_id']]['image'])){
                    $img = '<img src="/pub/media/gifts/'.$images[$option['option_id']]['image'].'?_='.microtime().'" />';
                }
            }

            $fieldset->addField(
                'option_'.$option['option_id'],
                'file',
                [
                    'name' => 'img['.$attribute['attribute_id'].']['.$option['option_id'].']',
                    'label' => $option['value'],
                    'title' => $option['value'],
                    'onchange' => 'window.oyiGiftsObj.optionImagePreview(this,'.$option['option_id'].');',
                    'after_element_html' => '<div class="option-images-preview">'.$img.'</div>'
                ]
            );
        }

        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
