<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Listing_Product_Action_DataBuilder_Promotions
    extends Ess_M2ePro_Model_Walmart_Listing_Product_Action_DataBuilder_Abstract
{
    //########################################

    /**
     * @return array
     */
    public function getData()
    {
        $data = array();

        if (!isset($this->_cachedData['promotions'])) {
            $this->_cachedData['promotions'] = $this->getWalmartListingProduct()->getValidPromotions();
        }

        $data['promotion_prices'] = $this->_cachedData['promotions'];

        return $data;
    }

    //########################################
}
