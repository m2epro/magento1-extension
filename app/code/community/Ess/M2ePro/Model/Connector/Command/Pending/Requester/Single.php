<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Connector_Command_Pending_Requester_Single extends Ess_M2ePro_Model_Abstract
{
    /** @var Ess_M2ePro_Model_Processing $processing */
    private $processing = null;

    /** @var Ess_M2ePro_Model_Request_Pending_Single $requestPendingSingle */
    private $requestPendingSingle = null;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Connector_Command_Pending_Requester_Single');
    }

    //########################################

    public function getProcessing()
    {
        if (!is_null($this->processing)) {
            return $this->processing;
        }

        return $this->processing = Mage::getModel('M2ePro/Processing')->load($this->getProcessingId());
    }

    public function getRequestPendingSingle()
    {
        if (!is_null($this->requestPendingSingle)) {
            return $this->requestPendingSingle;
        }

        return $this->requestPendingSingle = Mage::getModel('M2ePro/Request_Pending_Single')->load(
            $this->getRequestPendingSingleId()
        );
    }

    //########################################

    public function getProcessingId()
    {
        return (int)$this->getData('processing_id');
    }

    public function getRequestPendingSingleId()
    {
        return (int)$this->getData('request_pending_single_id');
    }

    //########################################
}