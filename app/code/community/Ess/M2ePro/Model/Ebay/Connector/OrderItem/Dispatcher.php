<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Connector_OrderItem_Dispatcher extends Mage_Core_Model_Abstract
{
    const ACTION_UPDATE_STATUS = 2;
    const ACTION_UPDATE_TRACK  = 3;

    //########################################

    public function process($action, $items, array $params = array())
    {
        $items = $this->prepareItems($items);
        $connector = null;

        switch ($action) {
            case self::ACTION_UPDATE_STATUS:
            case self::ACTION_UPDATE_TRACK:
                $connector = 'Ebay_Connector_OrderItem_Update_Status';
                break;
        }

        if ($connector === null) {
            return false;
        }

        return $this->processItems($items, $connector, $params);
    }

    //########################################

    protected function processItems(array $items, $connectorName, array $params = array())
    {
        if (empty($items)) {
            return false;
        }

        /** @var $items Ess_M2ePro_Model_Order_Item[] */

        foreach ($items as $item) {
            try {
                $dispatcher = Mage::getModel('M2ePro/Ebay_Connector_Dispatcher');

                $connector = $dispatcher->getCustomConnector($connectorName, $params);
                $connector->setOrderItem($item);

                $connector->process();
            } catch (Exception $e) {
                $item->getOrder()->addErrorLog(
                    'Action was not completed (Item: %item_id%, Transaction: %trn_id%). Reason: %msg%', array(
                        '!item_id' => $item->getChildObject()->getItemId(),
                        '!trn_id'  => $item->getChildObject()->getTransactionId(),
                        'msg'      => $e->getMessage()
                    )
                );

                return false;
            }
        }

        return true;
    }

    //########################################

    protected function prepareItems($items)
    {
        !is_array($items) && $items = array($items);

        $preparedItems = array();

        foreach ($items as $item) {
            if ($item instanceof Ess_M2ePro_Model_Order_Item) {
                $preparedItems[] = $item;
            } else if (is_numeric($item)) {
                $preparedItems[] = Mage::helper('M2ePro/Component_Ebay')->getObject('Order_Item', $item);
            }
        }

        return $preparedItems;
    }

    //########################################
}
