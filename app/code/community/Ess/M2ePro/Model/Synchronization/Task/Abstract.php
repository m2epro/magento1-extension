<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Synchronization_Task_Abstract
    extends Ess_M2ePro_Model_Synchronization_Task
{
    //########################################

    protected function getComponent()
    {
        return NULL;
    }

    protected function processTask($taskPath)
    {
        return parent::processTask('Task_'.$taskPath);
    }

    //########################################
}