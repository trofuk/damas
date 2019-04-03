<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Product options block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Dtrof\Catalog\Block\Product\View;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Options extends \Magento\Catalog\Block\Product\View\Options
{
    /**
     * @var
     */
    protected $productId;

    public function setProductId()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->productId = (int)$this->getRequest()->getParam('id');
        $product = $objectManager->create('Magento\Catalog\Model\Product')->load($this->productId);
        $this->setProduct($product);
    }

}
