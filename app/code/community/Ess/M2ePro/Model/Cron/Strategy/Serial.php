<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2EPro_Model_Cron_Strategy_Serial extends Ess_M2ePro_Model_Cron_Strategy_Abstract
{
    /**
     * @var Ess_M2ePro_Model_LockItem
     */
    private $lockItem = NULL;

    //########################################

    protected function getNick()
    {
        return Ess_M2ePro_Helper_Module_Cron::STRATEGY_SERIAL;
    }

    //########################################

    public function process()
    {
        if ($this->getLockItem()->isExist()) {
            return true;
        }

        return parent::process();
    }

    // ---------------------------------------

    /**
     * @param $taskNick
     * @return Ess_M2ePro_Model_Cron_Task_Abstract
     */
    protected function getTaskObject($taskNick)
    {
        $task = parent::getTaskObject($taskNick);
        return $task->setParentLockItem($this->getLockItem());
    }

    protected function processTasks()
    {
        $result = true;

        foreach ($this->getAllowedTasks() as $taskNick) {

            try {

                $tempResult = $this->getTaskObject($taskNick)->process();

                if (!is_null($tempResult) && !$tempResult) {
                    $result = false;
                }

                $this->getLockItem()->activate();

            } catch (Exception $exception) {

                $result = false;

                $this->getOperationHistory()->setContentData('exception', array(
                    'message' => $exception->getMessage(),
                    'file'    => $exception->getFile(),
                    'line'    => $exception->getLine(),
                    'trace'   => $exception->getTraceAsString(),
                ));

                Mage::helper('M2ePro/Module_Exception')->process($exception);
            }
        }

        return $result;
    }

    //########################################

    protected function beforeStart()
    {
        $this->getLockItem()->create();
        $this->getLockItem()->makeShutdownFunction();

        parent::beforeStart();
    }

    protected function afterEnd()
    {
        parent::afterEnd();

        $this->getLockItem()->remove();
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_LockItem
     */
    protected function getLockItem()
    {
        if (!is_null($this->lockItem)) {
            return $this->lockItem;
        }

        $this->lockItem = Mage::getModel('M2ePro/LockItem');
        $this->lockItem->setNick('cron_strategy_serial');

        return $this->lockItem;
    }

    //########################################
}