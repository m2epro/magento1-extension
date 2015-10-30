<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Buy_Magento_Product_Rule_Condition_Product
    extends Ess_M2ePro_Model_Magento_Product_Rule_Condition_Product
{
    //########################################

    protected function getCustomFilters()
    {
        $buyFilters = array(
            'buy_general_id'        => 'BuyGeneralId',
            'buy_sku'               => 'BuySku',
            'buy_online_qty'        => 'BuyOnlineQty',
            'buy_online_price'      => 'BuyOnlinePrice',
            'buy_status'            => 'BuyStatus'
        );

        return array_merge_recursive(
            parent::getCustomFilters(),
            $buyFilters
        );
    }

    /**
     * @param $filterId
     * @return Ess_M2ePro_Model_Magento_Product_Rule_Custom_Abstract
     */
    protected function getCustomFilterInstance($filterId)
    {
        $parentFilters = parent::getCustomFilters();
        if (isset($parentFilters[$filterId])) {
            return parent::getCustomFilterInstance($filterId);
        }

        $customFilters = $this->getCustomFilters();
        $this->_customFiltersCache[$filterId] = Mage::getModel(
            'M2ePro/Buy_Magento_Product_Rule_Custom_'.$customFilters[$filterId]
        );

        return $this->_customFiltersCache[$filterId];
    }

    //########################################
}