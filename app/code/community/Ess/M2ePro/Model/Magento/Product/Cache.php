<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Magento_Product_Cache extends Ess_M2ePro_Model_Magento_Product
{
    private $isCacheEnabled = false;

    //########################################

    public function getCacheValue($key)
    {
        $key = sha1('magento_product_'.$this->getProductId().'_'.$this->getStoreId().'_'.json_encode($key));
        return Mage::helper('M2ePro/Data_Cache_Session')->getValue($key);
    }

    public function setCacheValue($key, $value)
    {
        $key = sha1('magento_product_'.$this->getProductId().'_'.$this->getStoreId().'_'.json_encode($key));
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
        return $this->isCacheEnabled;
    }

    /**
     * @return $this
     */
    public function enableCache()
    {
        $this->isCacheEnabled = true;
        return $this;
    }

    /**
     * @return $this
     */
    public function disableCache()
    {
        $this->isCacheEnabled = false;
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

    public function getThumbnailImageLink()
    {
        return $this->getMethodData(__FUNCTION__);
    }

    public function getImageLink($attribute = 'image')
    {
        $args = func_get_args();
        return $this->getMethodData(__FUNCTION__, $args);
    }

    public function getGalleryImagesLinks($limitImages = 0)
    {
        $args = func_get_args();
        return $this->getMethodData(__FUNCTION__, $args);
    }

    //########################################

    public function hasRequiredOptions()
    {
        return $this->getMethodData(__FUNCTION__);
    }

    // ---------------------------------------

    public function getVariationInstance()
    {
        if (!is_null($this->_variationInstance)) {
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

        if (!is_null($params)) {
            $cacheKey[] = $params;
        }

        $cacheResult = $this->getCacheValue($cacheKey);

        if ($this->isCacheEnabled() && !is_null($cacheResult)) {
            return $cacheResult;
        }

        if (!is_null($params)) {
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