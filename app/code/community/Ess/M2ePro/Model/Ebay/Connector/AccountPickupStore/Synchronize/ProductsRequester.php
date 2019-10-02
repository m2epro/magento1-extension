<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Connector_AccountPickupStore_Synchronize_ProductsRequester
    extends Ess_M2ePro_Model_Ebay_Connector_Command_Pending_Requester
{
    const ACTION_UPDATE = 'update';
    const ACTION_DELETE = 'delete';

    /** @var Ess_M2ePro_Model_Ebay_Account_PickupStore_State[] $_pickupStoreStateItems */
    protected $_pickupStoreStateItems = array();

    protected $_requestData = array();

    /** @var Ess_M2ePro_Model_Ebay_Account_PickupStore_Log $_log */
    protected $_log;

    //########################################

    public function __construct(
        array $params = array(),
        Ess_M2ePro_Model_Marketplace $marketplace = null,
        Ess_M2ePro_Model_Account $account = null
    ) {
        $params['logs_action_id'] = $this->getLog()->getResource()->getNextActionId();
        parent::__construct($params, $marketplace, $account);
    }

    //########################################

    public function setPickupStoreStateItems(array $items)
    {
        $this->_pickupStoreStateItems = $items;
        return $this;
    }

    //########################################

    protected function getCommand()
    {
        return array('store', 'synchronize', 'products');
    }

    public function getRequestData()
    {
        if (!empty($this->_requestData)) {
            return $this->_requestData;
        }

        $requestData = array();

        foreach ($this->_pickupStoreStateItems as $stateItem) {
            if (!isset($requestData[$stateItem->getSku()])) {
                $requestData[$stateItem->getSku()] = array(
                    'sku'       => $stateItem->getSku(),
                    'locations' => array(),
                );
            }

            $locationData = array(
                'sku'         => $stateItem->getSku(),
                'location_id' => $stateItem->getAccountPickupStore()->getLocationId(),
                'action'      => $stateItem->isDeleted() ? self::ACTION_DELETE : self::ACTION_UPDATE,
            );

            if ($locationData['action'] == self::ACTION_UPDATE) {
                $locationData['qty'] = $stateItem->getTargetQty();
            }

            $requestData[$stateItem->getSku()]['locations'][] = $locationData;
        }

        return $this->_requestData = array('items' => $requestData);
    }

    //########################################

    protected function getProcessingRunnerModelName()
    {
        return 'Ebay_Connector_AccountPickupStore_Synchronize_ProcessingRunner';
    }

    protected function getProcessingParams()
    {
        return array_merge(
            parent::getProcessingParams(),
            array(
                'pickup_store_state_ids' => array_keys($this->_pickupStoreStateItems),
            )
        );
    }

    protected function getResponserParams()
    {
        $stateItemsData = array();

        foreach ($this->_pickupStoreStateItems as $id => $stateItem) {
            $stateItemsData[$id] = array(
                'online_qty' => $stateItem->getOnlineQty(),
                'target_qty' => $stateItem->getTargetQty(),
                'is_added'   => $stateItem->isAdded(),
                'is_deleted' => $stateItem->isDeleted(),
            );
        }

        return array_merge(
            parent::getResponserParams(),
            array(
                'pickup_store_state_items' => $stateItemsData,
                'logs_action_id'           => $this->_params['logs_action_id'],
            )
        );
    }

    //########################################

    protected function getLog()
    {
        if ($this->_log !== null) {
            return $this->_log;
        }

        return $this->_log = Mage::getModel('M2ePro/Ebay_Account_PickupStore_Log');
    }

    //########################################
}