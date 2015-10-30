<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Listing_Other_Action_Request_Selling
    extends Ess_M2ePro_Model_Ebay_Listing_Other_Action_Request
{
    //########################################

    /**
     * @return array
     */
    public function getData()
    {
        return array_merge(
            $this->getQtyData(),
            $this->getPriceData()
        );
    }

    //########################################

    /**
     * @return array
     */
    public function getQtyData()
    {
        if (!$this->getConfigurator()->isQtyAllowed()) {
            return array();
        }

        $qty = $this->getEbayListingOther()->getMappedQty();

        if (is_null($qty)) {
            return array();
        }

        return array(
            'qty' => $qty
        );
    }

    /**
     * @return array
     */
    public function getPriceData()
    {
        if (!$this->getConfigurator()->isPriceAllowed()) {
            return array();
        }

        $price = $this->getEbayListingOther()->getMappedPrice();

        if (is_null($price)) {
            return array();
        }

        return array(
            'price_fixed' => $price
        );
    }

    //########################################
}