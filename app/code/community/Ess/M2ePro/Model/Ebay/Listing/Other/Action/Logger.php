<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Listing_Other_Action_Logger extends Ess_M2ePro_Model_Ebay_Listing_Action_Logger
{
    protected $action = Ess_M2ePro_Model_Listing_Other_Log::ACTION_UNKNOWN;

    /**
     * @var Ess_M2ePro_Model_Listing_Other_Log
     */
    private $listingOtherLog = NULL;

    // ########################################

    public function logGlobalMessage(array $messageData,
                                     $priority = Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM)
    {
        if ($this->storeMode) {

            $this->storedMessages[] = array(
                'type' => $this->initLogType($messageData[Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_KEY]),
                'text' => $messageData[Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TEXT_KEY]
            );

            return;
        }

        $this->getListingOtherLog()
             ->addGlobalMessage($this->initiator,
                                $this->actionId,
                                $this->action,
                                $messageData[Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TEXT_KEY],
                                $this->initLogType($messageData[Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_KEY]),
                                $priority);
    }

    public function logListingOtherMessage(Ess_M2ePro_Model_Listing_Other $listingOther,
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

        $this->getListingOtherLog()
            ->addProductMessage($listingOther->getId() ,
                                $this->initiator ,
                                $this->actionId ,
                                $this->action ,
                                $messageData[Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TEXT_KEY],
                                $this->initLogType($messageData[Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_KEY]),
                                $priority);
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Listing_Other_Log
     */
    private function getListingOtherLog()
    {
        if (is_null($this->listingOtherLog)) {

            /** @var Ess_M2ePro_Model_Listing_Other_Log $listingOtherLog */
            $listingOtherLog = Mage::getModel('M2ePro/Listing_Other_Log');
            $listingOtherLog->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK);

            $this->listingOtherLog = $listingOtherLog;
        }

        return $this->listingOtherLog;
    }

    // ########################################
}