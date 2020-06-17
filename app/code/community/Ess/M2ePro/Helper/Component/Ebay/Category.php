<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Component_Ebay_Category extends Mage_Core_Helper_Abstract
{
    const TYPE_EBAY_MAIN       = 0;
    const TYPE_EBAY_SECONDARY  = 1;
    const TYPE_STORE_MAIN      = 2;
    const TYPE_STORE_SECONDARY = 3;

    const RECENT_MAX_COUNT = 20;

    //########################################

    public function isEbayCategoryType($type)
    {
        /*
         * dirty hack because of integer constants: in_array('any-string', []) returns true
         * in_array($type, [], true) CAN NOT be used!
         */
        if ((strlen($type) !== 1)) {
            return false;
        }

        return in_array((int)$type, $this->getEbayCategoryTypes());
    }

    public function isStoreCategoryType($type)
    {
        /*
        * dirty hack because of integer constants: in_array('any-string', []) returns true
        * in_array($type, [], true) CAN NOT be used!
        */
        if ((strlen($type) !== 1)) {
            return false;
        }

        return in_array((int)$type, $this->getStoreCategoryTypes());
    }

    public function getCategoriesTypes()
    {
        return array_merge(
            $this->getEbayCategoryTypes(),
            $this->getStoreCategoryTypes()
        );
    }

    public function getEbayCategoryTypes()
    {
        return array(
            self::TYPE_EBAY_MAIN,
            self::TYPE_EBAY_SECONDARY
        );
    }

    public function getStoreCategoryTypes()
    {
        return array(
            self::TYPE_STORE_MAIN,
            self::TYPE_STORE_SECONDARY
        );
    }

    //########################################

    public function getRecent($marketplaceOrAccountId, $categoryType, $excludeCategory = null)
    {
        /** @var $registryModel Ess_M2ePro_Model_Registry */
        $registryModel = Mage::getModel('M2ePro/Registry')->loadByKey('/ebay/category/recent/');
        $allRecentCategories = $registryModel->getValueFromJson();
        $configPath = $this->getRecentConfigPath($categoryType);

        if (!isset($allRecentCategories[$configPath]) ||
            !isset($allRecentCategories[$configPath][$marketplaceOrAccountId])) {
            return array();
        }

        $recentCategories = $allRecentCategories[$configPath][$marketplaceOrAccountId];

        if (in_array($categoryType, $this->getEbayCategoryTypes())) {
            $categoryHelper = Mage::helper('M2ePro/Component_Ebay_Category_Ebay');
        } else {
            $categoryHelper = Mage::helper('M2ePro/Component_Ebay_Category_Store');
        }

        $categoryIds = (array)explode(',', $recentCategories);
        $result = array();
        foreach ($categoryIds as $categoryId) {
            if ($categoryId === $excludeCategory) {
                continue;
            }

            $path = $categoryHelper->getPath($categoryId, $marketplaceOrAccountId);
            if (empty($path)) {
                continue;
            }

            $result[] = array(
                'id' => $categoryId,
                'path' => $path . ' (' . $categoryId . ')',
            );
        }

        return $result;
    }

    public function addRecent($categoryId, $marketplaceOrAccountId, $categoryType)
    {
        /** @var $registryModel Ess_M2ePro_Model_Registry */
        $registryModel = Mage::getModel('M2ePro/Registry')->loadByKey('/ebay/category/recent/');
        $allRecentCategories = $registryModel->getValueFromJson();
        $configPath = $this->getRecentConfigPath($categoryType);

        $categories = array();
        if (isset($allRecentCategories[$configPath][$marketplaceOrAccountId])) {
            $categories = (array)explode(',', $allRecentCategories[$configPath][$marketplaceOrAccountId]);
        }

        if (count($categories) >= self::RECENT_MAX_COUNT) {
            array_pop($categories);
        }

        array_unshift($categories, $categoryId);
        $categories = array_unique($categories);

        $allRecentCategories[$configPath][$marketplaceOrAccountId] = implode(',', $categories);

        $registryModel->setValue($allRecentCategories);
        $registryModel->save();
    }

    public function removeEbayRecent()
    {
        /** @var $registryModel Ess_M2ePro_Model_Registry */
        $registryModel = Mage::getModel('M2ePro/Registry')->loadByKey('/ebay/category/recent/');
        $allRecentCategories = $registryModel->getValueFromJson();

        foreach ($this->getEbayCategoryTypes() as $categoryType) {
            unset($allRecentCategories[$this->getRecentConfigPath($categoryType)]);
        }

        $registryModel->setValue($allRecentCategories);
        $registryModel->save();
    }

    public function removeStoreRecent()
    {
        /** @var $registryModel Ess_M2ePro_Model_Registry */
        $registryModel = Mage::getModel('M2ePro/Registry')->loadByKey('/ebay/category/recent/');
        $allRecentCategories = $registryModel->getValueFromJson();

        foreach ($this->getStoreCategoryTypes() as $categoryType) {
            unset($allRecentCategories[$this->getRecentConfigPath($categoryType)]);
        }

        $registryModel->setValue($allRecentCategories);
        $registryModel->save();
    }

    // ---------------------------------------

    protected function getRecentConfigPath($categoryType)
    {
        $configPaths = array(
            self::TYPE_EBAY_MAIN       => '/ebay/main/',
            self::TYPE_EBAY_SECONDARY  => '/ebay/secondary/',
            self::TYPE_STORE_MAIN      => '/store/main/',
            self::TYPE_STORE_SECONDARY => '/store/secondary/',
        );

        return $configPaths[$categoryType];
    }

    //########################################

    public function fillCategoriesPaths(array &$data, Ess_M2ePro_Model_Listing $listing)
    {
        $ebayCategoryHelper = Mage::helper('M2ePro/Component_Ebay_Category_Ebay');
        $ebayStoreCategoryHelper = Mage::helper('M2ePro/Component_Ebay_Category_Store');

        $temp = array(
            'category_main'            => array('call' => array($ebayCategoryHelper,'getPath'),
                                                'arg'  => $listing->getMarketplaceId()),
            'category_secondary'       => array('call' => array($ebayCategoryHelper,'getPath'),
                                                'arg'  => $listing->getMarketplaceId()),
            'store_category_main'      => array('call' => array($ebayStoreCategoryHelper,'getPath'),
                                                'arg'  => $listing->getAccountId()),
            'store_category_secondary' => array('call' => array($ebayStoreCategoryHelper,'getPath'),
                                                'arg'  => $listing->getAccountId()),
        );

        foreach ($temp as $key => $value) {
            if (!isset($data[$key.'_mode']) || !empty($data[$key.'_path'])) {
                continue;
            }

            if ($data[$key.'_mode'] == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY) {
                $data[$key.'_path'] = call_user_func($value['call'], $data[$key.'_id'], $value['arg']);
            }

            if ($data[$key.'_mode'] == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_ATTRIBUTE) {
                $attributeLabel = Mage::helper('M2ePro/Magento_Attribute')->getAttributeLabel(
                    $data[$key.'_attribute'], $listing->getStoreId()
                );
                $data[$key.'_path'] = 'Magento Attribute' . ' > ' . $attributeLabel;
            }
        }
    }

    //########################################

    public function getCategoryTitles()
    {
        $titles = array();

        $type = self::TYPE_EBAY_MAIN;
        $titles[$type] = Mage::helper('M2ePro')->__('Primary Category');

        $type = self::TYPE_EBAY_SECONDARY;
        $titles[$type] = Mage::helper('M2ePro')->__('Secondary Category');

        $type = self::TYPE_STORE_MAIN;
        $titles[$type] = Mage::helper('M2ePro')->__('Store Primary Category');

        $type = self::TYPE_STORE_SECONDARY;
        $titles[$type] = Mage::helper('M2ePro')->__('Store Secondary Category');

        return $titles;
    }

    public function getCategoryTitle($type)
    {
        $titles = $this->getCategoryTitles();

        if (isset($titles[$type])) {
            return $titles[$type];
        }

        return '';
    }

    //########################################
}
