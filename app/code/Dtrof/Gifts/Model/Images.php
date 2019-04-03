<?php namespace Dtrof\Gifts\Model;

use Dtrof\Gifts\Api\Data\ConfigurationInterface;
use Magento\Framework\DataObject\IdentityInterface;

class Images  extends \Magento\Framework\Model\AbstractModel implements ConfigurationInterface, IdentityInterface
{
    /**
     * CMS page cache tag
     */
    const CACHE_TAG = 'oyigifts_images';

    /**
     * @var string
     */
    protected $_cacheTag = 'oyigifts_images';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'oyigifts_images';

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resourceCollection;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param array $data
     */
    function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [])
    {
        $this->_resourceCollection = $resourceCollection;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);

    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Dtrof\Gifts\Model\ResourceModel\Images');
    }

    /**
     * Check if post url key exists
     * return post id if post exists
     *
     * @param string $url_key
     * @return int
     */
    public function checkUrlKey($url_key)
    {
        return $this->_getResource()->checkUrlKey($url_key);
    }

    /**
     * Prepare post's statuses.
     * Available event gifts_configuration_get_available_statuses to customize statuses.
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }
    /**
     * Return unique ID(s) for each object in system
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->getData(self::CONFIG_ID);
    }

    /**
     * Get Store ID
     *
     * @return int|null
     */
    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }

    /**
     * Get Attribute ID
     *
     * @return int|null
     */
    public function getAttributeId(){
        return $this->getData(self::ATTRIBUTE_ID);
    }

    /**
     * Get Entity ID
     *
     * @return int|null
     */
    public function getEntityId(){
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * Get Category Title
     *
     * @return string|null
     */
    public function getCategoryTitle()
    {
        return $this->getData(self::CATEGORY_NAME);
    }

    /**
     * Get Attribute Title
     *
     * @return string|null
     */
    public function getAttributeTitle()
    {
        return $this->getData(self::ATTRIBUTE_NAME);
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return \Dtrof\Gifts\Api\Data\ConfigurationInterface
     */
    public function setId($id)
    {
        return $this->setData(self::CONFIG_ID, $id);
    }

    /**
     * Set Store ID
     *
     * @param int $id
     * @return \Dtrof\Gifts\Api\Data\ConfigurationInterface
     */
    public function setStoreId($id)
    {
        return $this->setData(self::STORE_ID, $id);
    }

    /**
     * Set Attribute ID
     *
     * @param int $id
     * @return \Dtrof\Gifts\Api\Data\ConfigurationInterface
     */
    public function setAttributeId($id)
    {
        return $this->setData(self::ATTRIBUTE_ID, $id);
    }

    /**
     * Set Entity ID
     *
     * @param int $id
     * @return \Dtrof\Gifts\Api\Data\ConfigurationInterface
     */
    public function setEntityId($id){
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * Set Category Title
     *
     * @param string $title
     * @return \Dtrof\Gifts\Api\Data\ConfigurationInterface
     */
    public function setCategoryTitle($title)
    {
        return $this->setData(self::CATEGORY_NAME, $title);
    }

    /**
     * Set Attribute Title
     *
     * @param string $title
     * @return \Dtrof\Gifts\Api\Data\ConfigurationInterface
     */
    public function setAttributeTitle($id)
    {
        $attribute = $this->getAttributeById($id);
        return $this->setData(self::ATTRIBUTE_NAME, $attribute['frontend_label']);
    }

    public function getAttributeById($id)
    {
        return [
            'frontend_label' => 'Title '.$id.' - '
        ];
    }
}