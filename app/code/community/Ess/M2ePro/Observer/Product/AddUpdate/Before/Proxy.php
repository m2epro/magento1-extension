<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Observer_Product_AddUpdate_Before_Proxy
{
    protected $_productId = null;
    protected $_storeId   = null;

    protected $_data       = array();
    protected $_attributes = array();

    protected $_websiteIds    = array();
    protected $_categoriesIds = array();

    //########################################

    /**
     * @param int $value
     */
    public function setProductId($value)
    {
        $this->_productId = (int)$value;
    }

    /**
     * @return null|int
     */
    public function getProductId()
    {
        return $this->_productId;
    }

    // ---------------------------------------

    /**
     * @param int $value
     */
    public function setStoreId($value)
    {
        $this->_storeId = (int)$value;
    }

    /**
     * @return null|int
     */
    public function getStoreId()
    {
        return $this->_storeId;
    }

    //########################################

    public function setData($key, $value)
    {
        $this->_data[$key] = $value;
    }

    public function getData($key)
    {
        return isset($this->_data[$key]) ? $this->_data[$key] : null;
    }

    // ---------------------------------------

    /**
     * @param array $attributes
     */
    public function setAttributes(array $attributes = array())
    {
        $this->_attributes = $attributes;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->_attributes;
    }

    //########################################

    /**
     * @param array $ids
     */
    public function setWebsiteIds(array $ids = array())
    {
        $this->_websiteIds = $ids;
    }

    /**
     * @return array
     */
    public function getWebsiteIds()
    {
        return $this->_websiteIds;
    }

    // ---------------------------------------

    /**
     * @param array $ids
     */
    public function setCategoriesIds(array $ids = array())
    {
        $this->_categoriesIds = $ids;
    }

    /**
     * @return array
     */
    public function getCategoriesIds()
    {
        return $this->_categoriesIds;
    }

    //########################################
}
