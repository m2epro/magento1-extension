<?php

/*
 * @copyright  Copyright (c) 2014 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Buy_Synchronization_Orders_Abstract
    extends Ess_M2ePro_Model_Buy_Synchronization_Abstract
{
    // ##########################################################

    protected function getType()
    {
        return Ess_M2ePro_Model_Synchronization_Task_Abstract::ORDERS;
    }

    protected function processTask($taskPath)
    {
        return parent::processTask('Orders_'.$taskPath);
    }

    // ##########################################################
}