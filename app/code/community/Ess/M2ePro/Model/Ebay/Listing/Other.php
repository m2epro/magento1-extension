<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Listing_Other getParentObject()
 */
class Ess_M2ePro_Model_Ebay_Listing_Other extends Ess_M2ePro_Model_Component_Child_Ebay_Abstract
{
    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Listing_Other');
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

    public function getSku()
    {
        return $this->getData('sku');
    }

    /**
     * @return float
     */
    public function getItemId()
    {
        return (double)$this->getData('item_id');
    }

    // ---------------------------------------

    public function getTitle()
    {
        return $this->getData('title');
    }

    public function getCurrency()
    {
        return $this->getData('currency');
    }

    // ---------------------------------------

    public function getOnlineDuration()
    {
        return $this->getData('online_duration');
    }

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

    /**
     * @return int
     */
    public function getOnlineQtySold()
    {
        return (int)$this->getData('online_qty_sold');
    }

    /**
     * @return int
     */
    public function getOnlineBids()
    {
        return (int)$this->getData('online_bids');
    }

    // ---------------------------------------

    public function getStartDate()
    {
        return $this->getData('start_date');
    }

    public function getEndDate()
    {
        return $this->getData('end_date');
    }

    //########################################

    public function getRelatedStoreId()
    {
        return $this->getAccount()->getChildObject()->getRelatedStoreId($this->getParentObject()->getMarketplaceId());
    }

    //########################################

    public function afterMapProduct()
    {
        $existedRelation = Mage::getSingleton('core/resource')->getConnection('core_read')
            ->select()
            ->from(array('ei' => Mage::getResourceModel('M2ePro/Ebay_Item')->getMainTable()))
            ->where('`account_id` = ?', $this->getAccount()->getId())
            ->where('`marketplace_id` = ?', $this->getMarketplace()->getId())
            ->where('`item_id` = ?', $this->getItemId())
            ->where('`product_id` = ?', $this->getParentObject()->getProductId())
            ->where('`store_id` = ?', $this->getRelatedStoreId())
            ->query()
            ->fetchColumn();

        if ($existedRelation) {
            return;
        }

        $dataForAdd = array(
            'account_id'     => $this->getAccount()->getId(),
            'marketplace_id' => $this->getMarketplace()->getId(),
            'item_id'        => $this->getItemId(),
            'product_id'     => $this->getParentObject()->getProductId(),
            'store_id'       => $this->getRelatedStoreId()
        );

        Mage::getModel('M2ePro/Ebay_Item')->setData($dataForAdd)->save();
    }

    public function beforeUnmapProduct()
    {
        $existedRelation = Mage::getSingleton('core/resource')->getConnection('core_read')
           ->select()
           ->from(array('ei' => Mage::getResourceModel('M2ePro/Ebay_Item')->getMainTable()),
                  array())
           ->join(array('elp' => Mage::getResourceModel('M2ePro/Ebay_Listing_Product')->getMainTable()),
                  '(`elp`.`ebay_item_id` = `ei`.`id`)',
                  array('elp.listing_product_id'))
           ->where('`ei`.`item_id` = ?', $this->getItemId())
           ->where('`ei`.`account_id` = ?', $this->getAccount()->getId())
           ->query()
           ->fetchColumn();

        if ($existedRelation) {
            return;
        }

        Mage::getSingleton('core/resource')->getConnection('core_write')
            ->delete(Mage::getResourceModel('M2ePro/Ebay_Item')->getMainTable(),
                    array(
                        '`item_id` = ?' => $this->getItemId(),
                        '`product_id` = ?' => $this->getParentObject()->getProductId(),
                        '`account_id` = ?' => $this->getAccount()->getId()
                    ));
    }

    //########################################
}