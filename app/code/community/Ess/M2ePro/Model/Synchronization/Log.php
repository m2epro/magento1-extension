<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Synchronization_Log extends Ess_M2ePro_Model_Log_Abstract
{
    const TASK_OTHER  = 0;
    const _TASK_OTHER = 'Other Synchronization';

    const TASK_LISTINGS  = 2;
    const _TASK_LISTINGS = 'M2E Pro Listings Synchronization';

    const TASK_ORDERS  = 3;
    const _TASK_ORDERS = 'Orders Synchronization';

    const TASK_MARKETPLACES  = 4;
    const _TASK_MARKETPLACES = 'Marketplaces Synchronization';

    const TASK_OTHER_LISTINGS  = 5;
    const _TASK_OTHER_LISTINGS = '3rd Party Listings Synchronization';

    const TASK_REPRICING  = 6;
    const _TASK_REPRICING = 'Repricing Synchronization';

    /**
     * @var null|int
     */
    protected $_operationHistoryId = null;

    /**
     * @var int
     */
    protected $_task = self::TASK_OTHER;

    /**
     * @var int
     */
    protected $_initiator = Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Synchronization_Log');
    }

    //########################################

    /**
     * @param int $id
     */
    public function setOperationHistoryId($id)
    {
        $this->_operationHistoryId = (int)$id;
    }

    /**
     * @param int $initiator
     */
    public function setInitiator($initiator = Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN)
    {
        $this->_initiator = (int)$initiator;
    }

    /**
     * @param int $task
     */
    public function setSynchronizationTask($task = self::TASK_OTHER)
    {
        $this->_task = (int)$task;
    }

    //########################################

    public function addMessage($description = null, $type = null, $priority = null, array $additionalData = array())
    {
        $dataForAdd = $this->makeDataForAdd(
            $description,
            $type,
            $priority,
            $additionalData
        );

        $this->createMessage($dataForAdd);
    }

    //########################################

    public function clearMessages($task = null)
    {
        $filters = array();

        if ($task !== null) {
            $filters['task'] = $task;
        }

        if ($this->_componentMode !== null) {
            $filters['component_mode'] = $this->_componentMode;
        }

        $this->getResource()->clearMessages($filters);
    }

    //########################################

    protected function createMessage($dataForAdd)
    {
        $dataForAdd['operation_history_id'] = $this->_operationHistoryId;
        $dataForAdd['task'] = $this->_task;
        $dataForAdd['initiator'] = $this->_initiator;
        $dataForAdd['component_mode'] = $this->_componentMode;

        Mage::getModel('M2ePro/Synchronization_Log')
                 ->setData($dataForAdd)
                 ->save()
                 ->getId();
    }

    protected function makeDataForAdd(
        $description = null,
        $type = null,
        $priority = null,
        array $additionalData = array()
    ) {
        $dataForAdd = array();

        if ($description !== null) {
            $dataForAdd['description'] = Mage::helper('M2ePro')->__($description);
        } else {
            $dataForAdd['description'] = null;
        }

        if ($type !== null) {
            $dataForAdd['type'] = (int)$type;
        } else {
            $dataForAdd['type'] = self::TYPE_NOTICE;
        }

        if ($priority !== null) {
            $dataForAdd['priority'] = (int)$priority;
        } else {
            $dataForAdd['priority'] = self::PRIORITY_LOW;
        }

        $dataForAdd['additional_data'] = Mage::helper('M2ePro')->jsonEncode($additionalData);

        return $dataForAdd;
    }

    //########################################
}
