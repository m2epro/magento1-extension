<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Ebay_Order_Dispatcher extends Mage_Core_Model_Abstract
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
                    $orders, $action, 'Ess_M2ePro_Model_Connector_Ebay_Order_Update_Payment', $params
                );
                break;

            case self::ACTION_SHIP:
            case self::ACTION_SHIP_TRACK:
                $result = $this->processOrders(
                    $orders, $action, 'Ess_M2ePro_Model_Connector_Ebay_Order_Update_Shipping', $params
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
        if (count($orders) == 0) {
            return false;
        }

        /** @var $orders Ess_M2ePro_Model_Order[] */

        foreach ($orders as $order) {

            try {
                $connector = new $connectorName($params, $order, $action);
                if (!$connector->process()) {
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