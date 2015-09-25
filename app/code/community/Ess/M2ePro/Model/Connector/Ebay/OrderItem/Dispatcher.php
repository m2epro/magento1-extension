<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Ebay_OrderItem_Dispatcher extends Mage_Core_Model_Abstract
{
    // M2ePro_TRANSLATIONS
    // Action was not completed (Item: %item_id%, Transaction: %trn_id%). Reason: %msg%

    const ACTION_ADD_DISPUTE   = 1;
    const ACTION_UPDATE_STATUS = 2;

    // ########################################

    public function process($action, $items, array $params = array())
    {
        $items = $this->prepareItems($items);
        $connector = null;

        switch ($action) {
            case self::ACTION_ADD_DISPUTE:
                $connector = 'Ess_M2ePro_Model_Connector_Ebay_OrderItem_Add_Dispute';
                break;
            case self::ACTION_UPDATE_STATUS:
                $connector = 'Ess_M2ePro_Model_Connector_Ebay_OrderItem_Update_Status';
                break;
        }

        if (is_null($connector)) {
            return false;
        }

        return $this->processItems($items, $connector, $params);
    }

    // ########################################

    protected function processItems(array $items, $connectorName, array $params = array())
    {
        if (count($items) == 0) {
            return false;
        }

        /** @var $items Ess_M2ePro_Model_Order_Item[] */

        foreach ($items as $item) {

            try {
                $connector = new $connectorName($params, $item);
                if (!$connector->process()) {
                    return false;
                }
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

    // ########################################

    private function prepareItems($items)
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

    // ########################################
}