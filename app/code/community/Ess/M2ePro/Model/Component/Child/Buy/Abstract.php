<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Component_Child_Buy_Abstract extends Ess_M2ePro_Model_Component_Child_Abstract
{
    //########################################

    protected function getComponentMode()
    {
        return Ess_M2ePro_Helper_Component_Buy::NICK;
    }

    //########################################
}