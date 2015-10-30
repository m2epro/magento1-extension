<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Component_Ebay_Category extends Mage_Core_Helper_Abstract
{
    const TYPE_EBAY_MAIN = 0;
    const TYPE_EBAY_SECONDARY = 1;
    const TYPE_STORE_MAIN = 2;
    const TYPE_STORE_SECONDARY = 3;

    const RECENT_MAX_COUNT = 20;

    //########################################

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
        $configPath = $this->getRecentConfigPath($categoryType);
        $allRecentCategories = Mage::getModel('M2ePro/Registry')->load('/ebay/category/recent/', 'key')
                                                                ->getValueFromJson();

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
        $key = '/ebay/category/recent/';
        $configPath = $this->getRecentConfigPath($categoryType);

        /** @var $registryModel Ess_M2ePro_Model_Registry */
        $registryModel = Mage::getModel('M2ePro/Registry')->load($key, 'key');
        $allRecentCategories = $registryModel->getValueFromJson();

        $categories = array();
        if (isset($allRecentCategories[$configPath][$marketplaceOrAccountId])) {
            $categories = (array)explode(',', $allRecentCategories[$configPath][$marketplaceOrAccountId]);
        }

        if (count($categories) >= self::RECENT_MAX_COUNT) {
            array_pop($categories);
        }

        array_unshift($categories, $categoryId);
        $categories = array_unique($categories);

        $allRecentCategories[$configPath][$marketplaceOrAccountId] = implode(',' ,$categories);
        $registryModel->addData(array(
            'key' => $key,
            'value' => json_encode($allRecentCategories)
        ))->save();
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

    public function getSameTemplatesData($ids, $table, $modes)
    {
        $fields = array();

        foreach ($modes as $mode) {
            $fields[] = $mode.'_id';
            $fields[] = $mode.'_path';
            $fields[] = $mode.'_mode';
            $fields[] = $mode.'_attribute';
        }

        $select = Mage::getSingleton('core/resource')->getConnection('core_read')->select();
        $select->from($table, $fields);
        $select->where('id IN (?)', $ids);

        $templatesData = $select->query()->fetchAll(PDO::FETCH_ASSOC);

        $resultData = reset($templatesData);

        if (!$resultData) {
            return array();
        }

        foreach ($modes as $i => $mode) {

            if (!Mage::helper('M2ePro')->theSameItemsInData($templatesData, array_slice($fields,$i*4,4))) {
                $resultData[$mode.'_id'] = 0;
                $resultData[$mode.'_path'] = NULL;
                $resultData[$mode.'_mode'] = Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_NONE;
                $resultData[$mode.'_attribute'] = NULL;
                $resultData[$mode.'_message'] = Mage::helper('M2ePro')->__(
                    'Please, specify a value suitable for all chosen Products.'
                );
            }
        }

        return $resultData;
    }

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
}