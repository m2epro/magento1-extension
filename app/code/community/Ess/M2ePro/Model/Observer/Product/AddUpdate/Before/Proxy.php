<?php

/*
 * @copyright  Copyright (c) 2015 by  ESS-UA.
 */

class Ess_M2ePro_Model_Observer_Product_AddUpdate_Before_Proxy
{
    private $productId = NULL;
    private $storeId = NULL;

    private $data = array();
    private $attributes = array();

    private $websiteIds = array();
    private $categoriesIds = array();

    //####################################

    public function setProductId($value)
    {
        $this->productId = (int)$value;
    }

    public function getProductId()
    {
        return $this->productId;
    }

    //-------------------------------------

    public function setStoreId($value)
    {
        $this->storeId = (int)$value;
    }

    public function getStoreId()
    {
        return $this->storeId;
    }

    //####################################

    public function setData($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function getData($key)
    {
        return isset($this->data[$key]) ? $this->data[$key] : NULL;
    }

    //-------------------------------------

    public function setAttributes(array $attributes = array())
    {
        $this->attributes = $attributes;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    //####################################

    public function setWebsiteIds(array $ids = array())
    {
        $this->websiteIds = $ids;
    }

    public function getWebsiteIds()
    {
        return $this->websiteIds;
    }

    // ------------------------------------

    public function setCategoriesIds(array $ids = array())
    {
        $this->categoriesIds = $ids;
    }

    public function getCategoriesIds()
    {
        return $this->categoriesIds;
    }

    //####################################
}