<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Resource_Listing_Log getResource()
 */
class Ess_M2ePro_Model_Listing_Log extends Ess_M2ePro_Model_Log_Abstract
{
    const ACTION_UNKNOWN  = 1;
    const _ACTION_UNKNOWN = 'System';

    const ACTION_ADD_LISTING     = 2;
    const _ACTION_ADD_LISTING    = 'Add new Listing';
    const ACTION_DELETE_LISTING  = 3;
    const _ACTION_DELETE_LISTING = 'Delete existing Listing';

    const ACTION_ADD_PRODUCT_TO_LISTING       = 4;
    const _ACTION_ADD_PRODUCT_TO_LISTING      = 'Add Product to Listing';
    const ACTION_DELETE_PRODUCT_FROM_LISTING  = 5;
    const _ACTION_DELETE_PRODUCT_FROM_LISTING = 'Delete Item from Listing';

    const ACTION_ADD_NEW_CHILD_LISTING_PRODUCT  = 35;
    const _ACTION_ADD_NEW_CHILD_LISTING_PRODUCT = 'Add New Child Product';

    const ACTION_ADD_PRODUCT_TO_MAGENTO       = 6;
    const _ACTION_ADD_PRODUCT_TO_MAGENTO      = 'Add new Product to Magento Store';
    const ACTION_DELETE_PRODUCT_FROM_MAGENTO  = 7;
    const _ACTION_DELETE_PRODUCT_FROM_MAGENTO = 'Delete existing Product from Magento Store';

    const ACTION_CHANGE_PRODUCT_PRICE               = 8;
    const _ACTION_CHANGE_PRODUCT_PRICE              = 'Change of Product Price in Magento Store';
    const ACTION_CHANGE_PRODUCT_SPECIAL_PRICE       = 9;
    const _ACTION_CHANGE_PRODUCT_SPECIAL_PRICE      = 'Change of Product Special Price in Magento Store';
    const ACTION_CHANGE_PRODUCT_QTY                 = 10;
    const _ACTION_CHANGE_PRODUCT_QTY                = 'Change of Product QTY in Magento Store';
    const ACTION_CHANGE_PRODUCT_STOCK_AVAILABILITY  = 11;
    const _ACTION_CHANGE_PRODUCT_STOCK_AVAILABILITY = 'Change of Product Stock availability in Magento Store';
    const ACTION_CHANGE_PRODUCT_STATUS              = 12;
    const _ACTION_CHANGE_PRODUCT_STATUS             = 'Change of Product status in Magento Store';

    const ACTION_LIST_PRODUCT_ON_COMPONENT      = 13;
    const _ACTION_LIST_PRODUCT_ON_COMPONENT     = 'List Item on Channel';
    const ACTION_RELIST_PRODUCT_ON_COMPONENT    = 14;
    const _ACTION_RELIST_PRODUCT_ON_COMPONENT   = 'Relist Item on Channel';
    const ACTION_REVISE_PRODUCT_ON_COMPONENT    = 15;
    const _ACTION_REVISE_PRODUCT_ON_COMPONENT   = 'Revise Item on Channel';
    const ACTION_STOP_PRODUCT_ON_COMPONENT      = 16;
    const _ACTION_STOP_PRODUCT_ON_COMPONENT     = 'Stop Item on Channel';
    const ACTION_DELETE_PRODUCT_FROM_COMPONENT  = 24;
    const _ACTION_DELETE_PRODUCT_FROM_COMPONENT = 'Remove Item from Channel';
    const ACTION_STOP_AND_REMOVE_PRODUCT        = 17;
    const _ACTION_STOP_AND_REMOVE_PRODUCT       = 'Stop on Channel / Remove from Listing';
    const ACTION_DELETE_AND_REMOVE_PRODUCT      = 23;
    const _ACTION_DELETE_AND_REMOVE_PRODUCT     = 'Remove from Channel & Listing';
    const ACTION_SWITCH_TO_AFN_ON_COMPONENT     = 29;
    const _ACTION_SWITCH_TO_AFN_ON_COMPONENT    = 'Switching Fulfillment to AFN';
    const ACTION_SWITCH_TO_MFN_ON_COMPONENT     = 30;
    const _ACTION_SWITCH_TO_MFN_ON_COMPONENT    = 'Switching Fulfillment to MFN';

    const ACTION_CHANGE_PRODUCT_SPECIAL_PRICE_FROM_DATE  = 19;
    const _ACTION_CHANGE_PRODUCT_SPECIAL_PRICE_FROM_DATE = 'Change of Product Special Price from date in Magento Store';

