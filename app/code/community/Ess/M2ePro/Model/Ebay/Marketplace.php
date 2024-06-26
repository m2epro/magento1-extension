<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Marketplace getParentObject()
 */
class Ess_M2ePro_Model_Ebay_Marketplace extends Ess_M2ePro_Model_Component_Child_Ebay_Abstract
{
    protected $_info = null;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Marketplace');
    }

    //########################################

    public function getCurrency()
    {
        return $this->getData('currency');
    }

    public function getOriginCountry()
    {
        return $this->getData('origin_country');
    }

    public function getLanguageCode()
    {
        return $this->getData('language_code');
    }

    /**
     * @return bool
     */
    public function isMultivariationEnabled()
    {
        return (bool)$this->getData('is_multivariation');
    }

    /**
     * @return bool
     */
    public function isTaxTableEnabled()
    {
        return (bool)(int)$this->getData('is_tax_table');
    }

    /**
     * @return bool
     */
    public function isVatEnabled()
    {
        return (bool)(int)$this->getData('is_vat');
    }

    /**
     * @return bool
     */
    public function isStpEnabled()
    {
        return (bool)(int)$this->getData('is_stp');
    }

    /**
     * @return bool
     */
    public function isStpAdvancedEnabled()
    {
        return (bool)(int)$this->getData('is_stp_advanced');
    }

    /**
     * @return bool
     */
    public function isMapEnabled()
    {
        return (bool)(int)$this->getData('is_map');
    }

    /**
     * @return bool
     */
    public function isLocalShippingRateTableEnabled()
    {
        return (bool)(int)$this->getData('is_local_shipping_rate_table');
    }

    /**
     * @return bool
     */
    public function isInternationalShippingRateTableEnabled()
    {
        return (bool)(int)$this->getData('is_international_shipping_rate_table');
    }

    /**
     * @return bool
     */
    public function isEnglishMeasurementSystemEnabled()
    {
        return (bool)(int)$this->getData('is_english_measurement_system');
    }

    /**
     * @return bool
     */
    public function isMetricMeasurementSystemEnabled()
    {
        return (bool)(int)$this->getData('is_metric_measurement_system');
    }

    /**
     * @return bool
     */
    public function isManagedPaymentsEnabled()
    {
        return (bool)(int)$this->getData('is_managed_payments');
    }

    /**
     * @return bool
     */
    public function isFreightShippingEnabled()
    {
        return (bool)(int)$this->getData('is_freight_shipping');
    }

    /**
     * @return bool
     */
    public function isCalculatedShippingEnabled()
    {
        return (bool)(int)$this->getData('is_calculated_shipping');
    }

    /**
     * @return bool
     */
    public function isGlobalShippingProgramEnabled()
    {
        return (bool)(int)$this->getData('is_global_shipping_program');
    }

    /**
     * @return bool
     */
    public function isReturnDescriptionEnabled()
    {
        return (bool)(int)$this->getData('is_return_description');
    }

    /**
     * @return bool
     */
    public function isEpidEnabled()
    {
        return (bool)(int)$this->getData('is_epid');
    }

    /**
     * @return bool
     */
    public function isKtypeEnabled()
    {
        return (bool)(int)$this->getData('is_ktype');
    }

    /**
     * @return bool
     */
    public function isMultiMotorsEnabled()
    {
        return $this->isEpidEnabled() && $this->isKtypeEnabled();
    }

    //########################################

    /**
     * @param int $categoryId
     * @return array
     */
    public function getCategory($categoryId)
    {
        $tableCategories = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_ebay_dictionary_category');

        $dbSelect = Mage::getResourceModel('core/config')->getReadConnection()
            ->select()
            ->from($tableCategories, '*')
            ->where('`marketplace_id` = ?', (int)$this->getId())
            ->where('`category_id` = ?', (int)$categoryId);

        $categories = Mage::getResourceModel('core/config')
            ->getReadConnection()
            ->fetchAll($dbSelect);

        return !empty($categories) ? $categories[0] : array();
    }

    public function getChildCategories($parentId)
    {
        $tableCategories = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_ebay_dictionary_category');

        $dbSelect = Mage::getResourceModel('core/config')->getReadConnection()
            ->select()
            ->from($tableCategories, array('category_id', 'title', 'is_leaf'))
            ->where('`marketplace_id` = ?', (int)$this->getId())
            ->order(array('title ASC'));

        empty($parentId) ? $dbSelect->where('parent_category_id IS NULL')
            : $dbSelect->where('parent_category_id = ?', (int)$parentId);

        $categories = Mage::getResourceModel('core/config')
            ->getReadConnection()
            ->fetchAll($dbSelect);

        return $categories;
    }

    //########################################

    /**
     * @return array|null
     */
    public function getInfo()
    {
        if ($this->_info !== null) {
            return $this->_info;
        }

        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $coreResource = Mage::getSingleton('core/resource');
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $tableDictMarketplace = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_ebay_dictionary_marketplace');
        $tableDictShipping = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_ebay_dictionary_shipping');

        // table m2epro_ebay_dictionary_marketplace
        // ---------------------------------------
        $dbSelect = $connRead->select()
            ->from($tableDictMarketplace, '*')
            ->where('`marketplace_id` = ?', (int)$this->getId());
        $data = $connRead->fetchRow($dbSelect);
        // ---------------------------------------

        if (!$data) {
            return $this->_info = array();
        }

        // table m2epro_ebay_dictionary_shipping
        // ---------------------------------------
        $dbSelect = $connRead->select()
            ->from($tableDictShipping, '*')
            ->where('`marketplace_id` = ?', (int)$this->getId())
            ->order(array('title ASC'));
        $shippingMethods = $connRead->fetchAll($dbSelect);
        // ---------------------------------------

        if (!$shippingMethods) {
            $shippingMethods = array();
        }

        $categoryShippingMethods = array();
        foreach ($shippingMethods as $shippingMethod) {
            $category = Mage::helper('M2ePro')->jsonDecode($shippingMethod['category']);

            if (empty($category)) {
                $shippingMethod['data'] = Mage::helper('M2ePro')->jsonDecode($shippingMethod['data']);
                $categoryShippingMethods['']['methods'][] = $shippingMethod;
                continue;
            }

            if (!isset($categoryShippingMethods[$category['ebay_id']])) {
                $categoryShippingMethods[$category['ebay_id']] = array(
                    'title'   => $category['title'],
                    'methods' => array(),
                );
            }

            $shippingMethod['data'] = Mage::helper('M2ePro')->jsonDecode($shippingMethod['data']);
            $categoryShippingMethods[$category['ebay_id']]['methods'][] = $shippingMethod;
        }

        // ---------------------------------------

        return $this->_info = array(
            'dispatch'                   => Mage::helper('M2ePro')->jsonDecode($data['dispatch']),
            'packages'                   => Mage::helper('M2ePro')->jsonDecode($data['packages']),
            'return_policy'              => Mage::helper('M2ePro')->jsonDecode($data['return_policy']),
            'listing_features'           => Mage::helper('M2ePro')->jsonDecode($data['listing_features']),
            'payments'                   => Mage::helper('M2ePro')->jsonDecode($data['payments']),
            'shipping'                   => $categoryShippingMethods,
            'shipping_locations'         => Mage::helper('M2ePro')->jsonDecode($data['shipping_locations']),
            'shipping_locations_exclude' => Mage::helper('M2ePro')->jsonDecode($data['shipping_locations_exclude']),
            'tax_categories'             => Mage::helper('M2ePro')->jsonDecode($data['tax_categories'])
        );
    }

    // ---------------------------------------

    /**
     * @return array
     */
    public function getDispatchInfo()
    {
        $info = $this->getInfo();

        return isset($info['dispatch']) ? $info['dispatch'] : array();
    }

    /**
     * @return array
     */
    public function getPackageInfo()
    {
        $info = $this->getInfo();

        return isset($info['packages']) ? $info['packages'] : array();
    }

    /**
     * @return array
     */
    public function getReturnPolicyInfo()
    {
        $info = $this->getInfo();

        return isset($info['return_policy']) ? $info['return_policy'] : array();
    }

    /**
     * @return array
     */
    public function getListingFeatureInfo()
    {
        $info = $this->getInfo();

        return isset($info['listing_features']) ? $info['listing_features'] : array();
    }

    /**
     * @return array
     */
    public function getPaymentInfo()
    {
        $info = $this->getInfo();

        return isset($info['payments']) ? $info['payments'] : array();
    }

    /**
     * @return array
     */
    public function getShippingInfo()
    {
        $info = $this->getInfo();

        return isset($info['shipping']) ? $info['shipping'] : array();
    }

    /**
     * @return array
     */
    public function getShippingLocationInfo()
    {
        $info = $this->getInfo();

        return isset($info['shipping_locations']) ? $info['shipping_locations'] : array();
    }

    /**
     * @return array
     */
    public function getShippingLocationExcludeInfo()
    {
        $info = $this->getInfo();

        return isset($info['shipping_locations_exclude']) ? $info['shipping_locations_exclude'] : array();
    }

    /**
     * @return array
     */
    public function getTaxCategoryInfo()
    {
        $info = $this->getInfo();

        return isset($info['tax_categories']) ? $info['tax_categories'] : array();
    }

    //########################################

    public function save()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('marketplace');

        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('marketplace');

        return parent::delete();
    }

    //########################################
}
