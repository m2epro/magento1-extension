<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Amazon_Account as Account;

class Ess_M2ePro_Model_Amazon_Account_Builder extends Ess_M2ePro_Model_ActiveRecord_AbstractBuilder
{
    //########################################

    protected function prepareData()
    {
        $data = array();

        // tab: general
        // ---------------------------------------
        $keys = array(
            'title',
            'marketplace_id',
            'merchant_id'
        );
        foreach ($keys as $key) {
            if (isset($this->_rawData[$key])) {
                $data[$key] = $this->_rawData[$key];
            }
        }

        // tab: Unmanaged listings
        // ---------------------------------------
        $keys = array(
            'related_store_id',

            'other_listings_synchronization',
            'other_listings_mapping_mode'
        );
        foreach ($keys as $key) {
            if (isset($this->_rawData[$key])) {
                $data[$key] = $this->_rawData[$key];
            }
        }

        // Mapping
        // ---------------------------------------
        $tempData = array();
        $keys = array(
            'mapping_general_id_mode',
            'mapping_general_id_priority',
            'mapping_general_id_attribute',

            'mapping_sku_mode',
            'mapping_sku_priority',
            'mapping_sku_attribute',

            'mapping_title_mode',
            'mapping_title_priority',
            'mapping_title_attribute'
        );
        foreach ($keys as $key) {
            if (isset($this->_rawData[$key])) {
                $tempData[$key] = $this->_rawData[$key];
            }
        }

        $mappingSettings = array();
        if ($this->getModel()->getId()) {
            $mappingSettings = $this->getModel()->getChildObject()->getSettings('other_listings_mapping_settings');
        }

        if (isset($tempData['mapping_general_id_mode'])) {
            $mappingSettings['general_id']['mode'] = (int)$tempData['mapping_general_id_mode'];

            if ($tempData['mapping_general_id_mode'] ==
                Account::OTHER_LISTINGS_MAPPING_GENERAL_ID_MODE_CUSTOM_ATTRIBUTE
            ) {
                $mappingSettings['general_id']['priority'] = (int)$tempData['mapping_general_id_priority'];
                $mappingSettings['general_id']['attribute'] = (string)$tempData['mapping_general_id_attribute'];
            }
        }

        if (isset($tempData['mapping_sku_mode'])) {
            $mappingSettings['sku']['mode'] = (int)$tempData['mapping_sku_mode'];

            if ($tempData['mapping_sku_mode'] == Account::OTHER_LISTINGS_MAPPING_SKU_MODE_DEFAULT ||
                $tempData['mapping_sku_mode'] == Account::OTHER_LISTINGS_MAPPING_SKU_MODE_CUSTOM_ATTRIBUTE ||
                $tempData['mapping_sku_mode'] == Account::OTHER_LISTINGS_MAPPING_SKU_MODE_PRODUCT_ID
            ) {
                $mappingSettings['sku']['priority'] = (int)$tempData['mapping_sku_priority'];
            }

            if ($tempData['mapping_sku_mode'] == Account::OTHER_LISTINGS_MAPPING_SKU_MODE_CUSTOM_ATTRIBUTE) {
                $mappingSettings['sku']['attribute'] = (string)$tempData['mapping_sku_attribute'];
            }
        }

        if (isset($tempData['mapping_title_mode'])) {
            $mappingSettings['title']['mode'] = (int)$tempData['mapping_title_mode'];

            if ($tempData['mapping_title_mode'] == Account::OTHER_LISTINGS_MAPPING_TITLE_MODE_DEFAULT ||
                $tempData['mapping_title_mode'] == Account::OTHER_LISTINGS_MAPPING_TITLE_MODE_CUSTOM_ATTRIBUTE
            ) {
                $mappingSettings['title']['priority'] = (int)$tempData['mapping_title_priority'];
            }

            if ($tempData['mapping_title_mode'] == Account::OTHER_LISTINGS_MAPPING_TITLE_MODE_CUSTOM_ATTRIBUTE) {
                $mappingSettings['title']['attribute'] = (string)$tempData['mapping_title_attribute'];
            }
        }

        $data['other_listings_mapping_settings'] = Mage::helper('M2ePro')->jsonEncode($mappingSettings);

        // tab: orders
        // ---------------------------------------
        $data['magento_orders_settings'] = array();
        if ($this->getModel()->getId()) {
            $data['magento_orders_settings'] = $this->getModel()->getChildObject()->getSettings(
                'magento_orders_settings'
            );
        }

        // m2e orders settings
        // ---------------------------------------
        $tempKey = 'listing';
        $tempSettings = !empty($this->_rawData['magento_orders_settings'][$tempKey])
            ? $this->_rawData['magento_orders_settings'][$tempKey] : array();

        $keys = array(
            'mode',
            'store_mode',
            'store_id'
        );
        foreach ($keys as $key) {
            if (isset($tempSettings[$key])) {
                $data['magento_orders_settings'][$tempKey][$key] = $tempSettings[$key];
            }
        }

        // Unmanaged orders settings
        // ---------------------------------------
        $tempKey = 'listing_other';
        $tempSettings = !empty($this->_rawData['magento_orders_settings'][$tempKey])
            ? $this->_rawData['magento_orders_settings'][$tempKey] : array();

        $keys = array(
            'mode',
            'product_mode',
            'product_tax_class_id',
            'store_id'
        );
        foreach ($keys as $key) {
            if (isset($tempSettings[$key])) {
                $data['magento_orders_settings'][$tempKey][$key] = $tempSettings[$key];
            }
        }

        // order number settings
        // ---------------------------------------
        $tempKey = 'number';
        $tempSettings = !empty($this->_rawData['magento_orders_settings'][$tempKey])
            ? $this->_rawData['magento_orders_settings'][$tempKey] : array();

        if (!empty($tempSettings['source'])) {
            $data['magento_orders_settings'][$tempKey]['source'] = $tempSettings['source'];
        }

        if (!empty($tempSettings['apply_to_amazon'])) {
            $data['magento_orders_settings'][$tempKey]['apply_to_amazon'] = $tempSettings['apply_to_amazon'];
        }

        $prefixKeys = array(
            'prefix',
            'afn-prefix',
            'prime-prefix',
            'b2b-prefix',
        );
        $tempSettings = !empty($tempSettings['prefix']) ? $tempSettings['prefix'] : array();
        foreach ($prefixKeys as $key) {
            if (isset($tempSettings[$key])) {
                $data['magento_orders_settings'][$tempKey]['prefix'][$key] = $tempSettings[$key];
            }
        }

        // qty reservation
        // ---------------------------------------
        $tempKey = 'qty_reservation';
        $tempSettings = !empty($this->_rawData['magento_orders_settings'][$tempKey])
            ? $this->_rawData['magento_orders_settings'][$tempKey] : array();

        $keys = array(
            'days',
        );
        foreach ($keys as $key) {
            if (isset($tempSettings[$key])) {
                $data['magento_orders_settings'][$tempKey][$key] = $tempSettings[$key];
            }
        }

        // refund & cancellation
        // ---------------------------------------
        $tempKey = 'refund_and_cancellation';
        $tempSettings = !empty($this->_rawData['magento_orders_settings'][$tempKey])
            ? $this->_rawData['magento_orders_settings'][$tempKey] : array();

        $keys = array(
            'refund_mode',
        );
        foreach ($keys as $key) {
            if (isset($tempSettings[$key])) {
                $data['magento_orders_settings'][$tempKey][$key] = $tempSettings[$key];
            }
        }

        // fba
        // ---------------------------------------
        $tempKey = 'fba';
        $tempSettings = !empty($this->_rawData['magento_orders_settings'][$tempKey])
            ? $this->_rawData['magento_orders_settings'][$tempKey] : array();

        $keys = array(
            'mode',
            'store_mode',
            'store_id',
            'stock_mode'
        );
        foreach ($keys as $key) {
            if (isset($tempSettings[$key])) {
                $data['magento_orders_settings'][$tempKey][$key] = $tempSettings[$key];
            }
        }

        // tax settings
        // ---------------------------------------
        $tempKey = 'tax';
        $tempSettings = !empty($this->_rawData['magento_orders_settings'][$tempKey])
            ? $this->_rawData['magento_orders_settings'][$tempKey] : array();

        $keys = array(
            'mode',
            'amazon_collects',
            'amazon_collect_for_uk',
            'amazon_collect_for_eea',
            'import_tax_id_in_magento_order'
        );
        foreach ($keys as $key) {
            if (isset($tempSettings[$key])) {
                $data['magento_orders_settings'][$tempKey][$key] = $tempSettings[$key];
            }
        }

        if (isset($tempSettings['amazon_collects'])) {
            if ($this->isNeedExcludeStates()) {
                $data['magento_orders_settings'][$tempKey]['amazon_collects'] = $tempSettings['amazon_collects'];
            } else {
                $data['magento_orders_settings'][$tempKey]['amazon_collects'] = 0;
            }
        }

        if (isset($tempSettings['excluded_states'])) {
            $data['magento_orders_settings'][$tempKey]['excluded_states'] = explode(
                ',',
                $tempSettings['excluded_states']
            );
        }

        if (isset($tempSettings['excluded_countries'])) {
            $data['magento_orders_settings'][$tempKey]['excluded_countries'] = explode(
                ',',
                $tempSettings['excluded_countries']
            );
        }

        // customer settings
        // ---------------------------------------
        $tempKey = 'customer';
        $tempSettings = !empty($this->_rawData['magento_orders_settings'][$tempKey])
            ? $this->_rawData['magento_orders_settings'][$tempKey] : array();

        $keys = array(
            'mode',
            'id',
            'website_id',
            'group_id',
            'billing_address_mode',
        );
        foreach ($keys as $key) {
            if (isset($tempSettings[$key])) {
                $data['magento_orders_settings'][$tempKey][$key] = $tempSettings[$key];
            }
        }

        // Check if input data contains another field from customer settings.
        // It's used to determine if account data changed by user interface, or during token re-new.
        if (isset($tempSettings['mode'])) {
            $notificationsKeys = array(
                'order_created',
                'invoice_created'
            );
            $tempSettings = !empty($tempSettings['notifications']) ? $tempSettings['notifications'] : array();
            foreach ($notificationsKeys as $key) {
                $data['magento_orders_settings'][$tempKey]['notifications'][$key] = in_array($key, $tempSettings);
            }
        }

        // status mapping settings
        // ---------------------------------------
        $tempKey = 'status_mapping';
        $tempSettings = !empty($this->_rawData['magento_orders_settings'][$tempKey])
            ? $this->_rawData['magento_orders_settings'][$tempKey] : array();

        $keys = array(
            'mode',
            'processing',
            'shipped'
        );
        foreach ($keys as $key) {
            if (isset($tempSettings[$key])) {
                $data['magento_orders_settings'][$tempKey][$key] = $tempSettings[$key];
            }
        }

        // shipping information
        // ---------------------------------------
        $tempKey = 'shipping_information';
        $tempSettings = !empty($this->_rawData['magento_orders_settings'][$tempKey])
            ? $this->_rawData['magento_orders_settings'][$tempKey] : array();

        $keys = array(
            'ship_by_date',
            'update_without_track',
            'shipping_address_region_override',
        );
        foreach ($keys as $key) {
            if (isset($tempSettings[$key])) {
                $data['magento_orders_settings'][$tempKey][$key] = $tempSettings[$key];
            }
        }

        $data['magento_orders_settings'] = Mage::helper('M2ePro')
            ->jsonEncode($data['magento_orders_settings']);

        // tab: vat calculation service
        // ---------------------------------------
        $keys = array(
            'auto_invoicing',
            'invoice_generation',
            'create_magento_invoice',
            'create_magento_shipment',
        );
        foreach ($keys as $key) {
            if (isset($this->_rawData[$key])) {
                $data[$key] = $this->_rawData[$key];
            }
        }

        return $data;
    }

