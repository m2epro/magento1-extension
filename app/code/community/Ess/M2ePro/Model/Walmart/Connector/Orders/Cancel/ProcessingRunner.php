<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Connector_Orders_Cancel_ProcessingRunner
    extends Ess_M2ePro_Model_Connector_Command_Pending_Processing_Single_Runner
{
    // ########################################

    protected function eventBefore()
    {
        $params = $this->getParams();

        /** @var Ess_M2ePro_Model_Walmart_Order_Action_Processing $processingAction */
        $processingAction = Mage::getModel('M2ePro/Walmart_Order_Action_Processing');
        $processingAction->setData(array(
            'processing_id' => $this->getProcessingObject()->getId(),
            'order_id'      => $params['order_id'],
            'type'          => Ess_M2ePro_Model_Walmart_Order_Action_Processing::ACTION_TYPE_CANCEL,
            'request_data'  => Mage::helper('M2ePro')->jsonEncode($params['request_data']),
        ));
        $processingAction->save();
    }

    protected function setLocks()
    {
        parent::setLocks();

        $params = $this->getParams();

        /** @var Ess_M2ePro_Model_Order $order */
        $order = Mage::helper('M2ePro/Component_Walmart')->getObject('Order', $params['order_id']);
        $order->addProcessingLock('cancel_order', $this->getProcessingObject()->getId());
    }

    protected function unsetLocks()
    {
        parent::unsetLocks();

        $params = $this->getParams();

        /** @var Ess_M2ePro_Model_Order $order */
        $order = Mage::helper('M2ePro/Component_Walmart')->getObject('Order', $params['order_id']);
        $order->deleteProcessingLocks('cancel_order', $this->getProcessingObject()->getId());
    }

    // ########################################
}