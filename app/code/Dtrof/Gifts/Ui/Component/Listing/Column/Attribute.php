<?php
namespace Dtrof\Gifts\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

class Attribute extends Column
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
                $a = $connection->fetchRow("SELECT `frontend_label` FROM `eav_attribute` WHERE `attribute_id` = ".(int)$item['attribute_id']." LIMIT 1");
                $item[$name] = $a['frontend_label'];
            }
        }
        return $dataSource;
    }
}