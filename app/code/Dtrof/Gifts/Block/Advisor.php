<?php
namespace Dtrof\Gifts\Block;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\ResultFactory;
/**
 * Created by PhpStorm.
 * User: maxim
 * Date: 27.07.16
 * Time: 11:07
 */
class Advisor extends \Magento\Framework\View\Element\Template
{

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    protected $imageBuilder;
    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $_imageHelper;

    protected $eavConfig;
    protected $attributes;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Filesystem\Io\File $ioFile,
        \Amasty\Storelocator\Helper\Data $dataHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Eav\Model\Config $eavConfig,
        array $data
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_objectManager = $objectManager;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_imageHelper = $imageHelper;
        $this->_filesystem = $context->getFilesystem();
        $this->imageBuilder = $imageBuilder;
        $this->_jsonEncoder = $jsonEncoder;
        $this->_ioFile = $ioFile;
        $this->_coreRegistry = $registry;
        $this->attributes = $this->_objectManager->get('Dtrof\Gifts\Model\Configuration\Source\Attributes');
        parent::__construct($context, $data);
        $this->dataHelper = $dataHelper;
        $this->eavConfig = $eavConfig;
    }

    public function getProductById($productId)
    {
        $product = $this->_objectManager->get('Magento\Catalog\Model\Product')->load($productId);
        return $product;
    }

    public function getAttribute($configuration_id,$attribute_id,$parent_id)
    {
        $parent_link_id = '';
        $attribute = $this->attributes->getAttributeById($attribute_id);
        $attribute['options'] = $this->attributes->getSelectedAttributeOptions($configuration_id, $attribute['attribute_id'], $parent_id);
        $attribute['next_id'] = $this->attributes->childAttribute($attribute['attribute_id']);
        if($attribute_id == $attribute['next_id']) {
            $attribute['last'] = 1;
        } else {
            $attribute['last'] = 0;
        }
        if(empty($attribute['options'])){
            $parent_link_id = (int)$this->getRequest()->getParam('parent_id', false);
        }
        $attribute['configuration_id'] = $configuration_id;
        $nextAttribute = $this->attributes->getAttributeById($attribute['next_id']);
        $attribute['next_code'] = $nextAttribute['attribute_code'];
        if(!empty($attribute['options'])){
            foreach ($attribute['options'] as $key=>$option) {
                $parent_link_id = $option['parent_link'];
                $count = $this->getItemsCount($option,$parent_link_id);

                if($count == 0){
                    unset($attribute['options'][$key]);
                    continue;
                }
                $attribute['options'][$key] = $option;
                if($option['image'] == '') {
                    $attribute['options'][$key]['image'] = '/pub/media/gifts/noimage.png';
                } else {
                    $attribute['options'][$key]['image'] = '/pub/media/gifts/'.$option['image'];
                }

            }
        }
        $params = [];
        if(!empty($parent_link_id)){
            $params = $this->attributes->getReturnParams($parent_link_id);
        }
        $next_step = $this->getRequest()->getParam('next_step');
        $back = $this->getRequest()->getParam('back');
        $attribute['next_step'] = $next_step;

        if($attribute['next_step'] != '' && !$back){
            $attribute['next_step'].=',';
        }
        if(!$back){
            $attribute['next_step'] .= $parent_link_id;
        }
        $return_step = '';
        if($attribute['next_step'] != ''){
            $step_arr = explode(',',$attribute['next_step']);
            if(count($step_arr) > 1){
                unset($step_arr[count($step_arr)-1]);
                if(!empty($step_arr)){
                    $return_step = implode(',',$step_arr);
                }
            }
        }
        $attribute['view_url'] = $this->getViewAllUrl($attribute['next_step']);
        if($back){
            $attribute['next_step'] = $return_step;
        }


        $attribute['return_url'] = $this->getReturnUrl($params,$attribute['configuration_id']);
        if($attribute['next_step'] != ''){
            $attribute['return_url'] .= '&back=true';
        }
        if($return_step != ''){
            $attribute['return_url'] .= '&next_step='.$return_step;
        }
        $attribute['view_base_url'] =$this->getCategoryUrl();
        $attribute['return_parent_id'] = (isset($params['parent_link']))?$params['parent_link']:'';

        return $attribute;
    }

    public function getItemsCount($option,$pid)
    {
        $categoryId = (int)$this->getRequest()->getParam('id', false);
        $arr = [];
        if($pid > 0){
            $arr = $this->attributes->getViewAllParams($pid);

        }
        $arr[] = array(
            'attribute_code' =>strtolower($option['value']),
            'option_id' => $option['option_id'],
            'attribute_id' => $option['attribute_id'],
        );

        return $this->attributes->getAttributeItemsCount($arr,[$categoryId]);
    }

    public function getRootAttributeByEntity($id)
    {
        return $this->attributes->getRootAttributeByEntity($id);
    }

    public function getViewAllUrl($steps)
    {
        $url = '';
        if($steps != ''){
            $arr = $this->attributes->getViewAllParams($steps);
            if(!empty($arr)){
//                $arr = array_reverse($arr);
                foreach($arr as $key=>$val){
                    $str = ($key==0)?'?':'&';
                    $url.=$str.''.$val['attribute_code'].'='.$val['option_id'];
                }
            }
        }

        return $url;
    }

    public function getReturnUrl($params,$configuration_id)
    {
        $url = '/gifts/advisor/steppopup';
        $categoryId = (int)$this->getRequest()->getParam('id', false);
        if(isset($params['parent_link'])){
            $url .='?id='.$categoryId.'&configuration_id='.$configuration_id.'&parent_id='.$params['parent_link'].'&attribute_id='.$params['attribute_id'];
        }

        return $url;
    }

    public function getFirstStepCategories()
    {
        $attributes = [];
        $attributes['frontend_label'] = 'For Whom';
        $attributes['last'] = 0;
        $category = $this->_objectManager->get('Magento\Catalog\Model\Category')->load(97); // gifts category id
        $children = $category->getChildrenCategories();
        $x = 0;
        foreach($children as $item){
            $att = $this->getRootAttributeByEntity($item->getId());
            $_category = $this->_objectManager->get('Magento\Catalog\Model\Category')->load($item->getId());
            $attributes['options'][$x] = [
                'configuration_id' => $att['id'],
                'link_id' => 0,
                'next_id' => $att['attribute_id'],
                'root_cat' => $item->getId(),
                'value' => $_category->getName()
            ];
            $x++;
        }

        return $attributes;
    }

    public function getCategoryUrl()
    {
        $categoryId = (int)$this->getRequest()->getParam('id', false);
        if($categoryId > 0){
            $category = $this->_objectManager->get('Magento\Catalog\Model\Category')->load($categoryId);

            return $category->getUrl();
        }
        return false;
    }
    public function getCategoryName()
    {
        $categoryId = (int)$this->getRequest()->getParam('id', false);
        if($categoryId > 0){
            $category = $this->_objectManager->get('Magento\Catalog\Model\Category')->load($categoryId);

            return $category->getName();
        }
        return false;
    }


    public function getOneAttribute($code)
    {
        $attribute = $this->eavConfig->getAttribute('catalog_product', $code);
        return $attribute->getSource()->getAllOptions();
    }

    public function getOptionName($code,$id)
    {
        $attribute = $this->eavConfig->getAttribute('catalog_product', $code);
        return $attribute->getSource()->getOptionText($id);
    }

    /**
     * Retrieve current product model
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function setCategoryTv($category)
    {
        if (!$this->_coreRegistry->registry('category')) {
            $this->_coreRegistry->register('category', $category);
        }
        return $this->_coreRegistry->registry('category');
    }

    public function currentUrlParams()
    {
        $query = $_SERVER['QUERY_STRING'];
        $url = '';
        $relId = (int)$this->getRequest()->getParam('relationship');
        if($relId > 0) {
            if($url != '') {
                $url .= '&';
            }
            $url .= 'relationship='.$relId;
        }
        $occId = (int)$this->getRequest()->getParam('occasions');
        if($occId > 0) {
            if($url != '') {
                $url .= '&';
            }
            $url .= 'occasions='.$occId;
        }
        $stId = (int)$this->getRequest()->getParam('styles');
        if($stId > 0) {
            if($url != '') {
                $url .= '&';
            }
            $url .= 'styles='.$stId;
        }
        $url = '?'.$url.'&';

        $relationship = $url;
        $occasions = $url;
        $styles = $url;
        $re = "/relationship=\\d+/";
        $relationship = preg_replace($re, '', $url);
        $relationship = str_replace('?&', '?',$relationship);
        $relationship = str_replace('&&', '&',$relationship);
        $re = "/occasions=\\d+/";
        $occasions = preg_replace($re, '', $url);
        $occasions = str_replace('?&', '?',$occasions);
        $occasions = str_replace('&&', '&',$occasions);
        $re = "/styles=\\d+/";
        $styles = preg_replace($re, '', $url);
        $styles = str_replace('?&', '?',$styles);
        $styles = str_replace('&&', '&',$styles);

        $url = str_replace('&&', '&',$url);

        return array(
            'url' => $url,
            'relationship' => $relationship,
            'occasions' => $occasions,
            'styles' => $styles
        );
    }

    public function createNextUrl($params,$attributes,$val)
    {
        $url = '';
        if(!empty($params)){
            $url .= '?id=' .  $params['id'];
        }else{
            $url .= '?id=' .  $val['root_cat'];
        }
        $url .= '&configuration_id=' . $val['configuration_id'];
        $url .= '&parent_id=' . $val['link_id'];
        if(isset($attributes['next_id'])){
            $url .= '&attribute_id=' . $attributes['next_id'];
        }else{
            $url .= '&attribute_id=' . $val['next_id'];
        }
        if(isset($attributes['next_step'])){
            $url .= '&next_step=' . $attributes['next_step'];
        }

        return $url;
    }
}