    /**
     * @return array
     */
    public function getDefaultData()
    {
        return array(
            // general
            'title'            => '',
            'marketplace_id'   => 0,
            'merchant_id'      => '',

            // listing_other
            'related_store_id' => 0,

            'other_listings_synchronization'  => 1,
            'other_listings_mapping_mode'     => 1,
            'other_listings_mapping_settings' => array(),
            'mapping_sku_mode' => Account::OTHER_LISTINGS_MAPPING_SKU_MODE_DEFAULT,
            'mapping_sku_priority' => 1,

            // order
            'magento_orders_settings'         => array(
                'listing'                 => array(
                    'mode'       => 1,
                    'store_mode' => Account::MAGENTO_ORDERS_LISTINGS_STORE_MODE_DEFAULT,
                    'store_id'   => null
                ),
                'listing_other'           => array(
                    'mode'                 => 1,
                    'product_mode'         => Account::MAGENTO_ORDERS_LISTINGS_OTHER_PRODUCT_MODE_IGNORE,
                    'product_tax_class_id' => Ess_M2ePro_Model_Magento_Product::TAX_CLASS_ID_NONE,
                    'store_id'             => Mage::helper('M2ePro/Magento_Store')->getDefaultStoreId()
                ),
                'number'                  => array(
                    'source'          => Account::MAGENTO_ORDERS_NUMBER_SOURCE_MAGENTO,
                    'prefix'          => array(
                        'mode'         => 0,
                        'prefix'       => '',
                        'afn-prefix'   => '',
                        'prime-prefix' => '',
                        'b2b-prefix'   => '',
                    ),
                    'apply_to_amazon' => 0
                ),
                'tax'                     => array(
                    'mode' =>                        Account::MAGENTO_ORDERS_TAX_MODE_MIXED,
                    'amazon_collects'                => 1,
                    'excluded_states'                => $this->getGeneralExcludedStates(),
                    'excluded_countries'             => array(),
                    'amazon_collect_for_uk'          => Account::SKIP_TAX_FOR_UK_SHIPMENT_NONE,
                    'amazon_collect_for_eea'         => 0,
                    'import_tax_id_in_magento_order' => 0
                ),
                'customer'                => array(
                    'mode'                 => Account::MAGENTO_ORDERS_CUSTOMER_MODE_GUEST,
                    'id'                   => null,
                    'website_id'           => null,
                    'group_id'             => null,
                    'notifications'        => array(
                        'invoice_created' => false,
                        'order_created'   => false
                    ),
                    'billing_address_mode' =>
                        Account::USE_SHIPPING_ADDRESS_AS_BILLING_IF_SAME_CUSTOMER_AND_RECIPIENT
                ),
                'status_mapping'          => array(
                    'mode'       => Account::MAGENTO_ORDERS_STATUS_MAPPING_MODE_DEFAULT,
                    'processing' => Account::MAGENTO_ORDERS_STATUS_MAPPING_PROCESSING,
                    'shipped'    => Account::MAGENTO_ORDERS_STATUS_MAPPING_SHIPPED,
                ),
                'qty_reservation'         => array(
                    'days' => 1
                ),
                'refund_and_cancellation' => array(
                    'refund_mode' => 1,
                ),
                'fba'                     => array(
                    'mode'       => 1,
                    'store_mode' => 0,
                    'store_id'   => null,
                    'stock_mode' => 0
                ),
                'shipping_information' => array(
                    'ship_by_date'         => 1,
                    'update_without_track' => 1,
                    'shipping_address_region_override' => 1,
                ),
            ),

            // vcs_upload_invoices
            'auto_invoicing'                  => 0,
            'invoice_generation'              => 0,
            'create_magento_invoice'          => 1,
            'create_magento_shipment'         => 1
        );
    }

