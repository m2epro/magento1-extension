<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Observer_Abstract
{
    /**
     * @var null|Varien_Event_Observer
     */
    private $eventObserver = NULL;

    //########################################

    public function canProcess()
    {
        return true;
    }

    abstract public function process();

    //########################################

    public function beforeProcess() {}
    public function afterProcess() {}

    //########################################

    /**
     * @param Varien_Event_Observer $eventObserver
     */
    public function setEventObserver(Varien_Event_Observer $eventObserver)
    {
        $this->eventObserver = $eventObserver;
    }

    /**
     * @return Varien_Event_Observer
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getEventObserver()
    {
        if (!($this->eventObserver instanceof Varien_Event_Observer)) {
            throw new Ess_M2ePro_Model_Exception_Logic('Property "eventObserver" should be set first.');
        }

        return $this->eventObserver;
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