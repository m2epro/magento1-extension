<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Wizard_RemovedPlay extends Ess_M2ePro_Model_Wizard
{
    //########################################

    /**
     * @return bool
     */
    public function isActive()
    {
        return true;
    }

    /**
     * @return string
     */
    public function getNick()
    {
        return 'removedPlay';
    }

    //########################################
}