<?php


namespace Dtrof\Catalog\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $directoryList;
    protected $_categoryFactory;
    /**
     * Resource model of config data
     *
     * @var \Magento\Framework\App\Config\ConfigResource\ConfigInterface
     */
    protected $_resource;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\App\Config\ConfigResource\ConfigInterface $_resource,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory
    ) {
        parent::__construct($context);
        $this->directoryList = $directoryList;
        $this->_resource = $_resource;
        $this->_categoryFactory = $categoryFactory;
    }

    public function getCurrentWebsite()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->create('\Magento\Store\Model\StoreManagerInterface');
        return $storeManager->getStore()->getWebsiteId();
    }

    public function categoryIds($category_id)
    {
        $category = $this->_categoryFactory->create();
        $category->load($category_id);
        $array = array();
        $items = $this->getParentTree($category_id, array($category_id));
        $array[$items] = $items;
        return $array;
    }

    public function getParentTree($category_id, $ids = array())
    {
        $parent_id = intval($this->setCategory($category_id)->getParentId());
        if($parent_id > 0) {
            $ids[] = $parent_id;
            return $this->getParentTree($parent_id, $ids);
        }
        $ids = array_reverse($ids);
        if(isset($ids[1])) unset($ids[1]);
        if(isset($ids[0])) unset($ids[0]);
        $ids = $this->reIndexArray($ids);
        return reset($ids);
    }

    public function reIndexArray($array)
    {
        $reArray = array();
        if(!empty($array)) {
            foreach ($array as $item) {
                $reArray[] = $item;
            }
        }
        return $reArray;
    }

    public function setCategory($category_id)
    {
        $category = $this->_categoryFactory->create();
        $category->load($category_id);
        return $category;
    }

}
