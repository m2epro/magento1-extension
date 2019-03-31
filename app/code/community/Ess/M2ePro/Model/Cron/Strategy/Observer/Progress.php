<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Strategy_Observer_Progress
{
    private $isEnabled = false;

    /** @var Ess_M2ePro_Model_Lock_Item_Manager */
    private $lockItemManager = NULL;

    //########################################

    public function enable()
    {
        $this->isEnabled = true;
        return $this;
    }

    public function disable()
    {
        $this->isEnabled = false;
        return $this;
    }

    //########################################

    public function setLockItemManager(Ess_M2ePro_Model_Lock_Item_Manager $lockItemManager)
    {
        $this->lockItemManager = $lockItemManager;
        return $this;
    }

    //########################################

    public function process(Varien_Event_Observer $eventObserver)
    {
        if (!$this->isEnabled) {
            return;
        }

        if (is_null($this->lockItemManager)) {
            throw new Ess_M2ePro_Model_Exception_Logic('Lock Item Manager was not set.');
        }

        $eventName = $eventObserver->getEvent()->getName();
        $progressNick = $eventObserver->getEvent()->getProgressNick();

        $progress = Mage::getModel(
            'M2ePro/Lock_Item_Progress',
            array('lock_item_manager' => $this->lockItemManager, 'progress_nick' => $progressNick)
        );

        if ($eventName == Ess_M2ePro_Model_Cron_Strategy_Abstract::PROGRESS_START_EVENT_NAME) {
            $progress->start();
            return;
        }

        if ($eventName == Ess_M2ePro_Model_Cron_Strategy_Abstract::PROGRESS_SET_PERCENTAGE_EVENT_NAME) {
            $percentage = $eventObserver->getEvent()->getData('percentage');
            $progress->setPercentage($percentage);
            return;
        }

        if ($eventName == Ess_M2ePro_Model_Cron_Strategy_Abstract::PROGRESS_SET_DETAILS_EVENT_NAME) {
            $args = array(
                'percentage' => $eventObserver->getEvent()->getData('percentage'),
                'total'      => $eventObserver->getEvent()->getData('total')
            );
            $progress->setDetails($args);
            return;
        }

        if ($eventName == Ess_M2ePro_Model_Cron_Strategy_Abstract::PROGRESS_STOP_EVENT_NAME) {
            $progress->stop();
            return;
        }
    }

    //########################################
}