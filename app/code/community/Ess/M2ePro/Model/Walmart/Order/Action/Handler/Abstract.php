<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Walmart_Order_Action_Handler_Abstract
{
    /** @var Ess_M2ePro_Model_Order $_order */
    protected $_order = null;

    //########################################

    public function setOrder(Ess_M2ePro_Model_Order $order)
    {
        $this->_order = $order;
        return $this;
    }

    //########################################

    public function process()
    {
        if (!$this->isNeedProcess()) {
            return;
        }

        $dispatcher = Mage::getModel('M2ePro/Walmart_Connector_Dispatcher');

        $serverCommand = $this->getServerCommand();

        $connector = $dispatcher->getVirtualConnector(
            $serverCommand[0], $serverCommand[1], $serverCommand[2],
            $this->getRequestData(), null, $this->_order->getAccount()
        );

        try {
            $dispatcher->process($connector);
        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);

            $message = Mage::getModel('M2ePro/Connector_Connection_Response_Message');
            $message->initFromException($exception);

            $this->processError(array($message));

            return;
        }

        $responseData = $connector->getResponseData();

        if (empty($responseData)) {
            $this->processError($connector->getResponse()->getMessages()->getEntities());
            return;
        }

        $this->processResult($responseData);
    }

    //########################################

    abstract public function isNeedProcess();

    //########################################

    abstract protected function getServerCommand();

    abstract protected function getRequestData();

    abstract protected function processResult(array $responseData);

    /**
     * @param Ess_M2ePro_Model_Connector_Connection_Response_Message[] $messages
     */
    abstract protected function processError(array $messages = array());

    //########################################

    /**
     * @return Ess_M2ePro_Model_Order
     */
    protected function getOrder()
    {
        return $this->_order;
    }

    /**
     * @return Ess_M2ePro_Model_Walmart_Order
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getWalmartOrder()
    {
        return $this->getOrder()->getChildObject();
    }

    //########################################
}
