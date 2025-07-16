<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Account getParentObject()
 */
class Ess_M2ePro_Model_Walmart_Account extends Ess_M2ePro_Model_Component_Child_Walmart_Abstract
{
    const OTHER_LISTINGS_MAPPING_SKU_MODE_NONE             = 0;
    const OTHER_LISTINGS_MAPPING_SKU_MODE_DEFAULT          = 1;
    const OTHER_LISTINGS_MAPPING_SKU_MODE_CUSTOM_ATTRIBUTE = 2;
    const OTHER_LISTINGS_MAPPING_SKU_MODE_PRODUCT_ID       = 3;

    const OTHER_LISTINGS_MAPPING_UPC_MODE_NONE             = 0;
    const OTHER_LISTINGS_MAPPING_UPC_MODE_CUSTOM_ATTRIBUTE = 2;

    const OTHER_LISTINGS_MAPPING_GTIN_MODE_NONE             = 0;
    const OTHER_LISTINGS_MAPPING_GTIN_MODE_CUSTOM_ATTRIBUTE = 2;

    const OTHER_LISTINGS_MAPPING_WPID_MODE_NONE             = 0;
    const OTHER_LISTINGS_MAPPING_WPID_MODE_CUSTOM_ATTRIBUTE = 2;

    const OTHER_LISTINGS_MAPPING_TITLE_MODE_NONE             = 0;
    const OTHER_LISTINGS_MAPPING_TITLE_MODE_DEFAULT          = 1;
    const OTHER_LISTINGS_MAPPING_TITLE_MODE_CUSTOM_ATTRIBUTE = 2;

    const OTHER_LISTINGS_MAPPING_SKU_DEFAULT_PRIORITY        = 1;
    const OTHER_LISTINGS_MAPPING_UPC_DEFAULT_PRIORITY        = 2;
    const OTHER_LISTINGS_MAPPING_GTIN_DEFAULT_PRIORITY       = 3;
    const OTHER_LISTINGS_MAPPING_WPID_DEFAULT_PRIORITY       = 4;
    const OTHER_LISTINGS_MAPPING_TITLE_DEFAULT_PRIORITY      = 5;

    const MAGENTO_ORDERS_LISTINGS_STORE_MODE_DEFAULT = 0;
    const MAGENTO_ORDERS_LISTINGS_STORE_MODE_CUSTOM  = 1;

    const MAGENTO_ORDERS_LISTINGS_OTHER_PRODUCT_MODE_IGNORE = 0;
    const MAGENTO_ORDERS_LISTINGS_OTHER_PRODUCT_MODE_IMPORT = 1;

    const MAGENTO_ORDERS_NUMBER_SOURCE_MAGENTO = 'magento';
    const MAGENTO_ORDERS_NUMBER_SOURCE_CHANNEL = 'channel';

    const MAGENTO_ORDERS_TAX_MODE_NONE    = 0;
    const MAGENTO_ORDERS_TAX_MODE_CHANNEL = 1;
    const MAGENTO_ORDERS_TAX_MODE_MAGENTO = 2;
    const MAGENTO_ORDERS_TAX_MODE_MIXED   = 3;

    const MAGENTO_ORDERS_CUSTOMER_MODE_GUEST      = 0;
    const MAGENTO_ORDERS_CUSTOMER_MODE_PREDEFINED = 1;
    const MAGENTO_ORDERS_CUSTOMER_MODE_NEW        = 2;

    const MAGENTO_ORDERS_STATUS_MAPPING_MODE_DEFAULT = 0;
    const MAGENTO_ORDERS_STATUS_MAPPING_MODE_CUSTOM  = 1;

    const MAGENTO_ORDERS_STATUS_MAPPING_NEW        = 'pending';
    const MAGENTO_ORDERS_STATUS_MAPPING_PROCESSING = 'processing';
    const MAGENTO_ORDERS_STATUS_MAPPING_SHIPPED    = 'complete';

    /**
     * @var Ess_M2ePro_Model_Marketplace
     */
    protected $_marketplaceModel = null;

    /** @var Ess_M2ePro_Model_ActiveRecord_Factory */
    protected $_activeRecordFactory;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Walmart_Account');
        $this->_activeRecordFactory = Mage::getSingleton('M2ePro/ActiveRecord_Factory');
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    public function getMarketplace()
    {
        if ($this->_marketplaceModel === null) {
            $this->_marketplaceModel = Mage::helper('M2ePro/Component_Walmart')->getCachedObject(
                'Marketplace', $this->getMarketplaceId()
            );
        }

        return $this->_marketplaceModel;
    }

