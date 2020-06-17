<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_System_Servicing_Synchronize extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'system/servicing/synchronize';

    //########################################

    protected function performActions()
    {
        $dispatcher = Mage::getModel('M2ePro/Servicing_Dispatcher');
        $dispatcher->setInitiator($this->getInitiator());
        $dispatcher->process();
    }

    //########################################
}
