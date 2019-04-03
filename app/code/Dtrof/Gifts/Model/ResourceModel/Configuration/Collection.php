<?php namespace Dtrof\Gifts\Model\ResourceModel\Configuration;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
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
        $this->_init('Dtrof\Gifts\Model\Configuration', 'Dtrof\Gifts\Model\ResourceModel\Configuration');
//        $this->join('attribute_code','main_table.attribute_id = attribute_code.attribute_id','frontend_label');
    }

}