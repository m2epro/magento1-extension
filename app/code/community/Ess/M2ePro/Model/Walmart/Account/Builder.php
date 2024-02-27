<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Walmart_Account as Account;

class Ess_M2ePro_Model_Walmart_Account_Builder extends Ess_M2ePro_Model_ActiveRecord_AbstractBuilder
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
            'consumer_id',
            'private_key',
            'client_id',
            'client_secret'
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
            'mapping_sku_mode',
            'mapping_sku_priority',
            'mapping_sku_attribute',

            'mapping_upc_mode',
            'mapping_upc_priority',
            'mapping_upc_attribute',

            'mapping_gtin_mode',
            'mapping_gtin_priority',
            'mapping_gtin_attribute',

            'mapping_wpid_mode',
            'mapping_wpid_priority',
            'mapping_wpid_attribute',

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

        $temp = array(
            Account::OTHER_LISTINGS_MAPPING_SKU_MODE_DEFAULT,
            Account::OTHER_LISTINGS_MAPPING_SKU_MODE_CUSTOM_ATTRIBUTE,
            Account::OTHER_LISTINGS_MAPPING_SKU_MODE_PRODUCT_ID,
        );

        if (isset($tempData['mapping_sku_mode'])) {
            $mappingSettings['sku']['mode'] = (int)$tempData['mapping_sku_mode'];

            if (in_array($tempData['mapping_sku_mode'], $temp)) {
                $mappingSettings['sku']['priority'] = (int)$tempData['mapping_sku_priority'];
            }

            if ($tempData['mapping_sku_mode'] == Account::OTHER_LISTINGS_MAPPING_SKU_MODE_CUSTOM_ATTRIBUTE) {
                $mappingSettings['sku']['attribute'] = (string)$tempData['mapping_sku_attribute'];
            }
        }

        if (isset($tempData['mapping_upc_mode'])) {
            $mappingSettings['upc']['mode'] = (int)$tempData['mapping_upc_mode'];

            if ($tempData['mapping_upc_mode'] == Account::OTHER_LISTINGS_MAPPING_UPC_MODE_CUSTOM_ATTRIBUTE) {
                $mappingSettings['upc']['priority'] = (int)$tempData['mapping_upc_priority'];
                $mappingSettings['upc']['attribute'] = (string)$tempData['mapping_upc_attribute'];
            }
        }

        if (isset($tempData['mapping_gtin_mode'])) {
            $mappingSettings['gtin']['mode'] = (int)$tempData['mapping_gtin_mode'];

            if ($tempData['mapping_gtin_mode'] == Account::OTHER_LISTINGS_MAPPING_GTIN_MODE_CUSTOM_ATTRIBUTE) {
                $mappingSettings['gtin']['priority'] = (int)$tempData['mapping_gtin_priority'];
                $mappingSettings['gtin']['attribute'] = (string)$tempData['mapping_gtin_attribute'];
            }
        }

        if (isset($tempData['mapping_wpid_mode'])) {
            $mappingSettings['wpid']['mode'] = (int)$tempData['mapping_wpid_mode'];

            if ($tempData['mapping_wpid_mode'] == Account::OTHER_LISTINGS_MAPPING_WPID_MODE_CUSTOM_ATTRIBUTE) {
                $mappingSettings['wpid']['priority'] = (int)$tempData['mapping_wpid_priority'];
                $mappingSettings['wpid']['attribute'] = (string)$tempData['mapping_wpid_attribute'];
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

        $prefixKeys = array(
            'prefix',
        );
        $tempSettings = !empty($tempSettings['prefix']) ? $tempSettings['prefix'] : array();
        foreach ($prefixKeys as $key) {
            if (isset($tempSettings[$key])) {
                $data['magento_orders_settings'][$tempKey]['prefix'][$key] = $tempSettings[$key];
            }
        }

        // tax settings
        // ---------------------------------------
        $tempKey = 'tax';
        $tempSettings = !empty($this->_rawData['magento_orders_settings'][$tempKey])
            ? $this->_rawData['magento_orders_settings'][$tempKey] : array();

        $keys = array(
            'mode'
        );
        foreach ($keys as $key) {
            if (isset($tempSettings[$key])) {
                $data['magento_orders_settings'][$tempKey][$key] = $tempSettings[$key];
            }
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

        // Shipping information
        // ---------------------------------------
        $tempKey = 'shipping_information';
        $tempSettings = !empty($this->_rawData['magento_orders_settings'][$tempKey])
            ? $this->_rawData['magento_orders_settings'][$tempKey] : array();

        $keys = array(
            'ship_by_date',
            'shipping_address_region_override',
        );
        foreach ($keys as $key) {
            if (isset($tempSettings[$key])) {
                $data['magento_orders_settings'][$tempKey][$key] = $tempSettings[$key];
            }
        }

        $data['magento_orders_settings'] = Mage::helper('M2ePro')
            ->jsonEncode($data['magento_orders_settings']);

        // tab invoice and shipment
        // ---------------------------------------
        $keys = array(
            'create_magento_invoice',
            'create_magento_shipment'
        );
        foreach ($keys as $key) {
            if (isset($this->_rawData[$key])) {
                $data[$key] = $this->_rawData[$key];
            }
        }

        if (isset($this->_rawData['other_carrier']) && isset($this->_rawData['other_carrier_url'])) {
            $otherCarriers = array();
            $carriers = array_filter($this->_rawData['other_carrier']);
            $carrierURLs = array_filter($this->_rawData['other_carrier_url']);

            foreach ($carriers as $index => $code) {
                $otherCarriers[] = array(
                    'code' => $code,
                    'url'  => isset($carrierURLs[$index]) ? $carrierURLs[$index] : ''
                );
            }

            $data['other_carriers'] = Mage::helper('M2ePro')->jsonEncode($otherCarriers);
        }

        return $data;
    }

    public function getDefaultData()
    {
        return array(
            'title'          => '',
            'marketplace_id' => 0,
            'consumer_id'    => '',
            'private_key'    => '',
            'client_id'      => '',
            'client_secret'  => '',

            'related_store_id' => 0,

            'other_listings_synchronization'  => 1,
            'other_listings_mapping_mode'     => 1,
            'other_listings_mapping_settings' => array(
                'sku' => array(
                    'mode' => Account::OTHER_LISTINGS_MAPPING_SKU_MODE_DEFAULT,
                    'priority' => 1
                ),
            ),
            'mapping_sku_mode' => Account::OTHER_LISTINGS_MAPPING_SKU_MODE_DEFAULT,
            'mapping_sku_priority' => 1,

            'magento_orders_settings' => array(
                'listing'        => array(
                    'mode'       => 1,
                    'store_mode' => Account::MAGENTO_ORDERS_LISTINGS_STORE_MODE_DEFAULT,
                    'store_id'   => null
                ),
                'listing_other'  => array(
                    'mode'                 => 1,
                    'product_mode'         => Account::MAGENTO_ORDERS_LISTINGS_OTHER_PRODUCT_MODE_IGNORE,
                    'product_tax_class_id' => Ess_M2ePro_Model_Magento_Product::TAX_CLASS_ID_NONE,
                    'store_id'             => null,
                ),
                'number'         => array(
                    'source' => Account::MAGENTO_ORDERS_NUMBER_SOURCE_MAGENTO,
                    'prefix' => array(
                        'prefix' => '',
                    )
                ),
                'tax'            => array(
                    'mode' => Account::MAGENTO_ORDERS_TAX_MODE_MIXED
                ),
                'customer'       => array(
                    'mode'          => Account::MAGENTO_ORDERS_CUSTOMER_MODE_GUEST,
                    'id'            => null,
                    'website_id'    => null,
                    'group_id'      => null,
                    'notifications' => array(
                        'invoice_created' => false,
                        'order_created'   => false
                    ),
                ),
                'status_mapping' => array(
                    'mode'       => Account::MAGENTO_ORDERS_STATUS_MAPPING_MODE_DEFAULT,
                    'processing' => Account::MAGENTO_ORDERS_STATUS_MAPPING_PROCESSING,
                    'shipped'    => Account::MAGENTO_ORDERS_STATUS_MAPPING_SHIPPED,
                ),
                'refund_and_cancellation' => array(
                    'refund_mode' => 1,
                ),
                'shipping_information' => array(
                    'ship_by_date' => 1,
                ),
            ),
            'create_magento_invoice'  => 1,
            'create_magento_shipment' => 1,
            'other_carriers'          => array()
        );
    }
}
