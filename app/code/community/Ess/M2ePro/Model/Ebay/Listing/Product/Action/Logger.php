<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Listing_Product_Action_Logger extends Ess_M2ePro_Model_Ebay_Listing_Action_Logger
{
    protected $action = Ess_M2ePro_Model_Listing_Log::ACTION_UNKNOWN;

    /**
     * @var Ess_M2ePro_Model_Listing_Log
     */
    private $listingLog = NULL;

    //########################################

    /**
     * @param Ess_M2ePro_Model_Listing_Product $listingProduct
     * @param array $messageData
     * @param int $priority
     */
    public function logListingProductMessage(Ess_M2ePro_Model_Listing_Product $listingProduct,
                                             array $messageData,
                                             $priority = Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM)
    {
        if ($this->storeMode) {

            $this->storedMessages[] = array(
                'type' => $this->initLogType($messageData[Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_KEY]),
                'text' => $messageData[Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TEXT_KEY]
            );

            return;
        }

        $this->getListingLog()
            ->addProductMessage($listingProduct->getListingId() ,
                                $listingProduct->getProductId() ,
                                $listingProduct->getId() ,
                                $this->initiator ,
                                $this->actionId ,
                                $this->action ,
                                $messageData[Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TEXT_KEY],
                                $this->initLogType($messageData[Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_KEY]),
                                $priority);
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Listing_Log
     */
    private function getListingLog()
    {
        if (is_null($this->listingLog)) {

            /** @var Ess_M2ePro_Model_Listing_Log $listingLog */
            $listingLog = Mage::getModel('M2ePro/Listing_Log');
            $listingLog->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK);

            $this->listingLog = $listingLog;
        }

        return $this->listingLog;
    }

    //########################################
}