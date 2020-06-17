<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Listing_Product_Action_Logger
{
    protected $_action = Ess_M2ePro_Model_Listing_Log::ACTION_UNKNOWN;

    protected $_actionId = null;

    protected $_initiator = Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN;

    /**
     * @var Ess_M2ePro_Model_Walmart_Listing_Log
     */
    protected $_listingLog = null;

    protected $_status = Ess_M2ePro_Helper_Data::STATUS_SUCCESS;

    //########################################

    /**
     * @param $value
     */
    public function setAction($value)
    {
        $this->_action = (int)$value;
    }

    /**
     * @param $id
     */
    public function setActionId($id)
    {
        $this->_actionId = (int)$id;
    }

    /**
     * @param $value
     */
    public function setInitiator($value)
    {
        $this->_initiator = (int)$value;
    }

    //########################################

    /**
     * @return null|int
     */
    public function getActionId()
    {
        return $this->_actionId;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->_status;
    }

    /**
     * @param int $status
     */
    public function setStatus($status)
    {
        $this->_status = $status;
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Listing_Product $listingProduct
     * @param string $message
     * @param int $type
     */
    public function logListingProductMessage(
        Ess_M2ePro_Model_Listing_Product $listingProduct,
        Ess_M2ePro_Model_Connector_Connection_Response_Message $message
    ) {
        $this->getListingLog()->addProductMessage(
            $listingProduct->getListingId(),
            $listingProduct->getProductId(),
            $listingProduct->getId(),
            $this->_initiator,
            $this->_actionId,
            $this->_action,
            $message->getText(),
            $this->initLogType($message)
        );
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Walmart_Listing_Log
     */
    protected function getListingLog()
    {
        if ($this->_listingLog === null) {

            /** @var Ess_M2ePro_Model_Walmart_Listing_Log $listingLog */
            $listingLog = Mage::getModel('M2ePro/Walmart_Listing_Log');

            $this->_listingLog = $listingLog;
        }

        return $this->_listingLog;
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
}
