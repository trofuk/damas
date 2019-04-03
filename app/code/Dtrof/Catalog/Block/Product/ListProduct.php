<?php
/**
 * Created by PhpStorm.
 * User: maxim
 * Date: 12.07.16
 * Time: 11:58
 */

namespace Dtrof\Catalog\Block\Product;

class ListProduct extends \Magento\Catalog\Block\Product\ListProduct
{

    protected $_defaultToolbarBlock = 'Dtrof\Catalog\Block\Product\ProductList\Toolbar';

    /**
     * @var
     */
    protected $_configurableAttributes;

    /**
     * @return bool
     */
    public function checkType()
    {
        if($this->getRequest()->getParam('q') != ''){
            return false;
        } else {
            return true;
        }
    }

    /**
     * Retrieve configurable attributes data
     *
     * @param  \Magento\Catalog\Model\Product $product
     * @return \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute[]
     */
    public function getMetalType($product)
    {
        $data = array();
        if($product->getTypeId() == 'configurable'){
            $a = $product->getTypeInstance()->getConfigurableAttributesAsArray($product);

            $values = array();
            $attribute = 'ring_size';
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $helper = $objectManager->create('\Magento\ConfigurableProduct\Helper\Data');
            $options = $helper->getOptions($product, $this->getAllowProducts($product));
            foreach($a as $item) {
                $values[] = $item['values'];
                if($item['attribute_code'] == $attribute) {
                    $data['label'] = $item['frontend_label'];
                    $data['id'] = $item['attribute_id'];
                    $data['code'] = $item['attribute_code'];
                    foreach($values[0] as $value) {
                        $data['options'][] = array(
                            'value' => $value['value_index'],
                            'label' => $value['label'],
                            'product_id' => (isset($options[$item['attribute_id']][$value['value_index']]))?$options[$item['attribute_id']][$value['value_index']][0]:0
                        );
                    }

                }
            }
        }

        return $data;
    }

    /**
     * @param $product
     * @param $image
     * @return string
     */
    public function getChildInfo($product,$image)
    {
        $data = array();
        $_children = $product->getTypeInstance()->getUsedProducts($product);
        foreach($_children as $item) {
            $productId = $item->getId();
            $productData = $item->getData();
            $data[$productId]['id'] =  $productData['entity_id'];
            $data[$productId]['name'] =  $productData['name'];
            $data[$productId]['description'] =  strip_tags($productData['description']);
            if(isset($productData['small_image'])) {
                $productImage = $this->getImage($item, $image);
                $data[$productId]['image'] =  $productImage->toHtml();
            }
            $data[$productId]['price'] =  $productData['price'];
            $data[$productId]['parent_id'] =  $productData['parent_id'];
        }
        return json_encode($data);
    }

    /**
     * @param $attributes
     * @param $_product
     * @return array
     */
    public function getAttributes($attributes,$_product)
    {
        $data = array();
        foreach($attributes as $attribute) {
            $_code = $attribute->getAttributeCode();
            $value = $_product->getResource()->getAttribute($_code)->getFrontend()->getValue($_product);
            if(!empty($value)) {
                $data[$_code] = [
                    'code'  => $_code,
                    'value' => $value,
                    'label' => $_product->getResource()->getAttribute($_code)->getFrontendLabel()
                ];
            }
        }
        return $data;
    }

    /**
     * @param $product
     */
    public function setProduct($product)
    {
        $this->_product = $product;
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        if (!$this->_product) {
            $this->_product = $this->_coreRegistry->registry('product');
        }
        return $this->_product;
    }

    /**
     * @param array $excludeAttr
     * @return array
     */
    public function getAdditionalData(array $excludeAttr = [])
    {
        $data = [];
        $product = $this->getProduct();
        $attributes = $product->getAttributes();
        foreach ($attributes as $attribute) {
            if ($attribute->getIsVisibleOnFront() && in_array($attribute->getAttributeCode(), $excludeAttr)) {
                $value = $attribute->getFrontend()->getValue($product);

                if (!$product->hasData($attribute->getAttributeCode())) {
                    $value = __('N/A');
                } elseif ((string)$value == '') {
                    $value = __('No');
                } elseif ($attribute->getFrontendInput() == 'price' && is_string($value)) {
                    $value = $this->priceCurrency->convertAndFormat($value);
                }

                if (is_string($value) && strlen($value)) {
                    $data[$attribute->getAttributeCode()] = [
                        'label' => __($attribute->getStoreLabel()),
                        'value' => $value,
                        'code' => $attribute->getAttributeCode(),
                    ];
                }
            }
        }
        return $data;
    }

