<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Listing_Product_Action_Logger
{
    private $action    = Ess_M2ePro_Model_Listing_Log::ACTION_UNKNOWN;

    private $actionId  = NULL;

    private $initiator = Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN;

    /**
     * @var Ess_M2ePro_Model_Walmart_Listing_Log
     */
    private $listingLog = NULL;

    private $status = Ess_M2ePro_Helper_Data::STATUS_SUCCESS;

    //########################################

    /**
     * @param $value
     */
    public function setAction($value)
    {
        $this->action = (int)$value;
    }

    /**
     * @param $id
     */
    public function setActionId($id)
    {
        $this->actionId = (int)$id;
    }

    /**
     * @param $value
     */
    public function setInitiator($value)
    {
        $this->initiator = (int)$value;
    }

    //########################################

    /**
     * @return null|int
     */
    public function getActionId()
    {
        return $this->actionId;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Listing_Product $listingProduct
     * @param string $message
     * @param int $type
     * @param int $priority
     */
    public function logListingProductMessage(Ess_M2ePro_Model_Listing_Product $listingProduct,
                                             Ess_M2ePro_Model_Connector_Connection_Response_Message $message,
                                             $priority = Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM)
    {
        $this->getListingLog()->addProductMessage($listingProduct->getListingId() ,
                                                  $listingProduct->getProductId() ,
                                                  $listingProduct->getId() ,
                                                  $this->initiator ,
                                                  $this->actionId ,
                                                  $this->action ,
                                                  $message->getText(),
                                                  $this->initLogType($message),
                                                  $priority);
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Walmart_Listing_Log
     */
    private function getListingLog()
    {
        if (is_null($this->listingLog)) {

            /** @var Ess_M2ePro_Model_Walmart_Listing_Log $listingLog */
            $listingLog = Mage::getModel('M2ePro/Walmart_Listing_Log');

            $this->listingLog = $listingLog;
        }

        return $this->listingLog;
    }

    //########################################

    private function initLogType(Ess_M2ePro_Model_Connector_Connection_Response_Message $message)
    {
        if ($message->isError()) {
            $this->setStatus(Ess_M2ePro_Helper_Data::STATUS_ERROR);
            return Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR;
        }

        if ($message->isWarning()) {
            $this->setStatus(Ess_M2ePro_Helper_Data::STATUS_WARNING);
            return Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING;
        }

        if ($message->isSuccess()) {
            $this->setStatus(Ess_M2ePro_Helper_Data::STATUS_SUCCESS);
            return Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS;
        }

        if ($message->isNotice()) {
            $this->setStatus(Ess_M2ePro_Helper_Data::STATUS_SUCCESS);
            return Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE;
        }

        $this->setStatus(Ess_M2ePro_Helper_Data::STATUS_ERROR);

        return Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR;
    }

    //########################################
}