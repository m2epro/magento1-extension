<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Component_Amazon_Category extends Mage_Core_Helper_Abstract
{
    const RECENT_MAX_COUNT = 20;

    //########################################

    public function getRecent($marketplaceId, array $excludedCategory = array())
    {
        /** @var $registryModel Ess_M2ePro_Model_Registry */
        $registryModel = Mage::getModel('M2ePro/Registry')->load($this->getConfigGroup(), 'key');
        $allRecentCategories = $registryModel->getValueFromJson();

        if (!isset($allRecentCategories[$marketplaceId])) {
            return array();
        }

        $recentCategories = $allRecentCategories[$marketplaceId];

        foreach ($recentCategories as $index => $recentCategoryValue) {

            $isRecentCategoryExists = isset($recentCategoryValue['browsenode_id'], $recentCategoryValue['path']);

            $isCategoryEqualExcludedCategory = !empty($excludedCategory) &&
                ($excludedCategory['browsenode_id'] == $recentCategoryValue['browsenode_id'] &&
                 $excludedCategory['path']          == $recentCategoryValue['path']);

            if (!$isRecentCategoryExists || $isCategoryEqualExcludedCategory) {
                unset($recentCategories[$index]);
            }
        }

        // some categories can be not accessible in the current marketplaces build
        $this->removeNotAccessibleCategories($marketplaceId, $recentCategories);

        return array_reverse($recentCategories);
    }

    public function addRecent($marketplaceId, $browseNodeId, $categoryPath)
    {
        $key = $this->getConfigGroup();

        /** @var $registryModel Ess_M2ePro_Model_Registry */
        $registryModel = Mage::getModel('M2ePro/Registry')->load($key, 'key');
        $allRecentCategories = $registryModel->getValueFromJson();

        !isset($allRecentCategories[$marketplaceId]) && $allRecentCategories[$marketplaceId] = array();

        $recentCategories = $allRecentCategories[$marketplaceId];
        foreach ($recentCategories as $recentCategoryValue) {

            if (!isset($recentCategoryValue['browsenode_id'], $recentCategoryValue['path'])) {
                continue;
            }

            if ($recentCategoryValue['browsenode_id'] == $browseNodeId &&
                $recentCategoryValue['path'] == $categoryPath) {
                return;
            }
        }

        if (count($recentCategories) >= self::RECENT_MAX_COUNT) {
            array_shift($recentCategories);
        }

        $categoryInfo = array(
            'browsenode_id' => $browseNodeId,
            'path'          => $categoryPath
        );

        $recentCategories[] = $categoryInfo;
        $allRecentCategories[$marketplaceId] = $recentCategories;

        $registryModel->addData(array(
            'key'   => $key,
            'value' => json_encode($allRecentCategories)
        ))->save();
    }

    //########################################

    private function getConfigGroup()
    {
        return "/amazon/category/recent/";
    }

    private function removeNotAccessibleCategories($marketplaceId, array &$recentCategories)
    {
        if (empty($recentCategories)) {
            return;
        }

        $nodeIdsForCheck = array();
        foreach ($recentCategories as $categoryData) {
            $nodeIdsForCheck[] = $categoryData['browsenode_id'];
        }

        $select = Mage::getSingleton('core/resource')->getConnection('core_read')
            ->select()
            ->from(Mage::getSingleton('core/resource')->getTableName('m2epro_amazon_dictionary_category'))
            ->where('marketplace_id = ?', $marketplaceId)
            ->where('browsenode_id IN (?)', array_unique($nodeIdsForCheck));

        $queryStmt = $select->query();
        $tempCategories = array();

        while ($row = $queryStmt->fetch()) {
            $path = $row['path'] ? $row['path'] .'>'. $row['title'] : $row['title'];
            $key = $row['browsenode_id'] .'##'. $path;
            $tempCategories[$key] = $row;
        }

        foreach ($recentCategories as $categoryKey => &$categoryData) {

            $categoryPath = str_replace(' > ', '>', $categoryData['path']);
            $key = $categoryData['browsenode_id'] .'##'. $categoryPath;

            if (!array_key_exists($key, $tempCategories)) {
                $this->removeRecentCategory($categoryData, $marketplaceId);
                unset($recentCategories[$categoryKey]);
            }
        }
    }

    private function removeRecentCategory(array $category, $marketplaceId)
    {
        /** @var $registryModel Ess_M2ePro_Model_Registry */
        $registryModel = Mage::getModel('M2ePro/Registry')->load($this->getConfigGroup(), 'key');
        $allRecentCategories = $registryModel->getValueFromJson();
        $currentRecentCategories = $allRecentCategories[$marketplaceId];

        foreach ($currentRecentCategories as $index => $recentCategory) {
            if ($category['browsenode_id'] == $recentCategory['browsenode_id'] &&
                $category['path']          == $recentCategory['path']) {

                unset($allRecentCategories[$marketplaceId][$index]);
                break;
            }
        }

        $registryModel->addData(array(
            'key' => $this->getConfigGroup(),
            'value' => json_encode($allRecentCategories)
        ))->save();
    }

    //########################################
}