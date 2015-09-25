<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Config_Primary extends Ess_M2ePro_Model_Config_Abstract
{
    // ########################################

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

    // ########################################
}