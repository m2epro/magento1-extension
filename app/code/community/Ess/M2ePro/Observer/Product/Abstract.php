<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Observer_Product_Abstract extends Ess_M2ePro_Observer_Abstract
{
    /**
     * @var null|Mage_Catalog_Model_Product
     */
    protected $_product = null;

    /**
     * @var null|int
     */
    protected $_productId = null;
    /**
     * @var null|int
     */
    protected $_storeId = null;

    /**
     * @var null|Ess_M2ePro_Model_Magento_Product
     */
    protected $_magentoProduct = null;

    //########################################

    public function beforeProcess()
    {
        $product = $this->getEvent()->getProduct();

        if (!($product instanceof Mage_Catalog_Model_Product)) {
            throw new Ess_M2ePro_Model_Exception('Product event doesn\'t have correct Product instance.');
        }

        $this->_product = $product;

        $this->_productId = (int)$this->_product->getId();
        $this->_storeId   = (int)$this->_product->getData('store_id');
    }

    //########################################

    /**
     * @return Mage_Catalog_Model_Product
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getProduct()
    {
        if (!($this->_product instanceof Mage_Catalog_Model_Product)) {
            throw new Ess_M2ePro_Model_Exception_Logic('Property "Product" should be set first.');
        }

        return $this->_product;
    }

    /**
     * @return Mage_Catalog_Model_Product
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function reloadProduct()
    {
        if ($this->getProductId() <= 0) {
            throw new Ess_M2ePro_Model_Exception_Logic(
                'To reload Product instance product_id should be
                greater than 0.'
            );
        }

        $this->_product = Mage::getModel('catalog/product')->setStoreId($this->getStoreId())
                              ->load($this->getProductId());

        return $this->getProduct();
    }

    // ---------------------------------------

    /**
     * @return int
     */
    protected function getProductId()
    {
        return (int)$this->_productId;
    }

    /**
     * @return int
     */
    protected function getStoreId()
    {
        return (int)$this->_storeId;
    }

    //########################################

    /**
     * @return bool
     */
    protected function isAdminDefaultStoreId()
    {
        return $this->getStoreId() == Mage_Core_Model_App::ADMIN_STORE_ID;
    }

    /**
     * @return Ess_M2ePro_Model_Magento_Product
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getMagentoProduct()
    {
        if (!empty($this->_magentoProduct)) {
            return $this->_magentoProduct;
        }

        if ($this->getProductId() <= 0) {
            throw new Ess_M2ePro_Model_Exception_Logic(
                'To load Magento Product instance product_id should be
                greater than 0.'
            );
        }

        return $this->_magentoProduct = Mage::getModel('M2ePro/Magento_Product')->setProduct($this->getProduct());
    }

    //########################################
}
