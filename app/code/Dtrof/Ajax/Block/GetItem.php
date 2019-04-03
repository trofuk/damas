<?php
namespace Dtrof\Ajax\Block;

use Magento\Framework\App\Filesystem\DirectoryList;
/**
 * Created by PhpStorm.
 * User: maxim
 * Date: 27.07.16
 * Time: 11:07
 */
class GetItem extends \Magento\Framework\View\Element\Template
{

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    protected $imageBuilder;
    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $_imageHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Filesystem\Io\File $ioFile,
        \Amasty\Storelocator\Helper\Data $dataHelper,
        \Magento\Framework\Registry $registry,
        array $data
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_objectManager = $objectManager;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_imageHelper = $imageHelper;
        $this->_filesystem = $context->getFilesystem();
        $this->imageBuilder = $imageBuilder;
        $this->_jsonEncoder = $jsonEncoder;
        $this->_ioFile = $ioFile;
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
        $this->dataHelper = $dataHelper;
    }

    public function getProductById($productId)
    {
        $product = $this->_objectManager->get('Magento\Catalog\Model\Product')->load($productId);
        return $product;
    }

}