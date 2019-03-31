<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Strategy_Observer_KeepAlive
{
    const ACTIVATE_INTERVAL = 30;

    private $isEnabled = false;

    /** @var Ess_M2ePro_Model_Lock_Item_Manager */
    private $lockItemManager = NULL;

    private $circleStartTime = NULL;

    //########################################

    public function enable()
    {
        $this->isEnabled       = true;
        $this->circleStartTime = NULL;

        return $this;
    }

    public function disable()
    {
        $this->isEnabled       = false;
        $this->circleStartTime = NULL;

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

        if ($eventObserver->getEvent()->getData('object') &&
            ($eventObserver->getEvent()->getData('object') instanceof Ess_M2ePro_Model_Lock_Item)
        ) {
            return;
        }

        if ($eventObserver->getEvent()->getData('collection') &&
            ($eventObserver->getEvent()->getData('collection') instanceof Ess_M2ePro_Model_Mysql4_Lock_Item_Collection)
        ) {
            return;
        }

        if (is_null($this->circleStartTime)) {
            $this->circleStartTime = time();
            return;
        }

        if ($this->circleStartTime + self::ACTIVATE_INTERVAL > time()) {
            return;
        }

        $this->lockItemManager->activate();

        $this->circleStartTime = time();
    }

    //########################################
}