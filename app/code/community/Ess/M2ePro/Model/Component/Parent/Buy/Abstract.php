<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Component_Parent_Buy_Abstract extends Ess_M2ePro_Model_Component_Parent_Abstract
{
    //########################################

    public function __construct()
    {
        $args = func_get_args();
        empty($args[0]) && $args[0] = array();
        $params = $args[0];

        $params['child_mode'] = Ess_M2ePro_Helper_Component_Buy::NICK;

        parent::__construct($params);
    }

    //########################################
}