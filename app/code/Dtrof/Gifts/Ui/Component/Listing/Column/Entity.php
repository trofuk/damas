<?php
namespace Dtrof\Gifts\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

class Entity extends Column
{
    protected $model;
    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     * @param string $editUrl
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Dtrof\Gifts\Model\Configuration $configuration,
        \Magento\Framework\App\ResourceConnection $resource,
        array $components = [],
        array $data = []
    ) {
        $this->model = $resource;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                $connection = $this->model->getConnection();
                $a = $connection->fetchRow("
                    SELECT `value`
                    FROM `catalog_category_entity_varchar`
                    WHERE `attribute_id` = 42
                    AND `store_id` = ".$item['store_id']."
                    AND `entity_id` = ".$item['entity_id']." LIMIT 1");
                $item[$name] = $a['value'];
            }
        }
        return $dataSource;
    }
}