<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

final class Ess_M2ePro_Model_Synchronization_Task_Defaults_Inspector
    extends Ess_M2ePro_Model_Synchronization_Task_Defaults_Abstract
{
    //########################################

    /**
     * @return string
     */
    protected function getNick()
    {
        return '/inspector/';
    }

    /**
     * @return string
     */
    protected function getTitle()
    {
        return 'Inspector';
    }

    // ---------------------------------------

    /**
     * @return int
     */
    protected function getPercentsStart()
    {
        return 60;
    }

    /**
     * @return int
     */
    protected function getPercentsEnd()
    {
        return 100;
    }

    //########################################

    protected function performActions()
    {
        $result = true;

        $type = $this->getConfigValue($this->getFullSettingsPath().'product_changes/','type');
        $result = !$this->processTask('Inspector_ProductChanges_'.ucfirst($type)) ? false : $result;

        return $result;
    }

    //########################################
}