    const ACTION_CHANGE_PRODUCT_SPECIAL_PRICE_TO_DATE  = 20;
    const _ACTION_CHANGE_PRODUCT_SPECIAL_PRICE_TO_DATE = 'Change of Product Special Price to date in Magento Store';

    const ACTION_CHANGE_PRODUCT_TIER_PRICE  = 31;
    const _ACTION_CHANGE_PRODUCT_TIER_PRICE = 'Change of Product Tier Price in Magento Store';

    const ACTION_CHANGE_CUSTOM_ATTRIBUTE  = 18;
    const _ACTION_CHANGE_CUSTOM_ATTRIBUTE = 'Change of Product Custom Attribute in Magento Store';

    const ACTION_MOVE_TO_LISTING  = 21;
    const _ACTION_MOVE_TO_LISTING = 'Move to another Listing';

    const ACTION_MOVE_FROM_OTHER_LISTING  = 22;
    const _ACTION_MOVE_FROM_OTHER_LISTING = 'Move from Unmanaged Listing';

    const ACTION_SELL_ON_ANOTHER_SITE  = 33;
    const _ACTION_SELL_ON_ANOTHER_SITE = 'Sell On Another Marketplace';

    const ACTION_CHANNEL_CHANGE  = 25;
    const _ACTION_CHANNEL_CHANGE = 'External Change';
    
    const ACTION_REMAP_LISTING_PRODUCT = 34;
    const _ACTION_REMAP_LISTING_PRODUCT = 'Relink';

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Listing_Log');
    }

    //########################################

    public function addListingMessage(
        $listingId,
        $initiator = Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN,
        $actionId = null,
        $action = null,
        $description = null,
        $type = null,
        array $additionalData = array()
    ) {
        $dataForAdd = $this->makeDataForAdd(
            $listingId,
            $initiator,
            null,
            null,
            $actionId,
            $action,
            $description,
            $type,
            $additionalData
        );

        $this->createMessage($dataForAdd);
    }

    public function addProductMessage(
        $listingId,
        $productId,
        $listingProductId,
        $initiator = Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN,
        $actionId = null,
        $action = null,
        $description = null,
        $type = null,
        array $additionalData = array()
    ) {
        $dataForAdd = $this->makeDataForAdd(
            $listingId,
            $initiator,
            $productId,
            $listingProductId,
            $actionId,
            $action,
            $description,
            $type,
            $additionalData
        );

        $this->createMessage($dataForAdd);
    }

    //########################################

    public function clearMessages($listingId = null)
    {
        $filters = array();

        if ($listingId !== null) {
            $filters['listing_id'] = $listingId;
        }

        if ($this->_componentMode !== null) {
            $filters['component_mode'] = $this->_componentMode;
        }

        $this->getResource()->clearMessages($filters);
    }

    //########################################

    protected function createMessage($dataForAdd)
    {
        $listing = Mage::helper('M2ePro/Component')->getCachedComponentObject(
            $this->_componentMode,
            'Listing',
            $dataForAdd['listing_id']
        );

        $dataForAdd['listing_title'] = $listing->getData('title');
        $dataForAdd['account_id'] = $listing->getData('account_id');
        $dataForAdd['marketplace_id'] = $listing->getData('marketplace_id');

        if (isset($dataForAdd['product_id'])) {
            $dataForAdd['product_title'] = Mage::getModel('M2ePro/Magento_Product')
                ->getNameByProductId($dataForAdd['product_id']);
        } else {
            unset($dataForAdd['product_title']);
        }

        $dataForAdd['component_mode'] = $this->_componentMode;

        Mage::getModel('M2ePro/Listing_Log')
            ->setData($dataForAdd)
            ->save()
            ->getId();
    }

    protected function makeDataForAdd(
        $listingId,
        $initiator = Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN,
        $productId = null,
        $listingProductId = null,
        $actionId = null,
        $action = self::ACTION_UNKNOWN,
        $description = null,
        $type = self::TYPE_INFO,
        array $additionalData = array()
    ) {
        return array(
            'listing_id'         => (int)$listingId,
            'initiator'          => $initiator,
            'product_id'         => $productId,
            'listing_product_id' => $listingProductId,
            'action_id'          => $actionId,
            'action'             => $action,
            'description'        => $description,
            'type'               => $type,
            'additional_data'    => Mage::helper('M2ePro')->jsonEncode($additionalData)
        );
    }

    //########################################
}
