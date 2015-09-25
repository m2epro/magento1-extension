<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

final class Ess_M2ePro_Model_Cron_Type_Developer extends Ess_M2ePro_Model_Cron_Type_Abstract
{
    //####################################

    protected function getType()
    {
        return NULL;
    }

    //####################################

    protected function initialize()
    {
        $this->setInitiator(Ess_M2ePro_Helper_Data::INITIATOR_DEVELOPER);
    }

    protected function isPossibleToRun()
    {
        return !$this->getLockItem()->isExist();
    }

    //####################################
}