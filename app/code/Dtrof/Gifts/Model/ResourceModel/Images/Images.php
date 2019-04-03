<?php namespace Dtrof\Gifts\Model\ResourceModel\Images;

class Images extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Dtrof\Gifts\Model\Images', 'Dtrof\Gifts\Model\ResourceModel\Images');
    }

}