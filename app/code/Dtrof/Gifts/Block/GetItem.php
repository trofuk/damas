<?php
namespace Dtrof\Gifts\Block;

use Magento\Framework\App\Filesystem\DirectoryList;
/**
 * Created by PhpStorm.
 * User: maxim
 * Date: 27.07.16
 * Time: 11:07
 */
class GetItem extends \Magento\Framework\View\Element\Template
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
    protected $request;

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
        $this->request = $this->getRequest()->getParams();
    }

    public function getProductById($productId)
    {
        $product = $this->_objectManager->get('Magento\Catalog\Model\Product')->load($productId);
        return $product;
    }

    public function getRootAttribute($cat_id)
    {
        $rootAttribute = $this->attributes->getRootAttributeByEntity($cat_id);
        $attribute = array();
        if(!empty($rootAttribute)){
            $attribute = $this->attributes->getAttributeById($rootAttribute['attribute_id']);
            $attribute['options'] = $this->attributes->getSelectedAttributeOptions($rootAttribute['id'], $attribute['attribute_id'], 0);
            $attribute['next_id'] = $this->attributes->childAttribute($attribute['attribute_id']);
            $attribute['configuration_id'] = $rootAttribute['id'];
            $nextAttribute = $this->attributes->getAttributeById($attribute['next_id']);
            $attribute['next_code'] = $nextAttribute['attribute_code'];
            $attribute['selected'] = 0;
            if(isset($this->request[$attribute['attribute_code']])) {
                $attribute['selected'] = $this->request[$attribute['attribute_code']];
            }
            foreach ($attribute['options'] as $key=>$option) {
                $attribute['options'][$key] = $option;
                if($option['image'] == '') {
                    $attribute['options'][$key]['image'] = '/pub/media/gifts/noimage.png';
                } else {
                    $attribute['options'][$key]['image'] = '/pub/media/gifts/'.$option['image'];
                }
            }
            $attribute['url'] = $this->getParamsUrl($attribute['attribute_id'],$attribute['attribute_code']);
        }

        return $attribute;
    }

    public function getAttribute($attribute_id, $configuration_id)
    {
        $parent_id = $this->attributes->getParentId($attribute_id);
        $parentAttribute = $this->attributes->getAttributeById($parent_id);
        $parent_option_id = $this->request[$parentAttribute['attribute_code']];
        $parent_link_id = $this->attributes->getLinkId($configuration_id, $parent_id, $parent_option_id);
        $attribute = $this->attributes->getAttributeById($attribute_id);
        $attribute['options'] = $this->attributes->getSelectedAttributeOptions($configuration_id, $attribute['attribute_id'], $parent_link_id);
        $attribute['next_id'] = $this->attributes->childAttribute($attribute['attribute_id']);
        $attribute['configuration_id'] = $configuration_id;
        $nextAttribute = $this->attributes->getAttributeById($attribute['next_id']);
        $attribute['next_code'] = $nextAttribute['attribute_code'];
        $attribute['selected'] = 0;
        if(isset($this->request[$attribute['attribute_code']])) {
            $attribute['selected'] = $this->request[$attribute['attribute_code']];
        }
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
        $attribute['url'] = $this->getParamsUrl($attribute_id, $attribute['attribute_code']);
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

    public function getParamsUrl($attribute_id = 0, $attribute_code = '')
    {
        $params = $this->request;
        if($attribute_code != '') {
            unset($params[$attribute_code]);
        }
        $children = $this->attributes->getAttributeChildrenCode($attribute_id);
        $items = [];
        foreach($params as $key=>$value ){
            if($key != 'id' &&
                $key != 'cat' &&
                $key != 'isAjax' &&
                $key != '_' &&
                !in_array($key, $children)) {
                $items[] = $key.'='.$value;
            }
        }
        $url = '?'.implode('&',$items);
        $url = str_replace('?&','?',$url);
        $url = str_replace('&&','&',$url);
        return $url;
    }
    public function getParamsUrlNotTree($attribute_code = '')
    {
        $params = $this->request;
        if($attribute_code != '') {
            unset($params[$attribute_code]);
        }
        $items = [];
        foreach($params as $key=>$value ){
            if($key != 'id' &&
                $key != 'cat' &&
                $key != 'isAjax' &&
                $key != '_') {
                $items[] = $key.'='.$value;
            }
        }
        $url = '?'.implode('&',$items);
        $url = str_replace('?&','?',$url);
        $url = str_replace('&&','&',$url);
        return $url;
    }

    public function getChild()
    {
        $categoryId = (int)$this->getRequest()->getParam('id', false);
        // TODO переробити для ’oyi_gifts_configuration’
        $category = $this->_objectManager->get('Magento\Catalog\Model\Category')->load(97); // gifts category id
        $children = $category->getChildrenCategories();
        $child = array();$x = 0;
        foreach($children as $item){
            $_category = $this->_objectManager->get('Magento\Catalog\Model\Category')->load($item->getId());
            $child[$x] = $item->getData();
            $child[$x]['image'] = $_category->getImageUrl();
            $x++;
        }

        return $child;
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

    public function checkAttributeId()
    {
        $params = $this->getRequest()->getParams();
        $gifts_attributes = $this->attributes->getGiftsAttributes();
        $selected = [];$attributes = [];
        if(!empty($params)){
            foreach($params as $key=>$param){
                if(isset($gifts_attributes[$key])){
                    $selected[] = [
                        'code' => $key,
                        'id' => $gifts_attributes[$key]
                    ];
                }
            }

            if(!empty($selected)){

                $is_root = $this->attributes->checkIsRootAttribute($selected[0]['id']);
                if(count($is_root) > 0){
                    $attributes[0] = $this->getAttributeInfo($selected[0]['id'],$selected[0]['code']);
                }else{
                    $param = (int)$this->getRequest()->getParam($selected[0]['code']);
                    $attributes = $this->getAttributeInfoReverse($param);
                    $attributes = array_reverse($attributes);
                    $attributes[count($attributes)] = $this->getAttributeInfo($selected[0]['id'],$selected[0]['code']);
                }
            }
        }

        return $attributes;
    }

    public function getAttributeInfo($attribute_id,$attribute_code)
    {
        $categoryId = (int)$this->getRequest()->getParam('id', false);
        $category = $this->_objectManager->get('Magento\Catalog\Model\Category')->load($categoryId); // gifts category id
        $cat_ids = $category->getAllChildren(true);
        $attributes = $this->attributes->getAttributeById($attribute_id);

        $attributes['options'] = $this->getOneAttribute($attribute_id);
        if(!empty($attributes['options'])){
            foreach($attributes['options'] as $k => $option){

                $isset = $this->attributes->checkIssetAttributeInAdvisor($option['value']);
                if(count($isset) == 0){
                    unset($attributes['options'][$k]);
                    continue;
                }

                $count = $this->attributes->getAttributeItemsCount([[
                    'attribute_code' =>strtolower($attribute_code),
                    'option_id' => $option['value'],
                    'attribute_id' => $attribute_id
                ]],$cat_ids);
                if(empty($option['value']) || $count == 0){
                    unset($attributes['options'][$k]);
                    continue;
                }
                $img = $this->attributes->getAttributeImages($option['value']);
                if(isset($img['image'])){
                    if($img['image'] == '') {
                        $attributes['options'][$k]['image'] = '/pub/media/gifts/noimage.png';
                    } else {
                        $attributes['options'][$k]['image'] = '/pub/media/gifts/'.$img['image'];
                    }
                }else{
                    $attributes['options'][$k]['image'] = '/pub/media/gifts/noimage.png';
                }
                $attributes['url'] = $this->getParamsUrlNotTree($attribute_code);
                $attributes['selected'] = 0;
                if(isset($this->request[$attribute_code])) {
                    $attributes['selected'] = $this->request[$attribute_code];
                }
            }
        }

        return $attributes;
    }

    public function getAttributeInfoReverse($param)
    {
        $links = $this->giftsRecursion($param);
        $attributes = [];
        if(!empty($links)){
            foreach($links as $k=>$option){
                if(!isset($attributes[$option['attribute_id']]['attribute_id'])){
                    $attributes[$option['attribute_id']] = $this->attributes->getAttributeById($option['attribute_id']);
                    $attributes[$option['attribute_id']]['url'] = $this->getParamsUrlNotTree($attributes[$option['attribute_id']]['attribute_code']);
                    $attributes[$option['attribute_id']]['selected'] = 0;
                    if(isset($this->request[$attributes[$option['attribute_id']]['attribute_code']])) {
                        $attributes[$option['attribute_id']]['selected'] = $this->request[$attributes[$option['attribute_id']]['attribute_code']];
                    }
                }
                $attributes[$option['attribute_id']]['options'][$k] = [
                    'label' => $option['label'],
                    'value' =>$option['value']
                ];
                $img = $this->attributes->getAttributeImages($option['value']);
                if(isset($img['image'])){
                    if($img['image'] == '') {
                        $attributes[$option['attribute_id']]['options'][$k]['image'] = '/pub/media/gifts/noimage.png';
                    } else {
                        $attributes[$option['attribute_id']]['options'][$k]['image'] = '/pub/media/gifts/'.$img['image'];
                    }
                }else{
                    $attributes[$option['attribute_id']]['options'][$k]['image'] = '/pub/media/gifts/noimage.png';
                }

            }
        }

        return $attributes;
    }

    public function giftsRecursion($option_id, $arr =[])
    {
        $array = $this->attributes->getAllLinksByOptionId($option_id);
        if(!empty($array)) {
            $a = [];
            foreach($array as $item){
                $arr[$item['value']]['value'] = $item['value'];
                $arr[$item['value']]['attribute_id'] = $item['attribute_id'];
                $arr[$item['value']]['label'] = $item['label'];
                $a = $this->giftsRecursion($item['value'],$arr);
            }
            return $a;
        } else {
            return $arr;
        }
    }

}