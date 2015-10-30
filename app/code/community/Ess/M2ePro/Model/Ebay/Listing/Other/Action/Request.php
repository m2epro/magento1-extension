<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Ebay_Listing_Other_Action_Request extends Ess_M2ePro_Model_Ebay_Listing_Action_Request
{
    /**
     * @var Ess_M2ePro_Model_Listing_Other
     */
    private $listingOther = NULL;

    //########################################

    /**
     * @param Ess_M2ePro_Model_Listing_Other $object
     */
    public function setListingOther(Ess_M2ePro_Model_Listing_Other $object)
    {
        $this->listingOther = $object;
    }

    /**
     * @return Ess_M2ePro_Model_Listing_Other
     */
    protected function getListingOther()
    {
        return $this->listingOther;
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Other
     */
    protected function getEbayListingOther()
    {
        return $this->getListingOther()->getChildObject();
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    protected function getMarketplace()
    {
        return $this->getListingOther()->getMarketplace();
    }

    /**
     * @return Ess_M2ePro_Model_Account
     */
    protected function getAccount()
    {
        return $this->getListingOther()->getAccount();
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Magento_Product_Cache
     */
    protected function getMagentoProduct()
    {
        return $this->getListingOther()->getMagentoProduct();
    }

    //########################################
}