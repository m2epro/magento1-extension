<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Listing_Product_Action_Logger
{
    protected $_action = Ess_M2ePro_Model_Listing_Log::ACTION_UNKNOWN;

    protected $_actionId  = null;
    protected $_initiator = Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN;

    protected $_storeMode      = false;
    protected $_storedMessages = array();

    protected $_status = Ess_M2ePro_Helper_Data::STATUS_SUCCESS;

    /**
     * @var Ess_M2ePro_Model_Listing_Log
     */
    protected $_listingLog = null;

    //########################################

    /**
     * @param int $id
     */
    public function setActionId($id)
    {
        $this->_actionId = (int)$id;
    }

    /**
     * @return null|int
     */
    public function getActionId()
    {
        return $this->_actionId;
    }

    // ---------------------------------------

    /**
     * @param int $value
     */
    public function setAction($value)
    {
        $this->_action = (int)$value;
    }

    /**
     * @param int $value
     */
    public function setInitiator($value)
    {
        $this->_initiator = (int)$value;
    }

    //########################################

    public function setStoreMode($value)
    {
        $this->_storeMode = (bool)$value;
    }

    public function getStoredMessages()
    {
        return $this->_storedMessages;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->_status;
    }

    public function setStatus($status)
    {
        if ($status == Ess_M2ePro_Helper_Data::STATUS_ERROR) {
            $this->_status = Ess_M2ePro_Helper_Data::STATUS_ERROR;
            return;
        }

        if ($this->_status == Ess_M2ePro_Helper_Data::STATUS_ERROR) {
            return;
        }

        if ($status == Ess_M2ePro_Helper_Data::STATUS_WARNING) {
            $this->_status = Ess_M2ePro_Helper_Data::STATUS_WARNING;
            return;
        }

        if ($this->_status == Ess_M2ePro_Helper_Data::STATUS_WARNING) {
            return;
        }

        $this->_status = Ess_M2ePro_Helper_Data::STATUS_SUCCESS;
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Listing_Product $listingProduct
     * @param Ess_M2ePro_Model_Connector_Connection_Response_Message $message
     * @param int $priority
     */
    public function logListingProductMessage(
        Ess_M2ePro_Model_Listing_Product $listingProduct,
        Ess_M2ePro_Model_Connector_Connection_Response_Message $message,
        $priority = Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
    ) {
        if ($this->_storeMode) {
            $this->_storedMessages[] = array(
                'type' => $this->initLogType($message),
                'text' => $message->getText(),
            );

            return;
        }

        $this->getListingLog()
            ->addProductMessage(
                $listingProduct->getListingId(),
                $listingProduct->getProductId(),
                $listingProduct->getId(),
                $this->_initiator,
                $this->_actionId,
                $this->_action,
                $message->getText(),
                $this->initLogType($message),
                $priority
            );
    }

    //########################################

    protected function initLogType(Ess_M2ePro_Model_Connector_Connection_Response_Message $message)
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

    /**
     * @return Ess_M2ePro_Model_Listing_Log
     */
    protected function getListingLog()
    {
        if ($this->_listingLog === null) {

            /** @var Ess_M2ePro_Model_Listing_Log $listingLog */
            $listingLog = Mage::getModel('M2ePro/Listing_Log');
            $listingLog->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK);

            $this->_listingLog = $listingLog;
        }

        return $this->_listingLog;
    }

    //########################################
}
