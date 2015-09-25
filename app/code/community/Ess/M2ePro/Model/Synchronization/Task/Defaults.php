<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

final class Ess_M2ePro_Model_Synchronization_Task_Defaults
    extends Ess_M2ePro_Model_Synchronization_Task_Abstract
{
    //####################################

    protected function getType()
    {
        return Ess_M2ePro_Model_Synchronization_Task_Abstract::DEFAULTS;
    }

    protected function getNick()
    {
        return NULL;
    }

    // -----------------------------------

    protected function getPercentsStart()
    {
        return 0;
    }

    protected function getPercentsEnd()
    {
        return 100;
    }

    //####################################

    protected function performActions()
    {
        $result = true;

        $result = !$this->processTask('Defaults_DeletedProducts') ? false : $result;
        $result = !$this->processTask('Defaults_AddedProducts') ? false : $result;
        $result = !$this->processTask('Defaults_StopQueue') ? false : $result;
        $result = !$this->processTask('Defaults_Inspector') ? false : $result;

        return $result;
    }

    //####################################
}