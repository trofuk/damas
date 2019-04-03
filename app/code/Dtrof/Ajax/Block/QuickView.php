<?php
namespace Dtrof\Ajax\Block;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Catalog\Helper\ImageFactory as HelperFactory;
/**
 * Created by PhpStorm.
 * User: maxim
 * Date: 27.07.16
 * Time: 11:07
 */
class QuickView extends \Magento\Framework\View\Element\Template
{

    protected $_product;
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

    /**
     * @var \Magento\Checkout\Helper\Cart
     */
    protected $_cartHelper;

    /**
     * @var \Magento\Framework\Locale\FormatInterface
     */
    protected $_localeFormat;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Dtrof\Catalog\Block\Product\ListProduct
     */
    protected $listProductBlock;

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
        \Magento\Checkout\Helper\Cart $cartHelper,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Dtrof\Catalog\Block\Product\ListProduct $listProductBlock,
        HelperFactory $helperFactory,
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
        $this->helperFactory = $helperFactory;
        $this->_cartHelper = $cartHelper;
        $this->_localeFormat = $localeFormat;
        $this->_jsonEncoder = $jsonEncoder;
        $this->priceCurrency = $priceCurrency;
        $this->listProductBlock = $listProductBlock;
    }

    public function setProductById()
    {
        $productId = (int)$this->getRequest()->getParam('id');
        $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($productId);
        $this->setProduct($product);
    }

    /**
     * Set product object
     *
     * @param Product $product
     * @return \Magento\Catalog\Block\Product\View\Options
     */
    public function setProduct(\Magento\Catalog\Model\Product $product = null)
    {
        $this->_product = $product;
        return $this;
    }

    public function getProduct()
    {
        return $this->_product;
    }



    /**
     * Retrieve product image
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $imageId
     * @param array $attributes
     * @return \Magento\Catalog\Block\Product\Image
     */
    public function getImage($product, $imageId, $attributes = [])
    {
        return $this->imageBuilder->setProduct($product)
            ->setImageId($imageId)
            ->setAttributes($attributes)
            ->create();
    }

    /**
     * @param $product
     * @return mixed
     */
    public function getProductGalleryImages($product)
    {
        $images = $product->getMediaGalleryImages();
        if ($images instanceof \Magento\Framework\Data\Collection) {
            foreach ($images as $image) {
                /* @var \Magento\Framework\DataObject $image */
                $image->setData(
                    'small_image_url',
                    $this->_imageHelper->init($product, 'product_page_image_small')
                        ->setImageFile($image->getFile())
                        ->getUrl()
                );
                $image->setData(
                    'medium_image_url',
                    $this->_imageHelper->init($product, 'product_page_image_medium')
                        ->constrainOnly(true)->keepAspectRatio(true)->keepFrame(false)
                        ->setImageFile($image->getFile())
                        ->getUrl()
                );
                $image->setData(
                    'large_image_url',
                    $this->_imageHelper->init($product, 'product_page_image_large')
                        ->constrainOnly(true)->keepAspectRatio(true)->keepFrame(false)
                        ->setImageFile($image->getFile())
                        ->getUrl()
                );
            }
        }

        return $images;
    }

    /**
     * @param $product
     * @return mixed|string
     */
    public function getGalleryImages($product)
    {
        $imagesItems = [];
        $images = $this->getProductGalleryImages($product);
        foreach ($images as $image) {
            $imagesItems[] = [
                'thumb' => $image->getData('small_image_url'),
                'img' => $image->getData('medium_image_url'),
                'full' => $image->getData('large_image_url'),
                'caption' => $image->getLabel(),
                'position' => $image->getPosition(),
                'isMain' => $this->isMainImage($image,$product),
            ];
        }
        if (empty($imagesItems)) {
            $imagesItems[] = [
                'thumb' => $this->_imageHelper->getDefaultPlaceholderUrl('thumbnail'),
                'img' => $this->_imageHelper->getDefaultPlaceholderUrl('image'),
                'full' => $this->_imageHelper->getDefaultPlaceholderUrl('image'),
//                'caption' => '',
//                'position' => '0',
//                'isMain' => true,
            ];
        }
//        return json_encode($imagesItems);
        return $imagesItems;
    }

    /**
     * @param $image
     * @param $product
     * @return bool
     */
    public function isMainImage($image,$product)
    {
        return $product->getImage() == $image->getFile();
    }

    public function getAttributes($product)
    {
        $data = array();
        if($product->getTypeId() == 'configurable') {
            $a = $product->getTypeInstance()->getConfigurableAttributesAsArray($product);

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $helper = $objectManager->create('\Magento\ConfigurableProduct\Helper\Data');
            $options = $helper->getOptions($product, $this->getAllowProducts($product));
            $attribute = 'size';
            foreach ($a as $item) {
                if($item['attribute_code'] == $attribute) {
                    if(!empty($item['values'])){
                        foreach($item['values'] as $key=>$val){
                            $item['values'][$key]['product_id'] = (isset($options[$item['attribute_id']][$val['value_index']]))?$options[$item['attribute_id']][$val['value_index']][0]:0;
                        }
                    }
                    $data[] = array(
                        'label' => $item['label'],
                        'attribute_id' => $item['attribute_id'],
                        'attribute_code' => $item['attribute_code'],
                        'options' => $item['values'],
                    );
                }
            }
        }
        return $data;
    }

    public function getAllowProducts($prod)
    {
        if (!$this->hasAllowProducts()) {
            $products = [];
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $catalogProduct = $objectManager->create('\Magento\Catalog\Helper\Product');
            $skipSaleableCheck = $catalogProduct->getSkipSaleableCheck();
            $allProducts = $prod->getTypeInstance()->getUsedProducts($prod, null);
            foreach ($allProducts as $product) {
                if ($product->isSaleable() || $skipSaleableCheck) {
                    $products[] = $product;
                }
            }
            $this->setAllowProducts($products);
        }
        return $this->getData('allow_products');
    }

    /**
     * Return wishlist widget options
     *
     * @return array
     */
    public function getWishlistOptions($product)
    {
        return ['productType' => $product->getTypeId()];
    }

    public function getImageData($product, $imageId)
    {
        $this->product = $product;
        $this->imageId = $imageId;
        $a = $this->imageBuilder->create();
        return $a->getData();
    }

    /**
     * Whether redirect to cart enabled
     *
     * @return bool
     */
    public function isRedirectToCartEnabled()
    {
        return $this->_scopeConfig->getValue(
            'checkout/cart/redirect_to_cart',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieves url for form submitting.
     *
     * Some objects can use setSubmitRouteData() to set route and params for form submitting,
     * otherwise default url will be used
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $additional
     * @return string
     */
    public function getSubmitUrl($product, $additional = [])
    {
        $submitRouteData = $this->getData('submit_route_data');
        if ($submitRouteData) {
            $route = $submitRouteData['route'];
            $params = isset($submitRouteData['params']) ? $submitRouteData['params'] : [];
            $submitUrl = $this->getUrl($route, array_merge($params, $additional));
        } else {
            $submitUrl = $this->getAddToCartUrl($product, $additional);
        }
        return $submitUrl;
    }

    /**
     * Retrieve url for add product to cart
     * Will return product view page URL if product has required options
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $additional
     * @return string
     */
    public function getAddToCartUrl($product, $additional = [])
    {
        if ($product->getTypeInstance()->hasRequiredOptions($product)) {
            if (!isset($additional['_escape'])) {
                $additional['_escape'] = true;
            }
            if (!isset($additional['_query'])) {
                $additional['_query'] = [];
            }
            $additional['_query']['options'] = 'cart';

            return $this->getProductUrl($product, $additional);
        }
        return $this->_cartHelper->getAddUrl($product, $additional);
    }

    /**
     * Retrieve Product URL using UrlDataObject
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $additional the route params
     * @return string
     */
    public function getProductUrl($product, $additional = [])
    {
        if ($this->hasProductUrl($product)) {
            if (!isset($additional['_escape'])) {
                $additional['_escape'] = true;
            }
            return $product->getUrlModel()->getUrl($product, $additional);
        }

        return '#';
    }

    /**
     * Check Product has URL
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function hasProductUrl($product)
    {
        if ($product->getVisibleInSiteVisibilities()) {
            return true;
        }
        if ($product->hasUrlDataObject()) {
            if (in_array($product->hasUrlDataObject()->getVisibility(), $product->getVisibleInSiteVisibilities())) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get JSON encoded configuration array which can be used for JS dynamic
     * price calculation depending on product options
     *
     * @return string
     */
    public function getJsonConfig($product)
    {
        /* @var $product \Magento\Catalog\Model\Product */
//        $product = $this->getProduct();

        if (!$this->hasOptions($product)) {
            $config = [
                'productId' => $product->getId(),
                'priceFormat' => $this->_localeFormat->getPriceFormat()
            ];
            return $this->_jsonEncoder->encode($config);
        }

        $tierPrices = [];
        $tierPricesList = $product->getPriceInfo()->getPrice('tier_price')->getTierPriceList();
        foreach ($tierPricesList as $tierPrice) {
            $tierPrices[] = $this->priceCurrency->convert($tierPrice['price']->getValue());
        }
        $config = [
            'productId' => $product->getId(),
            'priceFormat' => $this->_localeFormat->getPriceFormat(),
            'prices' => [
                'oldPrice' => [
                    'amount' => $this->priceCurrency->convert(
                        $product->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue()
                    ),
                    'adjustments' => []
                ],
                'basePrice' => [
                    'amount' => $this->priceCurrency->convert(
                        $product->getPriceInfo()->getPrice('final_price')->getAmount()->getBaseAmount()
                    ),
                    'adjustments' => []
                ],
                'finalPrice' => [
                    'amount' => $this->priceCurrency->convert(
                        $product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue()
                    ),
                    'adjustments' => []
                ]
            ],
            'idSuffix' => '_clone',
            'tierPrices' => $tierPrices
        ];

        $responseObject = new \Magento\Framework\DataObject();
        $this->_eventManager->dispatch('catalog_product_view_config', ['response_object' => $responseObject]);
        if (is_array($responseObject->getAdditionalOptions())) {
            foreach ($responseObject->getAdditionalOptions() as $option => $value) {
                $config[$option] = $value;
            }
        }

        return $this->_jsonEncoder->encode($config);
    }

    /**
     * Return true if product has options
     *
     * @return bool
     */
    public function hasOptions($product)
    {
        if ($product->getTypeInstance()->hasOptions($product)) {
            return true;
        }
        return false;
    }

    public function getAddToCartPostParams($product)
    {
        return $this->listProductBlock->getAddToCartPostParams($product);
    }

    public function getForm()
    {
        return $this->getChildBlock('product_info')->toHtml();
    }
}