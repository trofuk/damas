<?php
/**
 * Created by PhpStorm.
 * User: maxim
 * Date: 12.07.16
 * Time: 11:06
 */

namespace Dtrof\Catalog\Block\Product\ProductList;

use \Magento\Catalog\Block\Product\ProductList\Toolbar as SpToolbar;
use Magento\Catalog\Model\Product\ProductList\Toolbar as ToolbarModel;
use Magento\Catalog\Helper\Product\ProductList;

/**
 * Product search result block
 */
class Toolbar extends SpToolbar
{
    public function getOnlineStatus($value)
    {
        $res = 0;
        $value = intval($value);
        $curent = $this->getRequest()->getParam('online_product');
        if($curent != ''){
            $active = explode(',',$curent);
            if(!empty($active)) {
                if(in_array($value,$active)) {
                    $res = 1;
                }
            }
        }

        return $res;
    }

    public function getCurentUrl($value)
    {
        $url = $this->setUrl();
        if($value > 0){
            $value = intval($value);
            $a = substr($url, -5);
            $curent = $this->getRequest()->getParam('online_product');
            if($curent != '') {
                if($value != $curent) {
                    if($a == '.html') {
                        $url .= '?online_product=' . $value;
                    } else {
                        $url .= '&online_product=' . $value;
                    }
                }
            } else {
                if($a == '.html') {
                    $url .= '?online_product=' . $value;
                } else {
                    $url .= '&online_product=' . $value;
                }
            }
        }

        return $url;
    }

    public function setUrl()
    {
        $url = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? "https://" : "http://";
        $url .= $_SERVER['HTTP_HOST'];
        $curent = $this->getRequest()->getParam('online_product');
        if($curent != ''){
            $request_uri = $_SERVER['REQUEST_URI'];
            $re = "/(online_product=([0-9]+))/";
            if(preg_match($re, $request_uri, $matches)){
                $request_uri = str_replace($matches[1],'',$request_uri);
                $request_uri = str_replace('&&','&',$request_uri);
            }
            $url .= $request_uri;
            if(substr($url, -1) == '&') {
                $url = rtrim($url,'&');
            }
            if(substr($url, -1) == '?') {
                $url = rtrim($url,'?');
            }
        }

        return $url;
    }

    /**
     * Return products collection instance
     *
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getCollection()
    {
        return $this->_collection;
    }

    /**
     * Get specified products limit display per page
     *
     * @return string
     */
    public function getLimit()
    {
        $limit = $this->_getData('_current_limit');
        if ($limit) {
            return $limit;
        }

        if($this->getRequest()->getParam('q') == ''){
            $limits = $this->getAvailableLimit();
        }else{
            $limits = [8=>8,15=>15,30=>30];
        }

        $defaultLimit = $this->getDefaultPerPageValue();
        if (!$defaultLimit || !isset($limits[$defaultLimit])) {
            $keys = array_keys($limits);
            $defaultLimit = $keys[0];
        }

        $limit = $this->_toolbarModel->getLimit();
        if (!$limit || !isset($limits[$limit])) {
            $limit = $defaultLimit;
        }

        if ($limit != $defaultLimit) {
            $this->_memorizeParam('limit_page', $limit);
        }

        $this->setData('_current_limit', $limit);
        return $limit;
    }

    /**
     * Set collection to pager
     *
     * @param \Magento\Framework\Data\Collection $collection
     * @return $this
     */
    public function setCollection($collection)
    {
        $this->_collection = $collection;

        $this->_collection->setCurPage($this->getCurrentPage());

        // we need to set pagination only if passed value integer and more that 0
        $limit = (int)$this->getLimit();
        if ($limit) {
            $this->_collection->setPageSize($limit);
        }
        if ($this->getCurrentOrder()) {
            $this->_collection->setOrder($this->getCurrentOrder(), $this->getCurrentDirection());
        }
        return $this;
    }

    /**
     * Render pagination HTML
     *
     * @return string
     */
    public function getPagerHtml()
    {
        $pagerBlock = $this->getChildBlock('product_list_toolbar_pager');

        if ($pagerBlock instanceof \Magento\Framework\DataObject) {
            /* @var $pagerBlock \Dtrof\Theme\Block\Html\Pager */
            $pagerBlock->setAvailableLimit($this->getAvailableLimit());

            $pagerBlock->setUseContainer(
                false
            )->setShowPerPage(
                false
            )->setShowAmounts(
                false
            )->setFrameLength(
                $this->_scopeConfig->getValue(
                    'design/pagination/pagination_frame',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                )
            )->setJump(
                $this->_scopeConfig->getValue(
                    'design/pagination/pagination_frame_skip',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                )
            )->setLimit(
                $this->getLimit()
            )->setCollection(
                $this->getCollection()
            );

            return $pagerBlock->toHtml();
        }

        return '';
    }

    /**
     * @return int
     */
    public function getTotalNum()
    {
        return $this->getCollection()->getSize();
    }

    /**
     * Set default Order field
     *
     * @param string $field
     * @return $this
     */
    public function setDefaultOrder($field)
    {
        $this->loadAvailableOrders();
        if(isset($this->_availableOrder['position'])){
            unset($this->_availableOrder['position']);
        }
        if (isset($this->_availableOrder[$field])) {
            $this->_orderField = $field;
        }
        return $this;
    }

    /**
     * Load Available Orders
     *
     * @return $this
     */
    private function loadAvailableOrders()
    {
        if ($this->_availableOrder === null) {
            $this->_availableOrder = $this->_catalogConfig->getAttributeUsedForSortByArray();
        }
        return $this;
    }
}