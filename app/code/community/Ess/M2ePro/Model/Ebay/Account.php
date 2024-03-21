<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Account getParentObject()
 */
class Ess_M2ePro_Model_Ebay_Account extends Ess_M2ePro_Model_Component_Child_Ebay_Abstract
{
    const MODE_SANDBOX    = 0;
    const MODE_PRODUCTION = 1;

    const FEEDBACKS_AUTO_RESPONSE_NONE   = 0;
    const FEEDBACKS_AUTO_RESPONSE_CYCLED = 1;
    const FEEDBACKS_AUTO_RESPONSE_RANDOM = 2;

    const OTHER_LISTINGS_MAPPING_TITLE_MODE_NONE             = 0;
    const OTHER_LISTINGS_MAPPING_TITLE_MODE_DEFAULT          = 1;
    const OTHER_LISTINGS_MAPPING_TITLE_MODE_CUSTOM_ATTRIBUTE = 2;

    const OTHER_LISTINGS_MAPPING_SKU_MODE_NONE             = 0;
    const OTHER_LISTINGS_MAPPING_SKU_MODE_DEFAULT          = 1;
    const OTHER_LISTINGS_MAPPING_SKU_MODE_PRODUCT_ID       = 2;
    const OTHER_LISTINGS_MAPPING_SKU_MODE_CUSTOM_ATTRIBUTE = 3;

    const OTHER_LISTINGS_MAPPING_ITEM_ID_MODE_NONE             = 0;
    const OTHER_LISTINGS_MAPPING_ITEM_ID_MODE_CUSTOM_ATTRIBUTE = 1;

    const OTHER_LISTINGS_MAPPING_SKU_DEFAULT_PRIORITY     = 1;
    const OTHER_LISTINGS_MAPPING_TITLE_DEFAULT_PRIORITY   = 2;
    const OTHER_LISTINGS_MAPPING_ITEM_ID_DEFAULT_PRIORITY = 3;

    const MAGENTO_ORDERS_LISTINGS_STORE_MODE_DEFAULT = 0;
    const MAGENTO_ORDERS_LISTINGS_STORE_MODE_CUSTOM  = 1;

    const MAGENTO_ORDERS_LISTINGS_OTHER_PRODUCT_MODE_IGNORE = 0;
    const MAGENTO_ORDERS_LISTINGS_OTHER_PRODUCT_MODE_IMPORT = 1;

    const MAGENTO_ORDERS_NUMBER_SOURCE_MAGENTO = 'magento';
    const MAGENTO_ORDERS_NUMBER_SOURCE_CHANNEL = 'channel';

    const MAGENTO_ORDERS_CREATE_CHECKOUT          = 2;
    const MAGENTO_ORDERS_CREATE_CHECKOUT_AND_PAID = 4;

    const MAGENTO_ORDERS_TAX_MODE_NONE    = 0;
    const MAGENTO_ORDERS_TAX_MODE_CHANNEL = 1;
    const MAGENTO_ORDERS_TAX_MODE_MAGENTO = 2;
    const MAGENTO_ORDERS_TAX_MODE_MIXED   = 3;

    const MAGENTO_ORDERS_CUSTOMER_MODE_GUEST      = 0;
    const MAGENTO_ORDERS_CUSTOMER_MODE_PREDEFINED = 1;
    const MAGENTO_ORDERS_CUSTOMER_MODE_NEW        = 2;

    const USE_SHIPPING_ADDRESS_AS_BILLING_ALWAYS                         = 0;
    const USE_SHIPPING_ADDRESS_AS_BILLING_IF_SAME_CUSTOMER_AND_RECIPIENT = 1;

    const MAGENTO_ORDERS_STATUS_MAPPING_MODE_DEFAULT = 0;
    const MAGENTO_ORDERS_STATUS_MAPPING_MODE_CUSTOM  = 1;

    const MAGENTO_ORDERS_STATUS_MAPPING_NEW     = 'pending';
    const MAGENTO_ORDERS_STATUS_MAPPING_PAID    = 'processing';
    const MAGENTO_ORDERS_STATUS_MAPPING_SHIPPED = 'complete';

    const MAGENTO_ORDERS_SKIP_EVTIN_MODE_ON  = 1;
    const MAGENTO_ORDERS_SKIP_EVTIN_MODE_OFF = 0;

