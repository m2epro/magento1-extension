<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

final class Ess_M2ePro_Model_Buy_Synchronization_Launcher
    extends Ess_M2ePro_Model_Buy_Synchronization_Abstract
{
    //########################################

    protected function getType()
    {
        return NULL;
    }

    protected function getNick()
    {
        return NULL;
    }

    // ---------------------------------------

    protected function getPercentsStart()
    {
        return 0;
    }

    protected function getPercentsEnd()
    {
        return 100;
    }

    //########################################

    protected function performActions()
    {
        $result = true;

        $result = !$this->processTask('Defaults') ? false : $result;
        $result = !$this->processTask('Orders') ? false : $result;
        $result = !$this->processTask('OtherListings') ? false : $result;
        $result = !$this->processTask('Templates') ? false : $result;
        $result = !$this->processTask('Marketplaces') ? false : $result;

        return $result;
    }

    //########################################
}