<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Resource_Synchronization_Log getResource()
 */
class Ess_M2ePro_Model_Synchronization_Log extends Ess_M2ePro_Model_Log_Abstract
{
    const TYPE_FATAL_ERROR = 100;

    const TASK_OTHER  = 0;
    const _TASK_OTHER = 'Other';

    const TASK_LISTINGS  = 2;
    const _TASK_LISTINGS = 'M2E Pro Listings';

    const TASK_OTHER_LISTINGS  = 5;
    const _TASK_OTHER_LISTINGS = '3rd Party Listings';

    const TASK_ORDERS  = 3;
    const _TASK_ORDERS = 'Orders';

    const TASK_MARKETPLACES  = 4;
    const _TASK_MARKETPLACES = 'Marketplaces';

    const TASK_REPRICING  = 6;
    const _TASK_REPRICING = 'Repricing';

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

    public function addMessageFromException(Exception $exception)
    {
        return $this->addMessage(
            $exception->getMessage(),
            Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
            array(),
            Mage::helper('M2ePro/Module_Exception')->getExceptionDetailedInfo($exception)
        );
    }

    public function addMessage(
        $description = null,
        $type = null,
        array $additionalData = array(),
        $detailedDescription = null
    ) {
        $dataForAdd = array(
            'description'          => $description,
            'detailed_description' => $detailedDescription,
            'type'                 => (int)$type,
            'additional_data'      => Mage::helper('M2ePro')->jsonEncode($additionalData),

            'operation_history_id' => $this->_operationHistoryId,
            'task'                 => $this->_task,
            'initiator'            => $this->_initiator,
            'component_mode'       => $this->_componentMode,
        );

        Mage::getModel('M2ePro/Synchronization_Log')
            ->setData($dataForAdd)
            ->save()
            ->getId();
    }

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

    public function setFatalErrorHandler()
    {
        $temp = Mage::helper('M2ePro/Data_Global')->getValue(__CLASS__.'-'.__METHOD__);
        if (!empty($temp)) {
            return;
        }

        Mage::helper('M2ePro/Data_Global')->setValue(__CLASS__.'-'.__METHOD__, true);

        $object = $this;
        // @codingStandardsIgnoreLine
        register_shutdown_function(
            function() use ($object) {
                $error = error_get_last();
                if ($error === null) {
                    return;
                }

                if (!in_array((int)$error['type'], array(E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR))) {
                    return;
                }

                // @codingStandardsIgnoreLine
                $trace = @debug_backtrace(false);
                $traceInfo = Mage::helper('M2ePro/Module_Exception')->getFatalStackTraceInfo($trace);

                $object->addMessage(
                    $error['message'],
                    $object::TYPE_FATAL_ERROR,
                    array(),
                    Mage::helper('M2ePro/Module_Exception')->getFatalErrorDetailedInfo($error, $traceInfo)
                );
            }
        );
    }

    //########################################
}
