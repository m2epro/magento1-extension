<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Buy_Synchronization_Defaults_Abstract
    extends Ess_M2ePro_Model_Buy_Synchronization_Abstract
{
    //####################################

    protected function getType()
    {
        return Ess_M2ePro_Model_Synchronization_Task_Abstract::DEFAULTS;
    }

    protected function processTask($taskPath)
    {
        return parent::processTask('Defaults_'.$taskPath);
    }

    //####################################
}