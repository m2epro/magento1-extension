<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

final class Ess_M2ePro_Model_Cron_Task_Servicing extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'servicing';

    //####################################

    protected function getNick()
    {
        return self::NICK;
    }

    protected function getMaxMemoryLimit()
    {
        return Ess_M2ePro_Model_Servicing_Dispatcher::MAX_MEMORY_LIMIT;
    }

    //####################################

    protected function performActions()
    {
        return Mage::getModel('M2ePro/Servicing_Dispatcher')->process();
    }

    //####################################
}