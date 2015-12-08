<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

final class Ess_M2ePro_Model_Synchronization_Dispatcher
{
    const MAX_MEMORY_LIMIT = 512;

    private $allowedComponents = array();
    private $allowedTasksTypes = array();

    private $lockItem = NULL;
    private $operationHistory = NULL;

    private $parentLockItem = NULL;
    private $parentOperationHistory = NULL;

    private $log = NULL;
    private $params = array();
    private $initiator = Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN;

    //########################################

    public function process()
    {
        $this->initialize();
        $this->updateLastAccess();

        if (!$this->isPossibleToRun()) {
            return true;
        }

        $this->updateLastRun();
        $this->beforeStart();

        $result = true;

        try {

            // global tasks
            $result = !$this->processTask('Synchronization_Task_Defaults') ? false : $result;

            // components tasks
            $result = !$this->processComponent(Ess_M2ePro_Helper_Component_Ebay::NICK) ? false : $result;
            $result = !$this->processComponent(Ess_M2ePro_Helper_Component_Amazon::NICK) ? false : $result;
            $result = !$this->processComponent(Ess_M2ePro_Helper_Component_Buy::NICK) ? false : $result;

        } catch (Exception $exception) {

            $result = false;

            Mage::helper('M2ePro/Module_Exception')->process($exception);

            $this->getOperationHistory()->setContentData('exception', array(
                'message' => $exception->getMessage(),
                'file'    => $exception->getFile(),
                'line'    => $exception->getLine(),
                'trace'   => $exception->getTraceAsString(),
            ));

            $this->getLog()->addMessage(
                Mage::helper('M2ePro')->__($exception->getMessage()),
                Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH
            );
        }

        $this->afterEnd();

        return $result;
    }

    // ---------------------------------------

    protected function processComponent($component)
    {
        if (!in_array($component,$this->getAllowedComponents())) {
            return false;
        }

        return $this->processTask(ucfirst($component).'_Synchronization_Launcher');
    }

    protected function processTask($taskPath)
    {
        $result = $this->makeTask($taskPath)->process();
        return is_null($result) || $result;
    }

    protected function makeTask($taskPath)
    {
        /** @var $task Ess_M2ePro_Model_Synchronization_Task **/
        $task = Mage::getModel('M2ePro/'.$taskPath);

        $task->setParentLockItem($this->getLockItem());
        $task->setParentOperationHistory($this->getOperationHistory());

        $task->setAllowedTasksTypes($this->getAllowedTasksTypes());

        $task->setLog($this->getLog());
        $task->setInitiator($this->getInitiator());
        $task->setParams($this->getParams());

        return $task;
    }

    //########################################

    /**
     * @param array $components
     */
    public function setAllowedComponents(array $components)
    {
        $this->allowedComponents = $components;
    }

    /**
     * @return array
     */
    public function getAllowedComponents()
    {
        return $this->allowedComponents;
    }

    // ---------------------------------------

    /**
     * @param array $types
     */
    public function setAllowedTasksTypes(array $types)
    {
        $this->allowedTasksTypes = $types;
    }

    /**
     * @return array
     */
    public function getAllowedTasksTypes()
    {
        return $this->allowedTasksTypes;
    }

    // ---------------------------------------

