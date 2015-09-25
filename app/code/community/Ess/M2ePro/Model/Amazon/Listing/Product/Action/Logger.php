<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Action_Logger
{
    private $action    = Ess_M2ePro_Model_Listing_Log::ACTION_UNKNOWN;

    private $actionId  = NULL;

    private $initiator = Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN;

    /**
     * @var Ess_M2ePro_Model_Amazon_Listing_Log
     */
    private $listingLog = NULL;

    private $status = Ess_M2ePro_Helper_Data::STATUS_SUCCESS;

    // ########################################

    public function setAction($value)
    {
        $this->action = (int)$value;
    }

    public function setActionId($id)
    {
        $this->actionId = (int)$id;
    }

    public function setInitiator($value)
    {
        $this->initiator = (int)$value;
    }

    // ########################################

    public function getActionId()
    {
        return $this->actionId;
    }

    // ----------------------------------------

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        if ($status == Ess_M2ePro_Helper_Data::STATUS_ERROR) {
            $this->status = Ess_M2ePro_Helper_Data::STATUS_ERROR;
            return;
        }

        if ($this->status == Ess_M2ePro_Helper_Data::STATUS_ERROR) {
            return;
        }

        if ($status == Ess_M2ePro_Helper_Data::STATUS_WARNING) {
            $this->status = Ess_M2ePro_Helper_Data::STATUS_WARNING;
            return;
        }

        if ($this->status == Ess_M2ePro_Helper_Data::STATUS_WARNING) {
            return;
        }

        $this->status = Ess_M2ePro_Helper_Data::STATUS_SUCCESS;
    }

    // ########################################

    /**
     * @param array $message
     * @return array
     */
    public function getConvertedMessageData(array $message)
    {
        $result = array(
            'text' => $message[Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TEXT_KEY]
        );

        switch ($message[Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_KEY]) {
            case Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_ERROR:
                    $result['type'] = Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR;
                break;
            case Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_WARNING:
                    $result['type'] = Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING;
                break;
            case Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_SUCCESS:
                    $result['type'] = Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS;
                break;
            case Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_NOTICE:
                    $result['type'] = Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE;
                break;
            default:
                    $result['type'] = Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR;
                break;
        }

        switch ($message[Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_KEY]) {

            case Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_WARNING:
            case Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_SUCCESS:
                    $result['priority'] = Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM;
                break;
            case Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_NOTICE:
                    $result['priority'] = Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW;
                break;
            case Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_ERROR:
            default:
                    $result['priority'] = Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH;
                break;
        }

        return $result;
    }

    public function logListingProductMessage(Ess_M2ePro_Model_Listing_Product $listingProduct,
                                             $message,
                                             $type = Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                                             $priority = Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM)
    {
        $this->setStatusByMessageType($type);

        $this->getListingLog()->addProductMessage($listingProduct->getListingId() ,
                                                  $listingProduct->getProductId() ,
                                                  $listingProduct->getId() ,
                                                  $this->initiator ,
                                                  $this->actionId ,
                                                  $this->action ,
                                                  $message,
                                                  $type,
                                                  $priority);
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing_Log
     */
    private function getListingLog()
    {
        if (is_null($this->listingLog)) {

            /** @var Ess_M2ePro_Model_Amazon_Listing_Log $listingLog */
            $listingLog = Mage::getModel('M2ePro/Amazon_Listing_Log');

            $this->listingLog = $listingLog;
        }

        return $this->listingLog;
    }

    private function setStatusByMessageType($messageType)
    {
        switch ($messageType) {
            case Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR:
                    $this->setStatus(Ess_M2ePro_Helper_Data::STATUS_ERROR);
                break;
            case Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING:
                    $this->setStatus(Ess_M2ePro_Helper_Data::STATUS_WARNING);
                break;
            case Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS:
                    $this->setStatus(Ess_M2ePro_Helper_Data::STATUS_SUCCESS);
                break;
            case Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE:
                    $this->setStatus(Ess_M2ePro_Helper_Data::STATUS_SUCCESS);
                break;
            default:
                    $this->setStatus(Ess_M2ePro_Helper_Data::STATUS_ERROR);
                break;
        }
    }

    // ########################################
}