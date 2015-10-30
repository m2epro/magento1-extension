<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_Stop_Request
    extends Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_Request
{
    //########################################

    protected function getActionData()
    {
        return array(
            'sku' => $this->getAmazonListingProduct()->getSku(),
            'qty' => 0
        );
    }

    //########################################
}