    /**
     * @param array $params
     */
    public function setParams(array $params)
    {
        $this->params = $params;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    // ---------------------------------------

    /**
     * @param int $value
     */
    public function setInitiator($value)
    {
        $this->initiator = (int)$value;
    }

    /**
     * @return int
     */
    public function getInitiator()
    {
        return $this->initiator;
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_LockItem $object
     */
    public function setParentLockItem(Ess_M2ePro_Model_LockItem $object)
    {
        $this->parentLockItem = $object;
    }

    /**
     * @return Ess_M2ePro_Model_LockItem
     */
    public function getParentLockItem()
    {
        return $this->parentLockItem;
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_OperationHistory $object
     */
    public function setParentOperationHistory(Ess_M2ePro_Model_OperationHistory $object)
    {
        $this->parentOperationHistory = $object;
    }

    /**
     * @return Ess_M2ePro_Model_OperationHistory
     */
    public function getParentOperationHistory()
    {
        return $this->parentOperationHistory;
    }

    //########################################

    protected function initialize()
    {
        Mage::helper('M2ePro/Client')->setMemoryLimit(self::MAX_MEMORY_LIMIT);
        Mage::helper('M2ePro/Module_Exception')->setFatalErrorHandler();
    }

    protected function updateLastAccess()
    {
        $currentDateTime = Mage::helper('M2ePro')->getCurrentGmtDate();
        $this->setConfigValue(NULL,'last_access',$currentDateTime);
    }

    protected function isPossibleToRun()
    {
        return (bool)(int)$this->getConfigValue(NULL,'mode') &&
               !$this->getLockItem()->isExist();
    }

    protected function updateLastRun()
    {
        $currentDateTime = Mage::helper('M2ePro')->getCurrentGmtDate();
        $this->setConfigValue(NULL,'last_run',$currentDateTime);
    }

    // ---------------------------------------

    protected function beforeStart()
    {
        $lockItemParentId = $this->getParentLockItem() ? $this->getParentLockItem()->getRealId() : NULL;
        $this->getLockItem()->create($lockItemParentId);
        $this->getLockItem()->makeShutdownFunction();

        $this->getOperationHistory()->cleanOldData();

        $operationHistoryParentId = $this->getParentOperationHistory() ?
                $this->getParentOperationHistory()->getObject()->getId() : NULL;
        $this->getOperationHistory()->start('synchronization',
                                            $operationHistoryParentId,
                                            $this->getInitiator());
        $this->getOperationHistory()->makeShutdownFunction();

        $this->getLog()->setOperationHistoryId($this->getOperationHistory()->getObject()->getId());

        if (in_array(Ess_M2ePro_Model_Synchronization_Task::ORDERS, $this->getAllowedTasksTypes())) {
            Mage::dispatchEvent('m2epro_synchronization_before_start', array());
        }

        if (in_array(Ess_M2ePro_Model_Synchronization_Task::TEMPLATES, $this->getAllowedTasksTypes())) {
            $this->clearOutdatedProductChanges();
        }
    }

    protected function afterEnd()
    {
        if (in_array(Ess_M2ePro_Model_Synchronization_Task::ORDERS, $this->getAllowedTasksTypes())) {
            Mage::dispatchEvent('m2epro_synchronization_after_end', array());
        }

        if (in_array(Ess_M2ePro_Model_Synchronization_Task::TEMPLATES, $this->getAllowedTasksTypes())) {
            $this->clearProcessedProductChanges();
        }

        $this->getOperationHistory()->stop();
        $this->getLockItem()->remove();
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Synchronization_LockItem
     */
    protected function getLockItem()
    {
        if (is_null($this->lockItem)) {
            $this->lockItem = Mage::getModel('M2ePro/Synchronization_LockItem');
        }
        return $this->lockItem;
    }

    /**
     * @return Ess_M2ePro_Model_Synchronization_OperationHistory
     */
    public function getOperationHistory()
    {
        if (is_null($this->operationHistory)) {
            $this->operationHistory = Mage::getModel('M2ePro/Synchronization_OperationHistory');
        }
        return $this->operationHistory;
    }

    /**
     * @return Ess_M2ePro_Model_Synchronization_Log
     */
    protected function getLog()
    {
        if (is_null($this->log)) {
            $this->log = Mage::getModel('M2ePro/Synchronization_Log');
            $this->log->setInitiator($this->getInitiator());
            $this->log->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::TASK_UNKNOWN);
        }
        return $this->log;
    }

    // ---------------------------------------

    protected function clearOutdatedProductChanges()
    {
        /** @var Mage_Core_Model_Resource $resource */
        $resource = Mage::getSingleton('core/resource');
        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = $resource->getConnection('core_write');

        $tempDate = new DateTime('now', new DateTimeZone('UTC'));
        $tempDate->modify('-'.$this->getConfigValue('/settings/product_change/', 'max_lifetime').' seconds');
        $tempDate = Mage::helper('M2ePro')->getDate($tempDate->format('U'));

        $connWrite->delete(
            $resource->getTableName('m2epro_product_change'),
            array(
                'update_date <= (?)' => $tempDate
            )
        );
    }

    protected function clearProcessedProductChanges()
    {
        /** @var Ess_M2ePro_Model_Mysql4_ProductChange_Collection $productChangeCollection */
        $productChangeCollection = Mage::getResourceModel('M2ePro/ProductChange_Collection');
        $productChangeCollection->setPageSize(
            (int)$this->getConfigValue('/settings/product_change/', 'max_count_per_one_time')
        );
        $productChangeCollection->setOrder('id', Varien_Data_Collection_Db::SORT_ORDER_ASC);

        /** @var Mage_Core_Model_Resource $resource */
        $resource = Mage::getSingleton('core/resource');
        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = $resource->getConnection('core_write');

        $connWrite->delete(
            $resource->getTableName('m2epro_product_change'),
            array(
                'id IN (?)' => $productChangeCollection->getColumnValues('id'),
                '(update_date <= \''.$this->getOperationHistory()->getObject()->getData('start_date').'\' OR
                  initiators NOT LIKE \'%'.Ess_M2ePro_Model_ProductChange::INITIATOR_OBSERVER.'%\')'
            )
        );
    }

    //########################################

    private function getConfig()
    {
        return Mage::helper('M2ePro/Module')->getSynchronizationConfig();
    }

    // ---------------------------------------

    private function getConfigValue($group, $key)
    {
        return $this->getConfig()->getGroupValue($group, $key);
    }

    private function setConfigValue($group, $key, $value)
    {
        return $this->getConfig()->setGroupValue($group, $key, $value);
    }

    //########################################
}