<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Listing_Product_Action_DataBuilder_LagTime
    extends Ess_M2ePro_Model_Walmart_Listing_Product_Action_DataBuilder_Abstract
{
    //########################################

    /**
     * @return array
     */
    public function getData()
    {
        if (!isset($this->_cachedData['lag_time'])) {
            $lagTime = $this->getWalmartListingProduct()->getSellingFormatTemplateSource()->getLagTime();
            $this->_cachedData['lag_time'] = $lagTime;
        }

        return array(
            'lag_time' => $this->_cachedData['lag_time']
        );
    }

    //########################################
}