    protected function isNeedExcludeStates()
    {
        if ($this->_rawData['marketplace_id'] != Ess_M2ePro_Helper_Component_Amazon::MARKETPLACE_US) {
            return false;
        }

        if ($this->_rawData['magento_orders_settings']['listing']['mode'] == 0 &&
            $this->_rawData['magento_orders_settings']['listing_other']['mode'] == 0) {
            return false;
        }

        if (!isset($this->_rawData['magento_orders_settings']['tax']['excluded_states'])) {
            return false;
        }

        return true;
    }

    protected function getGeneralExcludedStates()
    {
        return array(
            'AL',
            'AK',
            'AZ',
            'AR',
            'CA',
            'CO',
            'CT',
            'DC',
            'GA',
            'HI',
            'ID',
            'IL',
            'IN',
            'IA',
            'KY',
            'LA',
            'ME',
            'MD',
            'MA',
            'MI',
            'MN',
            'MS',
            'NE',
            'NV',
            'NJ',
            'NM',
            'NY',
            'NC',
            'ND',
            'OH',
            'OK',
            'PA',
            'PR',
            'RI',
            'SC',
            'SD',
            'TX',
            'UT',
            'VT',
            'VA',
            'WA',
            'WV',
            'WI',
            'WY',
        );
    }
}
