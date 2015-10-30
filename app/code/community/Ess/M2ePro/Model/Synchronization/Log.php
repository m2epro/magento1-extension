<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Synchronization_Log extends Ess_M2ePro_Model_Log_Abstract
{
    const TASK_UNKNOWN = 0;
    const _TASK_UNKNOWN = 'System';

    const TASK_DEFAULTS = 1;
    const _TASK_DEFAULTS = 'Default Synchronization';
    const TASK_TEMPLATES = 2;
    const _TASK_TEMPLATES = 'Inventory Synchronization';
    const TASK_ORDERS = 3;
    const _TASK_ORDERS = 'Orders Synchronization';
    const TASK_FEEDBACKS = 4;
    const _TASK_FEEDBACKS = 'Feedback Synchronization';
    const TASK_MARKETPLACES = 5;
    const _TASK_MARKETPLACES = 'Marketplaces Synchronization';
    const TASK_OTHER_LISTINGS = 6;
    const _TASK_OTHER_LISTINGS = '3rd Party Listings Synchronization';
    const TASK_POLICIES = 7;
    const _TASK_OTHER_POLICIES = 'Business Policies Synchronization';

    /**
     * @var null|int
     */
    private $operationHistoryId = NULL;

    /**
     * @var int
     */
    private $task = self::TASK_UNKNOWN;

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
    public function setSynchronizationTask($task = self::TASK_UNKNOWN)
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

    public function getActionTitle($type)
    {
        return $this->getActionTitleByClass(__CLASS__,$type);
    }

    public function getActionsTitles()
    {
        return $this->getActionsTitlesByClass(__CLASS__,'TASK_');
    }

    // ---------------------------------------

    public function clearMessages($task = NULL)
    {
        $columnName = !is_null($task) ? 'task' : NULL;
        $this->clearMessagesByTable('M2ePro/Synchronization_Log',$columnName,$task);
    }

    public function getLastActionIdConfigKey()
    {
        return 'synchronization';
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

        $dataForAdd['additional_data'] = json_encode($additionalData);

        return $dataForAdd;
    }

    //########################################
}