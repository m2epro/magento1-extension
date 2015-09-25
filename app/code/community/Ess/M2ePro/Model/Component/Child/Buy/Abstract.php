<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Component_Child_Buy_Abstract extends Ess_M2ePro_Model_Component_Child_Abstract
{
    // ########################################

    protected function getComponentMode()
    {
        return Ess_M2ePro_Helper_Component_Buy::NICK;
    }

    // ########################################
}