<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

final class Ess_M2ePro_Model_Synchronization_Task_Defaults_Inspector
    extends Ess_M2ePro_Model_Synchronization_Task_Defaults_Abstract
{
    //####################################

    protected function getNick()
    {
        return '/inspector/';
    }

    protected function getTitle()
    {
        return 'Inspector';
    }

    // -----------------------------------

    protected function getPercentsStart()
    {
        return 40;
    }

    protected function getPercentsEnd()
    {
        return 100;
    }

    //####################################

    protected function performActions()
    {
        $result = true;

        $type = $this->getConfigValue($this->getFullSettingsPath().'product_changes/','type');
        $result = !$this->processTask('Inspector_ProductChanges_'.ucfirst($type)) ? false : $result;

        return $result;
    }

    //####################################
}