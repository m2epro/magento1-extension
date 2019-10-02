<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Magento_Product_Cache extends Ess_M2ePro_Model_Magento_Product
{
    protected $_isCacheEnabled = false;

    //########################################

    public function getCacheValue($key)
    {
        $key = Mage::helper('M2ePro')->jsonEncode($key);
        $key = sha1('magento_product_'.$this->getProductId().'_'.$this->getStoreId().'_'.$key);
        return Mage::helper('M2ePro/Data_Cache_Session')->getValue($key);
    }

    public function setCacheValue($key, $value)
    {
        $key = Mage::helper('M2ePro')->jsonEncode($key);
        $key = sha1('magento_product_'.$this->getProductId().'_'.$this->getStoreId().'_'.$key);
        $tags = array(
            'magento_product',
            'magento_product_'.$this->getProductId().'_'.$this->getStoreId()
        );

        return Mage::helper('M2ePro/Data_Cache_Session')->setValue($key, $value, $tags);
    }

    public function clearCache()
    {
        return Mage::helper('M2ePro/Data_Cache_Session')->removeTagValues(
            'magento_product_'.$this->getProductId().'_'.$this->getStoreId()
        );
    }

    //########################################

    /**
     * @return bool
     */
    public function isCacheEnabled()
    {
        return $this->_isCacheEnabled;
    }

    /**
     * @return $this
     */
    public function enableCache()
    {
        $this->_isCacheEnabled = true;
        return $this;
    }

    /**
     * @return $this
     */
    public function disableCache()
    {
        $this->_isCacheEnabled = false;
        return $this;
    }

    //########################################

    public function exists()
    {
        return $this->getMethodData(__FUNCTION__);
    }

    //########################################

    /**
     * @return Mage_Catalog_Model_Product_Type_Abstract
     * @throws Exception
     */
    public function getTypeInstance()
    {
        return $this->getMethodData(__FUNCTION__);
    }

    /**
     * @return Mage_CatalogInventory_Model_Stock_Item
     * @throws Exception
     */
    public function getStockItem()
    {
        return $this->getMethodData(__FUNCTION__);
    }

    //########################################

    public function getTypeId()
    {
        return $this->getMethodData(__FUNCTION__);
    }

    //########################################

    public function isSimpleTypeWithCustomOptions()
    {
        return $this->getMethodData(__FUNCTION__);
    }

    //########################################

    public function getSku()
    {
        return $this->getMethodData(__FUNCTION__);
    }

    //########################################

    public function getName()
    {
        return $this->getMethodData(__FUNCTION__);
    }

    //########################################

    public function isStatusEnabled()
    {
        return $this->getMethodData(__FUNCTION__);
    }

    //########################################

    public function isStockAvailability()
    {
        return $this->getMethodData(__FUNCTION__);
    }

    //########################################

    public function getPrice()
    {
        return $this->getMethodData(__FUNCTION__);
    }

    //########################################

    public function getSpecialPrice()
    {
        return $this->getMethodData(__FUNCTION__);
    }

    //########################################

    public function getQty($lifeMode = false)
    {
        $args = func_get_args();
        return $this->getMethodData(__FUNCTION__, $args);
    }

    //########################################

    public function getAttributeValue($attributeCode)
    {
        $args = func_get_args();
        return $this->getMethodData(__FUNCTION__, $args);
    }

    //########################################

    public function getThumbnailImage()
    {
        return $this->getMethodData(__FUNCTION__);
    }

    public function getImage($attribute = 'image')
    {
        $args = func_get_args();
        return $this->getMethodData(__FUNCTION__, $args);
    }

    public function getGalleryImages($limitImages = 0)
    {
        $args = func_get_args();
        return $this->getMethodData(__FUNCTION__, $args);
    }

    //########################################

    public function getVariationInstance()
    {
        if ($this->_variationInstance !== null) {
            return $this->_variationInstance;
        }

        $this->_variationInstance = Mage::getModel('M2ePro/Magento_Product_Variation_Cache')->setMagentoProduct($this);
        return $this->_variationInstance;
    }

    //########################################

    protected function getMethodData($methodName, $params = null)
    {
        $cacheKey = array(
            __CLASS__,
            $methodName,
        );

        if ($params !== null) {
            $cacheKey[] = $params;
        }

        $cacheResult = $this->getCacheValue($cacheKey);

        if ($this->isCacheEnabled() && $cacheResult !== null) {
            return $cacheResult;
        }

        if ($params !== null) {
            $data = call_user_func_array(array('parent', $methodName), $params);
        } else {
            $data = call_user_func(array('parent', $methodName));
        }

        if (!$this->isCacheEnabled()) {
            return $data;
        }

        return $this->setCacheValue($cacheKey, $data);
    }

    //########################################
}
