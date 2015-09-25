<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

final class Ess_M2ePro_Model_Cron_Task_Processing extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'processing';

    //####################################

    protected function getNick()
    {
        return self::NICK;
    }

    protected function getMaxMemoryLimit()
    {
        return Ess_M2ePro_Model_Processing_Dispatcher::MAX_MEMORY_LIMIT;
    }

    //####################################

    protected function performActions()
    {
        $dispatcher = Mage::getModel('M2ePro/Processing_Dispatcher');

        $dispatcher->setInitiator($this->getInitiator());
        $dispatcher->setParentLockItem($this->getLockItem());
        $dispatcher->setParentOperationHistory($this->getOperationHistory());

        return $dispatcher->process();
    }

    //####################################
}