    /**
     * @param Ess_M2ePro_Model_Marketplace $instance
     */
    public function setMarketplace(Ess_M2ePro_Model_Marketplace $instance)
    {
         $this->_marketplaceModel = $instance;
    }

    //########################################

    public function getServerHash()
    {
        return $this->getData('server_hash');
    }

    /**
     * @return int
     */
    public function getMarketplaceId()
    {
        return (int)$this->getData('marketplace_id');
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return (string)$this->getData(Ess_M2ePro_Model_Resource_Walmart_Account::COLUMN_IDENTIFIER);
    }

    /**
     * @return int
     */
    public function getRelatedStoreId()
    {
        return (int)$this->getData('related_store_id');
    }

    // ---------------------------------------

    public function getInfo()
    {
        return $this->getData('info');
    }

    /**
     * @return array|null
     */
    public function getDecodedInfo()
    {
        $tempInfo = $this->getInfo();
        return $tempInfo === null ? null : Mage::helper('M2ePro')->jsonDecode($tempInfo);
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
     * @return string
     */
    public function getInventoryLastSynchronization()
    {
        return $this->getData('inventory_last_synchronization');
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
    public function getOtherListingsMappingUpcMode()
    {
        $setting = $this->getSetting(
            'other_listings_mapping_settings',
            array('upc', 'mode'),
            self::OTHER_LISTINGS_MAPPING_UPC_MODE_NONE
        );

        return (int)$setting;
    }

    /**
     * @return int
     */
    public function getOtherListingsMappingUpcPriority()
    {
        $setting = $this->getSetting(
            'other_listings_mapping_settings',
            array('upc', 'priority'),
            self::OTHER_LISTINGS_MAPPING_UPC_DEFAULT_PRIORITY
        );

        return (int)$setting;
    }

    public function getOtherListingsMappingUpcAttribute()
    {
        $setting = $this->getSetting(
            'other_listings_mapping_settings',
            array('upc', 'attribute')
        );

        return $setting;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getOtherListingsMappingGtinMode()
    {
        $setting = $this->getSetting(
            'other_listings_mapping_settings',
            array('gtin', 'mode'),
            self::OTHER_LISTINGS_MAPPING_GTIN_MODE_NONE
        );

        return (int)$setting;
    }

    /**
     * @return int
     */
    public function getOtherListingsMappingGtinPriority()
    {
        $setting = $this->getSetting(
            'other_listings_mapping_settings',
            array('gtin', 'priority'),
            self::OTHER_LISTINGS_MAPPING_GTIN_DEFAULT_PRIORITY
        );

        return (int)$setting;
    }

    public function getOtherListingsMappingGtinAttribute()
    {
        $setting = $this->getSetting(
            'other_listings_mapping_settings',
            array('gtin', 'attribute')
        );

        return $setting;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getOtherListingsMappingWpidMode()
    {
        $setting = $this->getSetting(
            'other_listings_mapping_settings',
            array('wpid', 'mode'),
            self::OTHER_LISTINGS_MAPPING_WPID_MODE_NONE
        );

        return (int)$setting;
    }

    /**
     * @return int
     */
    public function getOtherListingsMappingWpidPriority()
    {
        $setting = $this->getSetting(
            'other_listings_mapping_settings',
            array('wpid', 'priority'),
            self::OTHER_LISTINGS_MAPPING_WPID_DEFAULT_PRIORITY
        );

        return (int)$setting;
    }

    public function getOtherListingsMappingWpidAttribute()
    {
        $setting = $this->getSetting(
            'other_listings_mapping_settings',
            array('wpid', 'attribute')
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
    public function isOtherListingsMappingGtinModeNone()
    {
        return $this->getOtherListingsMappingGtinMode() == self::OTHER_LISTINGS_MAPPING_GTIN_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isOtherListingsMappingGtinModeCustomAttribute()
    {
        return $this->getOtherListingsMappingGtinMode() == self::OTHER_LISTINGS_MAPPING_GTIN_MODE_CUSTOM_ATTRIBUTE;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isOtherListingsMappingWpidModeNone()
    {
        return $this->getOtherListingsMappingWpidMode() == self::OTHER_LISTINGS_MAPPING_WPID_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isOtherListingsMappingWpidModeCustomAttribute()
    {
        return $this->getOtherListingsMappingWpidMode() == self::OTHER_LISTINGS_MAPPING_WPID_MODE_CUSTOM_ATTRIBUTE;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isOtherListingsMappingUpcModeNone()
    {
        return $this->getOtherListingsMappingUpcMode() == self::OTHER_LISTINGS_MAPPING_UPC_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isOtherListingsMappingUpcModeCustomAttribute()
    {
        return $this->getOtherListingsMappingUpcMode() == self::OTHER_LISTINGS_MAPPING_UPC_MODE_CUSTOM_ATTRIBUTE;
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
            'magento_orders_settings', array('listing', 'store_mode'),
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
            'magento_orders_settings', array('listing_other', 'product_mode'),
            self::MAGENTO_ORDERS_LISTINGS_OTHER_PRODUCT_MODE_IMPORT
        );

        return $setting == self::MAGENTO_ORDERS_LISTINGS_OTHER_PRODUCT_MODE_IMPORT;
    }

    /**
     * @return int
     */
    public function getMagentoOrdersListingsOtherProductTaxClassId()
    {
        $setting = $this->getSetting('magento_orders_settings', array('listing_other', 'product_tax_class_id'));

        return (int)$setting;
    }

    // ---------------------------------------

    public function getMagentoOrdersNumberSource()
    {
        $setting = $this->getSetting(
            'magento_orders_settings', array('number', 'source'), self::MAGENTO_ORDERS_NUMBER_SOURCE_MAGENTO
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

    public function getQtyReservationDays()
    {
        $setting = $this->getSetting('magento_orders_settings', array('qty_reservation', 'days'), 1);

        return (int)$setting;
    }

    // ----------------------------------------


    /**
     * @return bool
     */
    public function isMagentoOrdersTaxModeNone()
    {
        $setting = $this->getSetting('magento_orders_settings', array('tax', 'mode'));

        return $setting == self::MAGENTO_ORDERS_TAX_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isMagentoOrdersTaxModeChannel()
    {
        $setting = $this->getSetting('magento_orders_settings', array('tax', 'mode'));

        return $setting == self::MAGENTO_ORDERS_TAX_MODE_CHANNEL;
    }

    /**
     * @return bool
     */
    public function isMagentoOrdersTaxModeMagento()
    {
        $setting = $this->getSetting('magento_orders_settings', array('tax', 'mode'));

        return $setting == self::MAGENTO_ORDERS_TAX_MODE_MAGENTO;
    }

    /**
     * @return bool
     */
    public function isMagentoOrdersTaxModeMixed()
    {
        $setting = $this->getSetting('magento_orders_settings', array('tax', 'mode'));

        return $setting == self::MAGENTO_ORDERS_TAX_MODE_MIXED;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isMagentoOrdersCustomerGuest()
    {
        $setting = $this->getSetting(
            'magento_orders_settings', array('customer', 'mode'),
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
            'magento_orders_settings', array('customer', 'mode'),
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
            'magento_orders_settings', array('customer', 'mode'),
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

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isMagentoOrdersStatusMappingDefault()
    {
        $setting = $this->getSetting(
            'magento_orders_settings', array('status_mapping', 'mode'),
            self::MAGENTO_ORDERS_STATUS_MAPPING_MODE_DEFAULT
        );

        return $setting == self::MAGENTO_ORDERS_STATUS_MAPPING_MODE_DEFAULT;
    }

    public function getMagentoOrdersStatusProcessing()
    {
        if ($this->isMagentoOrdersStatusMappingDefault()) {
            return self::MAGENTO_ORDERS_STATUS_MAPPING_PROCESSING;
        }

        return $this->getSetting('magento_orders_settings', array('status_mapping', 'processing'));
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

    public function isMagentoOrdersInvoiceEnabled()
    {
        return (bool)$this->getData('create_magento_invoice');
    }

    public function isMagentoOrdersShipmentEnabled()
    {
        return (bool)$this->getData('create_magento_shipment');
    }

    /**
     * @return array
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getOtherCarriers()
    {
        return $this->getSettings('other_carriers');
    }

    public function deleteProcessingList()
    {
        $inventorySkuCollection = $this->_activeRecordFactory->getObjectCollection('Walmart_Listing_Product_Action_ProcessingList');
        $inventorySkuCollection->addFieldToFilter('account_id', $this->getId());
        foreach ($inventorySkuCollection->getItems() as $item) {
            $item->deleteInstance();
        }
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
