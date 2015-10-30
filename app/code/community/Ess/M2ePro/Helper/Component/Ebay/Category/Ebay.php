<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Component_Ebay_Category_Ebay extends Mage_Core_Helper_Abstract
{
    const CACHE_TAG = '_ebay_dictionary_data_';

    const PRODUCT_IDENTIFIER_STATUS_DISABLED = 0;
    const PRODUCT_IDENTIFIER_STATUS_ENABLED  = 1;
    const PRODUCT_IDENTIFIER_STATUS_REQUIRED = 2;

    //########################################

    /**
     * @param int $categoryId
     * @param int $marketplaceId
     * @param bool $includeTitle
     * @return string
     */
    public function getPath($categoryId, $marketplaceId, $includeTitle = true)
    {
        $category = Mage::helper('M2ePro/Component_Ebay')
            ->getCachedObject('Marketplace',(int)$marketplaceId)
            ->getChildObject()
            ->getCategory((int)$categoryId);

        if (!$category) {
            return '';
        }

        $category['path'] = str_replace(' > ', '>', $category['path']);
        return $category['path'] . ($includeTitle ? '>' . $category['title'] : '');
    }

    /**
     * @param int $categoryId
     * @param int $marketplaceId
     * @return int|null
     */
    public function getTopLevel($categoryId, $marketplaceId)
    {
        $topLevel = NULL;
        for ($i = 1; $i < 10; $i++) {

            $category = Mage::helper('M2ePro/Component_Ebay')
                ->getCachedObject('Marketplace',(int)$marketplaceId)
                ->getChildObject()
                ->getCategory((int)$categoryId);

            if (!$category || ($i == 1 && !$category['is_leaf'])) {
                return NULL;
            }

            $topLevel = $category['category_id'];

            if (!$category['parent_category_id']) {
                return $topLevel;
            }

            $categoryId = (int)$category['parent_category_id'];
        }

        return $topLevel;
    }

    // ---------------------------------------

    /**
     * @param int $categoryId
     * @param int $marketplaceId
     * @return bool|null
     */
    public function isVariationEnabled($categoryId, $marketplaceId)
    {
        $features = $this->getFeatures($categoryId, $marketplaceId);
        if (is_null($features)) {
            return NULL;
        }

        return !empty($features['variation_enabled']);
    }

    /**
     * @param int $categoryId
     * @param int $marketplaceId
     * @return bool
     */
    public function hasRequiredSpecifics($categoryId, $marketplaceId)
    {
        $specifics = $this->getSpecifics($categoryId, $marketplaceId);

        if (empty($specifics)) {
            return false;
        }

        foreach ($specifics as $specific) {
            if ($specific['required']) {
                return true;
            }
        }

        return false;
    }

    //########################################

    /**
     * @param int $categoryId
     * @param int $marketplaceId
     * @return array|null
     */
    public function getFeatures($categoryId, $marketplaceId)
    {
        $cacheHelper = Mage::helper('M2ePro/Data_Cache_Permanent');
        $cacheKey = '_ebay_category_features_'.$marketplaceId.'_'.$categoryId;

        if (($cacheValue = $cacheHelper->getValue($cacheKey)) !== false) {
            return $cacheValue;
        }

        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableDictCategory = Mage::getSingleton('core/resource')->getTableName('m2epro_ebay_dictionary_category');

        $dbSelect = $connRead->select()
                             ->from($tableDictCategory, 'features')
                             ->where('`marketplace_id` = ?',(int)$marketplaceId)
                             ->where('`category_id` = ?',(int)$categoryId);

        $categoryRow = $connRead->fetchAssoc($dbSelect);
        $categoryRow = array_shift($categoryRow);

        // not found marketplace category row
        if (!$categoryRow) {
            return NULL;
        }

        $features = array();
        if (!is_null($categoryRow['features'])) {
            $features = (array)json_decode($categoryRow['features'], true);
        }

        $cacheHelper->setValue($cacheKey,$features,array(self::CACHE_TAG));
        return $features;
    }

    // ---------------------------------------

    /**
     * @param int $categoryId
     * @param int $marketplaceId
     * @return array|null
     */
    public function getSpecifics($categoryId, $marketplaceId)
    {
        $cacheHelper = Mage::helper('M2ePro/Data_Cache_Permanent');
        $cacheKey = '_ebay_category_item_specifics_'.$categoryId.'_'.$marketplaceId;

        if (($cacheValue = $cacheHelper->getValue($cacheKey)) !== false) {
            return $cacheValue;
        }

        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableDictCategory = Mage::getSingleton('core/resource')->getTableName('m2epro_ebay_dictionary_category');

        $dbSelect = $connRead->select()
                             ->from($tableDictCategory,'*')
                             ->where('`marketplace_id` = ?', (int)$marketplaceId)
                             ->where('`category_id` = ?', (int)$categoryId);

        $categoryRow = $connRead->fetchAssoc($dbSelect);
        $categoryRow = array_shift($categoryRow);

        // not found marketplace category row
        if (!$categoryRow) {
            return NULL;
        }

        if (!$categoryRow['is_leaf']) {
            $cacheHelper->setValue($cacheKey,array(),array(self::CACHE_TAG));
            return array();
        }

        if (!is_null($categoryRow['item_specifics'])) {

            $specifics = (array)json_decode($categoryRow['item_specifics'],true);

        } else {

            $dispatcherObject = Mage::getModel('M2ePro/Connector_Ebay_Dispatcher');
            $connectorObj = $dispatcherObject->getVirtualConnector('category','get','specifics',
                                                                   array('category_id' => $categoryId), 'specifics',
                                                                   $marketplaceId, NULL, NULL);

            $specifics = (array)$dispatcherObject->process($connectorObj);

            /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
            $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
            $connWrite->update($tableDictCategory,
                               array('item_specifics' => json_encode($specifics)),
                               array('marketplace_id = ?' => (int)$marketplaceId,
                                     'category_id = ?' => (int)$categoryId));
        }

        $cacheHelper->setValue($cacheKey,$specifics,array(self::CACHE_TAG));
        return $specifics;
    }

    //########################################

    public function getSameTemplatesData($ids)
    {
        return Mage::helper('M2ePro/Component_Ebay_Category')->getSameTemplatesData(
            $ids, Mage::getResourceModel('M2ePro/Ebay_Template_Category')->getMainTable(),
            array('category_main')
        );
    }

    public function exists($categoryId, $marketplaceId)
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableDictCategories = Mage::getSingleton('core/resource')->getTableName('m2epro_ebay_dictionary_category');

        $dbSelect = $connRead->select()
                             ->from($tableDictCategories, 'COUNT(*)')
                             ->where('`marketplace_id` = ?', (int)$marketplaceId)
                             ->where('`category_id` = ?', (int)$categoryId);

        return $dbSelect->query()->fetchColumn() == 1;
    }

    public function isExistDeletedCategories()
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $etcTable = Mage::getModel('M2ePro/Ebay_Template_Category')->getResource()->getMainTable();
        $etocTable = Mage::getModel('M2ePro/Ebay_Template_OtherCategory')->getResource()->getMainTable();
        $edcTable = Mage::getSingleton('core/resource')->getTableName('m2epro_ebay_dictionary_category');

        // prepare category main select
        // ---------------------------------------
        $etcSelect = $connRead->select();
        $etcSelect->from(
                array('etc' => $etcTable)
            )
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(array(
                'category_main_id as category_id',
                'marketplace_id',
            ))
            ->where('category_main_mode = ?', Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY)
            ->group(array('category_id', 'marketplace_id'));
        // ---------------------------------------

        // prepare category secondary select
        // ---------------------------------------
        $etocSelect = $connRead->select();
        $etocSelect->from(
                array('etc' => $etocTable)
            )
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(array(
                'category_secondary_id as category_id',
                'marketplace_id',
            ))
            ->where('category_secondary_mode = ?', Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY)
            ->group(array('category_id', 'marketplace_id'));
        // ---------------------------------------

        $unionSelect = $connRead->select();
        $unionSelect->union(array(
            $etcSelect,
            $etocSelect,
        ));

        $mainSelect = $connRead->select();
        $mainSelect->reset()
            ->from(array('main_table' => $unionSelect))
            ->joinLeft(
                array('edc' => $edcTable),
                'edc.marketplace_id = main_table.marketplace_id
                 AND edc.category_id = main_table.category_id'
            )
            ->where('edc.category_id IS NULL');

        return $connRead->query($mainSelect)->fetchColumn() !== false;
    }

    //########################################
}