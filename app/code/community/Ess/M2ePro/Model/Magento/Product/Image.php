<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Magento_Product_Image
{
    protected $_url;
    protected $_path;

    protected $_hash;

    protected $_storeId = 0;
    protected $_area    = Mage_Core_Model_App_Area::AREA_FRONTEND;

    //########################################

    public function __construct($url, $path = null)
    {
        $this->_url  = $url;
        $this->_path = $path;
    }

    //########################################

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->_url;
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->_url = $url;
        return $this;
    }

    //----------------------------------------

    /**
     * @return string
     */
    public function getPath()
    {
        if ($this->_path === null) {
            $this->_path = $this->getPathByUrl();
        }

        return $this->_path;
    }

    /**
     * @param string|null $path
     * @return $this
     */
    public function setPath($path)
    {
        $this->_path = $path;
        return $this;
    }

    //----------------------------------------

    /**
     * @return string
     */
    public function getArea()
    {
        return $this->_area;
    }

    /**
     * @param string $area
     * @return $this
     */
    public function setArea($area)
    {
        $this->_area = $area;
        return $this;
    }

    //----------------------------------------

    /**
     * @return string
     */
    public function getHash()
    {
        if ($this->_hash) {
            return $this->_hash;
        }

        return $this->_hash = $this->generateHash($this->getUrl(), $this->getPath());
    }

    /**
     * @return $this
     */
    public function resetHash()
    {
        $this->_hash = null;
        return $this;
    }

    protected function generateHash($url, $path)
    {
        if ($this->isSelfHosted()) {
            return md5_file($path);
        }

        return sha1($url);
    }

    //----------------------------------------

    /**
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;
        return $this;
    }

    //########################################

    public function isSelfHosted()
    {
        return $this->getPath() && is_file($this->getPath());
    }

    //########################################

    public function getPathByUrl()
    {
        $imageUrl = str_replace('%20', ' ', $this->getUrl());
        $imageUrl = preg_replace('/^http(s)?:\/\//i', '', $imageUrl);

        $baseMediaUrl = $this->getBaseMediaUrl() . 'catalog/product';
        $baseMediaUrl = preg_replace('/^http(s)?:\/\//i', '', $baseMediaUrl);

        $baseMediaPath = Mage::getSingleton('catalog/product_media_config')->getBaseMediaPath();

        $imagePath = str_replace($baseMediaUrl, $baseMediaPath, $imageUrl);
        $imagePath = str_replace('/', DS, $imagePath);
        $imagePath = str_replace('\\', DS, $imagePath);

        return $imagePath;
    }

    public function getUrlByPath()
    {
        $baseMediaUrl  = $this->getBaseMediaUrl() . 'catalog/product';
        $baseMediaPath = Mage::getSingleton('catalog/product_media_config')->getBaseMediaPath();

        $imageLink = str_replace($baseMediaPath, $baseMediaUrl, $this->getPath());
        $imageLink = str_replace(DS, '/', $imageLink);

        return str_replace(' ', '%20', $imageLink);
    }

    //########################################

    protected function getBaseMediaUrl()
    {
        $shouldBeSecure = $this->getArea() == Mage_Core_Model_App_Area::AREA_FRONTEND
            ? Mage::helper('M2ePro/Component_Ebay_Images')->shouldBeUrlsSecure()
            : NULL;

        return Mage::app()->getStore($this->_storeId)->getBaseUrl(
            Mage_Core_Model_Store::URL_TYPE_MEDIA, $shouldBeSecure
        );
    }

    //########################################
}
