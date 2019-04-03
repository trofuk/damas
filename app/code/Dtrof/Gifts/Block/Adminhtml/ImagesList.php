<?php
namespace Dtrof\Gifts\Block\Adminhtml;

use Dtrof\Gifts\Api\Data\ConfigurationInterface;
use Dtrof\Gifts\Model\ResourceModel\Configuration\Collection as ConfigurationCollection;

class ImagesList extends \Magento\Framework\View\Element\Template implements
    \Magento\Framework\DataObject\IdentityInterface
{
    /**
     * @var \Dtrof\Gifts\Model\ResourceModel\Configuration\CollectionFactory
     */
    protected $_postCollectionFactory;

    /**
     * Construct
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Dtrof\Gifts\Model\ResourceModel\Configuration\CollectionFactory $postCollectionFactory,
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Dtrof\Gifts\Model\ResourceModel\Configuration\CollectionFactory $postCollectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_postCollectionFactory = $postCollectionFactory;
    }

//    /**
//     * @return \Dtrof\Gifts\Model\ResourceModel\Configuration\Collection
//     */
//    public function getConfigurations()
//    {
//        // Check if posts has already been defined
//        // makes our block nice and re-usable! We could
//        // pass the 'posts' data to this block, with a collection
//        // that has been filtered differently!
//        if (!$this->hasData('configurations')) {
//            $posts = $this->_postCollectionFactory
//                ->create()
//                ->addFilter('is_active', 1)
//                ->addOrder(
//                    ConfigurationInterface::CREATION_TIME,
//                    ConfigurationCollection::SORT_ORDER_DESC
//                );
//            $this->setData('configurations', $posts);
//        }
//        return $this->getData('configurations');
//    }
//
//    /**
//     * Return identifiers for produced content
//     *
//     * @return array
//     */
    public function getIdentities()
    {
        return [\Dtrof\Gifts\Model\Images::CACHE_TAG . '_' . 'list'];
    }

}