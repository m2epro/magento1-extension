<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Listing_Other getParentObject()
 */
class Ess_M2ePro_Model_Walmart_Listing_Other extends Ess_M2ePro_Model_Component_Child_Walmart_Abstract
{
    const EMPTY_TITLE_PLACEHOLDER = '--';

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Walmart_Listing_Other');
    }

    //########################################

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

    //########################################

    /**
     * @return string
     */
    public function getSku()
    {
        return $this->getData('sku');
    }

    /**
     * @return mixed
     */
    public function getGtin()
    {
        return $this->getData('gtin');
    }

    /**
     * @return mixed
     */
    public function getUpc()
    {
        return $this->getData('upc');
    }

    /**
     * @return mixed
     */
    public function getEan()
    {
        return $this->getData('ean');
    }

    /**
     * @return mixed
     */
    public function getWpid()
    {
        return $this->getData('wpid');
    }

    /**
     * @return mixed
     */
    public function getItemId()
    {
        return $this->getData('item_id');
    }

    // ---------------------------------------

    /**
     * @return string
     */
    public function getPublishStatus()
    {
        return $this->getData('publish_status');
    }

    /**
     * @return string
     */
    public function getLifecycleStatus()
    {
        return $this->getData('lifecycle_status');
    }

    /**
     * @return array
     */
    public function getStatusChangeReasons()
    {
        return $this->getSettings('status_change_reasons');
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isOnlinePriceInvalid()
    {
        return (bool)$this->getData('is_online_price_invalid');
    }

    // ---------------------------------------

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->getData('title');
    }

    // ---------------------------------------

    /**
     * @return float
     */
    public function getOnlinePrice()
    {
        return (float)$this->getData('online_price');
    }

    /**
     * @return int
     */
    public function getOnlineQty()
    {
        return (int)$this->getData('online_qty');
    }

    //########################################

    /**
     * @return mixed
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getRelatedStoreId()
    {
        return $this->getAccount()->getChildObject()->getRelatedStoreId();
    }

    //########################################

    public function afterMapProduct()
    {
        $dataForAdd = array(
            'account_id' => $this->getParentObject()->getAccountId(),
            'marketplace_id' => $this->getParentObject()->getMarketplaceId(),
            'sku' => $this->getSku(),
            'product_id' => $this->getParentObject()->getProductId(),
            'store_id' => $this->getRelatedStoreId()
        );

        Mage::getModel('M2ePro/Walmart_Item')->setData($dataForAdd)->save();
    }

    public function beforeUnmapProduct()
    {
        $existedRelation = Mage::getSingleton('core/resource')->getConnection('core_read')
            ->select()
            ->from(
                array('ai' => Mage::getResourceModel('M2ePro/Walmart_Item')->getMainTable()),
                array()
            )
            ->join(
                array('alp' => Mage::getResourceModel('M2ePro/Walmart_Listing_Product')->getMainTable()),
                '(`alp`.`sku` = `ai`.`sku`)',
                array('alp.listing_product_id')
            )
            ->where('`ai`.`sku` = ?', $this->getSku())
            ->where('`ai`.`account_id` = ?', $this->getParentObject()->getAccountId())
            ->where('`ai`.`marketplace_id` = ?', $this->getParentObject()->getMarketplaceId())
            ->query()
            ->fetchColumn();

        if ($existedRelation) {
            return;
        }

        Mage::getSingleton('core/resource')->getConnection('core_write')
            ->delete(
                Mage::getResourceModel('M2ePro/Walmart_Item')->getMainTable(),
                array(
                        '`account_id` = ?' => $this->getParentObject()->getAccountId(),
                        '`marketplace_id` = ?' => $this->getParentObject()->getMarketplaceId(),
                        '`sku` = ?' => $this->getSku(),
                        '`product_id` = ?' => $this->getParentObject()->getProductId()
                )
            );
    }

    //########################################
}
