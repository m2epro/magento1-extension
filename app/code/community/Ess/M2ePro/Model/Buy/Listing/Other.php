<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

/**
 * @method Ess_M2ePro_Model_Listing_Other getParentObject()
 */
class Ess_M2ePro_Model_Buy_Listing_Other extends Ess_M2ePro_Model_Component_Child_Buy_Abstract
{
    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Buy_Listing_Other');
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Account
     */
    public function getAccount()
    {
        return $this->getParentObject()->getAccount();
    }

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    public function getMarketplace()
    {
        return $this->getParentObject()->getMarketplace();
    }

    /**
     * @return Ess_M2ePro_Model_Magento_Product_Cache
     */
    public function getMagentoProduct()
    {
        return $this->getParentObject()->getMagentoProduct();
    }

    // ########################################

    public function getSku()
    {
        return $this->getData('sku');
    }

    public function getGeneralId()
    {
        return (int)$this->getData('general_id');
    }

    //-----------------------------------------

    public function getTitle()
    {
        return $this->getData('title');
    }

    //-----------------------------------------

    public function getOnlinePrice()
    {
        return (float)$this->getData('online_price');
    }

    public function getOnlineQty()
    {
        return (int)$this->getData('online_qty');
    }

    //-----------------------------------------

    public function getCondition()
    {
        return (int)$this->getData('condition');
    }

    public function getConditionNote()
    {
        return $this->getData('condition_note');
    }

    //-----------------------------------------

    public function getShippingStandardRate()
    {
        return (float)$this->getData('shipping_standard_rate');
    }

    public function getShippingExpeditedMode()
    {
        return (int)$this->getData('shipping_expedited_mode');
    }

    public function getShippingExpeditedRate()
    {
        return (float)$this->getData('shipping_expedited_rate');
    }

    // ########################################

    public function getRelatedStoreId()
    {
        return $this->getAccount()->getChildObject()->getRelatedStoreId();
    }

    // ########################################

    public function afterMapProduct()
    {
        $dataForAdd = array(
            'account_id' => $this->getParentObject()->getAccountId(),
            'marketplace_id' => $this->getParentObject()->getMarketplaceId(),
            'sku' => $this->getSku(),
            'product_id' => $this->getParentObject()->getProductId(),
            'store_id' => $this->getRelatedStoreId()
        );

        Mage::getModel('M2ePro/Buy_Item')->setData($dataForAdd)->save();
    }

    public function beforeUnmapProduct()
    {
        Mage::getSingleton('core/resource')->getConnection('core_write')
            ->delete(Mage::getResourceModel('M2ePro/Buy_Item')->getMainTable(),
            array(
                '`account_id` = ?' => $this->getParentObject()->getAccountId(),
                '`marketplace_id` = ?' => $this->getParentObject()->getMarketplaceId(),
                '`sku` = ?' => $this->getSku(),
                '`product_id` = ?' => $this->getParentObject()->getProductId()
            ));
    }

    // ########################################
}