<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Listing_Log extends Ess_M2ePro_Model_Listing_Log
{
    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK);
    }

    //########################################

    public function addProductMessage($listingId,
                                      $productId,
                                      $listingProductId,
                                      $initiator = Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN,
                                      $actionId = NULL,
                                      $action = NULL,
                                      $description = NULL,
                                      $type = NULL,
                                      $priority = NULL,
                                      array $additionalData = array())
    {
        $dataForAdd = $this->makeDataForAdd($listingId,
            $initiator,
            $productId,
            $listingProductId,
            $actionId,
            $action,
            $description,
            $type,
            $priority,
            $additionalData);

        $this->createMessage($dataForAdd);
    }

    //########################################
}