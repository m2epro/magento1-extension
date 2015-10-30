<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Buy_Account extends Ess_M2ePro_Model_Component_Child_Buy_Abstract
{
    const OTHER_LISTINGS_SYNCHRONIZATION_NO  = 0;
    const OTHER_LISTINGS_SYNCHRONIZATION_YES = 1;

    const OTHER_LISTINGS_MAPPING_MODE_NO  = 0;
    const OTHER_LISTINGS_MAPPING_MODE_YES = 1;

    const OTHER_LISTINGS_MAPPING_GENERAL_ID_MODE_NONE             = 0;
    const OTHER_LISTINGS_MAPPING_GENERAL_ID_MODE_CUSTOM_ATTRIBUTE = 1;

    const OTHER_LISTINGS_MAPPING_SKU_MODE_NONE             = 0;
    const OTHER_LISTINGS_MAPPING_SKU_MODE_DEFAULT          = 1;
    const OTHER_LISTINGS_MAPPING_SKU_MODE_CUSTOM_ATTRIBUTE = 2;
    const OTHER_LISTINGS_MAPPING_SKU_MODE_PRODUCT_ID       = 3;

    const OTHER_LISTINGS_MAPPING_SKU_DEFAULT_PRIORITY        = 1;
    const OTHER_LISTINGS_MAPPING_GENERAL_ID_DEFAULT_PRIORITY = 2;
    const OTHER_LISTINGS_MAPPING_TITLE_DEFAULT_PRIORITY      = 3;

    const OTHER_LISTINGS_MOVE_TO_LISTINGS_DISABLED = 0;
    const OTHER_LISTINGS_MOVE_TO_LISTINGS_ENABLED  = 1;

    const OTHER_LISTINGS_MOVE_TO_LISTINGS_SYNCH_MODE_NONE = 0;
    const OTHER_LISTINGS_MOVE_TO_LISTINGS_SYNCH_MODE_ALL  = 1;
    const OTHER_LISTINGS_MOVE_TO_LISTINGS_SYNCH_MODE_PRICE  = 2;
    const OTHER_LISTINGS_MOVE_TO_LISTINGS_SYNCH_MODE_QTY  = 3;

    const MAGENTO_ORDERS_LISTINGS_MODE_NO  = 0;
    const MAGENTO_ORDERS_LISTINGS_MODE_YES = 1;

    const MAGENTO_ORDERS_LISTINGS_STORE_MODE_DEFAULT = 0;
    const MAGENTO_ORDERS_LISTINGS_STORE_MODE_CUSTOM  = 1;

    const MAGENTO_ORDERS_LISTINGS_OTHER_MODE_NO  = 0;
    const MAGENTO_ORDERS_LISTINGS_OTHER_MODE_YES = 1;

    const MAGENTO_ORDERS_LISTINGS_OTHER_PRODUCT_MODE_IGNORE = 0;
    const MAGENTO_ORDERS_LISTINGS_OTHER_PRODUCT_MODE_IMPORT = 1;

    const MAGENTO_ORDERS_NUMBER_SOURCE_MAGENTO = 'magento';
    const MAGENTO_ORDERS_NUMBER_SOURCE_CHANNEL = 'channel';

    const MAGENTO_ORDERS_NUMBER_PREFIX_MODE_NO  = 0;
    const MAGENTO_ORDERS_NUMBER_PREFIX_MODE_YES = 1;

    const MAGENTO_ORDERS_TAX_MODE_NONE    = 0;
    const MAGENTO_ORDERS_TAX_MODE_CHANNEL = 1;
    const MAGENTO_ORDERS_TAX_MODE_MAGENTO = 2;
    const MAGENTO_ORDERS_TAX_MODE_MIXED   = 3;

    const MAGENTO_ORDERS_CUSTOMER_MODE_GUEST      = 0;
    const MAGENTO_ORDERS_CUSTOMER_MODE_PREDEFINED = 1;
    const MAGENTO_ORDERS_CUSTOMER_MODE_NEW        = 2;

    const MAGENTO_ORDERS_CUSTOMER_NEW_SUBSCRIPTION_MODE_NO  = 0;
    const MAGENTO_ORDERS_CUSTOMER_NEW_SUBSCRIPTION_MODE_YES = 1;

    const MAGENTO_ORDERS_STATUS_MAPPING_MODE_DEFAULT = 0;
    const MAGENTO_ORDERS_STATUS_MAPPING_MODE_CUSTOM  = 1;

    const MAGENTO_ORDERS_STATUS_MAPPING_NEW        = 'pending';
    const MAGENTO_ORDERS_STATUS_MAPPING_PROCESSING = 'processing';
    const MAGENTO_ORDERS_STATUS_MAPPING_SHIPPED    = 'complete';

    const MAGENTO_ORDERS_INVOICE_MODE_NO  = 0;
    const MAGENTO_ORDERS_INVOICE_MODE_YES = 1;

    const MAGENTO_ORDERS_SHIPMENT_MODE_NO  = 0;
    const MAGENTO_ORDERS_SHIPMENT_MODE_YES = 1;

    const FTP_NEW_SKU_ACCESS_NO  = 0;
    const FTP_NEW_SKU_ACCESS_YES = 1;

    const FTP_INVENTORY_ACCESS_NO  = 0;
    const FTP_INVENTORY_ACCESS_YES = 1;

    const FTP_ORDERS_ACCESS_NO  = 0;
    const FTP_ORDERS_ACCESS_YES = 1;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Buy_Account');
    }

    //########################################

    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        $items = $this->getBuyItems(true);
        foreach ($items as $item) {
            $item->deleteInstance();
        }

        $this->delete();

        return true;
    }

    //########################################

    public function getBuyItems($asObjects = false, array $filters = array())
    {
        return $this->getRelatedSimpleItems('Buy_Item','account_id',$asObjects,$filters);
    }

    //########################################

    public function getServerHash()
    {
        return $this->getData('server_hash');
    }

    public function getSellerId()
    {
        return $this->getData('seller_id');
    }

    /**
     * @return int
     */
    public function getRelatedStoreId()
    {
        return (int)$this->getData('related_store_id');
    }

    // ---------------------------------------

    public function getWebLogin()
    {
        return $this->getData('web_login');
    }

    public function getFtpLogin()
    {
        return $this->getData('ftp_login');
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getFtpNewSkuAccess()
    {
        return (int)$this->getData('ftp_new_sku_access');
    }

    /**
     * @return int
     */
    public function getFtpInventoryAccess()
    {
        return (int)$this->getData('ftp_inventory_access');
    }

    /**
     * @return int
     */
    public function getFtpOrdersAccess()
    {
        return (int)$this->getData('ftp_orders_access');
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isFtpNewSkuAccessEnabled()
    {
        return $this->getFtpNewSkuAccess() == self::FTP_NEW_SKU_ACCESS_YES;
    }

    /**
     * @return bool
     */
    public function isFtpInventoryAccessEnabled()
    {
        return $this->getFtpInventoryAccess() == self::FTP_INVENTORY_ACCESS_YES;
    }

    /**
     * @return bool
     */
    public function isFtpOrdersAccessEnabled()
    {
        return $this->getFtpOrdersAccess() == self::FTP_ORDERS_ACCESS_YES;
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
        return is_null($tempInfo) ? NULL : json_decode($tempInfo,true);
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
    public function getOtherListingsMappingGeneralIdMode()
    {
        $setting = $this->getSetting('other_listings_mapping_settings',
                                     array('general_id', 'mode'),
                                     self::OTHER_LISTINGS_MAPPING_GENERAL_ID_MODE_NONE);

        return (int)$setting;
    }

    /**
     * @return int
     */
    public function getOtherListingsMappingGeneralIdPriority()
    {
        $setting = $this->getSetting('other_listings_mapping_settings',
                                     array('general_id', 'priority'),
                                     self::OTHER_LISTINGS_MAPPING_GENERAL_ID_DEFAULT_PRIORITY);

        return (int)$setting;
    }

    public function getOtherListingsMappingGeneralIdAttribute()
    {
        $setting = $this->getSetting('other_listings_mapping_settings', array('general_id', 'attribute'));
        return $setting;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getOtherListingsMappingSkuMode()
    {
        $setting = $this->getSetting('other_listings_mapping_settings',
                                     array('sku', 'mode'),
                                     self::OTHER_LISTINGS_MAPPING_SKU_MODE_NONE);

        return (int)$setting;
    }

    /**
     * @return int
     */
    public function getOtherListingsMappingSkuPriority()
    {
        $setting = $this->getSetting('other_listings_mapping_settings',
                                     array('sku', 'priority'),
                                     self::OTHER_LISTINGS_MAPPING_SKU_DEFAULT_PRIORITY);

        return (int)$setting;
    }

    public function getOtherListingsMappingSkuAttribute()
    {
        $setting = $this->getSetting('other_listings_mapping_settings',
                                     array('sku', 'attribute'));

        return $setting;
    }

    //########################################

    /**
     * @return bool
     */
    public function isOtherListingsSynchronizationEnabled()
    {
        return $this->getOtherListingsSynchronization() == self::OTHER_LISTINGS_SYNCHRONIZATION_YES;
    }

    /**
     * @return bool
     */
    public function isOtherListingsMappingEnabled()
    {
        return $this->getOtherListingsMappingMode() == self::OTHER_LISTINGS_MAPPING_MODE_YES;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isOtherListingsMappingGeneralIdModeNone()
    {
        return $this->getOtherListingsMappingGeneralIdMode() == self::OTHER_LISTINGS_MAPPING_GENERAL_ID_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isOtherListingsMappingGeneralIdModeCustomAttribute()
    {
        return $this->getOtherListingsMappingGeneralIdMode() ==
            self::OTHER_LISTINGS_MAPPING_GENERAL_ID_MODE_CUSTOM_ATTRIBUTE;
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

    //########################################

    /**
     * @return bool
     */
    public function isOtherListingsMoveToListingsEnabled()
    {
        return (int)$this->getData('other_listings_move_mode') == self::OTHER_LISTINGS_MOVE_TO_LISTINGS_ENABLED;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isOtherListingsMoveToListingsSynchModeNone()
    {
        $setting = $this->getSetting(
            'other_listings_move_settings', 'synch', self::OTHER_LISTINGS_MOVE_TO_LISTINGS_SYNCH_MODE_NONE
        );
        return $setting == self::OTHER_LISTINGS_MOVE_TO_LISTINGS_SYNCH_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isOtherListingsMoveToListingsSynchModeAll()
    {
        $setting = $this->getSetting(
            'other_listings_move_settings', 'synch', self::OTHER_LISTINGS_MOVE_TO_LISTINGS_SYNCH_MODE_NONE
        );
        return $setting == self::OTHER_LISTINGS_MOVE_TO_LISTINGS_SYNCH_MODE_ALL;
    }

    /**
     * @return bool
     */
    public function isOtherListingsMoveToListingsSynchModeQty()
    {
        $setting = $this->getSetting(
            'other_listings_move_settings', 'synch', self::OTHER_LISTINGS_MOVE_TO_LISTINGS_SYNCH_MODE_NONE
        );
        return $setting == self::OTHER_LISTINGS_MOVE_TO_LISTINGS_SYNCH_MODE_QTY;
    }

    /**
     * @return bool
     */
    public function isOtherListingsMoveToListingsSynchModePrice()
    {
        $setting = $this->getSetting(
            'other_listings_move_settings', 'synch', self::OTHER_LISTINGS_MOVE_TO_LISTINGS_SYNCH_MODE_NONE
        );
        return $setting == self::OTHER_LISTINGS_MOVE_TO_LISTINGS_SYNCH_MODE_PRICE;
    }

    //########################################

    /**
     * @return bool
     */
    public function isMagentoOrdersListingsModeEnabled()
    {
        $setting = $this->getSetting('magento_orders_settings', array('listing', 'mode'),
                                     self::MAGENTO_ORDERS_LISTINGS_MODE_YES);

        return $setting == self::MAGENTO_ORDERS_LISTINGS_MODE_YES;
    }

    /**
     * @return bool
     */
    public function isMagentoOrdersListingsStoreCustom()
    {
        $setting = $this->getSetting('magento_orders_settings', array('listing', 'store_mode'),
                                     self::MAGENTO_ORDERS_LISTINGS_STORE_MODE_DEFAULT);

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
        $setting = $this->getSetting('magento_orders_settings', array('listing_other', 'mode'),
                                     self::MAGENTO_ORDERS_LISTINGS_OTHER_MODE_YES);

        return $setting == self::MAGENTO_ORDERS_LISTINGS_OTHER_MODE_YES;
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
        $setting = $this->getSetting('magento_orders_settings', array('listing_other', 'product_mode'));

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
     * @return bool
     */
    public function isMagentoOrdersNumberPrefixEnable()
    {
        $setting = $this->getSetting(
            'magento_orders_settings', array('number', 'prefix', 'mode'), self::MAGENTO_ORDERS_NUMBER_PREFIX_MODE_NO
        );
        return $setting == self::MAGENTO_ORDERS_NUMBER_PREFIX_MODE_YES;
    }

    public function getMagentoOrdersNumberPrefix()
    {
        return $this->getSetting('magento_orders_settings', array('number', 'prefix', 'prefix'), '');
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isMagentoOrdersTaxModeNone()
    {
        $setting = $this->getSetting('magento_orders_settings', array('tax', 'mode'),
                                     self::MAGENTO_ORDERS_TAX_MODE_MIXED);

        return $setting == self::MAGENTO_ORDERS_TAX_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isMagentoOrdersTaxModeChannel()
    {
        $setting = $this->getSetting('magento_orders_settings', array('tax', 'mode'),
                                     self::MAGENTO_ORDERS_TAX_MODE_MIXED);

        return $setting == self::MAGENTO_ORDERS_TAX_MODE_CHANNEL;
    }

    /**
     * @return bool
     */
    public function isMagentoOrdersTaxModeMagento()
    {
        $setting = $this->getSetting('magento_orders_settings', array('tax', 'mode'),
                                     self::MAGENTO_ORDERS_TAX_MODE_MIXED);

        return $setting == self::MAGENTO_ORDERS_TAX_MODE_MAGENTO;
    }

    /**
     * @return bool
     */
    public function isMagentoOrdersTaxModeMixed()
    {
        $setting = $this->getSetting('magento_orders_settings', array('tax', 'mode'),
                                     self::MAGENTO_ORDERS_TAX_MODE_MIXED);

        return $setting == self::MAGENTO_ORDERS_TAX_MODE_MIXED;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isMagentoOrdersCustomerGuest()
    {
        $setting = $this->getSetting('magento_orders_settings', array('customer', 'mode'),
                                     self::MAGENTO_ORDERS_CUSTOMER_MODE_GUEST);

        return $setting == self::MAGENTO_ORDERS_CUSTOMER_MODE_GUEST;
    }

    /**
     * @return bool
     */
    public function isMagentoOrdersCustomerPredefined()
    {
        $setting = $this->getSetting('magento_orders_settings', array('customer', 'mode'),
                                     self::MAGENTO_ORDERS_CUSTOMER_MODE_GUEST);

        return $setting == self::MAGENTO_ORDERS_CUSTOMER_MODE_PREDEFINED;
    }

    /**
     * @return bool
     */
    public function isMagentoOrdersCustomerNew()
    {
        $setting = $this->getSetting('magento_orders_settings', array('customer', 'mode'),
                                     self::MAGENTO_ORDERS_CUSTOMER_MODE_GUEST);

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
        $setting = $this->getSetting('magento_orders_settings', array('customer', 'subscription_mode'),
                                     self::MAGENTO_ORDERS_CUSTOMER_NEW_SUBSCRIPTION_MODE_NO);

        return $setting == self::MAGENTO_ORDERS_CUSTOMER_NEW_SUBSCRIPTION_MODE_YES;
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
        $setting = $this->getSetting('magento_orders_settings', array('status_mapping', 'mode'),
                                     self::MAGENTO_ORDERS_STATUS_MAPPING_MODE_DEFAULT);

        return $setting == self::MAGENTO_ORDERS_STATUS_MAPPING_MODE_DEFAULT;
    }

    public function getMagentoOrdersStatusNew()
    {
        if ($this->isMagentoOrdersStatusMappingDefault()) {
            return self::MAGENTO_ORDERS_STATUS_MAPPING_NEW;
        }

        return $this->getSetting('magento_orders_settings', array('status_mapping', 'new'));
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
    public function isMagentoOrdersInvoiceEnabled()
    {
        if ($this->isMagentoOrdersStatusMappingDefault()) {
            return true;
        }

        return $this->getSetting('magento_orders_settings', 'invoice_mode') == self::MAGENTO_ORDERS_INVOICE_MODE_YES;
    }

    /**
     * @return bool
     */
    public function isMagentoOrdersShipmentEnabled()
    {
        if ($this->isMagentoOrdersStatusMappingDefault()) {
            return true;
        }

        return $this->getSetting('magento_orders_settings', 'shipment_mode') == self::MAGENTO_ORDERS_SHIPMENT_MODE_YES;
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

    //########################################
}
