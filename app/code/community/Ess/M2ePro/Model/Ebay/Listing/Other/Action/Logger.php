<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Listing_Other_Action_Logger extends Ess_M2ePro_Model_Ebay_Listing_Action_Logger
{
    protected $action = Ess_M2ePro_Model_Listing_Other_Log::ACTION_UNKNOWN;

    /**
     * @var Ess_M2ePro_Model_Listing_Other_Log
     */
    private $listingOtherLog = NULL;

    //########################################

    /**
     * @param array $messageData
     * @param int $priority
     */
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

    /**
     * @param Ess_M2ePro_Model_Listing_Other $listingOther
     * @param array $messageData
     * @param int $priority
     */
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

    //########################################

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

    //########################################
}