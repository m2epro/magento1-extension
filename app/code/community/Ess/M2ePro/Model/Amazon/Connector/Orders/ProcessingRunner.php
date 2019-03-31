<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Connector_Orders_ProcessingRunner
    extends Ess_M2ePro_Model_Connector_Command_Pending_Processing_Single_Runner
{
    /** @var Ess_M2ePro_Model_Amazon_Order_Action_Processing $processingAction */
    private $processingAction;

    // ########################################

    protected function eventBefore()
    {
        $params = $this->getParams();

        /** @var Ess_M2ePro_Model_Amazon_Order_Action_Processing $processingAction */
        $processingAction = Mage::getModel('M2ePro/Amazon_Order_Action_Processing');
        $processingAction->setData(array(
            'processing_id' => $this->getProcessingObject()->getId(),
            'order_id'      => $params['order_id'],
            'type'          => $params{'action_type'},
            'request_data'  => Mage::helper('M2ePro')->jsonEncode($params['request_data']),
        ));
        $processingAction->save();
    }

    protected function setLocks()
    {
        parent::setLocks();

        $params = $this->getParams();

        /** @var Ess_M2ePro_Model_Order $order */
        $order = Mage::helper('M2ePro/Component_Amazon')->getObject('Order', $params['order_id']);
        $order->addProcessingLock($params['lock_name'], $this->getProcessingObject()->getId());
    }

    protected function unsetLocks()
    {
        parent::unsetLocks();

        $params = $this->getParams();

        /** @var Ess_M2ePro_Model_Order $order */
        $order = Mage::helper('M2ePro/Component_Amazon')->getObject('Order', $params['order_id']);
        $order->deleteProcessingLocks($params['lock_name'], $this->getProcessingObject()->getId());
    }

    // ########################################

    public function complete()
    {
        if ($this->getProcessingAction() && $this->getProcessingAction()->getId()) {
            $this->getProcessingAction()->deleteInstance();
        }

        parent::complete();
    }

    // ########################################

    protected function getProcessingAction()
    {
        if (!is_null($this->processingAction)) {
            return $this->processingAction;
        }

        $processingActionCollection = Mage::getResourceModel(
            'M2ePro/Amazon_Order_Action_Processing_Collection'
        );
        $processingActionCollection->addFieldToFilter('processing_id', $this->getProcessingObject()->getId());

        $processingAction = $processingActionCollection->getFirstItem();

        return $processingAction->getId() ? $this->processingAction = $processingAction : NULL;
    }

    // ########################################
}