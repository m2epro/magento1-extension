<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Observer_Abstract
{
    /**
     * @var null|Varien_Event_Observer
     */
    protected $_eventObserver = null;

    //########################################

    public function canProcess()
    {
        return true;
    }

    abstract public function process();

    //########################################

    public function beforeProcess()
    {
        return null;
    }

    public function afterProcess()
    {
        return null;
    }

    //########################################

    /**
     * @param Varien_Event_Observer $eventObserver
     */
    public function setEventObserver(Varien_Event_Observer $eventObserver)
    {
        $this->_eventObserver = $eventObserver;
    }

    /**
     * @return Varien_Event_Observer
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getEventObserver()
    {
        if (!($this->_eventObserver instanceof Varien_Event_Observer)) {
            throw new Ess_M2ePro_Model_Exception_Logic('Property "eventObserver" should be set first.');
        }

        return $this->_eventObserver;
    }

    //########################################

    /**
     * @return Varien_Event
     */
    protected function getEvent()
    {
        return $this->getEventObserver()->getEvent();
    }

    //########################################
}
