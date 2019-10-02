<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Strategy_Observer_KeepAlive
{
    const ACTIVATE_INTERVAL = 30;

    protected $_isEnabled = false;

    /** @var Ess_M2ePro_Model_Lock_Item_Manager */
    protected $_lockItemManager = null;

    protected $_circleStartTime = null;

    //########################################

    public function enable()
    {
        $this->_isEnabled       = true;
        $this->_circleStartTime = null;

        return $this;
    }

    public function disable()
    {
        $this->_isEnabled       = false;
        $this->_circleStartTime = null;

        return $this;
    }

    //########################################

    public function setLockItemManager(Ess_M2ePro_Model_Lock_Item_Manager $lockItemManager)
    {
        $this->_lockItemManager = $lockItemManager;
        return $this;
    }

    //########################################

    public function process(Varien_Event_Observer $eventObserver)
    {
        if (!$this->_isEnabled) {
            return;
        }

        if ($this->_lockItemManager === null) {
            throw new Ess_M2ePro_Model_Exception_Logic('Lock Item Manager was not set.');
        }

        if ($eventObserver->getEvent()->getData('object') &&
            ($eventObserver->getEvent()->getData('object') instanceof Ess_M2ePro_Model_Lock_Item)
        ) {
            return;
        }

        $collection = $eventObserver->getEvent()->getData('collection');
        if ($collection && ($collection instanceof Ess_M2ePro_Model_Resource_Lock_Item_Collection)) {
            return;
        }

        if ($this->_circleStartTime === null) {
            $this->_circleStartTime = time();
            return;
        }

        if ($this->_circleStartTime + self::ACTIVATE_INTERVAL > time()) {
            return;
        }

        $this->_lockItemManager->activate();

        $this->_circleStartTime = time();
    }

    //########################################
}
