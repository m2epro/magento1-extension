<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Magento_Product_Image
{
    protected $url;
    protected $path;

    protected $hash;

    protected $storeId = 0;
    protected $area = Mage_Core_Model_App_Area::AREA_FRONTEND;

    //########################################

    function __construct($url, $path = null)
    {
        $this->url  = $url;
        $this->path = $path;
    }

    //########################################

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    //----------------------------------------

    /**
     * @return string
     */
    public function getPath()
    {
        if (is_null($this->path)) {
            $this->path = $this->getPathByUrl();
        }

        return $this->path;
    }

    /**
     * @param string|null $path
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    //----------------------------------------

    /**
     * @return string
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * @param string $area
     * @return $this
     */
    public function setArea($area)
    {
        $this->area = $area;
        return $this;
    }

    //----------------------------------------

    /**
     * @return string
     */
    public function getHash()
    {
        if ($this->hash) {
            return $this->hash;
        }

        return $this->hash = $this->generateHash($this->getUrl(), $this->getPath());
    }

    /**
     * @return $this
     */
    public function resetHash()
    {
        $this->hash = null;
        return $this;
    }

    private function generateHash($url, $path)
    {
        if ($this->isSelfHosted()) {
            return md5_file($path);
        }

        return md5($url);
    }

    //----------------------------------------

    /**
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        $this->storeId = $storeId;
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

    private function getBaseMediaUrl()
    {
        $shouldBeSecure = $this->getArea() == Mage_Core_Model_App_Area::AREA_FRONTEND
            ? Mage::helper('M2ePro/Component_Ebay_Images')->shouldBeUrlsSecure()
            : NULL;

        return Mage::app()->getStore($this->storeId)->getBaseUrl(
            Mage_Core_Model_Store::URL_TYPE_MEDIA, $shouldBeSecure
        );
    }

    //########################################
}