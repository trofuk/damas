<?php

namespace Dtrof\Ajax\Controller\Location;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class Data extends Action
{
    /** @var \Magento\Framework\View\Result\PageFactory  */
    protected $resultPageFactory;
    protected $_httpRequest;
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\HTTP\PhpEnvironment\Request $httpRequest
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_httpRequest = $httpRequest;
        parent::__construct($context);
    }

    /**
     * Load the page defined in view/frontend/layout/samplenewpage_index_index.xml
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if ($this->getRequest()->isAjax()) {
            $positions = array();

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

            echo  json_encode($positions);
        } else {
            echo 'error - not ajax request';
        }
    }

}