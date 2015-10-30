<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Ebay_Synchronization_Defaults_Abstract
    extends Ess_M2ePro_Model_Ebay_Synchronization_Abstract
{
    //########################################

    /**
     * @return string
     */
    protected function getType()
    {
        return Ess_M2ePro_Model_Synchronization_Task_Abstract::DEFAULTS;
    }

    protected function processTask($taskPath)
    {
        return parent::processTask('Defaults_'.$taskPath);
    }

    //########################################
}