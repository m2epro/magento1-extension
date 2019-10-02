<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Connector_Command_Pending_Processing_Single extends Ess_M2ePro_Model_Abstract
{
    /** @var Ess_M2ePro_Model_Processing $_processing */
    protected $_processing = null;

    /** @var Ess_M2ePro_Model_Request_Pending_Single $_requestPendingSingle */
    protected $_requestPendingSingle = null;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Connector_Command_Pending_Processing_Single');
    }

    //########################################

    public function getProcessing()
    {
        if ($this->_processing !== null) {
            return $this->_processing;
        }

        return $this->_processing = Mage::getModel('M2ePro/Processing')->load($this->getProcessingId());
    }

    public function getRequestPendingSingle()
    {
        if ($this->_requestPendingSingle !== null) {
            return $this->_requestPendingSingle;
        }

        return $this->_requestPendingSingle = Mage::getModel('M2ePro/Request_Pending_Single')->load(
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