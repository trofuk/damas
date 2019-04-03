<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Dtrof\Catalog\Block\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;

/**
 * Product View block
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class View extends \Magento\Catalog\Block\Product\View
{

    protected $productId;

    public function setProductId()
    {
        $this->productId = (int)$this->getRequest()->getParam('id');
    }

    /**
     * Retrieve current product model
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        if (!$this->_coreRegistry->registry('product') && $this->productId) {
            $product = $this->productRepository->getById($this->productId);
            $this->_coreRegistry->register('product', $product);
        }
        return $this->_coreRegistry->registry('product');
    }
}
