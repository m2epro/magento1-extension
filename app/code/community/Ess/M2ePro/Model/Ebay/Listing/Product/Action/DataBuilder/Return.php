<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Listing_Product_Action_DataBuilder_Return
    extends Ess_M2ePro_Model_Ebay_Listing_Product_Action_DataBuilder_Abstract
{
    /**
     * @var Ess_M2ePro_Model_Ebay_Template_Return
     */
    private $returnTemplate = NULL;

    //########################################

    public function getData()
    {
        return array(
            'return' => array(
                'accepted'      => $this->getReturnTemplate()->getAccepted(),
                'option'        => $this->getReturnTemplate()->getOption(),
                'within'        => $this->getReturnTemplate()->getWithin(),
                'shipping_cost' => $this->getReturnTemplate()->getShippingCost(),

                'international_accepted'      => $this->getReturnTemplate()->getInternationalAccepted(),
                'international_option'        => $this->getReturnTemplate()->getInternationalOption(),
                'international_within'        => $this->getReturnTemplate()->getInternationalWithin(),
                'international_shipping_cost' => $this->getReturnTemplate()->getInternationalShippingCost(),

                'description' => $this->getReturnTemplate()->getDescription()
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