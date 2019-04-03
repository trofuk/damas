<?php
namespace Dtrof\Ajax\Block;

use Magento\Framework\App\Filesystem\DirectoryList;
/**
 * Created by PhpStorm.
 * User: maxim
 * Date: 27.07.16
 * Time: 11:07
 */
class Location extends \Magento\Framework\View\Element\Template
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

    protected $_httpRequest;

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
        \Magento\Framework\HTTP\PhpEnvironment\Request $httpRequest,
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
        $this->_httpRequest = $httpRequest;
        parent::__construct($context, $data);
        $this->dataHelper = $dataHelper;
    }

    public function getClientLocation()
    {

        $positions = array();
        $post = $this->getRequest()->getPostValue();
        if(!empty($post)){
            $positions['to'] = $post;
            $ip = $this->_httpRequest->getClientIp();
            $geoIpModel = $this->_objectManager->create('Amasty\Geoip\Model\Geolocation');
            $geodata = $geoIpModel->location($ip);
            $lat = $geodata->getLatitude();
            $lng = $geodata->getLongitude();

            if(!empty($lat) && !empty($lng)){
                $positions['from'] = array(
                    'lat' =>$lat,
                    'lng' => $lng
                );
            }
        }

        return $positions;
    }

}