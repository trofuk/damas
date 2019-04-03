<?php
/**
 * Created by PhpStorm.
 * User: coder
 * Date: 23.08.16
 * Time: 14:57
 */
namespace Dtrof\Catalog\Block\Product\ProductList;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\View\Element\AbstractBlock;

/**
 * Catalog product related items block
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Related extends \Magento\Catalog\Block\Product\ProductList\Related
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
            if($online_product != '') {
                $this->_productCollection->addAttributeToFilter('online_product', $online_product);
            }

            $this->prepareSortableFieldsByCategory($layer->getCurrentCategory());

            if ($origCategory) {
                $layer->setCurrentCategory($origCategory);
            }
        }

        return $this->_productCollection;
    }

    public function getRenderedHtml($_product)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->create('\MagicToolbox\Magic360\Block\Product\View\Gallery');
        $resource->renderGalleryHtml($_product,true);
        return $resource->getRenderedHtml($_product->getId());

    }
}