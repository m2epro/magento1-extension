<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request_Return
    extends Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request_Abstract
{
    /**
     * @var Ess_M2ePro_Model_Ebay_Template_Return
     */
    private $returnTemplate = NULL;

    //########################################

    /**
     * @return array
     */
    public function getData()
    {
        return array(
            'return' => array(
                'accepted'      => $this->getReturnTemplate()->getAccepted(),
                'option'        => $this->getReturnTemplate()->getOption(),
                'within'        => $this->getReturnTemplate()->getWithin(),
                'is_holiday_enabled' => $this->getReturnTemplate()->isHolidayEnabled(),
                'description'   => $this->getReturnTemplate()->getDescription(),
                'shipping_cost'  => $this->getReturnTemplate()->getShippingCost(),
                'restocking_fee' => $this->getReturnTemplate()->getRestockingFee()
            )
        );
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Return
     */
    private function getReturnTemplate()
    {
        if (is_null($this->returnTemplate)) {
            $this->returnTemplate = $this->getListingProduct()
                                         ->getChildObject()
                                         ->getReturnTemplate();
        }
        return $this->returnTemplate;
    }

    //########################################
}