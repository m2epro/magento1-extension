<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Connector_Order_Dispatcher extends Mage_Core_Model_Abstract
{
    const ACTION_PAY        = 1;
    const ACTION_SHIP       = 2;
    const ACTION_SHIP_TRACK = 3;

    // ########################################

    public function process($action, $orders, array $params = array())
    {
        $orders = $this->prepareOrders($orders);

        switch ($action) {
            case self::ACTION_PAY:
                $result = $this->processOrders(
                    $orders, $action, 'Ebay_Connector_Order_Update_Payment', $params
                );
                break;

            case self::ACTION_SHIP:
            case self::ACTION_SHIP_TRACK:
                $result = $this->processOrders(
                    $orders, $action, 'Ebay_Connector_Order_Update_Shipping', $params
                );
                break;

            default;
                $result = false;
                break;
        }

        return $result;
    }

    // ########################################

    protected function processOrders(array $orders, $action, $connectorName, array $params = array())
    {
        if (empty($orders)) {
            return false;
        }

        /** @var $orders Ess_M2ePro_Model_Order[] */

        foreach ($orders as $order) {
            try {
                $dispatcher = Mage::getModel('M2ePro/Ebay_Connector_Dispatcher');

                /** @var Ess_M2ePro_Model_Ebay_Connector_Order_Update_Abstract $connector */
                $connector = $dispatcher->getCustomConnector($connectorName, $params);
                $connector->setOrder($order);
                $connector->setAction($action);

                $dispatcher->process($connector);

                if ($connector->getStatus() == Ess_M2ePro_Helper_Data::STATUS_ERROR) {
                    return false;
                }
            } catch (Exception $e) {
                $order->addErrorLog(
                    'eBay Order Action was not completed. Reason: %msg%', array('msg' => $e->getMessage())
                );

                return false;
            }
        }

        return true;
    }

    // ########################################

    protected function prepareOrders($orders)
    {
        !is_array($orders) && $orders = array($orders);

        $preparedOrders = array();

        foreach ($orders as $order) {
            if ($order instanceof Ess_M2ePro_Model_Order) {
                $preparedOrders[] = $order;
            } elseif (is_numeric($order)) {
                $preparedOrders[] = Mage::helper('M2ePro/Component_Ebay')->getObject('Order', $order);
            }
        }

        return $preparedOrders;
    }

    // ########################################
}
