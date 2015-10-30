<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Component_Amazon_ProductData extends Mage_Core_Helper_Abstract
{
    const RECENT_MAX_COUNT = 5;

    //########################################

    public function getRecent($marketplaceId, $excludedProductDataNick = null)
    {
        /** @var $registryModel Ess_M2ePro_Model_Registry */
        $registryModel = Mage::getModel('M2ePro/Registry')->load($this->getConfigGroup(), 'key');
        $allRecent = $registryModel->getValueFromJson();

        if (!isset($allRecent[$marketplaceId])) {
            return array();
        }

        $recent = $allRecent[$marketplaceId];

        foreach ($recent as $index => $recentProductDataNick) {

            if ($excludedProductDataNick == $recentProductDataNick) {
                unset($recent[$index]);
            }
        }

        return array_reverse($recent);
    }

    public function addRecent($marketplaceId, $productDataNick)
    {
        $key = $this->getConfigGroup();

        /** @var $registryModel Ess_M2ePro_Model_Registry */
        $registryModel = Mage::getModel('M2ePro/Registry')->load($key, 'key');
        $allRecent = $registryModel->getValueFromJson();

        !isset($allRecent[$marketplaceId]) && $allRecent[$marketplaceId] = array();

        $recent = $allRecent[$marketplaceId];
        foreach ($recent as $recentProductDataNick) {

            if ($productDataNick == $recentProductDataNick) {
                return;
            }
        }

        if (count($recent) >= self::RECENT_MAX_COUNT) {
            array_shift($recent);
        }

        $recent[] = $productDataNick;
        $allRecent[$marketplaceId] = $recent;

        $registryModel->addData(array(
            'key'   => $key,
            'value' => json_encode($allRecent)
        ))->save();
    }

    //########################################

    private function getConfigGroup()
    {
        return "/amazon/product_data/recent/";
    }

    //########################################
}