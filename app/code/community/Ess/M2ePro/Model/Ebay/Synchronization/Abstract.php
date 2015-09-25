<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Ebay_Synchronization_Abstract
    extends Ess_M2ePro_Model_Synchronization_Task
{
    //####################################

    protected function getComponent()
    {
        return Ess_M2ePro_Helper_Component_Ebay::NICK;
    }

    //####################################
}