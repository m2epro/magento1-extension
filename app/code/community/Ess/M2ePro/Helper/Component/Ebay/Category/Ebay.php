<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
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
            ->getCachedObject('Marketplace', (int)$marketplaceId)
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
        $topLevel = null;
        for ($i = 1; $i < 10; $i++) {
            $category = Mage::helper('M2ePro/Component_Ebay')
                ->getCachedObject('Marketplace', (int)$marketplaceId)
                ->getChildObject()
                ->getCategory((int)$categoryId);

            if (!$category || ($i == 1 && !$category['is_leaf'])) {
                return null;
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
        if ($features === null) {
            return null;
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
        $tableDictCategory = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_ebay_dictionary_category');

        $dbSelect = $connRead->select()
                             ->from($tableDictCategory, 'features')
                             ->where('`marketplace_id` = ?', (int)$marketplaceId)
                             ->where('`category_id` = ?', (int)$categoryId);

        $categoryRow = $connRead->fetchAssoc($dbSelect);
        $categoryRow = array_shift($categoryRow);

        // not found marketplace category row
        if (!$categoryRow) {
            return null;
        }

        $features = array();
        if ($categoryRow['features'] !== null) {
            $features = (array)Mage::helper('M2ePro')->jsonDecode($categoryRow['features']);
        }

        $cacheHelper->setValue($cacheKey, $features, array(self::CACHE_TAG, 'marketplace'));
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
        $tableDictCategory = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_ebay_dictionary_category');

        $dbSelect = $connRead->select()
                             ->from($tableDictCategory, '*')
                             ->where('`marketplace_id` = ?', (int)$marketplaceId)
                             ->where('`category_id` = ?', (int)$categoryId);

        $categoryRow = $connRead->fetchAssoc($dbSelect);
        $categoryRow = array_shift($categoryRow);

        // not found marketplace category row
        if (!$categoryRow) {
            return null;
        }

        if (!$categoryRow['is_leaf']) {
            $cacheHelper->setValue($cacheKey, array(), array(self::CACHE_TAG, 'marketplace'));
            return array();
        }

        if ($categoryRow['item_specifics'] !== null) {
            $specifics = (array)Mage::helper('M2ePro')->jsonDecode($categoryRow['item_specifics']);
        } else {
            try {
                $dispatcherObject = Mage::getModel('M2ePro/Ebay_Connector_Dispatcher');
                $connectorObj = $dispatcherObject->getVirtualConnector(
                    'category', 'get', 'specifics',
                    array('category_id' => $categoryId), 'specifics',
                    $marketplaceId, null
                );

                $dispatcherObject->process($connectorObj);
                $specifics = (array)$connectorObj->getResponseData();
            } catch (\Exception $exception) {
                Mage::helper('M2ePro/Module_Exception')->process($exception);
                return null;
            }

            /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
            $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
            $connWrite->update(
                $tableDictCategory,
                array('item_specifics' => Mage::helper('M2ePro')->jsonEncode($specifics)),
                array(
                    'marketplace_id = ?' => (int)$marketplaceId,
                    'category_id = ?' => (int)$categoryId
                )
            );
        }

        $cacheHelper->setValue($cacheKey, $specifics, array(self::CACHE_TAG, 'marketplace'));
        return $specifics;
    }

    //########################################

    public function exists($categoryId, $marketplaceId)
    {
        $select = Mage::getSingleton('core/resource')->getConnection('core_read')
            ->select()
            ->from(
                Mage::helper('M2ePro/Module_Database_Structure')
                            ->getTableNameWithPrefix('m2epro_ebay_dictionary_category'),
                'COUNT(*)'
            )
            ->where('marketplace_id = ?', (int)$marketplaceId)
            ->where('category_id = ?', (int)$categoryId);

        return $select->query()->fetchColumn() == 1;
    }

    public function isExistDeletedCategories()
    {
        $stmt = Mage::getSingleton('core/resource')->getConnection('core_read')
            ->select()
            ->from(
                array('etc' => Mage::getModel('M2ePro/Ebay_Template_Category')->getResource()->getMainTable())
            )
            ->joinLeft(
                array(
                    'edc' => Mage::helper('M2ePro/Module_Database_Structure')
                                    ->getTableNameWithPrefix('m2epro_ebay_dictionary_category')
                ),
                'edc.marketplace_id = etc.marketplace_id AND edc.category_id = etc.category_id'
            )
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(
                array(
                    'etc.category_id',
                    'etc.marketplace_id',
                )
            )
            ->where('etc.category_mode = ?', Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY)
            ->where('edc.category_id IS NULL')
            ->group(
                array('etc.category_id', 'etc.marketplace_id')
            )
            ->query();

        return $stmt->fetchColumn() !== false;
    }

    //########################################
}