    /**
     * Retrieve loaded category collection
     *
     * @return AbstractCollection
     */
    protected function _getProductCollection()
    {
        if ($this->_productCollection === null) {
            $layer = $this->getLayer();
            /* @var $layer \Magento\Catalog\Model\Layer */
            if ($this->getShowRootCategory()) {
                $this->setCategoryId($this->_storeManager->getStore()->getRootCategoryId());
            }

            if ($this->getRequest()->isAjax()) {
                $cat_id = $this->getRequest()->getParam('cat');
                if($cat_id > 0){
                    $this->setCategoryId($cat_id);
                }
            }
            // if this is a product view page
            if ($this->_coreRegistry->registry('product')) {
                // get collection of categories this product is associated with
                $categories = $this->_coreRegistry->registry('product')
                    ->getCategoryCollection()->setPage(1, 1)
                    ->load();
                // if the product is associated with any category
                if ($categories->count()) {
                    // show products from this category
                    $this->setCategoryId(current($categories->getIterator()));
                }
            }

            $origCategory = null;
            if ($this->getCategoryId()) {
                try {
                    $category = $this->categoryRepository->get($this->getCategoryId());
                } catch (NoSuchEntityException $e) {
                    $category = null;
                }

                if ($category) {
                    $origCategory = $layer->getCurrentCategory();
                    $layer->setCurrentCategory($category);
                }
            }
            $this->_productCollection = $layer->getProductCollection();

            $online_product = $this->getRequest()->getParam('online_product');

//            if($online_product != '') {
//                $this->_productCollection->addAttributeToFilter('online_product', $online_product);
//            }

            $this->prepareSortableFieldsByCategory($layer->getCurrentCategory());

            if ($origCategory) {
                $layer->setCurrentCategory($origCategory);
            }
        }

        return $this->_productCollection;
    }

    /**
     * Get Allowed Products
     *
     * @return array
     */
    public function getAllowProducts($prod)
    {
        if (!$this->hasAllowProducts()) {
            $products = [];
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $catalogProduct = $objectManager->create('\Magento\Catalog\Helper\Product');
            $skipSaleableCheck = $catalogProduct->getSkipSaleableCheck();
            $allProducts = $prod->getTypeInstance()->getUsedProducts($prod, null);
            foreach ($allProducts as $product) {
                if ($product->isSaleable() || $skipSaleableCheck) {
                    $products[] = $product;
                }
            }
            $this->setAllowProducts($products);
        }
        return $this->getData('allow_products');
    }

    public function getRenderedHtml($_product)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->create('\MagicToolbox\Magic360\Block\Product\View\Gallery');
        $resource->renderGalleryHtml($_product,true);
        return $resource->getRenderedHtml($_product->getId());

    }

    /**
     * Get post parameters
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getAddToCartPostParams(\Magento\Catalog\Model\Product $product)
    {
        $url = $this->getAddToCartUrl($product);
        return [
            'action' => $url,
            'data' => [
                'product' => $product->getEntityId(),
                \Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED =>
                    $this->urlHelper->getEncodedUrl($url),
            ]
        ];
    }

    public function getAddToCartUrl($product, $additional = [])
    {
        return $this->_cartHelper->getAddUrl($product, $additional);
    }

    public function getCategoryById($categoryId)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $category = $objectManager->get('Magento\Catalog\Model\Category')->load($categoryId);
        return $category;
    }

    protected function _beforeToHtml()
    {
        $toolbar = $this->getToolbarBlock();

        // called prepare sortable parameters
        $collection = $this->_getProductCollection();

        $sort = $this->getRequest()->getParam('product_list_order');
        $dir = $this->getRequest()->getParam('product_list_dir');
        $categoryId = $this->getRequest()->getParam('id');
        $category = $this->getCategoryById($categoryId);

        if($sort == '' && $dir == ''){
            if($category->getCustomSorting() == 1) {
                $collection->getSelect()->order('cat_index_position ASC');
            } else {
                $collection->getSelect()->orderRand();
            }
        }else{
            // use sortable parameters
            $orders = $this->getAvailableOrders();
            if ($orders) {
                $toolbar->setAvailableOrders($orders);
            }
            $sort = $this->getSortBy();
            if ($sort) {
                $toolbar->setDefaultOrder($sort);
            }
            $dir = $this->getDefaultDirection();
            if ($dir) {
                $toolbar->setDefaultDirection($dir);
            }
        }
        $modes = $this->getModes();
        if ($modes) {
            $toolbar->setModes($modes);
        }

        // set collection to toolbar and apply sort
        $toolbar->setCollection($collection);

        $this->setChild('toolbar', $toolbar);
        $this->_eventManager->dispatch(
            'catalog_block_product_list_collection',
            ['collection' => $this->_getProductCollection()]
        );


        $this->_getProductCollection()->load();

        return parent::_beforeToHtml();
    }
}