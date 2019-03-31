<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Action_DataBuilder_Price_Business
    extends Ess_M2ePro_Model_Amazon_Listing_Product_Action_DataBuilder_Abstract
{
    const BUSINESS_DISCOUNTS_TYPE_FIXED = 'fixed';

    //########################################

    /**
     * @return array
     */
    public function getData()
    {
        $data = array();

        if (!isset($this->cachedData['business_price'])) {
            $this->cachedData['business_price'] = $this->getAmazonListingProduct()->getBusinessPrice();
        }

        if (!isset($this->cachedData['business_discounts'])) {
            $this->cachedData['business_discounts'] = $this->getAmazonListingProduct()->getBusinessDiscounts();
        }

        $data['business_price'] = $this->cachedData['business_price'];

        if ($businessDiscounts = $this->cachedData['business_discounts']) {
            ksort($businessDiscounts);

            $data['business_discounts'] = array(
                'type'   => self::BUSINESS_DISCOUNTS_TYPE_FIXED,
                'values' => $businessDiscounts
            );
        }

        return $data;
    }

    //########################################
}