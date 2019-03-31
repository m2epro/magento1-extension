<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Connector_Command_Pending_Processing_Partial extends Ess_M2ePro_Model_Abstract
{
    /** @var Ess_M2ePro_Model_Processing $processing */
    private $processing = null;

    /** @var Ess_M2ePro_Model_Request_Pending_Partial $requestPendingPartial */
    private $requestPendingPartial = null;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Connector_Command_Pending_Processing_Partial');
    }

    //########################################

    public function getProcessing()
    {
        if (!is_null($this->processing)) {
            return $this->processing;
        }

        return $this->processing = Mage::getModel('M2ePro/Processing')->load($this->getProcessingId());
    }

    public function getRequestPendingPartial()
    {
        if (!is_null($this->requestPendingPartial)) {
            return $this->requestPendingPartial;
        }

        return $this->requestPendingPartial = Mage::getModel('M2ePro/Request_Pending_Partial')->load(
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