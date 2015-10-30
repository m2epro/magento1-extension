<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Buy_Listing_Other_Moving
{

    /**
     * @var Ess_M2ePro_Model_Account|null
     */
    protected $account = NULL;

    protected $tempObjectsCache = array();

    //########################################

    public function initialize(Ess_M2ePro_Model_Account $account = NULL)
    {
        $this->account = $account;
        $this->tempObjectsCache = array();
    }

    //########################################

    /**
     * @param array $otherListings
     * @return bool
     */
    public function autoMoveOtherListingsProducts(array $otherListings)
    {
        $otherListingsFiltered = array();

        foreach ($otherListings as $otherListing) {

            if (!($otherListing instanceof Ess_M2ePro_Model_Listing_Other)) {
                continue;
            }

            /** @var $otherListing Ess_M2ePro_Model_Listing_Other */

            $otherListingsFiltered[] = $otherListing;
        }

        if (count($otherListingsFiltered) <= 0) {
            return false;
        }

        $sortedItems = array();

        /** @var $otherListing Ess_M2ePro_Model_Listing_Other */
        foreach ($otherListingsFiltered as $otherListing) {
            $sortedItems[$otherListing->getAccountId()][] = $otherListing;
        }

        $result = true;

        foreach ($sortedItems as $otherListings) {
            foreach ($otherListings as $otherListing) {
                /** @var $otherListing Ess_M2ePro_Model_Listing_Other */
                $temp = $this->autoMoveOtherListingProduct($otherListing);
                $temp === false && $result = false;
            }
        }

        return $result;
    }

    /**
     * @param Ess_M2ePro_Model_Listing_Other $otherListing
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function autoMoveOtherListingProduct(Ess_M2ePro_Model_Listing_Other $otherListing)
    {
        $this->setAccountByOtherListingProduct($otherListing);

        if (!$this->getAccount()->getChildObject()->isOtherListingsMoveToListingsEnabled()) {
            return false;
        }

        $listing = $this->getDefaultListing($otherListing);

        if (!($listing instanceof Ess_M2ePro_Model_Listing)) {
            return false;
        }

        $listingProduct = $listing->addProduct($otherListing->getProductId());

        if (!($listingProduct instanceof Ess_M2ePro_Model_Listing_Product)) {
            return false;
        }

        $dataForUpdate = array(
            'general_id' => $otherListing->getChildObject()->getGeneralId(),
            'sku' => $otherListing->getChildObject()->getSku(),
            'online_price' => $otherListing->getChildObject()->getOnlinePrice(),
            'online_qty' => $otherListing->getChildObject()->getOnlineQty(),
            'condition' => $otherListing->getChildObject()->getCondition(),
            'condition_note' => $otherListing->getChildObject()->getConditionNote(),
            'shipping_standard_rate' => $otherListing->getChildObject()->getShippingStandardRate(),
            'shipping_expedited_mode' => $otherListing->getChildObject()->getShippingExpeditedMode(),
            'shipping_expedited_rate' => $otherListing->getChildObject()->getShippingExpeditedRate(),
            'status' => $otherListing->getStatus(),
            'status_changer' => $otherListing->getStatusChanger()
        );

        $listingProduct->addData($dataForUpdate)->save();

        // Set listing store id to Buy Item
        // ---------------------------------------
        $itemsCollection = Mage::getModel('M2ePro/Buy_Item')->getCollection();

        $itemsCollection->addFieldToFilter(
            'account_id', $otherListing->getAccountId()
        );
        $itemsCollection->addFieldToFilter(
            'marketplace_id', Ess_M2ePro_Helper_Component_Buy::MARKETPLACE_ID
        );
        $itemsCollection->addFieldToFilter(
            'sku', $otherListing->getChildObject()->getSku()
        );
        $itemsCollection->addFieldToFilter(
            'product_id', $otherListing->getProductId()
        );
        if ($itemsCollection->getSize() > 0) {
            $itemsCollection->getFirstItem()->setData('store_id', $listing->getStoreId())->save();
        } else {
            $dataForAdd = array(
                'account_id' => $otherListing->getAccountId(),
                'marketplace_id' => Ess_M2ePro_Helper_Component_Buy::MARKETPLACE_ID,
                'sku' => $otherListing->getChildObject()->getSku(),
                'product_id' => $otherListing->getProductId(),
                'store_id' => $listing->getStoreId()
            );
            Mage::getModel('M2ePro/Buy_Item')->setData($dataForAdd)->save();
        }
        // ---------------------------------------

        $logModel = Mage::getModel('M2ePro/Listing_Other_Log');
        $logModel->setComponentMode(Ess_M2ePro_Helper_Component_Buy::NICK);
        $logModel->addProductMessage($otherListing->getId(),
                                     Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
                                     NULL,
                                     Ess_M2ePro_Model_Listing_Other_Log::ACTION_MOVE_LISTING,
                                     // M2ePro_TRANSLATIONS
                                     // Item was successfully Moved
                                     'Item was successfully Moved',
                                     Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
                                     Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);

        $tempLog = Mage::getModel('M2ePro/Listing_Log');
        $tempLog->setComponentMode(Ess_M2ePro_Helper_Component_Buy::NICK);
        $tempLog->addProductMessage( $listingProduct->getListingId(),
                                     $otherListing->getProductId(),
                                     $listingProduct->getId(),
                                     Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
                                     NULL,
                                     Ess_M2ePro_Model_Listing_Log::ACTION_MOVE_FROM_OTHER_LISTING,
                                     // M2ePro_TRANSLATIONS
                                     // Item was successfully Moved
                                     'Item was successfully Moved',
                                     Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
                                     Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);

        if (!$this->getAccount()->getChildObject()->isOtherListingsMoveToListingsSynchModeNone()) {
            Mage::getModel('M2ePro/ProductChange')
                ->addUpdateAction( $otherListing->getProductId(),
                                   Ess_M2ePro_Model_ProductChange::INITIATOR_UNKNOWN);
        }

        $otherListing->deleteInstance();

        return true;
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Listing_Other $otherListing
     * @return Ess_M2ePro_Model_Listing
     */
    public function getDefaultListing(Ess_M2ePro_Model_Listing_Other $otherListing)
    {
        $accountId = $this->getAccount()->getId();

        if (isset($this->tempObjectsCache['listing_'.$accountId])) {
            return $this->tempObjectsCache['listing_'.$accountId];
        }

        $tempCollection = Mage::helper('M2ePro/Component_Buy')->getCollection('Listing');
        $tempCollection->addFieldToFilter('main_table.title',
                                          'Default ('.$this->getAccount()->getTitle().')');
        $tempItem = $tempCollection->getFirstItem();

        if (!is_null($tempItem->getId())) {
            $this->tempObjectsCache['listing_'.$accountId] = $tempItem;
            return $tempItem;
        }

        $tempModel = Mage::helper('M2ePro/Component_Buy')->getModel('Listing');

        $dataForAdd = array(
            'title' => 'Default ('.$this->getAccount()->getTitle().')',
            'store_id' => $otherListing->getChildObject()->getRelatedStoreId(),
            'marketplace_id' => Ess_M2ePro_Helper_Component_Buy::MARKETPLACE_ID,
            'account_id' => $accountId,

            'template_selling_format_id'  => $this->getDefaultSellingFormatTemplate($otherListing)->getId(),
            'template_synchronization_id' => $this->getDefaultSynchronizationTemplate($otherListing)->getId(),

            'source_products' => Ess_M2ePro_Model_Listing::SOURCE_PRODUCTS_CUSTOM,

            'sku_mode' => Ess_M2ePro_Model_Buy_Listing::SKU_MODE_DEFAULT,
            'general_id_mode' => Ess_M2ePro_Model_Buy_Listing::GENERAL_ID_MODE_NOT_SET,
            'search_by_magento_title_mode' =>
                Ess_M2ePro_Model_Buy_Listing::SEARCH_BY_MAGENTO_TITLE_MODE_NONE,
            'condition_mode' => Ess_M2ePro_Model_Buy_Listing::CONDITION_MODE_DEFAULT,
            'condition_value' => Ess_M2ePro_Model_Buy_Listing::CONDITION_NEW,
            'condition_note_mode' => Ess_M2ePro_Model_Buy_Listing::CONDITION_NOTE_MODE_NONE,
            'shipping_standard_mode' =>
                                    Ess_M2ePro_Model_Buy_Listing::SHIPPING_MODE_DEFAULT,
            'shipping_expedited_mode' =>
                                    Ess_M2ePro_Model_Buy_Listing::SHIPPING_MODE_DEFAULT,
            'shipping_one_day_mode' =>
                                    Ess_M2ePro_Model_Buy_Listing::SHIPPING_MODE_DEFAULT,
            'shipping_two_day_mode' =>
                                    Ess_M2ePro_Model_Buy_Listing::SHIPPING_MODE_DEFAULT
        );

        $tempModel->addData($dataForAdd)->save();
        $this->tempObjectsCache['listing_'.$accountId] = $tempModel;

        return $tempModel;
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Listing_Other $otherListing
     * @return Ess_M2ePro_Model_Template_Synchronization
     */
    protected function getDefaultSynchronizationTemplate(Ess_M2ePro_Model_Listing_Other $otherListing)
    {
        $marketplaceId = Ess_M2ePro_Helper_Component_Buy::MARKETPLACE_ID;

        if (isset($this->tempObjectsCache['synchronization_'.$marketplaceId])) {
            return $this->tempObjectsCache['synchronization_'.$marketplaceId];
        }

        $tempCollection = Mage::helper('M2ePro/Component_Buy')->getCollection('Template_Synchronization');
        $tempCollection->addFieldToFilter('main_table.title','Default');
        $tempItem = $tempCollection->getFirstItem();

        if (!is_null($tempItem->getId())) {
            $this->tempObjectsCache['synchronization_'.$marketplaceId] = $tempItem;
            return $tempItem;
        }

        $tempModel = Mage::helper('M2ePro/Component_Buy')->getModel('Template_Synchronization');

        $dataForAdd = array(
            'title' => 'Default',
            'list_mode' => Ess_M2ePro_Model_Buy_Template_Synchronization::LIST_MODE_NONE,
            'list_status_enabled' => Ess_M2ePro_Model_Buy_Template_Synchronization::LIST_STATUS_ENABLED_YES,
            'list_is_in_stock' => Ess_M2ePro_Model_Buy_Template_Synchronization::LIST_IS_IN_STOCK_YES,
            'list_qty' => Ess_M2ePro_Model_Buy_Template_Synchronization::LIST_QTY_NONE,
            'list_qty_value' => 1,
            'list_qty_value_max' => 10,
            'relist_mode' => Ess_M2ePro_Model_Buy_Template_Synchronization::RELIST_MODE_NONE,
            'relist_filter_user_lock' => Ess_M2ePro_Model_Buy_Template_Synchronization::RELIST_FILTER_USER_LOCK_YES,
            'relist_status_enabled' => Ess_M2ePro_Model_Buy_Template_Synchronization::RELIST_STATUS_ENABLED_YES,
            'relist_is_in_stock' => Ess_M2ePro_Model_Buy_Template_Synchronization::RELIST_IS_IN_STOCK_YES,
            'relist_qty' => Ess_M2ePro_Model_Buy_Template_Synchronization::RELIST_QTY_NONE,
            'relist_qty_value' => 1,
            'relist_qty_value_max' => 10,
            'revise_update_qty' => Ess_M2ePro_Model_Buy_Template_Synchronization::REVISE_UPDATE_QTY_NONE,
            'revise_update_price' => Ess_M2ePro_Model_Buy_Template_Synchronization::REVISE_UPDATE_PRICE_NONE,
            'revise_change_selling_format_template' =>
                                Ess_M2ePro_Model_Template_Synchronization::REVISE_CHANGE_SELLING_FORMAT_TEMPLATE_NONE,
            'revise_change_listing' =>
                                Ess_M2ePro_Model_Template_Synchronization::REVISE_CHANGE_LISTING_NONE,
            'stop_status_disabled' => Ess_M2ePro_Model_Buy_Template_Synchronization::STOP_STATUS_DISABLED_NONE,
            'stop_out_off_stock' => Ess_M2ePro_Model_Buy_Template_Synchronization::STOP_OUT_OFF_STOCK_NONE,
            'stop_qty' => Ess_M2ePro_Model_Buy_Template_Synchronization::STOP_QTY_NONE,
            'stop_qty_value' => 0,
            'stop_qty_value_max' => 10
        );

        if ($this->getAccount()->getChildObject()->isOtherListingsMoveToListingsSynchModePrice() ||
            $this->getAccount()->getChildObject()->isOtherListingsMoveToListingsSynchModeAll()
        ) {
            $additionalPriceSettings = array(
                'revise_update_price' => Ess_M2ePro_Model_Buy_Template_Synchronization::REVISE_UPDATE_PRICE_YES,
                'revise_update_price_max_allowed_deviation_mode' =>
                    Ess_M2ePro_Model_Buy_Template_Synchronization::REVISE_MAX_ALLOWED_PRICE_DEVIATION_MODE_ON,
                'revise_update_price_max_allowed_deviation'      =>
                    Ess_M2ePro_Model_Buy_Template_Synchronization::REVISE_UPDATE_PRICE_MAX_ALLOWED_DEVIATION_DEFAULT,
            );

            $dataForAdd = array_merge($dataForAdd, $additionalPriceSettings);
        }

        if ($this->getAccount()->getChildObject()->isOtherListingsMoveToListingsSynchModeQty() ||
            $this->getAccount()->getChildObject()->isOtherListingsMoveToListingsSynchModeAll()
        ) {
            $additionalQtySettings = array(
                'revise_update_qty'    => Ess_M2ePro_Model_Buy_Template_Synchronization::REVISE_UPDATE_QTY_YES,
                'revise_update_qty_max_applied_value_mode' =>
                    Ess_M2ePro_Model_Buy_Template_Synchronization::REVISE_MAX_AFFECTED_QTY_MODE_ON,
                'revise_update_qty_max_applied_value' =>
                    Ess_M2ePro_Model_Buy_Template_Synchronization::REVISE_UPDATE_QTY_MAX_APPLIED_VALUE_DEFAULT,
                'relist_mode'          => Ess_M2ePro_Model_Buy_Template_Synchronization::RELIST_MODE_YES,
                'stop_status_disabled' => Ess_M2ePro_Model_Buy_Template_Synchronization::STOP_STATUS_DISABLED_YES,
                'stop_out_off_stock'   => Ess_M2ePro_Model_Buy_Template_Synchronization::STOP_OUT_OFF_STOCK_YES,
            );

            $dataForAdd = array_merge($dataForAdd, $additionalQtySettings);
        }

        $tempModel->addData($dataForAdd)->save();
        $this->tempObjectsCache['synchronization_'.$marketplaceId] = $tempModel;

        return $tempModel;
    }

    /**
     * @param Ess_M2ePro_Model_Listing_Other $otherListing
     * @return Ess_M2ePro_Model_Template_SellingFormat
     */
    protected function getDefaultSellingFormatTemplate(Ess_M2ePro_Model_Listing_Other $otherListing)
    {
        $marketplaceId = Ess_M2ePro_Helper_Component_Buy::MARKETPLACE_ID;

        if (isset($this->tempObjectsCache['selling_format_'.$marketplaceId])) {
            return $this->tempObjectsCache['selling_format_'.$marketplaceId];
        }

        $tempCollection = Mage::helper('M2ePro/Component_Buy')->getCollection('Template_SellingFormat');
        $tempCollection->addFieldToFilter('main_table.title','Default');
        $tempItem = $tempCollection->getFirstItem();

        if (!is_null($tempItem->getId())) {
            $this->tempObjectsCache['selling_format_'.$marketplaceId] = $tempItem;
            return $tempItem;
        }

        $tempModel = Mage::helper('M2ePro/Component_Buy')->getModel('Template_SellingFormat');

        $dataForAdd = array(
            'title' => 'Default',

            'qty_mode' => Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_PRODUCT,
            'qty_custom_value' => 1,

            'price_mode' => Ess_M2ePro_Model_Template_SellingFormat::PRICE_PRODUCT,
            'price_variation_mode' => Ess_M2ePro_Model_Buy_Template_SellingFormat::PRICE_VARIATION_MODE_PARENT
        );

        $tempModel->addData($dataForAdd)->save();
        $this->tempObjectsCache['selling_format_'.$marketplaceId] = $tempModel;

        return $tempModel;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Account
     */
    protected function getAccount()
    {
        return $this->account;
    }

    // ---------------------------------------

    protected function setAccountByOtherListingProduct(Ess_M2ePro_Model_Listing_Other $otherListing)
    {
        if (!is_null($this->account) && $this->account->getId() == $otherListing->getAccountId()) {
            return;
        }

        $this->account = Mage::helper('M2ePro/Component_Buy')->getCachedObject(
            'Account',$otherListing->getAccountId()
        );
    }

    //########################################
}