    /** @var Ess_M2ePro_Model_ActiveRecord_Factory */
    protected $_activeRecordFactory;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Account');
        $this->_activeRecordFactory = Mage::getSingleton('M2ePro/ActiveRecord_Factory');
    }

    //########################################

    /**
     * @return bool
     */
    public function hasFeedbackTemplate()
    {
        return (bool)Mage::getModel('M2ePro/Ebay_Feedback_Template')->getCollection()
            ->addFieldToFilter('account_id', $this->getId())
            ->getSize();
    }

    //########################################

    /**
     * @return int
     */
    public function getMode()
    {
        return (int)$this->getData('mode');
    }

    public function getServerHash()
    {
        return $this->getData('server_hash');
    }

    public function getUserId()
    {
        return $this->getData('user_id');
    }

    /**
     * @return bool
     */
    public function isModeProduction()
    {
        return $this->getMode() == self::MODE_PRODUCTION;
    }

    /**
     * @return bool
     */
    public function isModeSandbox()
    {
        return $this->getMode() == self::MODE_SANDBOX;
    }

    /**
     * @return bool
     */
    public function isImportShipByDateToMagentoOrder()
    {
        return (bool)$this->getSetting(
            'magento_orders_settings',
            array('shipping_information', 'ship_by_date'),
            true
        );
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getFeedbacksReceive()
    {
        return (int)$this->getData('feedbacks_receive');
    }

    /**
     * @return bool
     */
    public function isFeedbacksReceive()
    {
        return $this->getFeedbacksReceive() == 1;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getFeedbacksAutoResponse()
    {
        return (int)$this->getData('feedbacks_auto_response');
    }

    /**
     * @return bool
     */
    public function isFeedbacksAutoResponseDisabled()
    {
        return $this->getFeedbacksAutoResponse() == self::FEEDBACKS_AUTO_RESPONSE_NONE;
    }

    /**
     * @return bool
     */
    public function isFeedbacksAutoResponseCycled()
    {
        return $this->getFeedbacksAutoResponse() == self::FEEDBACKS_AUTO_RESPONSE_CYCLED;
    }

    /**
     * @return bool
     */
    public function isFeedbacksAutoResponseRandom()
    {
        return $this->getFeedbacksAutoResponse() == self::FEEDBACKS_AUTO_RESPONSE_RANDOM;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getFeedbacksAutoResponseOnlyPositive()
    {
        return (int)$this->getData('feedbacks_auto_response_only_positive');
    }

    /**
     * @return bool
     */
    public function isFeedbacksAutoResponseOnlyPositive()
    {
        return $this->getFeedbacksAutoResponseOnlyPositive() == 1;
    }

    //########################################

    /**
     * @return int
     */
    public function getOtherListingsSynchronization()
    {
        return (int)$this->getData('other_listings_synchronization');
    }

    /**
     * @return int
     */
    public function getOtherListingsMappingMode()
    {
        return (int)$this->getData('other_listings_mapping_mode');
    }

    /**
     * @return array
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getOtherListingsMappingSettings()
    {
        return $this->getSettings('other_listings_mapping_settings');
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getOtherListingsMappingSkuMode()
    {
        $setting = $this->getSetting(
            'other_listings_mapping_settings',
            array('sku', 'mode'),
            self::OTHER_LISTINGS_MAPPING_SKU_MODE_NONE
        );

        return (int)$setting;
    }

    /**
     * @return int
     */
    public function getOtherListingsMappingSkuPriority()
    {
        $setting = $this->getSetting(
            'other_listings_mapping_settings',
            array('sku', 'priority'),
            self::OTHER_LISTINGS_MAPPING_SKU_DEFAULT_PRIORITY
        );

        return (int)$setting;
    }

    public function getOtherListingsMappingSkuAttribute()
    {
        $setting = $this->getSetting(
            'other_listings_mapping_settings',
            array('sku', 'attribute')
        );

        return $setting;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getOtherListingsMappingTitleMode()
    {
        $setting = $this->getSetting(
            'other_listings_mapping_settings',
            array('title', 'mode'),
            self::OTHER_LISTINGS_MAPPING_TITLE_MODE_NONE
        );

        return (int)$setting;
    }

    /**
     * @return int
     */
    public function getOtherListingsMappingTitlePriority()
    {
        $setting = $this->getSetting(
            'other_listings_mapping_settings',
            array('title', 'priority'),
            self::OTHER_LISTINGS_MAPPING_TITLE_DEFAULT_PRIORITY
        );

        return (int)$setting;
    }

    public function getOtherListingsMappingTitleAttribute()
    {
        $setting = $this->getSetting('other_listings_mapping_settings', array('title', 'attribute'));

        return $setting;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getOtherListingsMappingItemIdMode()
    {
        $setting = $this->getSetting(
            'other_listings_mapping_settings',
            array('item_id', 'mode'),
            self::OTHER_LISTINGS_MAPPING_ITEM_ID_MODE_NONE
        );

        return (int)$setting;
    }

    /**
     * @return int
     */
    public function getOtherListingsMappingItemIdPriority()
    {
        $setting = $this->getSetting(
            'other_listings_mapping_settings',
            array('item_id', 'priority'),
            self::OTHER_LISTINGS_MAPPING_ITEM_ID_DEFAULT_PRIORITY
        );

        return (int)$setting;
    }

    public function getOtherListingsMappingItemIdAttribute()
    {
        $setting = $this->getSetting('other_listings_mapping_settings', array('item_id', 'attribute'));

        return $setting;
    }

    //########################################

    /**
     * @return bool
     */
    public function isOtherListingsSynchronizationEnabled()
    {
        return $this->getOtherListingsSynchronization() == 1;
    }

    /**
     * @return bool
     */
    public function isOtherListingsMappingEnabled()
    {
        return $this->getOtherListingsMappingMode() == 1;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isOtherListingsMappingSkuModeNone()
    {
        return $this->getOtherListingsMappingSkuMode() == self::OTHER_LISTINGS_MAPPING_SKU_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isOtherListingsMappingSkuModeDefault()
    {
        return $this->getOtherListingsMappingSkuMode() == self::OTHER_LISTINGS_MAPPING_SKU_MODE_DEFAULT;
    }

    /**
     * @return bool
     */
    public function isOtherListingsMappingSkuModeCustomAttribute()
    {
        return $this->getOtherListingsMappingSkuMode() == self::OTHER_LISTINGS_MAPPING_SKU_MODE_CUSTOM_ATTRIBUTE;
    }

    /**
     * @return bool
     */
    public function isOtherListingsMappingSkuModeProductId()
    {
        return $this->getOtherListingsMappingSkuMode() == self::OTHER_LISTINGS_MAPPING_SKU_MODE_PRODUCT_ID;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isOtherListingsMappingTitleModeNone()
    {
        return $this->getOtherListingsMappingTitleMode() == self::OTHER_LISTINGS_MAPPING_TITLE_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isOtherListingsMappingTitleModeDefault()
    {
        return $this->getOtherListingsMappingTitleMode() == self::OTHER_LISTINGS_MAPPING_TITLE_MODE_DEFAULT;
    }

    /**
     * @return bool
     */
    public function isOtherListingsMappingTitleModeCustomAttribute()
    {
        return $this->getOtherListingsMappingTitleMode() == self::OTHER_LISTINGS_MAPPING_TITLE_MODE_CUSTOM_ATTRIBUTE;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isOtherListingsMappingItemIdModeNone()
    {
        return $this->getOtherListingsMappingItemIdMode() == self::OTHER_LISTINGS_MAPPING_ITEM_ID_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isOtherListingsMappingItemIdModeCustomAttribute()
    {
        return $this->getOtherListingsMappingItemIdMode() == self::OTHER_LISTINGS_MAPPING_ITEM_ID_MODE_CUSTOM_ATTRIBUTE;
    }

    //########################################

    /**
     * @param int $marketplaceId
     * @return int
     */
    public function getRelatedStoreId($marketplaceId)
    {
        $storeId = $this->getSetting('marketplaces_data', array((int)$marketplaceId, 'related_store_id'));

        return $storeId !== null ? (int)$storeId : Mage_Core_Model_App::ADMIN_STORE_ID;
    }

    //########################################

    /**
     * @return bool
     */
    public function isMagentoOrdersListingsModeEnabled()
    {
        $setting = $this->getSetting('magento_orders_settings', array('listing', 'mode'), 1);

        return $setting == 1;
    }

    /**
     * @return bool
     */
    public function isMagentoOrdersListingsStoreCustom()
    {
        $setting = $this->getSetting(
            'magento_orders_settings',
            array('listing', 'store_mode'),
            self::MAGENTO_ORDERS_LISTINGS_STORE_MODE_DEFAULT
        );

        return $setting == self::MAGENTO_ORDERS_LISTINGS_STORE_MODE_CUSTOM;
    }

    /**
     * @return int
     */
    public function getMagentoOrdersListingsStoreId()
    {
        $setting = $this->getSetting('magento_orders_settings', array('listing', 'store_id'), 0);

        return (int)$setting;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isMagentoOrdersListingsOtherModeEnabled()
    {
        $setting = $this->getSetting('magento_orders_settings', array('listing_other', 'mode'), 1);

        return $setting == 1;
    }

    /**
     * @return int
     */
    public function getMagentoOrdersListingsOtherStoreId()
    {
        $setting = $this->getSetting('magento_orders_settings', array('listing_other', 'store_id'), 0);

        return (int)$setting;
    }

    /**
     * @return bool
     */
    public function isMagentoOrdersListingsOtherProductImportEnabled()
    {
        $setting = $this->getSetting(
            'magento_orders_settings',
            array('listing_other', 'product_mode'),
            self::MAGENTO_ORDERS_LISTINGS_OTHER_PRODUCT_MODE_IMPORT
        );

        return $setting == self::MAGENTO_ORDERS_LISTINGS_OTHER_PRODUCT_MODE_IMPORT;
    }

    /**
     * @return int
     */
    public function getMagentoOrdersListingsOtherProductTaxClassId()
    {
        $setting = $this->getSetting(
            'magento_orders_settings',
            array('listing_other', 'product_tax_class_id'),
            Ess_M2ePro_Model_Magento_Product::TAX_CLASS_ID_NONE
        );

        return (int)$setting;
    }

    // ---------------------------------------

    public function getMagentoOrdersNumberSource()
    {
        $setting = $this->getSetting(
            'magento_orders_settings',
            array('number', 'source'),
            self::MAGENTO_ORDERS_NUMBER_SOURCE_MAGENTO
        );

        return $setting;
    }

    /**
     * @return bool
     */
    public function isMagentoOrdersNumberSourceMagento()
    {
        return $this->getMagentoOrdersNumberSource() == self::MAGENTO_ORDERS_NUMBER_SOURCE_MAGENTO;
    }

    /**
     * @return bool
     */
    public function isMagentoOrdersNumberSourceChannel()
    {
        return $this->getMagentoOrdersNumberSource() == self::MAGENTO_ORDERS_NUMBER_SOURCE_CHANNEL;
    }

    // ---------------------------------------

    /**
     * @return string
     */
    public function getMagentoOrdersNumberRegularPrefix()
    {
        $settings = $this->getSetting('magento_orders_settings', array('number', 'prefix'));

        return isset($settings['prefix']) ? $settings['prefix'] : '';
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isMagentoOrdersNumberMarketplacePrefixUsed()
    {
        $settings = $this->getSetting('magento_orders_settings', array('number', 'prefix'));

        return isset($settings['use_marketplace_prefix']) ? (bool)$settings['use_marketplace_prefix'] : false;
    }

    // ---------------------------------------

    public function getMagentoOrdersCreationMode()
    {
        $setting = $this->getSetting(
            'magento_orders_settings',
            array('creation', 'mode'),
            self::MAGENTO_ORDERS_CREATE_CHECKOUT_AND_PAID
        );

        return $setting;
    }

    /**
     * @return bool
     */
    public function shouldCreateMagentoOrderWhenCheckedOut()
    {
        return $this->getMagentoOrdersCreationMode() == self::MAGENTO_ORDERS_CREATE_CHECKOUT;
    }

    /**
     * @return bool
     */
    public function shouldCreateMagentoOrderWhenCheckedOutAndPaid()
    {
        return $this->getMagentoOrdersCreationMode() == self::MAGENTO_ORDERS_CREATE_CHECKOUT_AND_PAID;
    }

    /**
     * @return int
     */
    public function getQtyReservationDays()
    {
        $setting = $this->getSetting('magento_orders_settings', array('qty_reservation', 'days'), 1);

        return (int)$setting;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isMagentoOrdersTaxModeNone()
    {
        $setting = $this->getSetting(
            'magento_orders_settings',
            array('tax', 'mode'),
            self::MAGENTO_ORDERS_TAX_MODE_MIXED
        );

        return $setting == self::MAGENTO_ORDERS_TAX_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isMagentoOrdersTaxModeChannel()
    {
        $setting = $this->getSetting(
            'magento_orders_settings',
            array('tax', 'mode'),
            self::MAGENTO_ORDERS_TAX_MODE_MIXED
        );

        return $setting == self::MAGENTO_ORDERS_TAX_MODE_CHANNEL;
    }

    /**
     * @return bool
     */
    public function isMagentoOrdersTaxModeMagento()
    {
        $setting = $this->getSetting(
            'magento_orders_settings',
            array('tax', 'mode'),
            self::MAGENTO_ORDERS_TAX_MODE_MIXED
        );

        return $setting == self::MAGENTO_ORDERS_TAX_MODE_MAGENTO;
    }

    /**
     * @return bool
     */
    public function isMagentoOrdersTaxModeMixed()
    {
        $setting = $this->getSetting(
            'magento_orders_settings',
            array('tax', 'mode'),
            self::MAGENTO_ORDERS_TAX_MODE_MIXED
        );

        return $setting == self::MAGENTO_ORDERS_TAX_MODE_MIXED;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isMagentoOrdersCustomerGuest()
    {
        $setting = $this->getSetting(
            'magento_orders_settings',
            array('customer', 'mode'),
            self::MAGENTO_ORDERS_CUSTOMER_MODE_GUEST
        );

        return $setting == self::MAGENTO_ORDERS_CUSTOMER_MODE_GUEST;
    }

    /**
     * @return bool
     */
    public function isMagentoOrdersCustomerPredefined()
    {
        $setting = $this->getSetting(
            'magento_orders_settings',
            array('customer', 'mode'),
            self::MAGENTO_ORDERS_CUSTOMER_MODE_GUEST
        );

        return $setting == self::MAGENTO_ORDERS_CUSTOMER_MODE_PREDEFINED;
    }

    /**
     * @return bool
     */
    public function isMagentoOrdersCustomerNew()
    {
        $setting = $this->getSetting(
            'magento_orders_settings',
            array('customer', 'mode'),
            self::MAGENTO_ORDERS_CUSTOMER_MODE_GUEST
        );

        return $setting == self::MAGENTO_ORDERS_CUSTOMER_MODE_NEW;
    }

    /**
     * @return int
     */
    public function getMagentoOrdersCustomerId()
    {
        $setting = $this->getSetting('magento_orders_settings', array('customer', 'id'));

        return (int)$setting;
    }

    /**
     * @return bool
     */
    public function isMagentoOrdersCustomerNewSubscribed()
    {
        $setting = $this->getSetting('magento_orders_settings', array('customer', 'subscription_mode'), 0);

        return $setting == 1;
    }

    /**
     * @return bool
     */
    public function isMagentoOrdersCustomerNewNotifyWhenCreated()
    {
        $setting = $this->getSetting('magento_orders_settings', array('customer', 'notifications', 'customer_created'));

        return (bool)$setting;
    }

    /**
     * @return bool
     */
    public function isMagentoOrdersCustomerNewNotifyWhenOrderCreated()
    {
        $setting = $this->getSetting('magento_orders_settings', array('customer', 'notifications', 'order_created'));

        return (bool)$setting;
    }

    /**
     * @return bool
     */
    public function isMagentoOrdersCustomerNewNotifyWhenInvoiceCreated()
    {
        $setting = $this->getSetting('magento_orders_settings', array('customer', 'notifications', 'invoice_created'));

        return (bool)$setting;
    }

    /**
     * @return int
     */
    public function getMagentoOrdersCustomerNewWebsiteId()
    {
        $setting = $this->getSetting('magento_orders_settings', array('customer', 'website_id'));

        return (int)$setting;
    }

    /**
     * @return int
     */
    public function getMagentoOrdersCustomerNewGroupId()
    {
        $setting = $this->getSetting('magento_orders_settings', array('customer', 'group_id'));

        return (int)$setting;
    }

    /**
     * @return bool
     */
    public function useMagentoOrdersShippingAddressAsBillingAlways()
    {
        return $this->getBillingAddressMode() == self::USE_SHIPPING_ADDRESS_AS_BILLING_ALWAYS;
    }

    /**
     * @return bool
     */
    public function useMagentoOrdersShippingAddressAsBillingIfSameCustomerAndRecipient()
    {
        return $this->getBillingAddressMode() == self::USE_SHIPPING_ADDRESS_AS_BILLING_IF_SAME_CUSTOMER_AND_RECIPIENT;
    }

    private function getBillingAddressMode()
    {
        return $this->getSetting(
            'magento_orders_settings',
            array('customer', 'billing_address_mode'),
            self::USE_SHIPPING_ADDRESS_AS_BILLING_IF_SAME_CUSTOMER_AND_RECIPIENT
        );
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isMagentoOrdersStatusMappingDefault()
    {
        $setting = $this->getSetting(
            'magento_orders_settings',
            array('status_mapping', 'mode'),
            self::MAGENTO_ORDERS_STATUS_MAPPING_MODE_DEFAULT
        );

        return $setting == self::MAGENTO_ORDERS_STATUS_MAPPING_MODE_DEFAULT;
    }

    // ---------------------------------------

    public function getMagentoOrdersStatusNew()
    {
        if ($this->isMagentoOrdersStatusMappingDefault()) {
            return self::MAGENTO_ORDERS_STATUS_MAPPING_NEW;
        }

        return $this->getSetting('magento_orders_settings', array('status_mapping', 'new'));
    }

    public function getMagentoOrdersStatusPaid()
    {
        if ($this->isMagentoOrdersStatusMappingDefault()) {
            return self::MAGENTO_ORDERS_STATUS_MAPPING_PAID;
        }

        return $this->getSetting('magento_orders_settings', array('status_mapping', 'paid'));
    }

    public function getMagentoOrdersStatusShipped()
    {
        if ($this->isMagentoOrdersStatusMappingDefault()) {
            return self::MAGENTO_ORDERS_STATUS_MAPPING_SHIPPED;
        }

        return $this->getSetting('magento_orders_settings', array('status_mapping', 'shipped'));
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isRefundEnabled()
    {
        $setting = $this->getSetting('magento_orders_settings', array('refund_and_cancellation', 'refund_mode'));
        return (bool)$setting;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isMagentoOrdersInvoiceEnabled()
    {
        return (bool)$this->getData('create_magento_invoice');
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isMagentoOrdersShipmentEnabled()
    {
        return (bool)$this->getData('create_magento_shipment');
    }

    /**
     * @return bool
     */
    public function isSkipEvtinModeOn()
    {
        return (bool)$this->getData('skip_evtin');
    }

    //########################################

    /**
     * @return array
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getUserPreferences()
    {
        return $this->getSettings('user_preferences');
    }

    public function updateUserPreferences()
    {
        $dispatcherObject = Mage::getModel('M2ePro/Ebay_Connector_Dispatcher');
        $connectorObj = $dispatcherObject->getVirtualConnector(
            'account',
            'get',
            'userPreferences',
            array(),
            null,
            null,
            $this->getId()
        );

        $dispatcherObject->process($connectorObj);
        $responseData = $connectorObj->getResponseData();

        if (empty($responseData['user_preferences'])) {
            return;
        }

        $this->setData(
            'user_preferences',
            Mage::helper('M2ePro')->jsonEncode(
                $responseData['user_preferences']
            )
        )->save();
    }

    // ---------------------------------------

    /**
     * @param bool $returnRealValue
     * @return bool|null
     */
    public function getOutOfStockControl()
    {
        $userPreferences = $this->getUserPreferences();
        if (isset($userPreferences['OutOfStockControlPreference'])) {
            return strtolower($userPreferences['OutOfStockControlPreference']) === 'true';
        }

        return false;
    }

    //########################################

    public function getRateTables()
    {
        return $this->getSettings('rate_tables');
    }

    public function updateRateTables()
    {
        $isTokenExist = $this->isTokenExist();

        if (empty($isTokenExist)) {
            return;
        }

        $dispatcherObject = Mage::getModel('M2ePro/Ebay_Connector_Dispatcher');
        $connectorObj = $dispatcherObject->getVirtualConnector(
            'account',
            'get',
            'shippingRateTables',
            array(),
            null,
            null,
            $this->getId()
        );

        $dispatcherObject->process($connectorObj);
        $responseData = $connectorObj->getResponseData();

        if (empty($responseData)) {
            return;
        }

        $this->setData('rate_tables', Mage::helper('M2ePro')->jsonEncode($responseData))->save();
    }

    public function isRateTablesExist()
    {
        $rateTables = $this->getRateTables();
        return !empty($rateTables);
    }

    // ---------------------------------------

    public function getSellApiTokenExpiredDate()
    {
        return $this->getData('sell_api_token_expired_date');
    }

    //The is_token_exist flag is needed for migration from Trading Api token to Sell Api token
    public function isTokenExist()
    {
        return (bool)$this->getData('is_token_exist');
    }

    public function setIsTokenExist($isTokenExist)
    {
        $this->setData('is_token_exist', $isTokenExist);
    }

    // ---------------------------------------

    public function getFeedbacksLastUsedId()
    {
        return $this->getData('feedbacks_last_used_id');
    }

    // ---------------------------------------

    public function getEbayStoreTitle()
    {
        return $this->getData('ebay_store_title');
    }

    public function getEbayStoreUrl()
    {
        return $this->getData('ebay_store_url');
    }

    public function getEbayStoreSubscriptionLevel()
    {
        return $this->getData('ebay_store_subscription_level');
    }

    public function getEbayStoreDescription()
    {
        return $this->getData('ebay_store_description');
    }

    public function getEbayStoreCategory($id)
    {
        $tableAccountStoreCategories = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_ebay_account_store_category');

        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $dbSelect = $connRead->select()
            ->from($tableAccountStoreCategories, '*')
            ->where('`account_id` = ?', (int)$this->getId())
            ->where('`category_id` = ?', (int)$id)
            ->order(array('sorder ASC'));

        $categories = $connRead->fetchAll($dbSelect);

        return !empty($categories) ? $categories[0] : array();
    }

    /**
     * @return array
     */
    public function getEbayStoreCategories()
    {
        $tableAccountStoreCategories = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_ebay_account_store_category');

        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $dbSelect = $connRead->select()
            ->from($tableAccountStoreCategories, '*')
            ->where('`account_id` = ?', (int)$this->getId())
            ->order(array('sorder ASC'));

        return $connRead->fetchAll($dbSelect);
    }

    public function buildEbayStoreCategoriesTreeRec($data, $rootId)
    {
        $children = array();

        foreach ($data as $node) {
            if ($node['parent_id'] == $rootId) {
                $children[] = array(
                    'id'        => $node['category_id'],
                    'text'      => $node['title'],
                    'allowDrop' => false,
                    'allowDrag' => false,
                    'children'  => array()
                );
            }
        }

        foreach ($children as &$child) {
            $child['children'] = $this->buildEbayStoreCategoriesTreeRec($data, $child['id']);
        }

        return $children;
    }

    public function buildEbayStoreCategoriesTree()
    {
        return $this->buildEbayStoreCategoriesTreeRec($this->getEbayStoreCategories(), 0);
    }

    //########################################

    public function updateShippingDiscountProfiles($marketplaceId)
    {
        $dispatcherObj = Mage::getModel('M2ePro/Ebay_Connector_Dispatcher');
        $connectorObj = $dispatcherObj->getVirtualConnector(
            'account',
            'get',
            'shippingDiscountProfiles',
            array(),
            null,
            $marketplaceId,
            $this->getId(),
            null
        );

        $dispatcherObj->process($connectorObj);
        $data = $connectorObj->getResponseData();

        if (empty($data)) {
            return;
        }

        if ($this->getData('ebay_shipping_discount_profiles') === null) {
            $profiles = array();
        } else {
            $profiles = Mage::helper('M2ePro')->jsonDecode($this->getData('ebay_shipping_discount_profiles'));
        }

        $profiles[$marketplaceId] = $data;

        $this->setData('ebay_shipping_discount_profiles', Mage::helper('M2ePro')->jsonEncode($profiles))
            ->save();
    }

    //########################################

    public function save()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('account');

        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('account');

        return parent::delete();
    }

    public function isRegionOverrideRequired()
    {
        return (bool)$this->getSetting(
            'magento_orders_settings',
            array('shipping_information', 'shipping_address_region_override'),
            1
        );
    }
}
