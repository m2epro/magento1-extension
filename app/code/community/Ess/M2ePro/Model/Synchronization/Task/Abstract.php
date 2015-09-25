<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Synchronization_Task_Abstract
    extends Ess_M2ePro_Model_Synchronization_Task
{
    //####################################

    protected function getComponent()
    {
        return NULL;
    }

    protected function processTask($taskPath)
    {
        return parent::processTask('Task_'.$taskPath);
    }

    //####################################
}