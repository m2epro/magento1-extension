<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Buy_Synchronization_Marketplaces_Abstract
    extends Ess_M2ePro_Model_Buy_Synchronization_Abstract
{
    //########################################

    protected function getType()
    {
        return Ess_M2ePro_Model_Synchronization_Task_Abstract::MARKETPLACES;
    }

    protected function processTask($taskPath)
    {
        return parent::processTask('Marketplaces_'.$taskPath);
    }

    //########################################
}