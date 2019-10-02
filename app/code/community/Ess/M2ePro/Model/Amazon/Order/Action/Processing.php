<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Order_Action_Processing extends Ess_M2ePro_Model_Abstract
{
    const ACTION_TYPE_UPDATE = 'update';
    const ACTION_TYPE_CANCEL = 'cancel';
    const ACTION_TYPE_REFUND = 'refund';

    //####################################

    /** @var Ess_M2ePro_Model_Order $order */
    protected $order = NULL;

    /** @var Ess_M2ePro_Model_Processing $processing */
    protected $processing = NULL;

    /** @var Ess_M2ePro_Model_Request_Pending_Single $requestPendingSingle */
    protected $requestPendingSingle = NULL;

    //####################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Amazon_Order_Action_Processing');
    }

    //####################################

    public function setOrder(Ess_M2ePro_Model_Order $order)
    {
        $this->order = $order;
        return $this;
    }

    public function getOrder()
    {
        if (!$this->getId()) {
            throw new Ess_M2ePro_Model_Exception_Logic('Instance must be loaded first.');
        }

        if ($this->order !== null) {
            return $this->order;
        }

        return $this->order = Mage::helper('M2ePro')->getObject('Order', $this->getOrderId());
    }

    // ---------------------------------------

    public function setProcessing(Ess_M2ePro_Model_Processing $processing)
    {
        $this->processing = $processing;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Processing
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getProcessing()
    {
        if (!$this->getId()) {
            throw new Ess_M2ePro_Model_Exception_Logic('Instance must be loaded first.');
        }

        if ($this->processing !== null) {
            return $this->processing;
        }

        return $this->processing = Mage::helper('M2ePro')->getObject('Processing', $this->getProcessingId());
    }

    //------------------------------------

    public function setRequestPendingSingle(Ess_M2ePro_Model_Request_Pending_Single $requestPendingSingle)
    {
        $this->requestPendingSingle = $requestPendingSingle;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Request_Pending_Single
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getRequestPendingSingle()
    {
        if (!$this->getId()) {
            throw new Ess_M2ePro_Model_Exception_Logic('Instance must be loaded first.');
        }

        if (!$this->getRequestPendingSingleId()) {
            return null;
        }

        if ($this->requestPendingSingle !== null) {
            return $this->requestPendingSingle;
        }

        return $this->requestPendingSingle = Mage::helper('M2ePro')->getObject(
            'Request_Pending_Single', $this->getRequestPendingSingleId()
        );
    }

    //####################################

    public function getOrderId()
    {
        return (int)$this->getData('order_id');
    }

    public function getProcessingId()
    {
        return (int)$this->getData('processing_id');
    }

    public function getRequestPendingSingleId()
    {
        return (int)$this->getData('request_pending_single_id');
    }

    public function getActionType()
    {
        return (int)$this->getData('action_type');
    }

    public function getRequestData()
    {
        return $this->getSettings('request_data');
    }

    //####################################
}
