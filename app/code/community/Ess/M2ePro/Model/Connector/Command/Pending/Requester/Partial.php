<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Connector_Command_Pending_Requester_Partial extends Ess_M2ePro_Model_Abstract
{
    /** @var Ess_M2ePro_Model_Processing $_processing */
    protected $_processing = null;

    /** @var Ess_M2ePro_Model_Request_Pending_Partial $_requestPendingPartial */
    protected $_requestPendingPartial = null;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Connector_Command_Pending_Requester_Partial');
    }

    //########################################

    public function getProcessing()
    {
        if ($this->_processing !== null) {
            return $this->_processing;
        }

        return $this->_processing = Mage::getModel('M2ePro/Processing')->load($this->getProcessingId());
    }

    public function getRequestPendingPartial()
    {
        if ($this->_requestPendingPartial !== null) {
            return $this->_requestPendingPartial;
        }

        return $this->_requestPendingPartial = Mage::getModel('M2ePro/Request_Pending_Partial')->load(
            $this->getRequestPendingPartialId()
        );
    }

    //########################################

    public function getProcessingId()
    {
        return (int)$this->getData('processing_id');
    }

    public function getRequestPendingPartialId()
    {
        return (int)$this->getData('request_pending_partial_id');
    }

    //########################################
}