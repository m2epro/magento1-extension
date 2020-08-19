<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Component_Walmart_Category extends Mage_Core_Helper_Abstract
{
    const RECENT_MAX_COUNT = 20;

    //########################################

    public function getRecent($marketplaceId, array $excludedCategory = array())
    {
        $allRecentCategories = Mage::helper('M2ePro/Module')->getRegistry()->getValueFromJson($this->getConfigGroup());

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
        $allRecentCategories = Mage::helper('M2ePro/Module')->getRegistry()->getValueFromJson($this->getConfigGroup());

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

        Mage::helper('M2ePro/Module')->getRegistry()->setValue($this->getConfigGroup(), $allRecentCategories);
    }

    //########################################

    protected function getConfigGroup()
    {
        return "/walmart/category/recent/";
    }

    protected function removeNotAccessibleCategories($marketplaceId, array &$recentCategories)
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
            ->from(
                Mage::helper('M2ePro/Module_Database_Structure')
                    ->getTableNameWithPrefix('m2epro_walmart_dictionary_category')
            )
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

    protected function removeRecentCategory(array $category, $marketplaceId)
    {
        $allRecentCategories = Mage::helper('M2ePro/Module')->getRegistry()->getValueFromJson($this->getConfigGroup());
        $currentRecentCategories = $allRecentCategories[$marketplaceId];

        foreach ($currentRecentCategories as $index => $recentCategory) {
            if ($category['browsenode_id'] == $recentCategory['browsenode_id'] &&
                $category['path']          == $recentCategory['path']) {
                unset($allRecentCategories[$marketplaceId][$index]);
                break;
            }
        }

        Mage::helper('M2ePro/Module')->getRegistry()->setValue($this->getConfigGroup(), $allRecentCategories);
    }

    //########################################
}
