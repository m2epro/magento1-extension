<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Synchronization_Log extends Ess_M2ePro_Model_Log_Abstract
{

    // TODO migration
//- Other Synchronization
//    const TASK_UNKNOWN = 0;
//    const _TASK_UNKNOWN = 'System';
//    const TASK_GENERAL = 1;
//    const _TASK_GENERAL = 'General Synchronization';

    const TASK_OTHER = 0;
    const _TASK_OTHER = 'Other Synchronization';

//- M2E Pro Listings Synchronization
//    const TASK_LISTINGS_PRODUCTS = 2;
//    const _TASK_LISTINGS_PRODUCTS = 'Listings Products Synchronization';
//    const TASK_TEMPLATES = 3;
//    const _TASK_TEMPLATES = 'Inventory Synchronization';

    const TASK_LISTINGS = 2;
    const _TASK_LISTINGS = 'M2E Pro Listings Synchronization';

//- Orders Synchronization
//    const TASK_ORDERS = 4;
    const TASK_ORDERS = 3;
    const _TASK_ORDERS = 'Orders Synchronization';

//- Marketplaces Synchronization
//    const TASK_MARKETPLACES = 5;
    const TASK_MARKETPLACES = 4;
    const _TASK_MARKETPLACES = 'Marketplaces Synchronization';

//- 3rd party Listings Synchronization
//    const TASK_OTHER_LISTINGS = 6;
    const TASK_OTHER_LISTINGS = 5;
    const _TASK_OTHER_LISTINGS = '3rd Party Listings Synchronization';

//delete
//    const TASK_POLICIES = 7;
//    const _TASK_OTHER_POLICIES = 'Business Policies Synchronization';

//- Amazon Repricing Synchronization
//    const TASK_REPRICING = 8;
    const TASK_REPRICING = 6;
    const _TASK_REPRICING = 'Repricing Synchronization';

    /**
     * @var null|int
     */
    private $operationHistoryId = NULL;

    /**
     * @var int
     */
    private $task = self::TASK_OTHER;

    /**
     * @var int
     */
    protected $initiator = Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN;

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
        $this->operationHistoryId = (int)$id;
    }

    /**
     * @param int $initiator
     */
    public function setInitiator($initiator = Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN)
    {
        $this->initiator = (int)$initiator;
    }

    /**
     * @param int $task
     */
    public function setSynchronizationTask($task = self::TASK_OTHER)
    {
        $this->task = (int)$task;
    }

    //########################################

    public function addMessage($description = NULL, $type = NULL, $priority = NULL, array $additionalData = array())
    {
        $dataForAdd = $this->makeDataForAdd($description,
                                            $type,
                                            $priority,
                                            $additionalData);

        $this->createMessage($dataForAdd);
    }

    //########################################

    public function clearMessages($task = NULL)
    {
        $filters = array();

        if (!is_null($task)) {
            $filters['task'] = $task;
        }
        if (!is_null($this->componentMode)) {
            $filters['component_mode'] = $this->componentMode;
        }

        $this->getResource()->clearMessages($filters);
    }

    //########################################

    protected function createMessage($dataForAdd)
    {
        $dataForAdd['operation_history_id'] = $this->operationHistoryId;
        $dataForAdd['task'] = $this->task;
        $dataForAdd['initiator'] = $this->initiator;
        $dataForAdd['component_mode'] = $this->componentMode;

        Mage::getModel('M2ePro/Synchronization_Log')
                 ->setData($dataForAdd)
                 ->save()
                 ->getId();
    }

    protected function makeDataForAdd($description = NULL, $type = NULL, $priority = NULL,
                                      array $additionalData = array())
    {
        $dataForAdd = array();

        if (!is_null($description)) {
            $dataForAdd['description'] = Mage::helper('M2ePro')->__($description);
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