<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Account_PickupStore_Log extends Ess_M2ePro_Model_Log_Abstract
{
    const ACTION_UNKNOWN = 1;
    const _ACTION_UNKNOWN = 'System';

    const ACTION_ADD_PRODUCT  = 2;
    const _ACTION_ADD_PRODUCT = 'Assign Product to the Store';

    const ACTION_DELETE_PRODUCT  = 3;
    const _ACTION_DELETE_PRODUCT = 'Unassign Product to the Store';

    const ACTION_UPDATE_QTY  = 4;
    const _ACTION_UPDATE_QTY = 'Change of Product QTY in Magento Store';

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Account_PickupStore_Log');
    }

    //########################################

    public function addMessage($accountPickupStoreStateId,
                               $actionId = NULL,
                               $action = NULL,
                               $description = NULL,
                               $type = NULL,
                               $priority = NULL)
    {
        $dataForAdd = $this->makeDataForAdd(
            $accountPickupStoreStateId,
            $actionId,
            $action,
            $description,
            $type,
            $priority);

        $this->createMessage($dataForAdd);
    }

    //########################################

    protected function createMessage($dataForAdd)
    {
        /** @var Ess_M2ePro_Model_Ebay_Account_PickupStore_State $accountPickupStoreState */
        $accountPickupStoreState = Mage::getModel('M2ePro/Ebay_Account_PickupStore_State')->loadInstance(
            $dataForAdd['account_pickup_store_state_id']
        );

        $accountPickupStore = $accountPickupStoreState->getAccountPickupStore();

        $dataForAdd['location_id']    = $accountPickupStore->getLocationId();
        $dataForAdd['location_title'] = $accountPickupStore->getName();

        Mage::getModel('M2ePro/Ebay_Account_PickupStore_Log')
            ->setData($dataForAdd)
            ->save();
    }

    protected function makeDataForAdd($accountPickupStoreStateId,
                                      $actionId = NULL,
                                      $action = NULL,
                                      $description = NULL,
                                      $type = NULL,
                                      $priority = NULL,
                                      array $additionalData = array())
    {
        $dataForAdd = array();

        $dataForAdd['account_pickup_store_state_id'] = (int)$accountPickupStoreStateId;

        if (!is_null($actionId)) {
            $dataForAdd['action_id'] = (int)$actionId;
        } else {
            $dataForAdd['action_id'] = NULL;
        }

        if (!is_null($action)) {
            $dataForAdd['action'] = (int)$action;
        } else {
            $dataForAdd['action'] = self::ACTION_UNKNOWN;
        }

        if (!is_null($description)) {
            $dataForAdd['description'] = $description;
        } else {
            $dataForAdd['description'] = NULL;
        }

        if (!is_null($type)) {
            $dataForAdd['type'] = (int)$type;
        } else {
            $dataForAdd['type'] = self::TYPE_NOTICE;
        }

        if (!is_null($priority)) {
            $dataForAdd['priority'] = (int)$priority;
        } else {
            $dataForAdd['priority'] = self::PRIORITY_LOW;
        }

        $dataForAdd['additional_data'] = Mage::helper('M2ePro')->jsonEncode($additionalData);

        return $dataForAdd;
    }

    //########################################
}