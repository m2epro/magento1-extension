<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Component_Walmart_ProductData extends Mage_Core_Helper_Abstract
{
    const RECENT_MAX_COUNT = 5;

    //########################################

    public function getRecent($marketplaceId, $excludedProductDataNick = null)
    {
        $allRecent = Mage::helper('M2ePro/Module')->getRegistry()->getValueFromJson($this->getConfigGroup());

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
        $allRecent = Mage::helper('M2ePro/Module')->getRegistry()->getValueFromJson($this->getConfigGroup());

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

        Mage::helper('M2ePro/Module')->getRegistry()->setValue($this->getConfigGroup(), $allRecent);
    }

    public function encodeWalmartSku($sku)
    {
        return rawurlencode($sku);
    }

    //########################################

    protected function getConfigGroup()
    {
        return "/walmart/product_data/recent/";
    }

    //########################################
}
