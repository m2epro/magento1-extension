<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Config_Primary extends Ess_M2ePro_Model_Config_Abstract
{
    //########################################

    public function __construct()
    {
        $args = func_get_args();
        empty($args[0]) && $args[0] = array();
        $params = $args[0];

        $params['orm'] = 'M2ePro/Config_Primary';

        parent::__construct($params);
    }

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Config_Primary');
    }

    //########################################
}