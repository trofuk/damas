<?php
namespace Dtrof\Gifts\Model\ResourceModel;

/**
 * Blog post mysql resource
 */
class Images extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{


    /**
     * Construct
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param string|null $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        $resourcePrefix = null
    ) {
        parent::__construct($context, $resourcePrefix);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('oyi_gifts_attributes', 'id');
    }

}